<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Vamreg_index_model extends MY_Model
{
	public $table = 'vamreg_index';
	public $primary_key = 'id';
	
	public function __construct()
	{
	    parent::__construct();
	}

    /*
    * Set is_antibiotic flag on products based on Vamreg_index cnk values
    */
    public function set_ab_status_on_product()
    {
		# reset : update products set `is_antibiotic` =0, `ab_unit` = null, `ab_unit_volume` = null;
        $sql = "
			UPDATE products p
			JOIN vamreg_index v ON v.cnk = p.cnk
			SET 
				p.is_antibiotic = 1,
				p.default_indication = IF(p.default_indication IS NULL, 'NONE', p.default_indication),

				p.ab_unit_volume = 
					IF(
					p.buy_volume != 1,
						IFNULL(v.packSize / NULLIF(p.buy_volume, 0), 1),
						1
					),

				p.ab_unit =
					IF(
						p.unit_sell = 'ml',
						'ML',
						IF(
							IF(
								p.buy_volume != 1,
									IFNULL(v.packSize / NULLIF(p.buy_volume, 0), 1),
									1
									) = 1,
							'PACKS',
							'PIECE'
						)
					)
			WHERE p.is_antibiotic = 0
			;
		";
        $this->db->query($sql);
        
        return $this->db->affected_rows();
    }


	public function get_linked()
	{
		$this->db->select('
			v.cnk, v.cti, v.ppnNl, v.packSize, v.maName, v.maNumber, v.mahName, 
			p.name as product_name, p.ab_unit, p.ab_unit_volume, p.id as product_id, p.buy_volume, p.unit_buy, p.sell_volume, p.unit_sell, p.is_antibiotic
			');
		$this->db->from($this->table . ' v');
		$this->db->order_by('v.ppnNl', 'ASC');
		# join products
		$this->db->join('products p', 'p.cnk = v.cnk', 'left');
		return $this->db->get()->result_array();
	}

	public function get_linked_stats()
	{
		$this->db->select('
			COUNT(v.cnk) as total_products,
			SUM(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END) as linked_products
		', false);

		$this->db->from($this->table . ' v');
		$this->db->join('products p', 'p.cnk = v.cnk', 'left');

		return $this->db->get()->row_array();
	}
}
