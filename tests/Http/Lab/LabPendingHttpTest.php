<?php

declare(strict_types=1);

/**
 * T255 - unmatched ("pending") lab results over HTTP: lab/pending, pending_detail, search_owners,
 * search_pets, recover_pending, delete_pending.
 *
 * Rules: only rows with resolved_at and deleted_at both NULL are active; the detail page shows the
 * non-empty identifiers, the parsed results and the pretty raw JSON; recover and delete are POST only
 * (405); recovering parses the payload with the device adapter, creates the lab report for the chosen
 * pet, marks the pending row resolved and redirects to the report; delete is a soft delete.
 */
final class LabPendingHttpTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    private function payload(string $serial, string $project = 'cPL', string $result = '187.4'): array
    {
        return [
            'serial_number' => $serial,
            'sample_id' => '998193',
            'test_end_time' => '2026-08-24T14:31:00+02:00',
            'project_number' => 'P-17',
            'project_name' => $project,
            'sample_type' => 'serum',
            'result' => $result,
            'unit' => 'ug/L',
            'ref_low' => '0',
            'ref_high' => '200',
        ];
    }

    private function pending(string $serial, array $overrides = [], ?array $payload = null, ?array $identifiers = null): int
    {
        return Factory::insert('lab_report_pending', $overrides + [
            'device' => 'lmscan',
            'source' => null,
            'source_id' => $serial,
            'identity_hash' => null,
            'raw_payload' => json_encode($payload ?? $this->payload($serial)),
            'identifiers' => json_encode($identifiers ?? ['pet_id' => '998193', 'owner_name' => null, 'pet_name' => 'Fikkie', 'chip' => '', 'phone' => null]),
            'reason' => 'pet_not_found',
            'created_at' => Factory::raw('NOW()'),
            'last_received_at' => Factory::raw('NOW()'),
        ]);
    }

    private function ownerWithPet(?string $last = null): array
    {
        $owner = Factory::owner(['last_name' => $last ?? Factory::unique('PendOwner'), 'first_name' => 'Jan']);

        return [$owner, Factory::pet($owner, ['name' => Factory::unique('PendPet')])];
    }

    // ---- list -------------------------------------------------------------------------------

    public function testListShowsActiveRowsOnly(): void
    {
        $active = $this->pending('ACT-' . uniqid());
        $resolved = $this->pending('RES-' . uniqid(), ['resolved_at' => Factory::raw('NOW()')]);
        $deleted = $this->pending('DEL-' . uniqid(), ['deleted_at' => Factory::raw('NOW()')]);

        $response = $this->get('lab/pending');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('lab/pending_detail/' . $active, $body);
        $this->assertStringNotContainsString('lab/pending_detail/' . $resolved . '"', $body);
        $this->assertStringNotContainsString('lab/pending_detail/' . $deleted . '"', $body);
    }

    public function testListShowsSourceReasonAndOnlyTheFilledIdentifiers(): void
    {
        $serial = 'LIST-' . uniqid();
        $this->pending($serial, [], null, ['pet_id' => '4242', 'owner_name' => 'Wouters', 'pet_name' => '', 'chip' => null, 'phone' => '']);

        $response = $this->get('lab/pending');

        $this->assertSee($response, 'lmscan');
        $this->assertSee($response, '#' . $serial);
        $this->assertSee($response, 'pet_not_found');
        $this->assertSee($response, 'owner name:');
        $this->assertSee($response, 'Wouters');
        $this->assertSee($response, 'pet id:');
        $this->assertDontSee($response, 'chip:');
        $this->assertDontSee($response, 'phone:');
    }

    public function testListIsOrderedByLastReceipt(): void
    {
        $old = $this->pending('ORD-A-' . uniqid(), ['last_received_at' => date('Y-m-d H:i:s', strtotime('-3 days'))]);
        $new = $this->pending('ORD-B-' . uniqid(), ['last_received_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))]);

        preg_match_all('#lab/pending_detail/(\d+)#', (string) $this->get('lab/pending')->getBody(), $m);
        $ids = array_map('intval', $m[1]);

        $this->assertLessThan(array_search($old, $ids, true), array_search($new, $ids, true));
    }

    // ---- detail -----------------------------------------------------------------------------

    public function testDetailShowsIdentifiersParsedResultsAndRawJson(): void
    {
        $serial = 'DET-' . uniqid();
        $id = $this->pending($serial, [], null, ['pet_id' => '998193', 'owner_name' => 'Wouters', 'pet_name' => 'Fikkie']);

        $response = $this->get('lab/pending_detail/' . $id);

        $this->assertStatus($response, 200);
        foreach (['Wouters', 'Fikkie', 'pet_not_found', 'lmscan', '#' . $serial, 'cPL', '187.4', 'ug/L'] as $needle) {
            $this->assertSee($response, $needle);
        }
        // the raw payload is pretty printed JSON
        $this->assertSee($response, '"serial_number": "' . $serial . '"');
        $this->assertStringContainsString('lab/recover_pending/' . $id, (string) $response->getBody());
        $this->assertStringContainsString('lab/delete_pending/' . $id, (string) $response->getBody());
    }

    public function testDetailOfAnUnsupportedDeviceWarnsAndStillShowsTheRawPayload(): void
    {
        $id = $this->pending('UNS-' . uniqid(), ['device' => 'nodevice'], ['hello' => 'world']);

        $response = $this->get('lab/pending_detail/' . $id);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'could only be shown partially');
        $this->assertSee($response, '"hello": "world"');
    }

    public function testDetailOfAPayloadThatIsNotJsonWarns(): void
    {
        $id = $this->pending('BAD-' . uniqid(), ['raw_payload' => 'not json at all']);

        $response = $this->get('lab/pending_detail/' . $id);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'not valid JSON');
        $this->assertSee($response, 'not json at all');
    }

    public function testDetailOfAMissingResolvedOrDeletedRowIs404(): void
    {
        $resolved = $this->pending('R404-' . uniqid(), ['resolved_at' => Factory::raw('NOW()')]);
        $deleted = $this->pending('D404-' . uniqid(), ['deleted_at' => Factory::raw('NOW()')]);
        $active = $this->pending('A404-' . uniqid());

        $this->assertStatus($this->get('lab/pending_detail/99999999'), 404);
        $this->assertStatus($this->get('lab/pending_detail/' . $resolved), 404);
        $this->assertStatus($this->get('lab/pending_detail/' . $deleted), 404);
        $this->assertStatus($this->get('lab/pending_detail/' . $active), 200);
    }

    // ---- search endpoints -------------------------------------------------------------------

    public function testSearchOwnersReturnsIdAndNameWithAddress(): void
    {
        $last = Factory::unique('Zoekmij');
        $owner = Factory::owner(['last_name' => $last, 'first_name' => 'Piet', 'street' => 'Dorpsstraat', 'nr' => '12', 'zip' => '2018']);
        Factory::owner(['last_name' => 'Anders' . $last]);

        $response = $this->get('lab/search_owners', ['term' => $last]);

        $this->assertStringStartsWith('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame(
            ['results' => [['id' => $owner, 'text' => 'Piet ' . $last . ' (#' . $owner . ') - Dorpsstraat 12 2018']]],
            $this->json($response),
            'the term matches the start of the name only'
        );
    }

    public function testSearchOwnersFindsByIdAndSkipsDeletedOwners(): void
    {
        $owner = Factory::owner();
        $last = Factory::unique('Weg');
        Factory::owner(['last_name' => $last, 'deleted_at' => date('Y-m-d H:i:s')]);

        $byId = $this->json($this->get('lab/search_owners', ['term' => (string) $owner]));
        $deleted = $this->json($this->get('lab/search_owners', ['term' => $last]));

        $this->assertContains($owner, array_column($byId['results'], 'id'));
        $this->assertSame([], $deleted['results']);
    }

    public function testSearchOwnersWithoutATermIsAnEmptyList(): void
    {
        $this->assertSame(['results' => []], $this->json($this->get('lab/search_owners')));
        $this->assertSame(['results' => []], $this->json($this->get('lab/search_owners', ['term' => '   '])));
    }

    public function testSearchPetsReturnsOnlyAssignablePetsOfThatOwner(): void
    {
        $owner = Factory::owner();
        $rex = Factory::pet($owner, ['name' => 'Rexo']);
        $rita = Factory::pet($owner, ['name' => 'Rita']);
        Factory::pet($owner, ['name' => 'Rexdead', 'death' => 1]);
        Factory::pet($owner, ['name' => 'Rexgone', 'transfered' => 1]);
        Factory::pet($owner, ['name' => 'Rexdel', 'deleted_at' => date('Y-m-d H:i:s')]);
        Factory::pet(Factory::owner(), ['name' => 'Rexother']);

        $all = $this->json($this->get('lab/search_pets', ['owner_id' => $owner]));
        $filtered = $this->json($this->get('lab/search_pets', ['owner_id' => $owner, 'term' => 'Rex']));

        $this->assertSame(
            [['id' => $rex, 'text' => 'Rexo (#' . $rex . ')'], ['id' => $rita, 'text' => 'Rita (#' . $rita . ')']],
            $all['results'],
            'alive, not transferred, not deleted, ordered by name'
        );
        $this->assertSame([['id' => $rex, 'text' => 'Rexo (#' . $rex . ')']], $filtered['results']);
    }

    public function testSearchPetsOfAMissingOrDeletedOwnerIsEmpty(): void
    {
        $deletedOwner = Factory::owner(['deleted_at' => date('Y-m-d H:i:s')]);
        Factory::pet($deletedOwner);

        $this->assertSame(['results' => []], $this->json($this->get('lab/search_pets', ['owner_id' => $deletedOwner])));
        $this->assertSame(['results' => []], $this->json($this->get('lab/search_pets')));
    }

    // ---- recover ----------------------------------------------------------------------------

    public function testRecoverCreatesTheReportForThePetAndResolvesThePendingRow(): void
    {
        $serial = 'REC-' . uniqid();
        $pending = $this->pending($serial);
        [$owner, $pet] = $this->ownerWithPet();
        $this->db()->exec('DELETE FROM log');

        $response = $this->post('lab/recover_pending/' . $pending, ['owner_id' => $owner, 'pet_id' => $pet]);

        $report = $this->row('SELECT * FROM lab_report WHERE source_id = ?', [$serial]);
        $this->assertNotNull($report);
        $this->assertSame($pet, (int) $report['pet_id']);
        $this->assertSame('lmscan', $report['device']);
        $this->assertSame('2026-08-24', substr((string) $report['sample_date'], 0, 10));
        $this->assertRedirectsTo($response, 'lab/detail/' . $report['id']);

        $result = $this->row('SELECT * FROM lab_results WHERE report_id = ?', [$report['id']]);
        $this->assertSame('cPL', $result['code']);
        $this->assertEqualsWithDelta(187.4, (float) $result['value_num'], 0.001);
        $this->assertSame('ug/L', $result['unit']);

        $row = $this->find('lab_report_pending', $pending);
        $this->assertNotNull($row['resolved_at']);
        $this->assertSame((int) $report['id'], (int) $row['report_id']);
        $this->assertSame($pet, (int) $row['resolved_pet_id']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['resolved_by']);
        $this->assertSame(1, $this->logCount('pending_lab_recovered'));

        // the row is gone from the queue and its detail page
        $this->assertStatus($this->get('lab/pending_detail/' . $pending), 404);
    }

    public function testRecoveredReportIsReachableFromTheRedirect(): void
    {
        $pending = $this->pending('RED-' . uniqid());
        [$owner, $pet] = $this->ownerWithPet();

        $response = $this->post('lab/recover_pending/' . $pending, ['owner_id' => $owner, 'pet_id' => $pet]);
        $detail = $this->get(ltrim((string) parse_url($this->redirectTarget($response), PHP_URL_PATH), '/'));

        $this->assertStatus($detail, 200);
        $this->assertSee($detail, 'cPL');
    }

    public function testRecoverByGetIsMethodNotAllowed(): void
    {
        $pending = $this->pending('GET-' . uniqid());

        $this->assertStatus($this->get('lab/recover_pending/' . $pending), 405);
        $this->assertNull($this->find('lab_report_pending', $pending)['resolved_at']);
    }

    public function testRecoverWithAPetOfAnotherOwnerFailsAndKeepsTheRowActive(): void
    {
        $serial = 'WRONG-' . uniqid();
        $pending = $this->pending($serial);
        [$owner] = $this->ownerWithPet();
        [, $foreignPet] = $this->ownerWithPet();

        $response = $this->post('lab/recover_pending/' . $pending, ['owner_id' => $owner, 'pet_id' => $foreignPet]);

        $this->assertRedirectsTo($response, 'lab/pending_detail/' . $pending);
        $this->assertNull($this->find('lab_report_pending', $pending)['resolved_at']);
        $this->assertRowMissing('lab_report', ['source_id' => $serial]);
        $this->assertSee($this->get('lab/pending_detail/' . $pending), 'Select a valid pet');
    }

    public function testRecoverOfAnAlreadyResolvedRowFails(): void
    {
        $serial = 'ONCE-' . uniqid();
        $pending = $this->pending($serial);
        [$owner, $pet] = $this->ownerWithPet();
        $this->post('lab/recover_pending/' . $pending, ['owner_id' => $owner, 'pet_id' => $pet]);

        $second = $this->post('lab/recover_pending/' . $pending, ['owner_id' => $owner, 'pet_id' => $pet]);

        $this->assertRedirectsTo($second, 'lab/pending_detail/' . $pending);
        $this->assertSame(1, $this->countWhere('lab_report', ['source_id' => $serial]));
    }

    public function testRecoverOfADeviceThatIsNotSupportedFails(): void
    {
        $pending = $this->pending('NODEV-' . uniqid(), ['device' => 'nodevice']);
        [$owner, $pet] = $this->ownerWithPet();

        $this->post('lab/recover_pending/' . $pending, ['owner_id' => $owner, 'pet_id' => $pet]);

        $this->assertNull($this->find('lab_report_pending', $pending)['resolved_at']);
        $this->assertSee($this->get('lab/pending_detail/' . $pending), 'could not be recovered');
    }

    // ---- delete -----------------------------------------------------------------------------

    public function testDeleteSoftDeletesTheRowAndRedirectsToTheList(): void
    {
        $serial = 'SOFT-' . uniqid();
        $pending = $this->pending($serial);
        $other = $this->pending('KEEP-' . uniqid());
        $this->db()->exec('DELETE FROM log');

        $response = $this->post('lab/delete_pending/' . $pending);

        $this->assertRedirectsTo($response, 'lab/pending');
        $row = $this->find('lab_report_pending', $pending);
        $this->assertNotNull($row, 'the row is kept');
        $this->assertNotNull($row['deleted_at']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['deleted_by']);
        $this->assertStringContainsString($serial, (string) $row['raw_payload'], 'payload retained');
        $this->assertNull($this->find('lab_report_pending', $other)['deleted_at']);
        $this->assertSame(1, $this->logCount('pending_lab_deleted'));
        $this->assertStringNotContainsString('lab/pending_detail/' . $pending . '"', (string) $this->get('lab/pending')->getBody());
    }

    public function testDeleteByGetIsMethodNotAllowedAndChangesNothing(): void
    {
        $pending = $this->pending('GETDEL-' . uniqid());

        $this->assertStatus($this->get('lab/delete_pending/' . $pending), 405);
        $this->assertNull($this->find('lab_report_pending', $pending)['deleted_at']);
    }

    public function testDeletingAMissingRowShowsAnError(): void
    {
        $response = $this->post('lab/delete_pending/99999999');

        $this->assertRedirectsTo($response, 'lab/pending');
        $this->assertSee($this->get('lab/pending'), 'no longer active');
    }

    public function testPendingScreensAreRefusedForNonStaff(): void
    {
        $pending = $this->pending('MEM-' . uniqid());
        $this->cookies->clear();
        $this->loginAs('member');

        $this->assertStatus($this->get('lab/pending'), 500);
        $this->assertStatus($this->post('lab/delete_pending/' . $pending), 500);
        $this->assertNull($this->find('lab_report_pending', $pending)['deleted_at']);
    }
}
