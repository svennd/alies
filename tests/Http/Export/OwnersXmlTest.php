<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T261 - export/owners/{from}/{to} (Export::owners, admin only).
 *
 * Rules: an owner is exported when created_at OR updated_at lies strictly after "from 00:00" and
 * strictly before "to 23:59" (so exactly 00:00:00 on the first day and anything from 23:59:00 on the
 * last day are missed); soft-deleted owners ARE exported (known bug); empty fields (NULL, '') are left out
 * but "0" is kept; no owner in the window gives <xml><owners></owners></xml>.
 *
 * Golden file tests/Fixtures/golden/golden_export_owners.xml was written by hand from the column list
 * of Export::owners and checked by eye; {OWNER_A}/{OWNER_B} stand for the generated ids.
 */
final class OwnersXmlTest extends HttpTestCase
{
    private const FROM = '2016-05-01';
    private const TO = '2016-05-31';

    /** @var array<string, int> */
    private static array $owners = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$owners['a'] = Factory::owner([
            'first_name' => 'Anna', 'last_name' => 'Golden', 'telephone' => '031234567', 'mobile' => '0470111222',
            'phone2' => null, 'phone3' => '', 'street' => 'Dorpsstraat', 'nr' => '12', 'zip' => '2018', 'city' => 'Antwerpen',
            'mail' => 'anna@example.test', 'msg' => '0', 'btw_nr' => null, 'invoice_addr' => null, 'invoice_contact' => '',
            'invoice_tel' => null, 'debts' => 0, 'low_budget' => 1, 'contact' => 1, 'last_bill' => '2016-04-30',
            'created_at' => '2016-05-02 10:00:00', 'updated_at' => '2016-05-02 10:00:00',
        ]);
        // created before the window, updated inside it
        self::$owners['b'] = Factory::owner([
            'first_name' => 'Bedrijf', 'last_name' => 'Silver', 'telephone' => '0312223334', 'mobile' => '0475333444',
            'phone2' => '0311111111', 'phone3' => '0322222222', 'street' => 'Kerkstraat', 'nr' => '3B', 'zip' => '2000',
            'city' => 'Antwerpen', 'mail' => 'info@silver.test', 'msg' => 'Let op', 'btw_nr' => 'BE0123456789',
            'invoice_addr' => 'Postbus 5', 'invoice_contact' => 'Boekhouding', 'invoice_tel' => '0319998887', 'debts' => 1,
            'low_budget' => 0, 'contact' => 2, 'last_bill' => '2016-05-20',
            'created_at' => '2015-12-31 10:00:00', 'updated_at' => '2016-05-15 09:30:00',
        ]);
        // outside: both dates before, both after, exactly at the opening, at the closing minute; plus a deleted one that is exported anyway
        self::$owners['before'] = Factory::owner(['created_at' => '2016-04-30 23:59:00', 'updated_at' => '2016-04-30 23:59:00']);
        self::$owners['after'] = Factory::owner(['created_at' => '2016-06-01 00:01:00', 'updated_at' => '2016-06-01 00:01:00']);
        self::$owners['opening'] = Factory::owner(['created_at' => '2016-05-01 00:00:00', 'updated_at' => '2016-05-01 00:00:00']);
        self::$owners['closing'] = Factory::owner(['created_at' => '2016-05-31 23:59:30', 'updated_at' => '2016-05-31 23:59:30']);
        self::$owners['deleted'] = Factory::owner(['created_at' => '2016-05-10 10:00:00', 'updated_at' => '2016-05-10 10:00:00', 'deleted_at' => '2016-05-11 10:00:00']);
        // inside, with an ampersand
        self::$owners['amp'] = Factory::owner([
            'last_name' => 'Pieters & Zoon', 'street' => 'Kerk & Zoon straat', 'nr' => '9',
            'created_at' => '2016-05-20 10:00:00', 'updated_at' => '2016-05-20 10:00:00',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function export(string $from = self::FROM, string $to = self::TO): array
    {
        $response = $this->get('export/owners/' . $from . '/' . $to);
        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('text/xml', $response->getHeaderLine('Content-Type'));

        $xml = simplexml_load_string((string) $response->getBody());
        $this->assertNotFalse($xml, 'the export must be well formed XML');

        return [$xml, (string) $response->getBody()];
    }

    private function idsIn(SimpleXMLElement $xml): array
    {
        $ids = [];
        foreach ($xml->owners->owner as $owner) {
            $ids[] = (int) $owner->id;
        }

        return $ids;
    }

    public function testOwnersCreatedOrUpdatedInTheWindowAreExportedAndOthersAreNot(): void
    {
        [$xml] = $this->export();

        $ids = $this->idsIn($xml);
        $this->assertContains(self::$owners['a'], $ids, 'created in the window');
        $this->assertContains(self::$owners['b'], $ids, 'updated in the window');
        $this->assertContains(self::$owners['amp'], $ids);
        foreach (['before', 'after', 'opening', 'closing'] as $key) {
            $this->assertNotContains(self::$owners[$key], $ids, $key);
        }
        $this->assertCount(4, $ids, 'a, b, amp and the deleted owner (see the known bug below)');
    }

    public function testExportMatchesTheGoldenFile(): void
    {
        [, $body] = $this->export();
        $document = simplexml_load_string($body);

        // keep only the two golden owners (the third one has the ampersand cases, tested separately)
        $golden = new DOMDocument('1.0');
        $root = $golden->appendChild($golden->createElement('xml'));
        $owners = $root->appendChild($golden->createElement('owners'));
        foreach ($document->owners->owner as $owner) {
            if (in_array((int) $owner->id, [self::$owners['a'], self::$owners['b']], true)) {
                $owners->appendChild($golden->importNode(dom_import_simplexml($owner), true));
            }
        }
        $actual = trim($golden->saveXML());

        $expected = trim(file_get_contents(__DIR__ . '/../../Fixtures/golden/golden_export_owners.xml'));
        $expected = str_replace(['{OWNER_A}', '{OWNER_B}'], [(string) self::$owners['a'], (string) self::$owners['b']], $expected);

        $this->assertSame($expected, $actual);
    }

    #[Group('known-bug')]
    public function testSoftDeletedOwnersAreExportedToo(): void
    {
        // KNOWN BUG: Export::get_owners does not filter deleted_at (Owners_model::get_all keeps deleted owners) — decide in CI4 whether to keep
        [$xml] = $this->export();

        $this->assertContains(self::$owners['deleted'], $this->idsIn($xml));
    }

    public function testEmptyFieldsAreLeftOutButZeroIsKept(): void
    {
        [$xml] = $this->export();
        $owner = null;
        foreach ($xml->owners->owner as $candidate) {
            if ((int) $candidate->id === self::$owners['a']) {
                $owner = $candidate;
            }
        }

        $this->assertNotNull($owner);
        foreach (['phone2', 'phone3', 'btw_nr', 'invoice_addr', 'invoice_contact', 'invoice_tel'] as $empty) {
            $this->assertCount(0, $owner->{$empty}, $empty . ' is NULL or empty and must not be exported');
        }
        $this->assertSame('0', (string) $owner->debts);
        $this->assertSame('0', (string) $owner->msg);
    }

    public function testAnEmptyWindowGivesAnEmptyOwnersElement(): void
    {
        $response = $this->get('export/owners/2010-01-01/2010-01-02');

        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('text/xml', $response->getHeaderLine('Content-Type'));
        $this->assertSame("<?xml version='1.0'?><xml><owners></owners></xml>", (string) $response->getBody());
    }

    public function testNamesAreEscapedButTheStreetWithAnAmpersandLosesItsValue(): void
    {
        [$xml] = $this->export();
        $owner = null;
        foreach ($xml->owners->owner as $candidate) {
            if ((int) $candidate->id === self::$owners['amp']) {
                $owner = $candidate;
            }
        }

        $this->assertNotNull($owner);
        $this->assertSame('Pieters & Zoon', (string) $owner->last_name, 'last_name goes through htmlspecialchars');
        // KNOWN BUG: street is not escaped; DOMDocument::createElement() cuts the value at the bare "&" — decide in CI4 whether to keep
        $this->assertSame('', (string) $owner->street);
        $this->assertSame('9', (string) $owner->nr, 'the other elements are untouched');
    }

    #[Group('known-bug')]
    public function testTheStreetWithAnAmpersandIsExportedEmpty(): void
    {
        // KNOWN BUG: see above; the element is written as an empty <street/> — decide in CI4 whether to keep
        [, $body] = $this->export();

        $this->assertStringContainsString('<street/><nr>9</nr>', $body);
    }

    public function testOnlyAdminsMayExport(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');

        $response = $this->get('export/owners/' . self::FROM . '/' . self::TO);

        $this->assertRedirectsTo($response, '');
        $this->assertStringNotContainsString('<owner>', (string) $response->getBody());
    }
}
