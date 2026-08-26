<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Class: Upgrade
class Upgrade extends Frontend_Controller 
{
    public function __construct() {
        parent::__construct();

		# only accept cli here
		if (!is_cli()) { show_error('Direct access is not allowed'); }
    }

	/*
	* function: up
	* migrate to latest or a specific version
	*/
	public function up($version = null)
	{
		$this->load->library('migration');

		$migrations = $this->migration->find_migrations();
		if (empty($migrations))
		{
			echo "No migrations found.\n";
			return false;
		}

		$current = $this->current_migration_version();

		if ($version === null || $version === '')
		{
			$target = $this->latest_migration_version($migrations);
			$result = $this->migration->latest();
		}
		else
		{
			$target = $this->normalize_migration_version($version);
			if ($target === false)
			{
				echo "Invalid version: expected a non-negative integer.\n";
				return false;
			}

			if ($target < $current)
			{
				echo "Refusing to migrate down via up(). Current version is " . sprintf('%03d', $current) . ". Use down() instead.\n";
				return false;
			}

			if (!$this->migration_version_exists($target, $migrations))
			{
				echo "Migration version " . sprintf('%03d', $target) . " does not exist.\n";
				return false;
			}

			$result = $this->migration->version($target);
		}

		if ($result === false)
		{
			echo "Migration failed: " . $this->migration->error_string() . "\n";
			return false;
		}

		if ($current === $target)
		{
			echo "Already at migration version " . sprintf('%03d', $current) . ".\n";
			return true;
		}

		echo "Migrated up from " . sprintf('%03d', $current) . " to " . sprintf('%03d', (int) $result) . ".\n";
		return true;
	}

	/*
	* function: down
	* migrate down one version or to a specific lower version
	*/
	public function down($version = null)
	{
		$this->load->library('migration');

		$migrations = $this->migration->find_migrations();
		$current = $this->current_migration_version();

		if ($current <= 0)
		{
			echo "Already at migration version 000.\n";
			return true;
		}

		if ($version === null || $version === '')
		{
			$target = max(0, $current - 1);
		}
		else
		{
			$target = $this->normalize_migration_version($version);
			if ($target === false)
			{
				echo "Invalid version: expected a non-negative integer.\n";
				return false;
			}

			if ($target > $current)
			{
				echo "Refusing to migrate up via down(). Current version is " . sprintf('%03d', $current) . ". Use up() instead.\n";
				return false;
			}

			if ($target > 0 && !$this->migration_version_exists($target, $migrations))
			{
				echo "Migration version " . sprintf('%03d', $target) . " does not exist.\n";
				return false;
			}
		}

		if ($target === $current)
		{
			echo "Already at migration version " . sprintf('%03d', $current) . ".\n";
			return true;
		}

		$result = $this->migration->version($target);
		if ($result === false)
		{
			echo "Migration failed: " . $this->migration->error_string() . "\n";
			return false;
		}

		echo "Migrated down from " . sprintf('%03d', $current) . " to " . sprintf('%03d', (int) $result) . ".\n";
		return true;
	}

	/*
	* function: retry_medilab_codes
	* rerun the idempotent data backfill from migration 049 without changing
	* the database migration version
	*/
	public function retry_medilab_codes()
	{
		$current = $this->current_migration_version();
		if ($current < 49)
		{
			echo "Migration 049 has not been applied yet. Use upgrade up instead.\n";
			return false;
		}

		$this->load->library('migration');
		require_once APPPATH . 'migrations/049_medilab_result_code_resolve.php';

		$retry = new Migration_medilab_result_code_resolve();
		$result = $retry->up();
		$stats = $retry->get_last_run_stats();

		if ($result !== '049')
		{
			echo "Medilab code retry failed. Check the application log.\n";
			return false;
		}

		$version_after = $this->current_migration_version();
		if ($version_after !== $current)
		{
			echo "Medilab code retry unexpectedly changed the migration version from "
				. sprintf('%03d', $current) . " to " . sprintf('%03d', $version_after) . ".\n";
			return false;
		}

		if ($stats['skipped_reason'] !== null)
		{
			echo "Medilab code retry skipped: " . $stats['skipped_reason'] . ".\n";
			return false;
		}

		echo "Medilab code retry completed: eligible=" . (int) $stats['eligible']
			. ", updated=" . (int) $stats['updated']
			. ", unresolved=" . (int) $stats['unresolved']
			. ", migration_version=" . sprintf('%03d', $version_after) . ".\n";
		return true;
	}

	/*
	* function: current_migration_version
	* read current database migration version
	*/
	private function current_migration_version()
	{
		$row = $this->db->select('version')->get('migrations')->row();
		return $row ? (int) $row->version : 0;
	}

	/*
	* function: latest_migration_version
	* get highest migration version on disk
	*/
	private function latest_migration_version(array $migrations)
	{
		$versions = array_map('intval', array_keys($migrations));
		return empty($versions) ? 0 : max($versions);
	}

	/*
	* function: normalize_migration_version
	* validate cli version input
	*/
	private function normalize_migration_version($version)
	{
		if (!is_scalar($version))
		{
			return false;
		}

		$version = trim((string) $version);
		if ($version === '' || !ctype_digit($version))
		{
			return false;
		}

		return (int) $version;
	}

	/*
	* function: migration_version_exists
	* verify target version exists on disk
	*/
	private function migration_version_exists($version, array $migrations)
	{
		return in_array((int) $version, array_map('intval', array_keys($migrations)), true);
	}
}
