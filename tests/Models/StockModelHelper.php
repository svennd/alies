<?php

declare(strict_types=1);

/**
 * Shared setup for the stock / products model tests (slot of T200-T209).
 * Models are loaded under the aliases the application uses ($this->stock is used inside Stock_model).
 */
trait StockModelHelper
{
    protected $stock;

    protected function setUpStock(): void
    {
        Factory::useMysqli($this->ci->db->conn_id);
        $this->stock = $this->model('Stock_model', 'stock');
    }

    /** @return array<string,mixed>|null */
    protected function stockRow(int $id): ?array
    {
        return $this->ci->db->where('id', $id)->get('stock')->row_array();
    }

    /** @return array<int,array<string,mixed>> */
    protected function stockRowsOf(int $productId): array
    {
        return $this->ci->db->where('product_id', $productId)->order_by('id', 'ASC')->get('stock')->result_array();
    }

    /** @return array<int,array<string,mixed>> */
    protected function stockLog(int $productId, ?string $event = null): array
    {
        $this->ci->db->where('product', $productId);
        if ($event !== null) {
            $this->ci->db->where('event', $event);
        }

        return $this->ci->db->order_by('id', 'ASC')->get('log_stock')->result_array();
    }

    /** @return array<int,array<string,mixed>> */
    protected function logRows(string $event): array
    {
        return $this->ci->db->where('event', $event)->order_by('id', 'ASC')->get('log')->result_array();
    }
}
