<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T262 - export/pets[/{days}] (Export::pets, admin only).
 *
 * Rules: without a number of days every pet that is not soft deleted is exported; with {days} only pets
 * whose created_at or updated_at lies in the last {days} days (and before now); empty fields are
 * omitted, "0" is kept; every pet element has the documented children in a fixed order; an empty
 * result is <xml><pets></pets></xml>.
 */
final class PetsXmlTest extends HttpTestCase
{
    /** @var array<string, int> */
    private static array $pets = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $owner = Factory::owner();
        $old = date('Y-m-d H:i:s', strtotime('-40 days'));
        $recent = date('Y-m-d H:i:s', strtotime('-3 days'));

        self::$pets['full'] = Factory::pet($owner, [
            'name' => 'Fullpet', 'type' => 1, 'birth' => '2015-02-03', 'death' => 1, 'death_date' => '2020-01-02', 'breed' => 5,
            'gender' => 1, 'color' => 'grijs', 'last_weight' => 4.25, 'lost' => 0, 'chip' => '981000000000777',
            'nr_vac_book' => 'VB-12', 'note' => 'a note', 'nutritional_advice' => 'less', 'created_at' => $recent, 'updated_at' => $recent,
        ]);
        self::$pets['sparse'] = Factory::pet($owner, [
            'name' => 'Sparsepet', 'death_date' => null, 'chip' => null, 'note' => '', 'nr_vac_book' => null, 'created_at' => $recent, 'updated_at' => $recent,
        ]);
        self::$pets['updated'] = Factory::pet($owner, ['name' => 'Updatedpet', 'created_at' => $old, 'updated_at' => $recent]);
        self::$pets['old'] = Factory::pet($owner, ['name' => 'Oldpet', 'created_at' => $old, 'updated_at' => $old]);
        self::$pets['deleted'] = Factory::pet($owner, ['name' => 'Deletedpet', 'created_at' => $recent, 'updated_at' => $recent, 'deleted_at' => $recent]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function export(string $path): SimpleXMLElement
    {
        $response = $this->get($path);
        $this->assertStatus($response, 200);
        $this->assertStringStartsWith('text/xml', $response->getHeaderLine('Content-Type'));
        $xml = simplexml_load_string((string) $response->getBody());
        $this->assertNotFalse($xml, 'well formed XML');

        return $xml;
    }

    /** @return list<int> */
    private function ids(SimpleXMLElement $xml): array
    {
        $ids = [];
        foreach ($xml->pets->pet as $pet) {
            $ids[] = (int) $pet->id;
        }

        return $ids;
    }

    private function pet(SimpleXMLElement $xml, int $id): SimpleXMLElement
    {
        foreach ($xml->pets->pet as $pet) {
            if ((int) $pet->id === $id) {
                return $pet;
            }
        }
        $this->fail('pet ' . $id . ' not exported');
    }

    public function testWithoutDaysEveryPetThatIsNotDeletedIsExported(): void
    {
        $expected = array_map('intval', array_column($this->rows('SELECT id FROM pets WHERE deleted_at IS NULL ORDER BY id'), 'id'));

        $ids = $this->ids($this->export('export/pets'));

        $this->assertSame($expected, $ids);
        $this->assertNotContains(self::$pets['deleted'], $ids);
        $this->assertContains(self::$pets['old'], $ids);
        $this->assertContains(Fixtures::PET_REX, $ids);
    }

    public function testWithDaysOnlyPetsCreatedOrUpdatedRecentlyAreExported(): void
    {
        $ids = $this->ids($this->export('export/pets/7'));

        $this->assertContains(self::$pets['full'], $ids);
        $this->assertContains(self::$pets['sparse'], $ids);
        $this->assertContains(self::$pets['updated'], $ids, 'updated 3 days ago');
        $this->assertNotContains(self::$pets['old'], $ids);
    }

    public function testTheWindowFollowsTheNumberOfDays(): void
    {
        $this->assertNotContains(self::$pets['old'], $this->ids($this->export('export/pets/30')));
        $this->assertContains(self::$pets['old'], $this->ids($this->export('export/pets/60')));
    }

    public function testEveryDocumentedChildIsWrittenInOrder(): void
    {
        $pet = $this->pet($this->export('export/pets'), self::$pets['full']);

        $names = [];
        foreach ($pet->children() as $child) {
            $names[] = $child->getName();
        }
        $this->assertSame(
            ['id', 'type', 'name', 'birth', 'death', 'death_date', 'breed', 'gender', 'color', 'last_weight', 'lost', 'chip', 'nr_vac_book', 'note', 'nutritional_advice', 'owner', 'location', 'init_vet'],
            $names,
            'lost is 0 and is kept; everything else is filled'
        );
        $this->assertSame('Fullpet', (string) $pet->name);
        $this->assertSame('1', (string) $pet->type);
        $this->assertSame('2015-02-03', (string) $pet->birth);
        $this->assertSame('2020-01-02', (string) $pet->death_date);
        $this->assertSame('4.25', (string) $pet->last_weight);
        $this->assertSame('981000000000777', (string) $pet->chip);
        $this->assertSame('VB-12', (string) $pet->nr_vac_book);
        $this->assertSame('a note', (string) $pet->note);
        $this->assertSame('less', (string) $pet->nutritional_advice);
        $this->assertSame((int) $this->find('pets', self::$pets['full'])['owner'], (int) $pet->owner);
    }

    public function testEmptyFieldsAreOmittedAndZeroIsKept(): void
    {
        $pet = $this->pet($this->export('export/pets'), self::$pets['sparse']);

        foreach (['death_date', 'chip', 'note', 'nr_vac_book', 'nutritional_advice'] as $empty) {
            $this->assertCount(0, $pet->{$empty}, $empty);
        }
        $this->assertSame('0', (string) $pet->death);
        $this->assertSame('0', (string) $pet->lost);
        $this->assertSame('0', (string) $pet->gender);
    }

    public function testAWindowWithoutPetsGivesAnEmptyPetsElement(): void
    {
        // pets created "now" are always recent, so age every pet for this test and restore the dates afterwards
        $snapshot = $this->rows('SELECT id, created_at, updated_at FROM pets');
        $this->db()->exec('UPDATE pets SET created_at = DATE_SUB(NOW(), INTERVAL 400 DAY), updated_at = DATE_SUB(NOW(), INTERVAL 400 DAY)');

        try {
            $response = $this->get('export/pets/7');
        } finally {
            $restore = $this->db()->prepare('UPDATE pets SET created_at = ?, updated_at = ? WHERE id = ?');
            foreach ($snapshot as $row) {
                $restore->execute([$row['created_at'], $row['updated_at'], $row['id']]);
            }
        }

        $this->assertSame("<?xml version='1.0'?><xml><pets></pets></xml>", (string) $response->getBody());
        $this->assertStringStartsWith('text/xml', $response->getHeaderLine('Content-Type'));
    }

    public function testOnlyAdminsMayExport(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');

        $response = $this->get('export/pets');

        $this->assertRedirectsTo($response, '');
        $this->assertStringNotContainsString('<pet>', (string) $response->getBody());
    }
}
