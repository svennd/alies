<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class ApiRate_model extends MY_Model
{
	public $table = 'api_rate_limit';
	public $primary_key = 'id';

    public function hit(int $apiKeyId, string $minute, int $limit): bool
    {
        $sql = "
            INSERT INTO {$this->table} (api_key_id, minute, count)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE count = count + 1
        ";

        $this->db->query($sql, [$apiKeyId, $minute]);

        $row = $this->db
            ->select('count')
            ->where('api_key_id', $apiKeyId)
            ->where('minute', $minute)
            ->get($this->table)
            ->row();

        return $row && $row->count <= $limit;
    }

    public function cleanup(int $days = 2): void
    {
        $sql = "
            DELETE FROM api_rate_limit
            WHERE minute < DATE_FORMAT(NOW() - INTERVAL ? DAY, '%Y%m%d%H%i')
        ";

        $this->db->query($sql, [$days]);
    }
}