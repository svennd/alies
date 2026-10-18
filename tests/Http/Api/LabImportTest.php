<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T336 - POST api/lab/import/{device} (controllers/api/Lab.php, LabResultService::ingest).
 *
 * Auth first (see ApiAuthTest), then: unknown device = HTML 404, body that is not a JSON object = 400,
 * otherwise the device adapter parses the payload and the service resolves the pet in this order:
 * pet_id, chip, phone + owner/pet, owner + pet name, "owner/pet" in owner_name, pet name.
 * Found: lab_report + lab_results (+ lab_plots), {"status":"ok"}. Not found: lab_report_pending, {"status":"pending"}.
 * A report is identified by (device, source_id): delivering it again refreshes the report and replaces its results.
 * Every test uses its own source id, because the database is shared inside the class.
 */
final class LabImportTest extends HttpTestCase
{
    private string $key;

    protected function setUp(): void
    {
        parent::setUp();
        $this->key = Factory::apiKey();
    }

    // ---- helpers ------------------------------------------------------------------------------

    private function fixture(string $name): array
    {
        return json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/Fixtures/' . $name), true, 512, JSON_THROW_ON_ERROR);
    }

    private function import(string $device, array|string $payload): \Psr\Http\Message\ResponseInterface
    {
        return $this->postJson('api/lab/import/' . $device, $payload, ['X-API-Key' => $this->key]);
    }

    /** @return array<string, mixed> */
    private function body(\Psr\Http\Message\ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true) ?? [];
    }

    private function newPet(array $overrides = []): int
    {
        return Factory::pet(Factory::owner(), $overrides);
    }

    /** The ms4s2 fixture names pet 998193; created once per class. */
    private function fixturePet(): int
    {
        $existing = $this->scalar('SELECT id FROM pets WHERE id = 998193');

        return $existing ? (int) $existing : $this->newPet(['id' => 998193]);
    }

    private function missingPetId(): int
    {
        return 900000 + random_int(1000, 99999);
    }

    private function report(string $device, string $sourceId): ?array
    {
        return $this->row('SELECT * FROM lab_report WHERE device = ? AND source_id = ?', [$device, $sourceId]);
    }

    private function ms4s2(string $id, int|string $petId, array $extra = []): array
    {
        return array_merge($this->fixture('ms4s2_payload.json'), ['id' => $id, 'pet_id' => (string) $petId], $extra);
    }

    // ---- generic behaviour --------------------------------------------------------------------

    public function testAnUnknownDeviceAnswersWithAnHtmlNotFound(): void
    {
        $before = $this->rows('SELECT api_key_id, count FROM api_rate_limit');
        $response = $this->client->post('api/lab/import/unknown', ['body' => '{}', 'headers' => ['X-API-Key' => $this->key], 'http_errors' => false]);

        // KNOWN BUG: show_404() answers with the HTML error page, not the JSON error the other failures give - decide in CI4 whether to keep
        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertNull(json_decode((string) $response->getBody(), true));
        // authentication and the rate limit run before the device is looked up, so the call is counted
        $id = (int) $this->scalar('SELECT id FROM api_keys WHERE key_hash = ?', [$this->key]);
        $this->assertSame(1, (int) $this->scalar('SELECT SUM(count) FROM api_rate_limit WHERE api_key_id = ?', [$id]));
        $this->assertNotSame($before, $this->rows('SELECT api_key_id, count FROM api_rate_limit'));
    }

    public function testTheDeviceNameIsCaseInsensitive(): void
    {
        $response = $this->import('MS4S2', $this->ms4s2('case-' . uniqid(), $this->newPet()));
        $this->assertSame('ok', $this->body($response)['status']);
    }

    public function testABodyThatIsNotJsonIsRefusedWith400(): void
    {
        foreach (['not json at all', '"just a string"', '12', ''] as $body) {
            $response = $this->import('ms4s2', $body);
            $this->assertSame(400, $response->getStatusCode(), 'body: ' . $body);
            $this->assertSame(['status' => 'error', 'message' => 'Invalid JSON payload'], $this->body($response));
        }
        $this->assertSame(0, $this->countWhere('lab_report_pending'));
    }

    public function testNothingIsImportedWithoutAnApiKey(): void
    {
        $before = $this->countWhere('lab_report');
        $response = $this->client->post('api/lab/import/ms4s2', ['body' => json_encode($this->ms4s2('nokey-' . uniqid(), $this->newPet())), 'http_errors' => false]);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame($before, $this->countWhere('lab_report'));
    }

    // ---- ms4s2 --------------------------------------------------------------------------------

    public function testMs4s2WithAKnownPetCreatesTheReportAndResults(): void
    {
        $pet = $this->fixturePet();
        $payload = $this->fixture('ms4s2_payload.json');
        $payload['id'] = $sid = 'ok-' . uniqid();

        $response = $this->import('ms4s2', $payload);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame(['status' => 'ok'], $this->body($response));
        $report = $this->report('ms4s2', $sid);
        $this->assertNotNull($report);
        $this->assertSame($pet, (int) $report['pet_id']);
        $this->assertSame('2025-10-21', substr((string) $report['sample_date'], 0, 10));
        $expected = count($payload['experiments']) + count($payload['wbc_calc']);
        $this->assertGreaterThan(5, $expected);
        $this->assertSame($expected, $this->countWhere('lab_results', ['report_id' => $report['id']]));
        $this->assertSame(0, $this->countWhere('lab_report_pending', ['source_id' => $sid]));
    }

    public function testMs4s2ResultValuesAreStoredNumerically(): void
    {
        $pet = $this->newPet();
        $sid = 'val-' . uniqid();
        $this->import('ms4s2', $this->ms4s2($sid, $pet, [
            'wbc_calc' => [],
            'experiments' => ['WBC' => ['value' => '5.66', 'unit' => 'G/L', 'min' => '5', 'max' => '15', 'pct' => 0.3]],
        ]));

        $rows = $this->rows('SELECT * FROM lab_results WHERE report_id = ?', [$this->report('ms4s2', $sid)['id']]);
        $this->assertCount(1, $rows);
        $this->assertEqualsWithDelta(5.66, (float) $rows[0]['value_num'], 0.001);
        $this->assertSame('G/L', $rows[0]['unit']);
        $this->assertEqualsWithDelta(5.0, (float) $rows[0]['ref_min'], 0.001);
        $this->assertEqualsWithDelta(15.0, (float) $rows[0]['ref_max'], 0.001);
    }

    public function testMs4s2WithAnUnknownPetIsKeptPending(): void
    {
        $sid = 'pend-' . uniqid();
        $payload = $this->ms4s2($sid, $this->missingPetId(), ['owner_name' => 'NOBODYX/NOPETX', 'phone' => '0400000000']);

        $response = $this->import('ms4s2', $payload);

        $this->assertSame(['status' => 'pending'], $this->body($response));
        $this->assertNull($this->report('ms4s2', $sid), 'no report yet');
        $pending = $this->row('SELECT * FROM lab_report_pending WHERE device = ? AND source_id = ?', ['ms4s2', $sid]);
        $this->assertNotNull($pending);
        $this->assertSame('pet_not_found', $pending['reason']);
        $this->assertNull($pending['resolved_at']);
        $this->assertSame($sid, json_decode((string) $pending['raw_payload'], true)['id']);
        $identifiers = json_decode((string) $pending['identifiers'], true);
        $this->assertSame('NOBODYX/NOPETX', $identifiers['owner_name']);
        $this->assertSame('0400000000', $identifiers['phone']);
    }

    public function testDeliveringTheSameUnmatchedResultAgainRefreshesOnePendingRow(): void
    {
        $sid = 'pend2-' . uniqid();
        $payload = $this->ms4s2($sid, $this->missingPetId(), ['owner_name' => 'NOBODYX/NOPETX', 'phone' => '0400000000']);

        $this->import('ms4s2', $payload);
        $payload['software_version'] = 'second';
        $this->import('ms4s2', $payload);

        // the identity (device, source_id) is unique among active pending rows
        $this->assertSame(1, $this->countWhere('lab_report_pending', ['source_id' => $sid]));
        $this->assertNotNull($this->row('SELECT id FROM lab_report_pending WHERE source_id = ? AND last_received_at IS NOT NULL', [$sid]));

        // a different report is a different pending row
        $this->import('ms4s2', $this->ms4s2($sid . 'b', $this->missingPetId(), ['owner_name' => 'NOBODYX/NOPETX', 'phone' => '0400000000']));
        $this->assertSame(1, $this->countWhere('lab_report_pending', ['source_id' => $sid . 'b']));
        $this->assertSame(1, $this->countWhere('lab_report_pending', ['source_id' => $sid]));
    }

    public function testAPendingResultIsResolvedWhenThePetExistsAtTheNextDelivery(): void
    {
        $sid = 'resolve-' . uniqid();
        $petId = $this->missingPetId();
        $this->import('ms4s2', $this->ms4s2($sid, $petId, ['owner_name' => 'NOBODYX/NOPETX', 'phone' => '0400000000']));
        $this->assertNull($this->scalar('SELECT resolved_at FROM lab_report_pending WHERE source_id = ?', [$sid]));

        $pet = $this->newPet(['id' => $petId]);
        $response = $this->import('ms4s2', $this->ms4s2($sid, $petId));

        $this->assertSame(['status' => 'ok'], $this->body($response));
        $report = $this->report('ms4s2', $sid);
        $this->assertSame($pet, (int) $report['pet_id']);
        $pending = $this->row('SELECT * FROM lab_report_pending WHERE source_id = ?', [$sid]);
        $this->assertNotNull($pending['resolved_at']);
        $this->assertSame((int) $report['id'], (int) $pending['report_id']);
        $this->assertSame($pet, (int) $pending['resolved_pet_id']);
    }

    public function testDeliveringTheSameReportTwiceKeepsOneReportWithReplacedResults(): void
    {
        $this->fixturePet();
        $sid = 'dup-' . uniqid();
        $first = $this->fixture('ms4s2_payload.json');
        $first['id'] = $sid;
        $this->import('ms4s2', $first);
        $report = $this->report('ms4s2', $sid);
        $oldIds = array_column($this->rows('SELECT id FROM lab_results WHERE report_id = ?', [$report['id']]), 'id');
        $count = count($oldIds);

        $second = $first;
        $second['experiments']['WBC']['value'] = '9.99';
        $response = $this->import('ms4s2', $second);

        $this->assertSame(['status' => 'ok'], $this->body($response));
        $this->assertSame(1, $this->countWhere('lab_report', ['device' => 'ms4s2', 'source_id' => $sid]));
        $this->assertSame((int) $report['id'], (int) $this->report('ms4s2', $sid)['id']);
        $newRows = $this->rows('SELECT * FROM lab_results WHERE report_id = ?', [$report['id']]);
        $this->assertCount($count, $newRows, 'same number of results, not doubled');
        // the old rows are gone (hard replaced), not updated in place
        $this->assertSame([], array_intersect($oldIds, array_column($newRows, 'id')));
        $values = array_map('floatval', array_column($newRows, 'value_num'));
        $this->assertContains(9.99, $values);
        $this->assertNotContains(5.66, $values);
    }

    public function testDeliveringTheSameSourceIdForAnotherPetRefreshesTheReportOfTheFirstPet(): void
    {
        $petA = $this->newPet();
        $petB = $this->newPet();
        $sid = 'other-pet-' . uniqid();
        $this->import('ms4s2', $this->ms4s2($sid, $petA));

        $response = $this->client->post('api/lab/import/ms4s2', [
            'body' => json_encode($this->ms4s2($sid, $petB)), 'headers' => ['X-API-Key' => $this->key], 'http_errors' => false,
        ]);

        // the report already exists, so it is refreshed for the pet it belongs to: pet_id in the payload is not consulted
        $this->assertSame(['status' => 'ok'], $this->body($response));
        $this->assertSame($petA, (int) $this->report('ms4s2', $sid)['pet_id']);
    }

    #[Group('known-bug')]
    public function testAPhoneWithAnOwnerNameWithoutASlashIsAServerError(): void
    {
        $sid = 'typeerror-' . uniqid();
        $payload = $this->ms4s2($sid, $this->missingPetId(), ['owner_name' => 'KEPPENS', 'phone' => '0477.88.14.64']);

        $response = $this->client->post('api/lab/import/ms4s2', [
            'body' => json_encode($payload), 'headers' => ['X-API-Key' => $this->key], 'http_errors' => false,
        ]);

        // KNOWN BUG: splitOwnerPet gives pet = null and findByOwnerPhoneAndPet(string, string, string) throws a TypeError - decide in CI4 whether to keep
        $this->assertSame(500, $response->getStatusCode());
        $this->assertNull($this->report('ms4s2', $sid));
        $this->assertSame(0, $this->countWhere('lab_report_pending', ['source_id' => $sid]), 'the transaction was rolled back, nothing is queued');
        // positive control: without a phone the same owner name ends as pending
        $payload['phone'] = '';
        $payload['id'] = $sid . 'b';
        $this->assertSame(['status' => 'pending'], $this->body($this->import('ms4s2', $payload)));
    }

    public function testThePetIsFoundByPhoneAndOwnerSlashPetWhenThePetIdIsUnknown(): void
    {
        $owner = Factory::owner(['last_name' => 'KEPPENS', 'mobile' => '0477881464']);
        $pet = Factory::pet($owner, ['name' => 'MAURICE']);
        $sid = 'phone-' . uniqid();

        $response = $this->import('ms4s2', $this->ms4s2($sid, $this->missingPetId()));

        $this->assertSame(['status' => 'ok'], $this->body($response));
        $this->assertSame($pet, (int) $this->report('ms4s2', $sid)['pet_id']);
    }

    public function testThePetIdWinsOverTheOwnerName(): void
    {
        $byId = $this->newPet();
        $owner = Factory::owner(['last_name' => 'KEPPENS', 'mobile' => '0477881464']);
        Factory::pet($owner, ['name' => 'MAURICE']);
        $sid = 'order-' . uniqid();

        $this->import('ms4s2', $this->ms4s2($sid, $byId));

        $this->assertSame($byId, (int) $this->report('ms4s2', $sid)['pet_id']);
    }

    public function testAnAmbiguousMatchStaysPending(): void
    {
        $name = 'TWIN' . strtoupper(substr(uniqid(), -6));
        $owner = Factory::owner(['last_name' => 'AMBIG' . $name, 'mobile' => '0466000111']);
        Factory::pet($owner, ['name' => $name]);
        Factory::pet($owner, ['name' => $name]);
        $sid = 'ambig-' . uniqid();

        $response = $this->import('ms4s2', $this->ms4s2($sid, $this->missingPetId(), ['owner_name' => 'AMBIG' . $name . '/' . $name, 'phone' => '0466000111']));

        $this->assertSame(['status' => 'pending'], $this->body($response));
    }

    // ---- ikems --------------------------------------------------------------------------------

    private function ikems(string $id, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'chkdatetime' => '21/10/2025',
            'software_version' => '1.2.3',
            'panel_id' => 'P1',
            'experiments' => [
                ['N' => 'GLU', 'I' => ['5.5', '3', '7', 'mmol/L']],
                ['N' => 'ALT', 'I' => ['41', '10', '100', 'U/L']],
            ],
            'summary' => 'all good',
            'errorcode' => 'E7',
        ], $extra);
    }

    public function testIkemsWithAKnownPetCreatesReportAndResultsIncludingSummaryAndErrorCode(): void
    {
        $pet = $this->newPet();
        $sid = 'ik-' . uniqid();

        $response = $this->import('ikems', $this->ikems($sid, ['pet_id' => (string) $pet]));

        $this->assertSame(['status' => 'ok'], $this->body($response));
        $report = $this->report('ikems', $sid);
        $this->assertNotNull($report);
        $this->assertSame($pet, (int) $report['pet_id']);
        $this->assertSame('2025-10-21', substr((string) $report['sample_date'], 0, 10));
        $this->assertSame('1.2.3', $report['software_version']);
        $this->assertSame(4, $this->countWhere('lab_results', ['report_id' => $report['id']]), '2 experiments + summary + errorcode');
        $texts = array_column($this->rows('SELECT value_text FROM lab_results WHERE report_id = ? AND value_text IS NOT NULL', [$report['id']]), 'value_text');
        sort($texts);
        $this->assertSame(['E7', 'all good'], $texts);
        $glu = $this->row('SELECT * FROM lab_results WHERE report_id = ? AND unit = ?', [$report['id'], 'mmol/L']);
        $this->assertEqualsWithDelta(5.5, (float) $glu['value_num'], 0.001);
        $this->assertEqualsWithDelta(3.0, (float) $glu['ref_min'], 0.001);
        $this->assertEqualsWithDelta(7.0, (float) $glu['ref_max'], 0.001);
    }

    public function testIkemsUsesTheLargestNumberAmongPetIdAndPatientNumberAsPetId(): void
    {
        $pet = $this->newPet();
        $sid = 'ik-big-' . uniqid();

        $this->import('ikems', $this->ikems($sid, ['pet_id' => '1', 'patient_number' => (string) $pet]));

        $this->assertSame($pet, (int) $this->report('ikems', $sid)['pet_id']);
    }

    public function testIkemsWithAnUnknownPetIsKeptPending(): void
    {
        $sid = 'ik-pend-' . uniqid();

        $response = $this->import('ikems', $this->ikems($sid, ['pet_id' => (string) $this->missingPetId()]));

        $this->assertSame(['status' => 'pending'], $this->body($response));
        $this->assertNull($this->report('ikems', $sid));
        $this->assertRowExists('lab_report_pending', ['device' => 'ikems', 'source_id' => $sid, 'reason' => 'pet_not_found']);
    }

    public function testIkemsDeliveredTwiceReplacesTheResults(): void
    {
        $pet = $this->newPet();
        $sid = 'ik-dup-' . uniqid();
        $this->import('ikems', $this->ikems($sid, ['pet_id' => (string) $pet]));
        $reportId = (int) $this->report('ikems', $sid)['id'];

        $second = $this->ikems($sid, ['pet_id' => (string) $pet, 'summary' => '', 'errorcode' => '']);
        $this->assertSame(['status' => 'ok'], $this->body($this->import('ikems', $second)));

        $this->assertSame(1, $this->countWhere('lab_report', ['device' => 'ikems', 'source_id' => $sid]));
        $this->assertSame(2, $this->countWhere('lab_results', ['report_id' => $reportId]), 'summary and errorcode are gone');
    }

    // ---- lmscan -------------------------------------------------------------------------------

    public function testLmscanWithAKnownPetCreatesOneResult(): void
    {
        $pet = $this->newPet();
        $payload = $this->fixture('lmscan_payload.json');
        $payload['serial_number'] = $sid = 'LM-' . uniqid();
        $payload['sample_id'] = (string) $pet;

        $response = $this->import('lmscan', $payload);

        $this->assertSame(['status' => 'ok'], $this->body($response));
        $report = $this->report('lmscan', $sid);
        $this->assertSame($pet, (int) $report['pet_id']);
        $this->assertSame('2026-08-24', substr((string) $report['sample_date'], 0, 10));
        $results = $this->rows('SELECT * FROM lab_results WHERE report_id = ?', [$report['id']]);
        $this->assertCount(1, $results);
        $this->assertEqualsWithDelta(187.4, (float) $results[0]['value_num'], 0.001);
        $this->assertSame('ug/L', $results[0]['unit']);
        $this->assertEqualsWithDelta(200.0, (float) $results[0]['ref_max'], 0.001);
        $this->assertSame('P-17', json_decode((string) $report['metadata'], true)['project_number']);
    }

    public function testLmscanWithAnUnknownSampleIdIsKeptPending(): void
    {
        $payload = $this->fixture('lmscan_payload.json');
        $payload['serial_number'] = $sid = 'LM-p-' . uniqid();
        $payload['sample_id'] = (string) $this->missingPetId();

        $this->assertSame(['status' => 'pending'], $this->body($this->import('lmscan', $payload)));
        $this->assertRowExists('lab_report_pending', ['device' => 'lmscan', 'source_id' => $sid]);
    }

    // ---- medilab ------------------------------------------------------------------------------

    public function testMedilabFindsThePetByTheChipInTheResults(): void
    {
        $chip = '98100' . random_int(1000000000, 9999999999);
        $pet = $this->newPet(['chip' => $chip]);
        $payload = $this->fixture('medilab_payload.json');
        $payload['source_id'] = $sid = 'ML-' . uniqid();
        $payload['results'][3]['text_value'] = ' ' . $chip . ' ';

        $response = $this->import('medilab', $payload);

        $this->assertSame(['status' => 'ok'], $this->body($response));
        $report = $this->row('SELECT * FROM lab_report WHERE source_id = ?', [$sid]);
        $this->assertSame($pet, (int) $report['pet_id']);
        $this->assertSame('medilab', $report['device']);
        $this->assertSame('medilab', $report['source']);
        $this->assertSame('2026-08-24', substr((string) $report['sample_date'], 0, 10));
        // 4 results in the payload, the glucose row says "Niet Medegedeeld" and is dropped
        $this->assertSame(3, $this->countWhere('lab_results', ['report_id' => $report['id']]));
        $this->assertSame(0, $this->countWhere('lab_results', ['report_id' => $report['id'], 'code' => 'Glucose']));
    }

    public function testMedilabWithoutAMatchIsKeptPendingWithTheParsedPatient(): void
    {
        $payload = $this->fixture('medilab_payload.json');
        $payload['source_id'] = $sid = 'ML-p-' . uniqid();
        $payload['patient'] = 'NOMATCHX, (NOPETY)';
        $payload['results'][3]['text_value'] = '';

        $this->assertSame(['status' => 'pending'], $this->body($this->import('medilab', $payload)));

        $pending = $this->row('SELECT * FROM lab_report_pending WHERE source_id = ?', [$sid]);
        $this->assertNotNull($pending);
        $this->assertSame('medilab', $pending['device']);
        $identifiers = json_decode((string) $pending['identifiers'], true);
        $this->assertSame('NOMATCHX', $identifiers['owner_name']);
        $this->assertSame('NOPETY', $identifiers['pet_name']);
        $this->assertNull($identifiers['chip']);
    }

    public function testMedilabFindsThePetByOwnerAndPetName(): void
    {
        $owner = Factory::owner(['last_name' => 'MLOWNER' . strtoupper(substr(uniqid(), -5))]);
        $ownerName = $this->scalar('SELECT last_name FROM owners WHERE id = ?', [$owner]);
        $pet = Factory::pet($owner, ['name' => 'MLPET']);
        $payload = $this->fixture('medilab_payload.json');
        $payload['source_id'] = $sid = 'ML-n-' . uniqid();
        $payload['patient'] = $ownerName . ', (MLPET)';
        $payload['results'][3]['text_value'] = '';

        $this->assertSame(['status' => 'ok'], $this->body($this->import('medilab', $payload)));
        $this->assertSame($pet, (int) $this->row('SELECT * FROM lab_report WHERE source_id = ?', [$sid])['pet_id']);
    }
}
