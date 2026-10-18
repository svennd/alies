<?php

declare(strict_types=1);

/**
 * T235 - Rx list page (rx/list/{pet}).
 * Source: application/controllers/Rx.php, Rx_model::get_images, views/rx/index.php
 *
 * The images of a pet are grouped by study date, newest date first; each group lists its images by description
 * and links the stored file (data/stored/rx/<path>.jpg). A pet without images gets an "No images found" notice.
 * The seed has 3 rx rows for Rex (2 on 2024-01-10, 1 on 2024-03-02); other pets get their own rows here.
 */
final class RxListTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    public function testTheSeededImagesAreGroupedByStudyDateNewestFirst(): void
    {
        $page = $this->get('rx/list/' . Fixtures::PET_REX);

        $this->assertStatus($page, 200);
        $text = $this->text($page);
        $this->assertSame(1, preg_match('/2024-03-02.*Abdomen LL.*2024-01-10.*Thorax/s', $text), 'two groups, newest first');
        $body = (string) $page->getBody();
        $this->assertSame(1, substr_count($body, '<strong>2024-03-02</strong>'));
        $this->assertSame(1, substr_count($body, '<strong>2024-01-10</strong>'), 'both January images share one group');
        $this->assertSame(2, preg_match_all('/<strong>\d{4}-\d\d-\d\d<\/strong>/', $body), 'exactly two groups');
        foreach (['Abdomen LL', 'Thorax LL', 'Thorax VD'] as $description) {
            $this->assertSee($page, $description);
        }
        $this->assertSee($page, 'Janssens', 'owner in the header');
    }

    public function testEveryImageLinksTheStoredFileAndTheNewestIsShownFirst(): void
    {
        // the importer stores the path WITHOUT extension, the view appends .jpg
        $pet = Factory::pet(Factory::owner());
        $dir = 'rxlink_' . uniqid();
        foreach ([['2025-02-01', 'old'], ['2025-06-01', 'new']] as [$date, $name]) {
            Factory::insert('rx', [
                'path' => $dir . '/' . $name, 'pet_id' => $pet, 'studydate' => $date, 'description' => 'D-' . $name,
                'bodypart' => 'X', 'series' => 1, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
            ]);
        }

        $body = (string) $this->get('rx/list/' . $pet)->getBody();

        $base = rtrim(self::baseUrl(), '/') . '/data/stored/rx/' . $dir;
        $this->assertStringContainsString('data-image="' . $base . '/old.jpg"', $body);
        $this->assertStringContainsString('data-image="' . $base . '/new.jpg"', $body);
        $this->assertStringContainsString('<img src="' . $base . '/new.jpg"', $body, 'the viewer starts with the newest image');
    }

    public function testOnlyTheImagesOfThatPetAreShown(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::insert('rx', [
            'path' => 'rxlist_' . uniqid() . '/one', 'pet_id' => $pet, 'studydate' => '2025-05-05',
            'description' => 'Eigen opname', 'bodypart' => 'KNEE', 'series' => 1,
            'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()'),
        ]);

        $page = $this->get('rx/list/' . $pet);

        $this->assertSee($page, 'Eigen opname');
        $this->assertSee($page, '2025-05-05');
        $this->assertDontSee($page, 'Thorax LL');
        $this->assertDontSee($this->get('rx/list/' . Fixtures::PET_REX), 'Eigen opname');
    }

    public function testAPetWithoutImagesGetsANotice(): void
    {
        $page = $this->get('rx/list/' . Factory::pet(Factory::owner()));

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'No images found');
    }
}
