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
        if (isset($this->ci->db) && $this->ci->db->trans_status() !== false) {
            $this->ci->db->trans_rollback();
            $this->ci->db->reset_query();
        }

        parent::tearDown();
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
