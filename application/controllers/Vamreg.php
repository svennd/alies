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

	const VAMREG_IN 		= true;
	const VAMREG_OUT 		= false;

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
		echo "IN: <br><br>";
        $this->load->library('Vamregclient', [
            'apiKey' => $this->key,
            'prod'   => $this->conf['vamreg_prod'] ?? false,
        ]);
        
        $declarations = $this->vamreg_in
            ->where('status', ['DRAFT', 'SENT', 'ERROR'])
            ->get_all();

        foreach ($declarations as $decl) {
            if (!empty($decl['api_declaration_id'])) {
                echo "Deleting declaration ID {$decl['api_declaration_id']} from VAMREG...<br>\n";

                $res = $this->vamregclient->delete($decl['api_declaration_id']);

                if ($res['http_code'] !== "200") 
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
                    echo "Failed to delete declaration ID {$decl['api_declaration_id']} from VAMREG. HTTP code: {$res['http_code']}<br>\n";
                }
            }
        }

		echo "OUT: <br><br>";
        $declarations = $this->vamreg_out
            ->where('status', ['DRAFT', 'SENT', 'ERROR'])
            ->get_all();
		
		foreach ($declarations as $decl) {
            if (!empty($decl['api_declaration_id'])) {
                echo "Deleting declaration ID {$decl['api_declaration_id']} from VAMREG...<br>\n";

                $res = $this->vamregclient->delete($decl['api_declaration_id']);

                if ($res['http_code'] !== 200) 
                {
                    $this->vamreg_out
                        ->where('id', $decl['id'])
                        ->update([
                            'status'                => 'DRAFT',
                            'api_declaration_id'    => null,
                            'sent_at'               => null
                        ]);
                }
                else
                {
                    echo "Failed to delete declaration ID {$decl['api_declaration_id']} from VAMREG. HTTP code: {$res['http_code']}<br>\n";
                }
            }
        }

		# set error to draft for all remaining
		$this->vamreg_in->where('status', 'ERROR')->update(['status' => 'DRAFT']);
		$this->vamreg_out->where('status', 'ERROR')->update(['status' => 'DRAFT']);
    }

	# sending page
	public function index($year = null, $quarter = null, $status = null)
    {
		// get quarter context
		extract($this->quarterContext($year, $quarter));

		$inAgg = $this->vamreg_in->send_draft_aggregate($startDate, $endDate);
		$outAgg = $this->vamreg_out->send_draft_aggregate($startDate, $endDate);
		$products = $this->vamreg_index->get_linked_stats();
		$deadline = $this->buildSendDeadlineData($endDate);

		$inAgg = is_array($inAgg) ? $inAgg : ['draft_rows' => 0];
		$outAgg = is_array($outAgg) ? $outAgg : ['draft_rows' => 0];

		$data = array(
                        'year'       	=> $year,
                        'quarter'    	=> $quarter,
                        'prevY'      	=> $prevY,
                        'prevQ'      	=> $prevQ,
                        'nextY'      	=> $nextY,
                        'nextQ'      	=> $nextQ,
                        'isCurrentQuarter' => $isCurrentQuarter,
                        'status'     	=> $status,
						'inAgg'         => $inAgg,
						'outAgg'        => $outAgg,
						'products'      => $products,
						'deadline'      => $deadline,
						'startDate'     => $startDate,
						'endDate'       => $endDate
					);
					
		$this->_render_page('admin/vamreg/send', $data);
    }
	
	# in by default
    public function in($year = null, $quarter = null, $status = null)
    {
		// get quarter context
		extract($this->quarterContext($year, $quarter));

		$data = array(
						'in_buffer' => 
                        $this->vamreg_in
                            ->fields('id, cnk, in_quantity_pack_count, delivery, status, api_error')
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

	private function buildSendDeadlineData(string $quarterEndDate): array
	{
		$quarterEnd = (new DateTimeImmutable($quarterEndDate))->setTime(23, 59, 59);
		$autoSendMonths = $this->resolveAutoSendMonths();
		$autoSendDate = $quarterEnd->modify('+' . $autoSendMonths . ' month')->setTime(2, 0, 0);
		$editWindowClose = $quarterEnd->modify('+1 month +14 days')->setTime(23, 59, 59);
		$now = new DateTimeImmutable('now');

		$state = 'before_quarter_close';
		$statusLabel = 'Quarter open';
		if ($now > $quarterEnd && $now < $autoSendDate) {
			$state = 'edit_window';
			$statusLabel = 'Edit window';
		} elseif ($now >= $autoSendDate && $now <= $editWindowClose) {
			$state = 'autosend_due';
			$statusLabel = 'Auto-send due';
		} elseif ($now > $editWindowClose) {
			$state = 'closed_window';
			$statusLabel = 'Edit window closed';
		}

		$daysToAutoSend = (int)$now->diff($autoSendDate)->format('%r%a');
		$daysToEditWindowClose = (int)$now->diff($editWindowClose)->format('%r%a');

		return [
			'quarter_end' => $quarterEnd->format('Y-m-d'),
			'auto_send_at' => $autoSendDate->format('Y-m-d H:i'),
			'edit_window_closes_at' => $editWindowClose->format('Y-m-d H:i'),
			'auto_send_months' => $autoSendMonths,
			'days_to_auto_send' => $daysToAutoSend,
			'days_to_edit_window_close' => $daysToEditWindowClose,
			'state' => $state,
			'status_label' => $statusLabel,
		];
	}

	private function resolveAutoSendMonths(): int
	{
		$fallback = 1;
		$raw = null;

		if (isset($this->conf['vamreg_auto_send_months'])) {
			$raw = $this->conf['vamreg_auto_send_months'];
		}

		if (is_array($raw) && isset($raw['value'])) {
			$decoded = base64_decode((string)$raw['value'], true);
			$raw = ($decoded !== false) ? $decoded : $raw['value'];
		}

		$months = (int)$raw;
		return ($months >= 1 && $months <= 6) ? $months : $fallback;
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

    public function post_all($year = null, $quarter = null, string $model = "IN")
    {
        $this->load->library('Vamregclient', [
            'apiKey' => $this->key,
            'prod'   => $this->conf['vamreg_prod'] ?? false,
        ]);

        $year    	= $year ?? date('Y');
        $quarter 	= $quarter ?? quarter_from_date();
		$repo 		= ($model === "IN") ? $this->vamreg_in : $this->vamreg_out;

        [$startDate, $endDate] = quarter_start_end($year, $quarter);

		$drafts = $repo->get_all_drafts_by_date($startDate, $endDate);

		if (!$drafts) {
			$this->respondWithStatus(self::VAMREG_NOTHING);
			return;
		}

		$declarations = array_map(
			fn($row) => $model === "IN"
				? $this->buildInDeclaration($row)
				: $this->buildOutDeclaration($row),
			$drafts
		);

        # upload the results
        $result = $this->vamregclient->uploadBulk($declarations);

        # handle the result
        # this will redirect to index
        $this->handleVamregResult(
            $result,
            $declarations,
            (int)$year,
            (int)$quarter,
            $repo
        );
    }

	public function post_all_out($year = null, $quarter = null)
	{
		return $this->post_all($year, $quarter, "OUT");
	}

	private function buildInDeclaration(array $row): array
	{
		return [
			'internal_id'        => $row['id'],
			'register'           => 'IN',
			'dateTime'           => (new DateTime($row['delivery']))->format('Y-m-d\TH:i:s.000\Z'),
			'productType'        => $row['product_type'],
			'providerType'       => $row['provider_type'],
			'cnk'                => $row['cnk'],
			'inQuantityPackCount'=> (int)$row['in_quantity_pack_count'],
		];
	}

	private function buildOutDeclaration(array $row): array
	{
		return [
			'internal_id'              => $row['id'],
			'register'                 => 'OUT',
			'dateTime'                 => (new DateTime($row['out_date']))->format('Y-m-d\TH:i:s.000\Z'),
			'productType'              => $row['product_type'],
			'targetSpecies'            => $row['target_species'],
			'indication'               => $row['indication'] ?? null,
			'cnk'                      => $row['cnk'],
			'veterinarianOrderNumber'  => $row['ordernr'],
			'outQuantityType'          => $row['out_quantity_type'],
			'outQuantityPackCount'     => $row['out_quantity_pack_count'] !== null ? (float)$row['out_quantity_pack_count'] : null,
			'outQuantityUnitCount'     => $row['out_quantity_unit_count'] !== null ? (float)$row['out_quantity_unit_count'] : null,
			'outQuantityUnit'          => $row['out_quantity_unit'],
		];
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
    protected function handleVamregResult(array $result, array $declarations, int $year, int $quarter, $model)
    {
        switch ($result['http_code']) {

            case 200:
                foreach ($declarations as $i => $row) {
                    $model->update([
                        'status'             => 'SENT',
                        'api_declaration_id' => $result['response'][$i]['id'] ?? null,
                        'sent_at'            => date('Y-m-d H:i:s')
                    ], $row['internal_id']);
                }

				$this->respondWithStatus(self::VAMREG_OK);
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
					
                    $model->update([
                        'status'  	=> 'ERROR',
						'api_error' => $this->get_vamreg_error_message($result['response'][$idx]),
                        'sent_at' 	=> date('Y-m-d H:i:s'),
                    ], $declarations[$idx]['internal_id']);
                }

				$this->respondWithStatus(self::VAMREG_ERR_VALID);
                return;

            case 403:
				$this->respondWithStatus(self::VAMREG_ERR_AUTH);
                return;

            default:
				$this->respondWithStatus(self::VAMREG_ERR_API);
                return;
        }
    }

	private function respondWithStatus(int $status): void
	{
		$this->output->set_content_type('application/json');
		$statusHtml = $this->load->view('admin/vamreg/blocks/vamreg_status', ['status' => $status], true);
		$this->output->set_output(json_encode([
			'status' => $status,
			'status_html' => $statusHtml,
		]));
	}

	private function get_vamreg_error_message(array $error): string
	{
		if (isset($error['message'])) {
			return $error['message'];
		} elseif (isset($error['field']) && isset($error['englishMessage'])) {
			return "Error in field '{$error['field']}': {$error['englishMessage']}";
		} else {
			return 'Unknown error';
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
