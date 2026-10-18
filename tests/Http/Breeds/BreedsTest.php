<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T075-T079 - Breeds controller (list, detail, add, edit, autocomplete, merge, rebuild frequencies).
 * Admin checks of edit/merge are covered by T039 (AdminChecksInsideVetControllersTest); here the
 * normal behaviour and data effects. Every test creates its own breeds (unique names), because the
 * rebuild resets the frequency of every breed and tests of one class share the database.
 */
final class BreedsTest extends HttpTestCase
{
    private function completeBreed(array $override = []): array
    {
        return $override + [
            'submit' => 1,
            'name' => Factory::unique('Newbreed'),
            'type' => 1,
            'male_min_weight' => '3.5',
            'male_max_weight' => '5.5',
            'female_min_weight' => '2.5',
            'female_max_weight' => '4.5',
        ];
    }

    // ---- T075 -------------------------------------------------------------------------------

    public function testTheListShowsEveryBreedWithItsPetCount(): void
    {
        $breed = Factory::breed(['name' => 'Countable Zq']);
        $owner = Factory::owner();
        Factory::pet($owner, ['breed' => $breed]);
        Factory::pet($owner, ['breed' => $breed]);
        Factory::pet($owner, ['breed' => $breed, 'death' => 1]);
        $empty = Factory::breed(['name' => 'Emptybreed Zq']);
        $this->loginAs('vet');

        $page = $this->get('breeds');

        $this->assertStatus($page, 200);
        // the list does not filter dead or lost pets ("it will filter the breeds"): 3 pets in total
        $this->assertMatchesRegularExpression(
            '#Countable Zq</td>\s*<td><a href="[^"]*breeds/index/' . $breed . '">3</a>#',
            (string) $page->getBody()
        );
        $this->assertMatchesRegularExpression(
            '#Emptybreed Zq</td>\s*<td><a href="[^"]*breeds/index/' . $empty . '">0</a>#',
            (string) $page->getBody()
        );
        $this->assertSee($page, 'Labrador');
        $this->assertSee($page, 'Europese korthaar');
    }

    public function testTheDetailListsAliveNotLostPetsWithTheOwnersStreetAndCity(): void
    {
        $breed = Factory::breed(['name' => 'Detailbreed']);
        $owner = Factory::owner(['street' => 'Detailstraat', 'city' => 'Detailstad']);
        $alive = Factory::pet($owner, ['breed' => $breed, 'name' => 'Aliveone']);
        Factory::pet($owner, ['breed' => $breed, 'name' => 'Deadone', 'death' => 1]);
        Factory::pet($owner, ['breed' => $breed, 'name' => 'Lostone', 'lost' => 1]);
        $otherBreed = Factory::pet($owner, ['breed' => 4, 'name' => 'Otherbreedone']);
        $this->loginAs('vet');

        $page = $this->get('breeds/index/' . $breed);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Aliveone');
        $this->assertStringContainsString('pets/fiche/' . $alive, (string) $page->getBody());
        $this->assertStringContainsString('owners/detail/' . $owner, (string) $page->getBody());
        $this->assertSee($page, 'Detailstraat');
        $this->assertSee($page, 'Detailstad');
        $this->assertDontSee($page, 'Deadone');
        $this->assertDontSee($page, 'Lostone');
        $this->assertDontSee($page, 'Otherbreedone');
        // "only showing alive, not lost : 1 / 3"
        $this->assertMatchesRegularExpression('#:\s*1\s*/\s*3\b#', $this->text($page));
    }

    // ---- T076 -------------------------------------------------------------------------------

    public function testAVetCanAddABreedAndItStartsWithFrequencyZero(): void
    {
        $this->loginAs('vet');
        $form = $this->completeBreed(['name' => 'Addedbreed Zq']);

        $this->assertStatus($this->get('breeds/add'), 200);
        $response = $this->post('breeds/add', $form);

        $this->assertRedirectsTo($response, 'breeds');
        $row = $this->row("SELECT * FROM breeds WHERE name = 'Addedbreed Zq'");
        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row['type']);
        $this->assertSame(0, (int) $row['freq']);
        $this->assertEqualsWithDelta(3.5, (float) $row['male_min_weight'], 0.001);
        $this->assertEqualsWithDelta(5.5, (float) $row['male_max_weight'], 0.001);
        $this->assertEqualsWithDelta(2.5, (float) $row['female_min_weight'], 0.001);
        $this->assertEqualsWithDelta(4.5, (float) $row['female_max_weight'], 0.001);
        $this->assertGreaterThan(0, $this->logCount('add_breed'));
    }

    public function testPostingWithoutSubmitOnlyShowsTheForm(): void
    {
        $this->loginAs('vet');
        $before = $this->countWhere('breeds');

        $response = $this->post('breeds/add', ['name' => 'Nosubmit Zq', 'type' => 0]);

        $this->assertStatus($response, 200);
        $this->assertSame($before, $this->countWhere('breeds'));
    }

    public function testAnAdminCanEditABreedButTheFrequencyIsKept(): void
    {
        $breed = Factory::breed(['name' => 'Beforeedit', 'freq' => 7, 'type' => 0]);
        $this->loginAs('admin');

        $response = $this->post('breeds/edit/' . $breed, [
            'submit' => 1, 'name' => 'Afteredit', 'type' => 1, 'freq' => 99,
            'male_min_weight' => '6', 'male_max_weight' => '7',
            'female_min_weight' => '4', 'female_max_weight' => '5',
        ]);

        $this->assertStatus($response, 200);
        $row = $this->find('breeds', $breed);
        $this->assertSame('Afteredit', $row['name']);
        $this->assertSame(1, (int) $row['type']);
        $this->assertSame(7, (int) $row['freq'], 'freq is not editable');
        $this->assertEqualsWithDelta(7.0, (float) $row['male_max_weight'], 0.001);
        $this->assertSee($response, 'Afteredit');
    }

    #[Group('known-bug')]
    public function testEditingAMissingBreedIsAServerError(): void
    {
        // KNOWN BUG: $current_breed['type'] is read from `false` - decide in CI4 whether to answer 404
        $this->loginAs('admin');

        $this->assertStatus($this->get('breeds/edit/999999'), 500);
    }

    // ---- T077 -------------------------------------------------------------------------------

    public function testSearchReturnsMatchingBreedsOrderedByFrequency(): void
    {
        $low = Factory::breed(['name' => 'Srchzq low', 'freq' => 1, 'type' => 0]);
        $high = Factory::breed(['name' => 'Srchzq high', 'freq' => 9, 'type' => 0]);
        $cat = Factory::breed(['name' => 'Srchzq cat', 'freq' => 50, 'type' => 1]);
        $this->loginAs('vet');

        $dogs = $this->json($this->get('breeds/search_breed/srchzq', ['type' => 0]));
        $this->assertSame(
            [['id' => (string) $high, 'text' => 'Srchzq high'], ['id' => (string) $low, 'text' => 'Srchzq low']],
            $dogs['results']
        );

        $cats = $this->json($this->get('breeds/search_breed/srchzq', ['type' => 1]));
        $this->assertSame([['id' => (string) $cat, 'text' => 'Srchzq cat']], $cats['results']);

        $all = $this->json($this->get('breeds/search_breed/srchzq'));
        $this->assertSame(
            [(string) $cat, (string) $high, (string) $low],
            array_column($all['results'], 'id'),
            'without type every type is searched, highest frequency first'
        );
    }

    #[Group('known-bug')]
    public function testSearchWithoutAMatchHasAnEmptyBody(): void
    {
        // KNOWN BUG: `return 0` instead of an empty result list - decide in CI4 whether to keep
        $this->loginAs('vet');

        $response = $this->get('breeds/search_breed/qqqnomatchqqq', ['type' => 0]);

        $this->assertStatus($response, 200);
        $this->assertSame('', (string) $response->getBody());
    }

    // ---- T078 -------------------------------------------------------------------------------

    #[Group('known-bug')]
    public function testMergingMovesThePetsDeletesTheOldBreedButNotTheSecondBreed(): void
    {
        $old = Factory::breed(['name' => 'Mergeold']);
        $new = Factory::breed(['name' => 'Mergenew']);
        $owner = Factory::owner();
        $pet = Factory::pet($owner, ['breed' => $old]);
        $mixed = Factory::pet($owner, ['breed' => Fixtures::BREED_UNKNOWN, 'breed2' => $old]);
        $this->loginAs('admin');

        $response = $this->post('breeds/merge/' . $old, ['new_breed' => $new]);

        $this->assertRedirectsTo($response, 'breeds');
        $this->assertSame($new, (int) $this->find('pets', $pet)['breed']);
        $this->assertRowMissing('breeds', ['id' => $old]);
        $this->assertRowExists('breeds', ['id' => $new]);
        // KNOWN BUG: pets.breed2 still points to the deleted breed - decide in CI4 whether to keep
        $this->assertSame($old, (int) $this->find('pets', $mixed)['breed2']);
        $this->assertGreaterThan(0, $this->logCount('merge_breeds'));
    }

    public function testMergingABreedIntoItselfIsRefused(): void
    {
        $breed = Factory::breed(['name' => 'Mergeself']);
        $pet = Factory::pet(Factory::owner(), ['breed' => $breed]);
        $this->loginAs('admin');

        $response = $this->post('breeds/merge/' . $breed, ['new_breed' => $breed]);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'You are trying to merge the same breed');
        $this->assertRowExists('breeds', ['id' => $breed]);
        $this->assertSame($breed, (int) $this->find('pets', $pet)['breed']);
    }

    // ---- T079 -------------------------------------------------------------------------------

    public function testRebuildSetsTheFrequencyOfBreedsWithMoreThanFiveRecentAlivePets(): void
    {
        $x = Factory::breed(['name' => 'Freqx', 'freq' => 3]);
        $five = Factory::breed(['name' => 'Freqfive', 'freq' => 3]);
        $y = Factory::breed(['name' => 'Freqy', 'freq' => 3]);
        $stale = Factory::breed(['name' => 'Freqstale', 'freq' => 40]);
        $owner = Factory::owner();
        for ($i = 0; $i < 6; $i++) {
            Factory::pet($owner, ['breed' => $x]);
        }
        // do not count: dead, lost, older than 5 years
        Factory::pet($owner, ['breed' => $x, 'death' => 1]);
        Factory::pet($owner, ['breed' => $x, 'lost' => 1]);
        Factory::pet($owner, ['breed' => $x, 'birth' => date('Y-m-d', strtotime('-9 years'))]);
        for ($i = 0; $i < 5; $i++) {
            Factory::pet($owner, ['breed' => $five]);
        }
        for ($i = 0; $i < 2; $i++) {
            Factory::pet($owner, ['breed' => $y]);
        }
        // breed 1 (unknown) is never given a frequency, however many pets
        for ($i = 0; $i < 8; $i++) {
            Factory::pet($owner, ['breed' => Fixtures::BREED_UNKNOWN]);
        }
        $this->loginAs('vet');

        $response = $this->get('breeds/rebuild_frequenty');

        $this->assertStatus($response, 200);
        $this->assertSame(6, (int) $this->find('breeds', $x)['freq']);
        $this->assertSame(0, (int) $this->find('breeds', $five)['freq'], 'exactly 5 is not enough');
        $this->assertSame(0, (int) $this->find('breeds', $y)['freq']);
        $this->assertSame(0, (int) $this->find('breeds', $stale)['freq'], 'every other breed is reset');
        $this->assertSame(0, (int) $this->find('breeds', Fixtures::BREED_UNKNOWN)['freq']);
        $this->assertGreaterThan(0, $this->logCount('rebuild_freq'));
    }
}
