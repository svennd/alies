<?php

// application/libraries/LabResultService.php
class LabResultService {

    protected $CI;
    public $canonical_map;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('LabResult_model');
        $this->CI->load->model('LabReport_model');
        $this->CI->load->model('LabPlots_model', 'lab_plots');
        $this->CI->load->model('LabReportPending_model');
        $this->CI->load->model('Pets_model');

        # load canonicals
        $this->CI->config->load('lab/canonical', true);
        $this->canonical_map = $this->CI->config->item('canonical','lab/canonical');
    }

    public function ingest(DeviceAdapterInterface $adapter, array $payload)
    {
        // parse to standard format
        $data = $adapter->parse($payload);

        // log_message('error', 'LabResultService ingest data: '.json_encode($data));

        // resolve pet_id
        $pet_id = $this->resolvePetId($data);

        // pet does not exist, save to pending
        if ($pet_id === null)
        {
            $this->CI->LabReportPending_model->create([
                'device'        => $data['device'],
                'raw_payload'   => json_encode($payload),
                'identifiers'   => json_encode([
                    'pet_id'     => $data['pet_id'] ?? null,
                    'owner_name' => $data['owner_name'] ?? null,
                    'pet_name'   => $data['pet_name'] ?? null,
                    'chip'       => $data['chip'] ?? null,
                    'phone'      => $data['phone'] ?? null
                ]),
                'reason'      => 'pet_not_found',
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            return ['status' => 'pending'];
        }

        // store report
        $report_id = $this->store_report($pet_id, $data);
        
        // store every result
        foreach ($data['results'] as $r) {
            $r = $this->normalizeResultValue($r);
            $r = $this->canonical($r);
            $this->CI->LabResult_model->save($report_id, $r);
        }

        // store plots
        if (!empty($data['plots'])) {
            $this->store_plots($report_id, $data['plots']);
        }

        return ['status' => 'ok'];
    }

    # store the lab report, return report ID
    private function store_report(int $pet_id, array $data): int
    {
         $existing = $this->CI->LabReport_model->findBySource(
                $data['device'] ?? null,
                $data['source'] ?? null,
                $data['source_id'] ?? null
            );

        # duplicate report, update existing
        if ($existing) {
            $report_id = $existing->id;

            // update timestamp
            $this->CI->LabReport_model->touch($report_id);

            // remove old results
            $this->CI->LabResult_model->deleteByReport($report_id);

        } else {

            // create new report
            $report_id = $this->CI->LabReport_model->create([
                'pet_id'            => $pet_id,
                'device'            => $data['device'] ?? null,
                'source'            => $data['source'] ?? null,
                'source_id'         => $data['source_id'] ?? null,
                'sample_date'       => $data['sample_date'] ?? null,
                'software_version'  => $data['software_version'] ?? null,
                'metadata'          => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'created_at'        => date('Y-m-d H:i:s')
            ]);
        }

        return (int)$report_id;
    }

    /*
    * Store plots associated with report
    */
    private function store_plots(int $report_id, array $plots)
    {
        if (empty($plots)) {
            return;
        }

        $this->CI->lab_plots->where(array('report_id' => (int) $report_id))->delete();

        foreach ($plots as $type => $values) {
            $this->CI->lab_plots->insert([
                'report_id' => (int) $report_id,
                'type'      => $type,
                'data'      => json_encode($values)
            ]);
        }
    }

    /*
    * Map lab specific name to canonical name
    */
    private function canonical(array $r)
    {
        if (!isset($this->canonical_map[$r['code']])) {
            log_message('error', 'mapping failed canonical for ' . $r["code"]);
        }

        $r['code'] = $this->canonical_map[$r['code']] ?? $r['code'];
        return $r;
    }

    /*
    * Normalize result value into numeric and text fields
    */
    private function normalizeResultValue(array $r)
    {
        if (is_numeric($r['value'])) {
            $r['value_num']  = (float)$r['value'];
            $r['value_text'] = null;
        } else {
            $r['value_num']  = null;
            $r['value_text'] = trim($r['value']);
        }

        unset($r['value']);
        return $r;
    }

    /*
    * Resolve pet ID from data
    */
    private function resolvePetId($data)
    {
        // direct pet id
        if (!empty($data['pet_id'])) {
            $pet = $this->CI->Pets_model->does_pet_exist($data['pet_id']);
            if ($pet) return $pet;
        }

        // if we lucky with chip number
        if (!empty($data['chip'])) {
            $matches = $this->CI->Pets_model->findByChipNumber($data['chip']);
            if ($matches && count($matches) === 1) {
                return $matches[0]['id'];
            }
        }

        // phone + owner/pet
        // note: we use the phone to make the match a bit more "reliable"
        if (!empty($data['phone']) && !empty($data['owner_name'])) {
            $phone = $this->normalizePhone($data['phone']);
            $names = $this->splitOwnerPet($data['owner_name']);

            // log_message('error', 'searching pet id with phone:'.$phone.' and owner:'. $names['owner'] . ' name:' . $names['pet'] . '');
            if ($phone && $names['owner']) {
                $matches = $this->CI->Pets_model->findByOwnerPhoneAndPet(
                    $phone,
                    $names['owner'],
                    $names['pet']
                );

                if (count($matches) === 1) {
                    return $matches[0]->pet_id;
                }
            }
        }
        // 3. owner + pet
        if (!empty($data['owner_name']) && !empty($data['pet_name'])) {
            $matches = $this->CI->Pets_model->findByOwnerAndPet(
                $data['owner_name'],
                $data['pet_name']
            );
            if (count($matches) === 1) {
                return $matches[0]->pet_id;
            }
        }

        // owner is LAST_NAME/PET_NAME
        if (!empty($data['owner_name'])) {
            $names = $this->splitOwnerPet($data['owner_name']);

            // log_message('error', 'searching pet id with owner:'. $names['owner'] . ' name:' . $names['pet'] . '');
            if ($names['owner'] && $names['pet']) {
                $matches = $this->CI->Pets_model->findByOwnerAndPet(
                    $names['owner'],
                    $names['pet']
                );
                // log_message('error', 'found '.count($matches).' matches');
                if (count($matches) === 1) {
                    return $matches[0]->pet_id;
                }
            }
        }

        // pet name only (last resort)
        if (!empty($data['pet_name'])) {
            $matches = $this->CI->Pets_model->findByPetName($data['pet_name']);
            if ($matches && count($matches) === 1) {
                return $matches[0]['id'];
            }
        }

        // log_message('error', 'unresolved');
        // 4. unresolved
        return null;
    }

    private function normalizePhone($phone)
    {
        return preg_replace('/\D+/', '', $phone);
    }

    private function splitOwnerPet($value)
    {
        $parts = array_map('trim', explode('/', $value, 2));
        return [
            'owner' => $parts[0] ?? null,
            'pet'   => $parts[1] ?? null
        ];
    }
}
