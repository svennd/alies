<?php 

# pulls new list of medical products

class Vamreg_sync
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

	public function sync_medicinal_products(string $apiKey, string $prod)
	{
		if (empty($apiKey) || !isset($prod)) {
			return false;
		}

		$this->CI->load->library('vamregclient', [
			'apiKey' => $apiKey,
			'prod'   => $prod,
		]);

        $res = $this->CI->vamregclient->get('/medicinal-product');

        if ($res['http_code'] !== 200 || !is_array($res['response'])) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($res['response'] as $p) {
            $row = [
                'cti'        => (string)$p['cti'],
                'cnk'        => (string)$p['cnk'],
                'ppnNl'      => $p['ppnNl'] ?? '',
                'packSize'   => $p['packSize'] ?? null,
                'susage'     => $p['usage'],
                'maName'     => $p['maName'] ?? null,
                'maNumber'   => $p['maNumber'] ?? null,
                'mahName'    => $p['mahName'] ?? null,
                'updated_at' => $now,
            ];

            $this->CI->db->where('cti', $row['cti'])->update('vamreg_index', $row);

            if ($this->CI->db->affected_rows() === 0) {
                $this->CI->db->insert('vamreg_index', $row);
            }
        }

        return true;
    }
}
