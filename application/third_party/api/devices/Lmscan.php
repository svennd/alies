<?php
// handle LMSCAN device json payloads
require_once __DIR__.'/DeviceAdapterInterface.php';

class Lmscan implements DeviceAdapterInterface {

    public function parse(array $payload)
    {
        return [
            'device'      => 'lmscan',
            'source_id'   => $payload['serial_number'],

            # patient / sample
            'pet_id'      => $payload['sample_id'] ?? null,

            # meta
            'sample_date' => substr($payload['test_end_time'], 0, 10),

            # reports
            'results'     => [
                $this->normalize($payload)
            ],

            'metadata' => [
                'project_number' => $payload['project_number'],
                'test_end_time'  => $payload['test_end_time'],
                'sample_type'    => $payload['sample_type'],
            ]
        ];
    }

    private function normalize(array $p)
    {
        return [
            'code'    => $p['project_name'],
            'value'   => (float)$p['result'],
            'unit'    => $p['unit'] ?: null,
            'ref_min' => is_numeric($p['ref_low'])  ? (float)$p['ref_low']  : null,
            'ref_max' => is_numeric($p['ref_high']) ? (float)$p['ref_high'] : null
        ];
    }
}
