<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Vamreg_out_buffer_model extends MY_Model
{
	public $table = 'vamreg_out_buffer';
	public $primary_key = 'id';
	
	public function __construct()
	{
	    parent::__construct();
	}

	public function summary(string $startDate, string $endDate)
	{
		# COALESCE -> returns the first true value (for sum)
		return $this->db
			->select('
				MIN(status) AS status,
				vamreg_out_buffer.cnk,
				SUM(COALESCE(out_quantity_pack_count, out_quantity_unit_count)) AS total_quantity,
				MAX(CASE 
					WHEN COALESCE(out_quantity_pack_count, out_quantity_unit_count) < 0 
					THEN 1 ELSE 0 
					END) AS has_negative,
				wholesale.description AS wholesale_description,
				out_quantity_type, out_quantity_unit
			')
			->from('vamreg_out_buffer')
			->where('out_date >=', $startDate)
			->where('out_date <=', $endDate)
			->group_by('cnk')
			->join('wholesale', 'wholesale.cnk = vamreg_out_buffer.cnk', 'left')
			->get()
			->result_array();
	}

	public function get_by_cnk(string $cnk, string $startDate, string $endDate)
	{
		return $this->db
			->select('
			out.id, out.status as status, out.cnk as cnk, out.event, out.out_date, out.target_species, out.indication,
			COALESCE(out_quantity_pack_count, out_quantity_unit_count) AS total_quantity,
			COALESCE(out_quantity_unit, out_quantity_type) AS unit,
			v.first_name as vet_name,
			p.name as product_name,
			pt.name as pet_name,
			o.last_name as owner
			')
			->from('vamreg_out_buffer out')
			->join('users v', 'v.id = out.vet', 'left')
			->join('products p', 'p.cnk = out.cnk', 'left')
			->join('events e', 'e.id = out.event', 'left')
			->join('pets pt', 'pt.id = e.pet', 'left')
			->join('owners o', 'o.id = pt.owner', 'left')
			->where('out.cnk', $cnk)
			->where('out_date >=', $startDate)
			->where('out_date <=', $endDate)
			->get()
			->result_array();
	}

	public function get_simplified(int $id)
	{
		return $this->db
			->select('		id, 
							cnk, 
							COALESCE(out_quantity_pack_count, out_quantity_unit_count) AS total_quantity,
							COALESCE(out_quantity_unit, out_quantity_type) AS unit,
							out_quantity_pack_count, out_quantity_unit_count,
							out_quantity_unit, out_quantity_type,
							out_date
						')
			->from('vamreg_out_buffer')
			->where('id', $id)
			->get()
			->row_array();
	}
}

/*
generate demo data : 
truncate vamreg_out_buffer;
INSERT INTO vamreg_out_buffer (
  id,
  event,
  event_line,
  cnk,
  out_quantity_type,
  out_quantity_pack_count,
  out_quantity_unit_count,
  out_quantity_unit,
  out_date,
  product_type,
  target_species,
  indication,
  vet,
  status,
  created_at
)
SELECT
  ROW_NUMBER() OVER ()                                            AS id,
  ep.event_id                                                    AS event,
  ep.id                                                          AS event_line,
  p.cnk                                                          AS cnk,
  CASE
    WHEN COALESCE(p.ab_unit,'ML') = 'PACKS' THEN 'PACKS'
    ELSE 'UNITS'
  END                                                            AS out_quantity_type,
  CASE
    WHEN COALESCE(p.ab_unit,'ML') = 'PACKS' THEN ep.volume
    ELSE NULL
  END                                                            AS out_quantity_pack_count,
  CASE
    WHEN COALESCE(p.ab_unit,'ML') <> 'PACKS' THEN ep.volume
    ELSE NULL
  END                                                            AS out_quantity_unit_count,
  CASE
    WHEN COALESCE(p.ab_unit,'ML') IN ('G','ML','PIECE','TUBE','PRESTATION')
      THEN COALESCE(p.ab_unit,'ML')
    ELSE 'ML'
  END                                                            AS out_quantity_unit,
  DATE(ep.created_at)                                            AS out_date,
  'BE'                                                           AS product_type,
  'DOG'                                                          AS target_species,
  NULLIF(p.default_indication,'NONE')                            AS indication,
  4                                                             AS vet,
  'DRAFT'                                                        AS status,
  NOW()                                                          AS created_at
FROM events_products ep
JOIN products p ON p.id = ep.product_id
WHERE p.is_antibiotic = 1
  AND ep.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH);

*/