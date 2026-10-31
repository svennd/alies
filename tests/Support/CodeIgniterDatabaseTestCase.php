<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class CodeIgniterDatabaseTestCase extends TestCase
{
    protected CI_Controller $ci;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ci = get_instance();
        $this->ci->db->trans_strict(false);
        $this->ci->db->trans_begin();

        if (isset($this->ci->logs)) {
            $this->ci->logs->user = (object) ['id' => SYSTEM];
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->ci->db)) {
            // Always roll back, also when a query failed on purpose (trans_status() is then false).
            // Skipping it leaves the transaction open, and every following test runs inside it.
            for ($depth = 0; $depth < 10 && $this->ci->db->trans_rollback(); $depth++) {
            }
            $this->ci->db->reset_query();
        }

        $this->resetPendingModelState();

        parent::tearDown();
    }

    /**
     * Every test shares the model instances on the CodeIgniter object. MY_Model keeps a pending
     * fields() selection when a get_all() finds nothing (see MyModelStaleSelectTest), which would then
     * change the next read of a later test that uses the same alias (for example `events`).
     */
    private function resetPendingModelState(): void
    {
        foreach (get_object_vars($this->ci) as $candidate) {
            if (!$candidate instanceof MY_Model) {
                continue;
            }
            Closure::bind(function (): void {
                $this->_select = '*';
                if (property_exists($this, '_requested')) {
                    $this->_requested = array();
                }
            }, $candidate, MY_Model::class)();
        }
    }

    protected function model(string $model, string $alias)
    {
        if (!isset($this->ci->{$alias})) {
            $this->ci->load->model($model, $alias);
        }

        return $this->ci->{$alias};
    }

    protected function existingId(string $table, string $column = 'id'): int
    {
        $row = $this->ci->db
            ->select($column)
            ->order_by($column, 'ASC')
            ->limit(1)
            ->get($table)
            ->row_array();

        $this->assertNotNull($row, 'Missing fixture row in table: ' . $table);

        return (int) $row[$column];
    }

    protected function uniqueString(string $prefix): string
    {
        return sprintf(
            '%s_%s_%04d',
            $prefix,
            gmdate('YmdHis'),
            random_int(1000, 9999)
        );
    }
}
