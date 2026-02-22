<?php
// handle IKEMS device json payloads
require_once __DIR__.'/DeviceAdapterInterface.php';

class Ikems implements DeviceAdapterInterface {

    public function parse(array $payload)
    {
        $results = [];

        if (!empty($payload['experiments'])) {
            foreach ($payload['experiments'] as $row) {
                $results[] = $this->normalize($row);
            }
        }

        # add summary and errorcode as special results
        if (!empty($payload['summary'])) {
            $results[] = [
                'code'      => 'SUMMARY',
                'value'     => $payload['summary'],
                'unit'      => null,
                'ref_min'   => null,
                'ref_max'   => null
            ];
        }

        if (!empty($payload['errorcode'])) {
            $results[] = [
                'code'      => 'ERRORCODE',
                'value'     => $payload['errorcode'],
                'unit'      => null,
                'ref_min'   => null,
                'ref_max'   => null
            ];
        }
        // resolve identification
        $ident = $this->resolveIdentification($payload);

        return [
            'device'      => 'ikems',
            'source_id'   => $payload['id'] ?? null,

            // identification
            'pet_id'      => $ident['pet_id'],
            'owner_name'  => $ident['owner_name'],

            'sample_date' => $this->parseDate($payload['chkdatetime'] ?? null),
            'results'     => $results,
            'software_version' => $payload['software_version'] ?? null,
            'metadata' => [
                'panel_id'    => $payload['panel_id'] ?? null,
                'panel_index' => $payload['panel_index'] ?? null,
                'panel_lot'   => $payload['panel_lot'] ?? null,
                'machine_id'  => $payload['machine_id'] ?? null,
            ],
        ];
    }

    /*
    * function: resolveIdentification
    * resolve pet identification from payload
    */
    private function resolveIdentification(array $payload): array
    {
        $ints  = [];
        $names = [];

        foreach (['pet_id', 'patient_number', 'pet_name'] as $key) {
            if (empty($payload[$key])) continue;

            $v = trim((string)$payload[$key]);

            if (ctype_digit($v)) {
                $ints[] = (int)$v;
            } else {
                $names[] = $v;
            }
        }

        $petId = null;
        if ($ints) {
            rsort($ints);
            $petId = (string)$ints[0];
            array_shift($ints);
        }

        return [
            'pet_id'     => $petId ?: null,
            'owner_name' => implode(' / ', array_merge(
                $names,
                array_map('strval', $ints)
            )) ?: null
        ];
    }


    private function normalize(array $row)
    {
        $i = $row['I'] ?? [];

        return [
            'code'    => $row['N'] ?? null,
            'value'   => $i[0] ?? null,
            'unit'    => $i[3] ?: null,
            'ref_min' => is_numeric($i[1] ?? null) ? (float)$i[1] : null,
            'ref_max' => is_numeric($i[2] ?? null) ? (float)$i[2] : null
        ];
    }

    private function parseDate($date)
    {
        if (!$date) return null;
        $d = DateTime::createFromFormat('d/m/Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }
}
