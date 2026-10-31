<?php

declare(strict_types=1);

require_once APPPATH . 'third_party/api/devices/Medilab.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

/**
 * T017 - application/third_party/api/devices/Medilab.php: payloads of the Medilab laboratory (API.md).
 *
 * Rules (from the code, confirmed by the API documentation):
 *  - "OWNER, (PET)" is split into owner and pet; anything else is all owner name
 *  - a result with lab_code 89114 carries the chip number
 *  - a result whose text_value is "niet medegedeeld" (any case) is dropped
 *  - a result is numeric only when its text_value is numeric: then `value` is used
 *
 * Rows without a text_value make the adapter call strcasecmp(null, ...), which PHP 8.4 reports as
 * deprecated; those tests ignore that deprecation on purpose.
 */
final class MedilabTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        $payload = json_decode(
            (string) file_get_contents(__DIR__ . '/../../Fixtures/medilab_payload.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return array_merge($payload, $overrides);
    }

    /** @return array<string, mixed> one result row as the device sends it */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'lab_code' => '1',
            'lab_name' => 'Test',
            'value' => '1',
            'text_value' => '1',
            'unit' => 'u',
            'min' => '0',
            'max' => '2',
        ], $overrides);
    }

    private function parse(array $payload): array
    {
        return (new Medilab())->parse($payload);
    }

    // ---- the whole payload ------------------------------------------------------------------

    public function testTheDocumentedExampleIsParsed(): void
    {
        $parsed = $this->parse($this->payload());

        $this->assertSame('medilab', $parsed['device']);
        $this->assertSame('medilab', $parsed['source']);
        $this->assertSame('ML-2026-87219', $parsed['source_id']);
        $this->assertSame('PEETERS', $parsed['owner_name']);
        $this->assertSame('MILO', $parsed['pet_name']);
        $this->assertSame('981000000000123', $parsed['chip']);
        $this->assertSame('2026-08-24', $parsed['sample_date']);
        $this->assertSame(
            ['device', 'source', 'source_id', 'owner_name', 'pet_name', 'chip', 'sample_date', 'results'],
            array_keys($parsed)
        );
    }

    public function testResultsAreNormalisedAndTheNotReportedOneIsDropped(): void
    {
        $results = $this->parse($this->payload())['results'];

        // 4 rows in, the "Niet Medegedeeld" glucose is dropped
        $this->assertSame(['Hemoglobine', 'Interpretation', 'Chip'], array_column($results, 'code'));
        $this->assertSame(
            ['code' => 'Hemoglobine', 'value' => 14.2, 'unit' => 'g/dL', 'ref_min' => 12.0, 'ref_max' => 18.0],
            $results[0]
        );
        $this->assertSame(
            ['code' => 'Interpretation', 'value' => 'normal', 'unit' => null, 'ref_min' => null, 'ref_max' => null],
            $results[1]
        );
    }

    public function testThePayloadWithoutAnythingGivesAnEmptyReport(): void
    {
        $parsed = $this->parse([]);

        $this->assertSame(
            [
                'device' => 'medilab',
                'source' => null,
                'source_id' => null,
                'owner_name' => null,
                'pet_name' => null,
                'chip' => null,
                'sample_date' => null,
                'results' => [],
            ],
            $parsed
        );
    }

    public function testTheSampleDateIsPassedThroughUnchanged(): void
    {
        $this->assertSame('24/08/2026 10:00', $this->parse($this->payload(['sample_date' => '24/08/2026 10:00']))['sample_date']);
    }

    // ---- patient ----------------------------------------------------------------------------

    /** @return array<string, array{?string, ?string, ?string}> */
    public static function patients(): array
    {
        return [
            'owner and pet' => ['PEETERS, (MILO)', 'PEETERS', 'MILO'],
            'a dot before the comma is swallowed' => ['Peeters., (Milo)', 'Peeters', 'Milo'],
            'spaces and a dot before the comma' => ['Peeters . , (Milo)', 'Peeters', 'Milo'],
            'spaces around the pet are trimmed' => ['PEETERS,  ( MILO )', 'PEETERS', 'MILO'],
            'multi word owner' => ['DE SMET, (BELLE)', 'DE SMET', 'BELLE'],
            'only an owner' => ['PEETERS', 'PEETERS', null],
            'owner is trimmed' => ['  PEETERS  ', 'PEETERS', null],
            'a comma without brackets is all owner' => ['PEETERS, MILO', 'PEETERS, MILO', null],
            'text after the brackets is all owner' => ['PEETERS, (MILO) extra', 'PEETERS, (MILO) extra', null],
            'empty pet in brackets' => ['PEETERS, ()', 'PEETERS', ''],
            'empty string' => ['', null, null],
            'null' => [null, null, null],
            'missing' => [null, null, null],
        ];
    }

    #[DataProvider('patients')]
    public function testPatientIsSplitIntoOwnerAndPet(?string $patient, ?string $owner, ?string $pet): void
    {
        $parsed = $this->parse($this->payload(['patient' => $patient]));

        $this->assertSame($owner, $parsed['owner_name']);
        $this->assertSame($pet, $parsed['pet_name']);
    }

    // ---- chip -------------------------------------------------------------------------------

    public function testTheChipIsTheTrimmedTextOfResultCode89114(): void
    {
        $results = [$this->row(['lab_code' => '89114', 'text_value' => "981000000000999\n"])];

        $this->assertSame('981000000000999', $this->parse($this->payload(['results' => $results]))['chip']);
    }

    public function testTheChipLabCodeMayBeANumber(): void
    {
        $results = [$this->row(['lab_code' => 89114, 'text_value' => '981000000000999'])];

        $this->assertSame('981000000000999', $this->parse($this->payload(['results' => $results]))['chip']);
    }

    public function testTheFirstChipResultWins(): void
    {
        $results = [
            $this->row(['lab_code' => '89114', 'text_value' => '111']),
            $this->row(['lab_code' => '89114', 'text_value' => '222']),
        ];

        $this->assertSame('111', $this->parse($this->payload(['results' => $results]))['chip']);
    }

    /** @return array<string, array{?string}> */
    public static function emptyChips(): array
    {
        return [
            'empty' => [''],
            'blank' => ['   '],
            'not reported' => ['niet medegedeeld'],
        ];
    }

    #[DataProvider('emptyChips')]
    #[IgnoreDeprecations]
    public function testAChipResultWithoutAChipGivesNull(string $text): void
    {
        $results = [$this->row(['lab_code' => '89114', 'text_value' => $text])];

        $this->assertNull($this->parse($this->payload(['results' => $results]))['chip']);
    }

    public function testWithoutAChipResultTheChipIsNull(): void
    {
        $this->assertNull($this->parse($this->payload(['results' => [$this->row()]]))['chip']);
    }

    public function testTheChipResultItselfStaysInTheResultsWithoutAValue(): void
    {
        // quirk: the chip row is also a normal result; its text is numeric, so the (empty) `value` field is used
        $results = $this->parse($this->payload())['results'];

        $chip = $results[2];
        $this->assertSame('Chip', $chip['code']);
        $this->assertNull($chip['value']);
    }

    // ---- results ----------------------------------------------------------------------------

    public function testTheCodeIsTheNameAndFallsBackToTheLabCode(): void
    {
        $results = [
            $this->row(['lab_name' => 'Glucose', 'lab_code' => '5']),
            $this->row(['lab_name' => '', 'lab_code' => '6']),
            $this->row(['lab_name' => null, 'lab_code' => 7]),
        ];

        $codes = array_column($this->parse($this->payload(['results' => $results]))['results'], 'code');

        $this->assertSame(['Glucose', '6', 7], $codes);
    }

    public function testNumericResultsUseTheValueField(): void
    {
        $results = [$this->row(['value' => '14.20', 'text_value' => '14.2'])];

        $this->assertSame(14.2, $this->parse($this->payload(['results' => $results]))['results'][0]['value']);
    }

    public function testTextResultsKeepTheirText(): void
    {
        $results = [$this->row(['value' => '', 'text_value' => 'POSITIEF'])];

        $this->assertSame('POSITIEF', $this->parse($this->payload(['results' => $results]))['results'][0]['value']);
    }

    #[IgnoreDeprecations]
    #[Group('known-bug')]
    public function testANumericValueWithoutATextValueIsLost(): void
    {
        // KNOWN BUG: the adapter decides numeric-or-text on text_value, so a number sent only in `value`
        // becomes null (API.md tells clients to send the number in both fields)
        $withoutText = $this->row(['value' => '14.2']);
        unset($withoutText['text_value']);
        $emptyText = $this->row(['value' => '14.2', 'text_value' => '']);

        $results = $this->parse($this->payload(['results' => [$withoutText, $emptyText]]))['results'];

        $this->assertNull($results[0]['value']);
        $this->assertNull($results[1]['value']);
    }

    public function testNumericTextWithoutANumericValueIsNull(): void
    {
        $results = [$this->row(['value' => 'n/a', 'text_value' => '12'])];

        $this->assertNull($this->parse($this->payload(['results' => $results]))['results'][0]['value']);
    }

    /** @return array<string, array{string}> */
    public static function notReported(): array
    {
        return [
            'lower case' => ['niet medegedeeld'],
            'upper case' => ['NIET MEDEGEDEELD'],
            'mixed case' => ['Niet Medegedeeld'],
        ];
    }

    #[DataProvider('notReported')]
    public function testResultsThatWereNotReportedAreDroppedInAnyCase(string $text): void
    {
        $results = [$this->row(['text_value' => $text]), $this->row(['lab_name' => 'Kept'])];

        $parsed = $this->parse($this->payload(['results' => $results]))['results'];

        $this->assertSame(['Kept'], array_column($parsed, 'code'));
    }

    public function testReferenceLimitsAreOnlyKeptWhenTheyAreNumbers(): void
    {
        $results = [
            $this->row(['min' => '12.5', 'max' => '18']),
            $this->row(['min' => '', 'max' => '-']),
            $this->row(['min' => 0, 'max' => '0']),
        ];

        $parsed = $this->parse($this->payload(['results' => $results]))['results'];

        $this->assertSame([12.5, 18.0], [$parsed[0]['ref_min'], $parsed[0]['ref_max']]);
        $this->assertSame([null, null], [$parsed[1]['ref_min'], $parsed[1]['ref_max']]);
        $this->assertSame([0.0, 0.0], [$parsed[2]['ref_min'], $parsed[2]['ref_max']]);
    }

    public function testAnEmptyUnitBecomesNull(): void
    {
        $results = [$this->row(['unit' => '']), $this->row(['unit' => 'mmol/L'])];

        $parsed = $this->parse($this->payload(['results' => $results]))['results'];

        $this->assertNull($parsed[0]['unit']);
        $this->assertSame('mmol/L', $parsed[1]['unit']);
    }

    public function testEmptyResultListGivesNoResults(): void
    {
        $this->assertSame([], $this->parse($this->payload(['results' => []]))['results']);
    }
}
