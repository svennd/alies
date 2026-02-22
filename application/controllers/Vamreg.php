<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Vamreg
class Vamreg extends Admin_Controller
{
    public $key = null;

    const VAMREG_NOTHING    = -1;
    const VAMREG_OK         = 200;
    const VAMREG_ERR_VALID  = 400; # validation error
    const VAMREG_ERR_AUTH   = 403; # authentication error
    const VAMREG_ERR_API    = 500; # generic server error

	public function __construct()
	{
		parent::__construct();
        
		# models
		$this->load->model('Products_model', 'products');
        $this->load->model('Vamreg_index_model', 'vamreg_index');
        $this->load->model('Vamreg_in_buffer_model', 'vamreg_in');
        $this->load->model('Vamreg_out_buffer_model', 'vamreg_out');

        # helpers
        $this->load->helper('quarter');
        $this->load->helper('cnk');
        $this->load->helper('vamreg');

        $this->key = base64_decode($this->conf['vamreg_api_key']['value']) ?? null;
        if (!$this->key) {
            log_message('error', 'VAMREG API key missing');
        }
	}

	# debug
    public function reset()
    {
        $this->load->library('Vamregclient', [
            'apiKey' => $this->key,
            'prod'   => $this->conf['vamreg_prod'] ?? false,
        ]);
        
        $declarations = $this->vamreg_in
            ->where('status', ['DRAFT', 'SENT'])
            ->get_all();

        foreach ($declarations as $decl) {
            if (!empty($decl['api_declaration_id'])) {
                echo "Deleting declaration ID {$decl['api_declaration_id']} from VAMREG...\n";

                $res = $this->vamregclient->delete($decl['api_declaration_id']);

                if ($res['http_code'] !== 200) 
                {
                    $this->vamreg_in
                        ->where('id', $decl['id'])
                        ->update([
                            'status'                => 'DRAFT',
                            'api_declaration_id'    => null,
                            'sent_at'               => null
                        ]);
                }
                else
                {
                    echo "Failed to delete declaration ID {$decl['api_declaration_id']} from VAMREG. HTTP code: {$res['http_code']}\n";
                }
            }
        }
    }

	# in by default
    public function index($year = null, $quarter = null, $status = null)
    {
		// get quarter context
		extract($this->quarterContext($year, $quarter));

		$data = array(
						'in_buffer' => 
                        $this->vamreg_in
                            ->fields('id, cnk, in_quantity_pack_count, delivery, status')
                            ->with_vamreg_index('fields: ppnNL, packSize, maName, maNumber')
                            ->with_wholesale('fields: description')
                            ->where('delivery >=', $startDate)
                            ->where('delivery <=', $endDate)
                            ->get_all(),
                        'year'       => $year,
                        'quarter'    => $quarter,
                        'prevY'      => $prevY,
                        'prevQ'      => $prevQ,
                        'nextY'      => $nextY,
                        'nextQ'      => $nextQ,
                        'isCurrentQuarter' => $isCurrentQuarter,
                        'status'     => $status 
					);
					
		$this->_render_page('admin/vamreg/in', $data);
    }
	
	# out
	public function out($year = null, $quarter = null, $status = null)
    {
		// get quarter context
		extract($this->quarterContext($year, $quarter));

		$data = array(
						'out_buffer'	=> $this->vamreg_out->summary($startDate, $endDate),
                        'year'       	=> $year,
                        'quarter'    	=> $quarter,
                        'prevY'      	=> $prevY,
                        'prevQ'      	=> $prevQ,
                        'nextY'      	=> $nextY,
                        'nextQ'      	=> $nextQ,
                        'isCurrentQuarter' => $isCurrentQuarter,
                        'status'     	=> $status 
					);
					
		$this->_render_page('admin/vamreg/out', $data);
    }

	# get the detailed list for a single cnk product
	public function out_detail(string $cnk, $year = null, $quarter = null)
	{
		// get quarter context
		extract($this->quarterContext($year, $quarter));
		
		$data = array(
			'CNK'           => $cnk,
			'entries' 		=> $this->vamreg_out->get_by_cnk($cnk, $startDate, $endDate),
			'year'       	=> $year,
			'quarter'    	=> $quarter,
			'prevY'      	=> $prevY,
			'prevQ'      	=> $prevQ,
			'nextY'      	=> $nextY,
			'nextQ'      	=> $nextQ,
			'isCurrentQuarter' => $isCurrentQuarter
		);
		$this->_render_page('admin/vamreg/out_detail', $data);
	}
	
	public function product_list()
	{
		$data = array(
						'products' => $this->vamreg_index->get_linked()
					);
					
		$this->_render_page('admin/vamreg/product_list', $data);
	}

    public function post_all($year = null, $quarter = null)
    {
        $this->load->library('Vamregclient', [
            'apiKey' => $this->key,
            'prod'   => $this->conf['vamreg_prod'] ?? false,
        ]);

        $year    = $year ?? date('Y');
        $quarter = $quarter ?? quarter_from_date();

        [$startDate, $endDate] = quarter_start_end($year, $quarter);

        $drafts = $this->vamreg_in
            ->where(['status' => 'DRAFT'])
            ->where('delivery >=', $startDate)
            ->where('delivery <=', $endDate)
            ->get_all();

        if (!$drafts) {
            redirect("vamreg/index/$year/$quarter/". self::VAMREG_NOTHING);
            return;
        }

        $declarations = [];
        foreach ($drafts as $row) {
            $declarations[] = [
                'internal_id'       => $row['id'],
                'register'          => 'IN',
                'dateTime'          => (new DateTime($row['delivery']))->format('Y-m-d\TH:i:s.000\Z'),// note 'c' does not work (also p fails)
                'productType'       => $row['product_type'],
                'providerType'      => $row['provider_type'],
                'cnk'               => $row['cnk'],
                'inQuantityPackCount' => (int)$row['in_quantity_pack_count']
            ];
        }

        # upload the results
        $result = $this->vamregclient->uploadBulk($declarations);

        # handle the result
        # this will redirect to index
        $this->handleVamregResult($result, $drafts, $declarations, $year, $quarter);
    }

	/*
	* helper to get quarter context (start/end dates, prev/next quarter info)
	*/
	private function quarterContext(?int $year, ?int $quarter): array
	{
		$year    ??= date('Y');
		$quarter ??= quarter_from_date();

		[$startDate, $endDate] = quarter_start_end($year, $quarter);
		[$prevY, $prevQ]       = quarter_prev($year, $quarter);
		[$nextY, $nextQ]       = quarter_next($year, $quarter);

		return [
			'year'             => $year,
			'quarter'          => $quarter,
			'startDate'        => $startDate,
			'endDate'          => $endDate,
			'prevY'            => $prevY,
			'prevQ'            => $prevQ,
			'nextY'            => $nextY,
			'nextQ'            => $nextQ,
			'isCurrentQuarter' => is_current_quarter($year, $quarter),
		];
	}

    /*
    * handle Vamreg result
    */
    protected function handleVamregResult(array $result, array $drafts, array $declarations, int $year, int $quarter)
    {
        switch ($result['http_code']) {

            case 200:
                foreach ($drafts as $i => $row) {
                    $this->vamreg_in->update([
                        'status'             => 'SENT',
                        'api_declaration_id' => $result['response'][$i]['id'] ?? null,
                        'sent_at'            => date('Y-m-d H:i:s')
                    ], $row['id']);
                }
                redirect("vamreg/index/$year/$quarter/". self::VAMREG_OK);
                return;

            case 400:
                $badIdx = [];
                foreach ($result['response'] as $err) {
                    if (preg_match('/^declarations\[(\d+)\]\./', $err['field'] ?? '', $m)) {
                        $badIdx[(int)$m[1]] = true;
                    }
                }

                foreach (array_keys($badIdx) as $idx) {
                    if (!isset($declarations[$idx])) continue;
                    $this->vamreg_in->update([
                        'status'  => 'ERROR',
                        'sent_at' => date('Y-m-d H:i:s'),
                    ], $declarations[$idx]['internal_id']);
                }

                redirect("vamreg/index/$year/$quarter/". self::VAMREG_ERR_VALID);
                return;

            case 403:
                log_message('error', 'VAMREG auth failure');
                redirect("vamreg/index/$year/$quarter/". self::VAMREG_ERR_AUTH);
                return;

            default:
                log_message('error', 'VAMREG API error: ' . $result['http_code']);
                redirect("vamreg/index/$year/$quarter/". self::VAMREG_ERR_API);
                return;
        }
    }

    public function edit(int $id, $year = null, $quarter = null)
    {
        $data = [
            'entry' => $this->vamreg_in->with_wholesale('fields:description')->get($id),
            'year'  => $year,
            'quarter' => $quarter
        ];

        $this->_render_page('admin/vamreg/edit', $data);
    }

    public function save(int $id, $year = null, $quarter = null)
    {
        $new_volume = $this->input->post('new_volume');

        if ($new_volume !== null && is_numeric($new_volume) && (int)$new_volume > 0) {
            $this->vamreg_in->update([
                'in_quantity_pack_count' => (int)$new_volume
            ], $id);
        }

        redirect("vamreg/index/$year/$quarter");
    }

    public function edit_out(int $id, $year = null, $quarter = null)
    {
        $data = [
            'entry' => $this->vamreg_out->get_simplified($id),
            'year'  => $year,
            'quarter' => $quarter
        ];

        $this->_render_page('admin/vamreg/edit_out', $data);
    }

    public function save_out(int $id, $year = null, $quarter = null)
    {
        $new_volume = (float)$this->input->post('new_volume');
        $ab_unit 	= $this->input->post('ab_unit');
		$cnk 		= $this->input->post('cnk');

		$this->vamreg_out->update(
			array_merge(get_vamreg_out_unit($ab_unit, $new_volume),
				array(
					"out_date" => $this->input->post('out_date')
				)), $id);

        redirect("vamreg/out_detail/$cnk/$year/$quarter");
    }

    public function lock(int $id)
    {
        $this->vamreg_in->update(['status' => 'INVALID'], $id);
        redirect('vamreg');
    }

    public function unlock(int $id)
    {
        $this->vamreg_in->update(['status' => 'DRAFT'], $id);
        redirect('vamreg');
    }

	public function remove(int $id, string $cnk, $year = null, $quarter = null)
	{
        $this->vamreg_out->update(['status' => 'INVALID'], $id);
		redirect("vamreg/out_detail/$cnk/$year/$quarter");
	}
	public function restore(int $id, string $cnk, $year = null, $quarter = null)
	{
        $this->vamreg_out->update(['status' => 'DRAFT'], $id);
		redirect("vamreg/out_detail/$cnk/$year/$quarter");
	}
	
	public function refresh()
	{
		$this->load->library('vamreg_sync');
		$sync_status = $this->vamreg_sync->sync_medicinal_products(
			base64_decode($this->conf['vamreg_api_key']['value']),
			base64_decode($this->conf['vamreg_push']['value'])
		);
		$this->logs->logger(INFO, "vamreg", "user synchronized medicinal products from Vamreg : " . ($sync_status ? "success" : "failure"));

		  echo ($sync_status ? json_encode(['status' => 'success']) : json_encode(['status' => 'failure']));
	}
}
