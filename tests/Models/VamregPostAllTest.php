<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHarness.php';

use PHPUnit\Framework\Attributes\Group;

/**
 * T283 and T285 (in-process part) - Vamreg::post_all, post_all_out, handleVamregResult and reset.
 *
 * The controller runs through VamregHarness with a recording client, so the register is never called.
 * Rules: DRAFT rows of the quarter become declarations; answer 200 marks them SENT with the id the
 * register returned (by position); 400 marks the rows named in the errors as ERROR; 403 and anything
 * else change nothing and answer 403 / 500.
 * Fixed quarter 2031 Q1.
 */
final class VamregPostAllTest extends CodeIgniterDatabaseTestCase
{
    private VamregHarness $h;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->ci->db->query('DELETE FROM vamreg_in_buffer');
        $this->ci->db->query('DELETE FROM vamreg_out_buffer');
        $this->h = new VamregHarness();
    }

    protected function tearDown(): void
    {
        $this->h->release();
        parent::tearDown();
    }

    private function rowOf(string $table, int $id): array
    {
        return $this->ci->db->get_where($table, ['id' => $id])->row_array();
    }

    public function testWithoutDraftsTheAnswerIsMinusOneAndNothingIsSent(): void
    {
        VamregHelper::in('2031-02-10', ['status' => 'SENT']);

        $this->h->controller->post_all(2031, 1);

        $this->assertSame(-1, $this->h->answer()['status']);
        $this->assertCount(0, $this->h->client->requests);
    }

    public function testInDraftsAreSentAsDeclarationsAndMarkedSent(): void
    {
        $a = VamregHelper::in('2031-01-01', ['in_quantity_pack_count' => 3, 'cnk' => '1111111']);
        $b = VamregHelper::in('2031-03-31', ['in_quantity_pack_count' => 4, 'cnk' => '2222222', 'product_type' => 'FOREIGN', 'provider_type' => 'DIST_EU']);
        $skip = VamregHelper::in('2031-04-01');
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [['id' => 'aaa'], ['id' => 'bbb']])]);

        $this->h->controller->post_all(2031, 1);

        $this->assertSame(200, $this->h->answer()['status']);
        $this->assertCount(1, $this->h->client->requests);
        $this->assertSame('POST', $this->h->client->requests[0]['method']);
        $this->assertSame('/declaration', $this->h->client->requests[0]['path']);
        $body = $this->h->client->body();
        $this->assertCount(2, $body);
        $this->assertSame(['internal_id' => (string) $a, 'register' => 'IN', 'dateTime' => '2031-01-01T00:00:00.000Z', 'productType' => 'BE',
            'providerType' => 'DIST_BE', 'cnk' => '1111111', 'inQuantityPackCount' => 3], $body[0]);
        $this->assertSame('2031-03-31T00:00:00.000Z', $body[1]['dateTime']);
        $this->assertSame('FOREIGN', $body[1]['productType']);
        $this->assertSame(4, $body[1]['inQuantityPackCount']);

        $ra = $this->rowOf('vamreg_in_buffer', $a);
        $rb = $this->rowOf('vamreg_in_buffer', $b);
        $this->assertSame('SENT', $ra['status']);
        $this->assertSame('aaa', $ra['api_declaration_id']);
        $this->assertNotNull($ra['sent_at']);
        $this->assertSame('bbb', $rb['api_declaration_id']);
        $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $skip)['status']);
    }

    public function testOutDraftsCarryTheSpeciesIndicationAndTheVetsOrderNumber(): void
    {
        $a = VamregHelper::out('2031-02-10', ['out_quantity_pack_count' => 1.5, 'vet' => 2]);
        $b = VamregHelper::out('2031-02-11', ['out_quantity_type' => 'UNITS', 'out_quantity_pack_count' => null,
            'out_quantity_unit_count' => 2.5, 'out_quantity_unit' => 'ML', 'indication' => null, 'target_species' => 'CAT', 'vet' => 2]);
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [['id' => 'o1'], ['id' => 'o2']])]);

        $this->h->controller->post_all_out(2031, 1);

        $this->assertSame(200, $this->h->answer()['status']);
        $body = $this->h->client->body();
        $this->assertSame('OUT', $body[0]['register']);
        $this->assertSame('DOG', $body[0]['targetSpecies']);
        $this->assertSame('RESP', $body[0]['indication']);
        $this->assertSame('123456', $body[0]['veterinarianOrderNumber']);
        $this->assertSame('PACKS', $body[0]['outQuantityType']);
        $this->assertEqualsWithDelta(1.5, $body[0]['outQuantityPackCount'], 0.001);
        $this->assertNull($body[0]['outQuantityUnitCount']);
        $this->assertNull($body[0]['outQuantityUnit']);
        $this->assertSame('CAT', $body[1]['targetSpecies']);
        $this->assertNull($body[1]['indication']);
        $this->assertEqualsWithDelta(2.5, $body[1]['outQuantityUnitCount'], 0.001);
        $this->assertSame('ML', $body[1]['outQuantityUnit']);
        $this->assertSame('SENT', $this->rowOf('vamreg_out_buffer', $a)['status']);
        $this->assertSame('o2', $this->rowOf('vamreg_out_buffer', $b)['api_declaration_id']);
    }

    #[Group('known-bug')]
    public function testAShortAnswerStillMarksEveryRowSentWithoutADeclarationId(): void
    {
        // KNOWN BUG: ids are matched by position; a missing id gives SENT with api_declaration_id NULL
        $a = VamregHelper::in('2031-02-10');
        $b = VamregHelper::in('2031-02-11');
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [['id' => 'only']])]);

        $this->h->controller->post_all(2031, 1);

        $this->assertSame('only', $this->rowOf('vamreg_in_buffer', $a)['api_declaration_id']);
        $rb = $this->rowOf('vamreg_in_buffer', $b);
        $this->assertSame('SENT', $rb['status']);
        $this->assertNull($rb['api_declaration_id']);
    }

    #[Group('known-bug')]
    public function testA400AnswerPicksTheMessageByErrorPositionNotByDeclaration(): void
    {
        // KNOWN BUG: the message of declaration $idx is read from response[$idx] (the idx-th error)
        $a = VamregHelper::in('2031-02-10');
        $b = VamregHelper::in('2031-02-11');
        $this->h->client->answers([VamregModelsRecordingClient::answer(400, [
            ['field' => 'declarations[1].cnk', 'message' => 'about-one'],
            ['field' => 'declarations[0].cnk', 'englishMessage' => 'about-zero'],
        ])]);

        $this->h->controller->post_all(2031, 1);

        $this->assertSame(400, $this->h->answer()['status']);
        $ra = $this->rowOf('vamreg_in_buffer', $a);
        $rb = $this->rowOf('vamreg_in_buffer', $b);
        $this->assertSame('ERROR', $ra['status']);
        $this->assertSame('ERROR', $rb['status']);
        // swapped: row a (declaration 0) gets error 0, which is about declaration 1
        $this->assertSame('about-one', $ra['api_error']);
        $this->assertSame("Error in field 'declarations[0].cnk': about-zero", $rb['api_error']);
        $this->assertNotNull($ra['sent_at']);
    }

    #[Group('known-bug')]
    public function testA400AnswerWithOneErrorForTheSecondRowIsATypeError(): void
    {
        // KNOWN BUG: response[1] does not exist, null goes to get_vamreg_error_message(array) = TypeError (a 500 over HTTP)
        $a = VamregHelper::in('2031-02-10');
        $b = VamregHelper::in('2031-02-11');
        $this->h->client->answers([VamregModelsRecordingClient::answer(400, [['field' => 'declarations[1].cnk', 'message' => 'x']])]);

        try {
            $this->h->controller->post_all(2031, 1);
            $this->fail('expected a TypeError');
        } catch (TypeError) {
            $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $a)['status']);
            $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $b)['status']);
        }
    }

    #[Group('known-bug')]
    public function testA400AnswerWithoutADeclarationIndexChangesNothing(): void
    {
        // KNOWN BUG: errors that do not name declarations[n] leave every row DRAFT, the answer is still 400
        $a = VamregHelper::in('2031-02-10');
        $this->h->client->answers([VamregModelsRecordingClient::answer(400, [['message' => 'general'], ['field' => 'declarations[9].cnk', 'message' => 'far']])]);

        $this->h->controller->post_all(2031, 1);

        $this->assertSame(400, $this->h->answer()['status']);
        $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $a)['status']);
    }

    public function testA403AndOtherAnswersChangeNothing(): void
    {
        $a = VamregHelper::in('2031-02-10');

        foreach ([[403, 403], [500, 500], [502, 500], [404, 500]] as [$http, $expected]) {
            $this->h->client->answers([VamregModelsRecordingClient::answer($http, [])]);
            $this->h->controller->post_all(2031, 1);
            $this->assertSame($expected, $this->h->answer()['status'], "http $http");
            $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $a)['status']);
        }
    }

    #[Group('known-bug')]
    public function testAConnectionErrorAnswersFiveHundredAfterAnUndefinedKeyWarning(): void
    {
        // KNOWN BUG: the client answers ['error' => ...] without http_code; the controller reads it anyway
        $a = VamregHelper::in('2031-02-10');
        $this->h->client->answers([['error' => 'Connection refused']]);
        $warnings = 0;
        set_error_handler(static function () use (&$warnings): bool { $warnings++; return true; });
        try {
            $this->h->controller->post_all(2031, 1);
        } finally {
            restore_error_handler();
        }

        $this->assertSame(500, $this->h->answer()['status']);
        $this->assertGreaterThanOrEqual(1, $warnings);
        $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $a)['status']);
    }

    public function testWithoutAQuarterTheCurrentQuarterIsSent(): void
    {
        $today = VamregHelper::in(date('Y-m-d'));
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [['id' => 'now']])]);

        $this->h->controller->post_all();

        $this->assertSame('SENT', $this->rowOf('vamreg_in_buffer', $today)['status']);
    }

    #[Group('known-bug')]
    public function testTheClientIsBuiltWithTheProductionFlagFromAnArrayThatIsAlwaysTruthy(): void
    {
        // KNOWN BUG: conf['vamreg_prod'] is ['value' => ...], so even base64('0') selects production;
        // and refresh() reads another setting (vamreg_push). Nothing is sent: there are no drafts.
        $read = function (): array {
            $client = get_instance()->vamregclient;
            $r = new ReflectionClass(Vamregclient::class);
            return [$r->getProperty('apiKey')->getValue($client), $r->getProperty('prod')->getValue($client)];
        };

        $this->h->release();
        $this->h->controller->post_all(2031, 1);
        [$key, $prod] = $read();
        $this->assertSame('test-key', $key);
        $this->assertFalse($prod);

        $this->h->release();
        $this->h->controller->conf['vamreg_prod'] = VamregHarness::setting('0');
        $this->h->controller->post_all(2031, 1);
        [, $prod] = $read();
        $this->assertSame(['value' => base64_encode('0'), 'updated_at' => null, 'created_at' => null], $prod);
        $this->assertSame(-1, $this->h->answer()['status']);
    }

    // ---- T285 reset --------------------------------------------------------------------------

    #[Group('known-bug')]
    public function testResetInBranchAlwaysResetsBecauseItComparesTheCodeToAString(): void
    {
        // KNOWN BUG: http_code is an int, "!== '200'" is always true, so a failed DELETE also resets the row
        $ok = VamregHelper::in('2031-02-10', ['status' => 'SENT', 'api_declaration_id' => 'dec-ok', 'sent_at' => '2031-04-01 10:00:00', 'api_error' => 'e']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [])]);
        $printed = $this->h->capture(fn () => $this->h->controller->reset());

        $this->assertSame('DELETE', $this->h->client->requests[0]['method']);
        $this->assertSame('/declaration/dec-ok', $this->h->client->requests[0]['path']);
        $row = $this->rowOf('vamreg_in_buffer', $ok);
        $this->assertSame('DRAFT', $row['status']);
        $this->assertNull($row['api_declaration_id']);
        $this->assertNull($row['sent_at']);
        $this->assertNull($row['api_error']);
        $this->assertStringNotContainsString('Failed to delete', $printed);

        $bad = VamregHelper::in('2031-02-11', ['status' => 'SENT', 'api_declaration_id' => 'dec-bad']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(500, [])]);
        $printed = $this->h->capture(fn () => $this->h->controller->reset());

        $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $bad)['status']);
        $this->assertNull($this->rowOf('vamreg_in_buffer', $bad)['api_declaration_id']);
        $this->assertStringNotContainsString('Failed to delete', $printed);
    }

    #[Group('known-bug')]
    public function testResetOutBranchIsInverted(): void
    {
        // KNOWN BUG: OUT resets the row when DELETE failed and reports "Failed" when DELETE worked
        $ok = VamregHelper::out('2031-02-10', ['status' => 'SENT', 'api_declaration_id' => 'out-ok']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(200, [])]);
        $printed = $this->h->capture(fn () => $this->h->controller->reset());

        $row = $this->rowOf('vamreg_out_buffer', $ok);
        $this->assertSame('SENT', $row['status']);
        $this->assertSame('out-ok', $row['api_declaration_id']);
        $this->assertStringContainsString('Failed to delete declaration ID out-ok', $printed);

        $bad = VamregHelper::out('2031-02-11', ['status' => 'SENT', 'api_declaration_id' => 'out-bad']);
        $this->h->client->answers([VamregModelsRecordingClient::answer(500, [])]);
        $this->h->capture(fn () => $this->h->controller->reset());

        $this->assertSame('DRAFT', $this->rowOf('vamreg_out_buffer', $bad)['status']);
        $this->assertNull($this->rowOf('vamreg_out_buffer', $bad)['api_declaration_id']);
    }

    public function testResetOnlyDeletesRowsWithADeclarationIdAndTurnsErrorsIntoDrafts(): void
    {
        $noId = VamregHelper::in('2031-02-10', ['status' => 'DRAFT']);
        $invalid = VamregHelper::in('2031-02-11', ['status' => 'INVALID', 'api_declaration_id' => 'keep-me']);
        $error = VamregHelper::in('2031-02-12', ['status' => 'ERROR']);
        $outError = VamregHelper::out('2031-02-12', ['status' => 'ERROR']);

        $this->h->capture(fn () => $this->h->controller->reset());

        $this->assertCount(0, $this->h->client->requests);
        $this->assertSame('INVALID', $this->rowOf('vamreg_in_buffer', $invalid)['status']);
        $this->assertSame('keep-me', $this->rowOf('vamreg_in_buffer', $invalid)['api_declaration_id']);
        $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $error)['status']);
        $this->assertSame('DRAFT', $this->rowOf('vamreg_out_buffer', $outError)['status']);
        $this->assertSame('DRAFT', $this->rowOf('vamreg_in_buffer', $noId)['status']);
    }

    #[Group('known-bug')]
    public function testResetWithEmptyBuffersLoopsOverFalse(): void
    {
        // KNOWN BUG: get_all() returns false for no rows and reset() foreach-es over it (PHP warning, once per register)
        $this->h->capture(fn () => $this->h->controller->reset());

        $this->assertCount(2, $this->h->warnings);
        $this->assertStringContainsString('foreach()', $this->h->warnings[0]);
        $this->assertCount(0, $this->h->client->requests);
    }
}
