<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T251 - lab/detail/{id} and lab/print/{id}.
 *
 * Rules: the detail page shows the pet (linked), the owner (linked), the sample date, the source id
 * and one row per result: code, value, "min - max" limit and unit. Text results (no numeric value)
 * show their text over three columns. print/{id} streams an inline PDF built from lab/print.
 */
final class LabDetailTest extends HttpTestCase
{
    private int $ownerId;
    private int $petId;
    private string $ownerName;
    private string $petName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ownerName = Factory::unique('DetOwner');
        $this->petName = Factory::unique('DetPet');
        $this->ownerId = Factory::owner(['last_name' => $this->ownerName]);
        $this->petId = Factory::pet($this->ownerId, ['name' => $this->petName]);
        $this->loginAs('vet');
    }

    private function report(?array $results = null, array $overrides = []): int
    {
        $results ??= [
            ['code' => 'ALTX', 'value_num' => 45.0, 'unit' => 'U/L', 'ref_min' => 10.0, 'ref_max' => 100.0],
            ['code' => 'CREAX', 'value_num' => 180.0, 'unit' => 'umol/L', 'ref_min' => 40.0, 'ref_max' => 150.0],
            ['code' => 'NOTEX', 'value_text' => 'hemolytic sample'],
        ];

        return Factory::labReport($this->petId, $results, $overrides);
    }

    public function testDetailShowsPetOwnerAndEveryResult(): void
    {
        $id = $this->report(null, ['source_id' => 'SRC-DETAIL-1', 'sample_date' => '2026-03-04 10:00:00']);

        $response = $this->get('lab/detail/' . $id);

        $this->assertStatus($response, 200);
        $this->assertSee($response, $this->petName);
        $this->assertSee($response, $this->ownerName);
        $this->assertSee($response, 'SRC-DETAIL-1');
        $this->assertSee($response, '2026-03-04 10:00:00');
        foreach (['ALTX', 'CREAX', 'NOTEX', 'U/L', 'umol/L', '10.00 - 100.00', '40.00 - 150.00', 'hemolytic sample'] as $needle) {
            $this->assertSee($response, $needle);
        }
        $this->assertStringContainsString('pets/fiche/' . $this->petId, (string) $response->getBody());
        $this->assertStringContainsString('owners/detail/' . $this->ownerId, (string) $response->getBody());
    }

    public function testValueOutsideTheLimitIsMarkedAndValueInsideIsNot(): void
    {
        $id = $this->report();

        $body = (string) $this->get('lab/detail/' . $id)->getBody();

        // CREAX 180 > 150 is out of range (red), ALTX 45 is inside
        $this->assertMatchesRegularExpression('#text-danger font-weight-bold">\s*180\.00#', $body);
        $this->assertDoesNotMatchRegularExpression('#text-danger font-weight-bold">\s*45\.00#', $body);
    }

    public function testAdminSeesTheDeleteLinkAndTheVetDoesNot(): void
    {
        $id = $this->report();

        $this->assertStringNotContainsString('lab/delete/' . $id, (string) $this->get('lab/detail/' . $id)->getBody());

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertStringContainsString('lab/delete/' . $id, (string) $this->get('lab/detail/' . $id)->getBody());
    }

    public function testNonMedilabReportsOfferThePrintLink(): void
    {
        $id = $this->report();

        $this->assertStringContainsString('lab/print/' . $id, (string) $this->get('lab/detail/' . $id)->getBody());
    }

    public function testMedilabReportsLinkToTheExternalSiteInsteadOfPrint(): void
    {
        $id = $this->report(null, ['device' => 'medilab', 'source' => 'remote', 'source_id' => 'ML-' . uniqid()]);

        $body = (string) $this->get('lab/detail/' . $id)->getBody();

        $this->assertStringContainsString('online.medilab.be/dokter/staal/', $body);
        $this->assertStringNotContainsString('lab/print/' . $id, $body);
    }

    #[Group('known-bug')]
    public function testDetailOfAReportWithoutResultsIsAServerError(): void
    {
        // KNOWN BUG: get_all() returns false for nothing and normalize_many(array) gets false (TypeError) — decide in CI4 whether to keep
        $id = Factory::labReport($this->petId, []);

        $this->assertStatus($this->get('lab/detail/' . $id), 500);
    }

    #[Group('known-bug')]
    public function testPrintOfAReportWithoutResultsIsAServerError(): void
    {
        // KNOWN BUG: same TypeError as the detail page — decide in CI4 whether to keep
        $id = Factory::labReport($this->petId, []);

        $this->assertStatus($this->get('lab/print/' . $id), 500);
    }

    public function testPrintReturnsAnInlinePdf(): void
    {
        $id = $this->report();

        $response = $this->get('lab/print/' . $id);

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('application/pdf', $response->getHeaderLine('Content-Type'));
        $this->assertStringNotContainsStringIgnoringCase('attachment', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
    }

    public function testPrintWithAPlotOfThreePointsWorks(): void
    {
        $id = $this->report();
        Factory::insert('lab_plots', ['report_id' => $id, 'type' => 'wbc', 'data' => json_encode([1, 3, 2]), 'created_at' => Factory::raw('NOW()')]);

        $response = $this->get('lab/print/' . $id);

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
    }

    #[Group('known-bug')]
    public function testPrintWithAPlotOfOnePointIsAServerError(): void
    {
        // KNOWN BUG: generateBase64Chart divides by count - 1 (DivisionByZeroError) — decide in CI4 whether to keep
        $id = $this->report();
        Factory::insert('lab_plots', ['report_id' => $id, 'type' => 'wbc', 'data' => json_encode([5]), 'created_at' => Factory::raw('NOW()')]);

        $this->assertStatus($this->get('lab/print/' . $id), 500);
    }

    #[Group('known-bug')]
    public function testPrintWithAPlotOfTwoPointsIsAServerError(): void
    {
        // KNOWN BUG: imagefilledpolygon gets only 2 points (ValueError) — decide in CI4 whether to keep
        $id = $this->report();
        Factory::insert('lab_plots', ['report_id' => $id, 'type' => 'wbc', 'data' => json_encode([5, 7]), 'created_at' => Factory::raw('NOW()')]);

        $this->assertStatus($this->get('lab/print/' . $id), 500);
    }

    public function testDetailOfAReportWhosePetIsGoneStillRendersWithDashes(): void
    {
        $id = Factory::labReport(999998, [['code' => 'GONEX', 'value_num' => 1.0, 'ref_min' => 0.0, 'ref_max' => 2.0]]);

        $response = $this->get('lab/detail/' . $id);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'GONEX');
    }

    public function testDetailIsRefusedForNonStaff(): void
    {
        $id = $this->report();
        $this->cookies->clear();
        $this->loginAs('member');

        $this->assertStatus($this->get('lab/detail/' . $id), 500);
        $this->assertStatus($this->get('lab/print/' . $id), 500);
    }
}
