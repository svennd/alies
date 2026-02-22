<?php
// handle Medilab device json payloads
require_once __DIR__.'/DeviceAdapterInterface.php';

class Medilab implements DeviceAdapterInterface {

    private const CHIP_LAB_CODE = 89114;

    public function parse(array $payload)
    {
        $results = [];

        if (!empty($payload['results'])) {
            foreach ($payload['results'] as $row) {
                # drop if null
                $r = $this->normalize($row);
                if ($r) $results[] = $r;
            }
        }

        [$owner, $pet] = $this->parsePatient($payload['patient'] ?? null);

        $chip = $this->extractChip($payload['results'] ?? []);

        return [
            'device'      => 'medilab',
            'source'      => $payload['source'] ?? null,
            'source_id'   => $payload['source_id'] ?? null,

            # patient identification
            'owner_name' => $owner,
            'pet_name'   => $pet,
            'chip'       => $chip,

            # meta
            'sample_date' => $payload['sample_date'] ?? null,

            # reports
            'results'     => $results,
        ];
    }

    private function normalize(array $row)
    {
        $min = is_numeric($row['min']) ? (float)$row['min'] : null;
        $max = is_numeric($row['max']) ? (float)$row['max'] : null;
        $val = is_numeric($row['value']) ? (float)$row['value'] : null;
        $text_value = isset($row['text_value']) && trim($row['text_value']) !== '' ? $row['text_value'] : null;

        if (strcasecmp($text_value, 'niet medegedeeld') === 0) {
            return null;
        }

        return [
            'code'        => ($row['lab_name'] ? $row['lab_name'] : $row['lab_code']),
            'value'       => is_numeric($text_value) ? $val : $text_value,
            'unit'        => $row['unit'] ?: null,
            'ref_min'     => $min,
            'ref_max'     => $max
        ];
    }

    private function parsePatient(?string $patient): array
    {
        if (!$patient) return [null, null];

        if (preg_match('/^(.*?)[\s\.]*,\s*\((.*?)\)$/', $patient, $m)) {
            return [
                trim($m[1]),
                trim($m[2]),
            ];
        }

        return [trim($patient), null];
    }

    # in the rare cases where chip is included in results
    # this is a perfect way to detect the pet_id
    private function extractChip(array $results): ?string
    {
        foreach ($results as $row) {
            if (($row['lab_code'] ?? null) == self::CHIP_LAB_CODE) {
                $v = trim((string)($row['text_value'] ?? ''));
                return $v !== '' && $v != "niet medegedeeld" ? $v : null;
            }
        }
        return null;
    }
}
