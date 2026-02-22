<?php
// handle MS4S2 device json payloads
require_once __DIR__.'/DeviceAdapterInterface.php';

class Ms4s2 implements DeviceAdapterInterface {

    public function parse(array $payload)
    {
        // log_message('error', 'MS4S2 Payload: '.json_encode($payload));
        $results = [];

        foreach (['experiments', 'wbc_calc'] as $group) {
            if (!isset($payload[$group])) continue;

            foreach ($payload[$group] as $key => $row) {

                $results[] = $this->normalize(
                    $key,
                    $row
                );
            }
        }

        [$plots, $markers] = $this->parsePlots($payload ?? null);

        return [
            'device'       => 'ms4s2',
            'source_id'    => $payload['id'],

            # patient identification
            'pet_id'       => $payload['pet_id'],
            'owner_name'   => $payload['owner_name'],
            'species'      => strtolower($payload['species']),
            'phone'        => $payload['phone'],
            
            # meta
            'sample_date'  => "{$payload['year']}-{$payload['month']}-{$payload['day']}",

            # reports
            'results'      => $results,

            # plots and markers (mono)
            'plots'    => $plots,
            'metadata' => $markers ? ['markers' => $markers] : null
        ];
    }

    private function parsePlots(array $payload)
    {
        if (empty($payload['plots'])) {
            return [null, null];
        }

        $plots = [];
        foreach (['THR','RBC','WBC'] as $k) {
            if (isset($payload['plots'][$k])) {
                $plots[$k] = array_map('intval', $payload['plots'][$k]);
            }
        }

        $markers = null;
        if (!empty($payload['markers'])) {
            $markers = array_map('intval', $payload['markers']);
        }

        return [$plots ?: null, $markers];
    }

    private function normalize($code, $row)
    {
        $value = (float)$row['value'];
        $min = is_numeric($row['min']) ? (float)$row['min'] : null;
        $max = is_numeric($row['max']) ? (float)$row['max'] : null;

        return [
            'code'      => $code,
            'value'     => $value,
            'unit'      => $row['unit'] ?: null,
            'ref_min'   => $min,
            'ref_max'   => $max
        ];
    }
}
