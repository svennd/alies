<?php

declare(strict_types=1);

/**
 * T068 - the medical history block of the pet fiche. Source: Events_model::get_pet_history, views/pets/fiche/block_history.php.
 *
 * Events with no_history = 1 are hidden; newest first; each entry has its products and procedures,
 * the number of uploads and linked (non-deleted, same pet) labs, and its vets without duplicates.
 * (Model level: tests/Models/PetMedicalHistoryModelTest.php.)
 */
final class PetMedicalHistoryTest extends HttpTestCase
{
    /** Number shown inside the link that points at $anchor of the given event. */
    private function badge(string $body, int $event, string $anchor): ?int
    {
        $pattern = '#href="[^"]*events/event/' . $event . '\#' . preg_quote($anchor, '#') . '"[^>]*>(.*?)</a>#s';
        if (!preg_match($pattern, $body, $m)) {
            return null;
        }

        return (int) trim(strip_tags(preg_replace('#<span class="sr-only">.*?</span>#s', '', $m[1])));
    }

    private function upload(int $event, string $name): void
    {
        Factory::insert('events_upload', [
            'event' => $event, 'filename' => $name, 'size' => 10, 'mime' => 'text/plain', 'comment' => '',
            'user' => Fixtures::USER_VET, 'location' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function testHiddenEventsAreLeftOutAndTheRestIsNewestFirst(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::event($pet, 1, 4, ['title' => 'Oldest history', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))]);
        Factory::event($pet, 1, 4, ['title' => 'Middle history', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);
        Factory::event($pet, 1, 4, ['title' => 'Newest history', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 days'))]);
        Factory::event($pet, 1, 4, ['title' => 'Secret history', 'no_history' => 1, 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))]);
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $pet);

        $text = $this->text($page);
        $this->assertDontSee($page, 'Secret history');
        $newest = strpos($text, 'Newest history');
        $middle = strpos($text, 'Middle history');
        $oldest = strpos($text, 'Oldest history');
        $this->assertNotFalse($newest);
        $this->assertNotFalse($middle);
        $this->assertNotFalse($oldest);
        $this->assertLessThan($middle, $newest);
        $this->assertLessThan($oldest, $middle);
    }

    public function testAPetWithoutEventsShowsTheEmptyState(): void
    {
        $empty = Factory::pet(Factory::owner());
        $full = Factory::pet(Factory::owner());
        Factory::event($full, 1, 4, ['title' => 'Some visit']);
        $this->loginAs('vet');

        $emptyBody = (string) $this->get('pets/fiche/' . $empty)->getBody();
        $fullBody = (string) $this->get('pets/fiche/' . $full)->getBody();

        $this->assertStringNotContainsString('<article', $emptyBody);
        $this->assertStringContainsString('<article', $fullBody);
    }

    public function testEachEntryListsItsProductsAndProcedures(): void
    {
        $pet = Factory::pet(Factory::owner());
        $event = Factory::event($pet, 1, 4, ['title' => 'Treated', 'anamnese' => 'Rapport met tekst']);
        $product = Factory::product(['name' => 'Historyproduct', 'unit_sell' => 'ml']);
        $procedure = Factory::procedure(['name' => 'Historyprocedure']);
        Factory::eventProduct($event, $product, 2.5);
        Factory::eventProcedure($event, $procedure, 1.0);
        $bare = Factory::event($pet, 1, 4, ['title' => 'Bare visit']);
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $pet);

        $this->assertSee($page, '2.50 ml Historyproduct');
        $this->assertSee($page, '1.00 Historyprocedure');
        $this->assertSee($page, 'Rapport met tekst');
        // each article holds only its own lines
        preg_match_all('#<article.*?</article>#s', (string) $page->getBody(), $articles);
        $this->assertCount(2, $articles[0]);
        foreach ($articles[0] as $article) {
            if (str_contains($article, 'Bare visit')) {
                $this->assertStringNotContainsString('Historyproduct', $article);
                $this->assertStringNotContainsString('Historyprocedure', $article);
            } else {
                $this->assertStringContainsString('Historyproduct', $article);
            }
        }
        $this->assertNotNull($bare);
    }

    public function testUploadAndLabCountsAreShownPerEvent(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);
        $other = Factory::pet($owner);
        $event = Factory::event($pet, 1, 4, ['title' => 'With files']);
        $plain = Factory::event($pet, 1, 4, ['title' => 'Plain visit']);
        $this->upload($event, 'a-' . uniqid() . '.txt');
        $this->upload($event, 'b-' . uniqid() . '.txt');
        $this->upload($event, 'c-' . uniqid() . '.txt');
        $labOk = Factory::labReport($pet);
        $labOk2 = Factory::labReport($pet);
        $labDeleted = Factory::labReport($pet, [], ['deleted_at' => date('Y-m-d H:i:s')]);
        $labOtherPet = Factory::labReport($other);
        foreach ([$labOk, $labOk2, $labDeleted, $labOtherPet] as $lab) {
            Factory::insert('events_labs', ['event_id' => $event, 'lab_id' => $lab]);
        }
        $this->loginAs('vet');

        $body = (string) $this->get('pets/fiche/' . $pet)->getBody();

        $this->assertSame(3, $this->badge($body, $event, 'files'), 'three uploads');
        $this->assertSame(2, $this->badge($body, $event, 'event-lab-results'), 'only labs of this pet that are not deleted');
        $this->assertNull($this->badge($body, $plain, 'files'));
        $this->assertNull($this->badge($body, $plain, 'event-lab-results'));
    }

    public function testVetNamesAreListedOnceAndMissingVetsShowUnknown(): void
    {
        $pet = Factory::pet(Factory::owner());
        $this->loginAs('vet');
        // the same vet as main and first support, plus another one as second support
        Factory::event($pet, 1, 4, ['title' => 'Two vets', 'vet' => Fixtures::USER_VET, 'vet_support_1' => Fixtures::USER_VET, 'vet_support_2' => Fixtures::USER_VET2]);
        Factory::event($pet, 1, 4, ['title' => 'No vets', 'vet' => 0, 'vet_support_1' => 0, 'vet_support_2' => 0]);

        $page = $this->get('pets/fiche/' . $pet);

        preg_match_all('#<article.*?</article>#s', (string) $page->getBody(), $articles);
        $this->assertCount(2, $articles[0]);
        foreach ($articles[0] as $article) {
            $vets = trim(preg_replace('/\s+/', ' ', strip_tags(preg_match('#pet-history__vets">(.*?)</span>#s', $article, $m) ? $m[1] : '')));
            if (str_contains($article, 'Two vets')) {
                $this->assertSame('Victor, Veerle', $vets);
            } else {
                $this->assertSame('unknown', $vets);
            }
        }
    }

    public function testTheLocationAndTheTypeAndUnfinishedMarkAreShown(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::event($pet, 1, 4, ['title' => 'Op visit', 'type' => 1, 'location' => Fixtures::LOCATION_CAR, 'report' => 2]);
        Factory::event($pet, 1, 4, ['title' => 'Open report visit', 'type' => 0, 'report' => 0]);
        $this->loginAs('vet');

        $page = $this->get('pets/fiche/' . $pet);

        $carName = (string) $this->scalar('SELECT name FROM stock_location WHERE id = ?', [Fixtures::LOCATION_CAR]);
        preg_match_all('#<article.*?</article>#s', (string) $page->getBody(), $articles);
        foreach ($articles[0] as $article) {
            if (str_contains($article, 'Op visit')) {
                $this->assertStringContainsString($carName, $article);
                $this->assertStringContainsString('data-history-type="1"', $article);
                $this->assertStringNotContainsString('fa-unlock', $article, 'a finished report has no unlock icon');
            } else {
                $this->assertStringContainsString('data-history-type="0"', $article);
                $this->assertStringContainsString('fa-unlock', $article, 'an unfinished report is marked');
            }
        }
    }
}
