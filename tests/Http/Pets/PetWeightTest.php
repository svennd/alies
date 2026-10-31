<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T064 - weight endpoints of Pets. Source: application/controllers/Pets.php, views/pets/weight_history.php.
 *
 * push_weight (AJAX) and add_weight both insert a pets_weight row and set last_weight when weight > 0.
 * history_weight: the controller sorts ascending (the chart gets that order), the table shows newest first.
 * del_weight hard-deletes the row.
 */
final class PetWeightTest extends HttpTestCase
{
    private function weights(int $pet): array
    {
        return array_map('floatval', array_column($this->rows('SELECT weight FROM pets_weight WHERE pets = ? ORDER BY id', [$pet]), 'weight'));
    }

    public function testPushWeightInsertsARowAndUpdatesTheLastWeight(): void
    {
        $pet = Factory::pet(Factory::owner(), ['last_weight' => 5.00]);
        $this->loginAs('vet');

        $response = $this->ajaxPost('pets/push_weight/' . $pet, ['weight' => '7.25']);

        $this->assertStatus($response, 200);
        $this->assertSame([7.25], $this->weights($pet));
        $this->assertEqualsWithDelta(7.25, (float) $this->find('pets', $pet)['last_weight'], 0.001);
    }

    public function testPushWeightWithZeroOrNothingChangesNothing(): void
    {
        $pet = Factory::pet(Factory::owner(), ['last_weight' => 5.00]);
        $this->loginAs('vet');

        $this->ajaxPost('pets/push_weight/' . $pet, ['weight' => '0']);
        $this->ajaxPost('pets/push_weight/' . $pet, []);
        $this->ajaxPost('pets/push_weight/' . $pet, ['weight' => '-3']);

        $this->assertSame([], $this->weights($pet));
        $this->assertEqualsWithDelta(5.0, (float) $this->find('pets', $pet)['last_weight'], 0.001);
        // positive control: a real weight works for the same pet
        $this->ajaxPost('pets/push_weight/' . $pet, ['weight' => '6']);
        $this->assertSame([6.0], $this->weights($pet));
    }

    public function testAddWeightInsertsAndRedirectsToTheHistory(): void
    {
        $pet = Factory::pet(Factory::owner(), ['last_weight' => 5.00]);
        $this->loginAs('vet');

        $response = $this->post('pets/add_weight/' . $pet, ['submit' => '1', 'weight' => '8.5']);

        $this->assertRedirectsTo($response, 'pets/history_weight/' . $pet);
        $this->assertSame([8.5], $this->weights($pet));
        $this->assertEqualsWithDelta(8.5, (float) $this->find('pets', $pet)['last_weight'], 0.001);
    }

    public function testAddWeightNeedsSubmitAndAPositiveWeightButStillRedirects(): void
    {
        $pet = Factory::pet(Factory::owner(), ['last_weight' => 5.00]);
        $this->loginAs('vet');

        $noSubmit = $this->post('pets/add_weight/' . $pet, ['weight' => '9']);
        $zero = $this->post('pets/add_weight/' . $pet, ['submit' => '1', 'weight' => '0']);

        $this->assertRedirectsTo($noSubmit, 'pets/history_weight/' . $pet);
        $this->assertRedirectsTo($zero, 'pets/history_weight/' . $pet);
        $this->assertSame([], $this->weights($pet));
        $this->assertEqualsWithDelta(5.0, (float) $this->find('pets', $pet)['last_weight'], 0.001);
    }

    public function testTheHistoryPageListsTheWeightsAndTheNames(): void
    {
        $owner = Factory::owner(['last_name' => 'Weightowner']);
        $pet = Factory::pet($owner, ['name' => 'Weightpet']);
        Factory::petWeight($pet, 11.10, date('Y-m-d H:i:s', strtotime('-30 days')));
        Factory::petWeight($pet, 12.20, date('Y-m-d H:i:s', strtotime('-20 days')));
        Factory::petWeight($pet, 13.30, date('Y-m-d H:i:s', strtotime('-10 days')));
        $this->loginAs('vet');

        $page = $this->get('pets/history_weight/' . $pet);

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Weightowner');
        $this->assertSee($page, 'Weightpet');
        $body = (string) $page->getBody();
        // the chart data is in ascending (oldest first) order
        $this->assertMatchesRegularExpression('/data: \["11\.10","12\.20","13\.30"\]/', $body);
        // the table shows the newest first
        $text = $this->text($page);
        $this->assertLessThan(strpos($text, '12.20'), strpos($text, '13.30'));
        $this->assertLessThan(strpos($text, '11.10'), strpos($text, '12.20'));
    }

    public function testDeleteWeightRemovesTheRowForGoodAndRedirects(): void
    {
        $pet = Factory::pet(Factory::owner());
        $keep = Factory::petWeight($pet, 3.0);
        $gone = Factory::petWeight($pet, 4.0);
        $this->loginAs('vet');

        $response = $this->get('pets/del_weight/' . $gone . '/' . $pet);

        $this->assertRedirectsTo($response, 'pets/history_weight/' . $pet);
        $this->assertRowMissing('pets_weight', ['id' => $gone]);
        $this->assertRowExists('pets_weight', ['id' => $keep]);
    }

    #[Group('known-bug')]
    public function testLastWeightIsNotRecalculatedAfterADelete(): void
    {
        // KNOWN BUG: deleting the newest weight leaves pets.last_weight on the deleted value, and a GET deletes - decide in CI4 whether to keep
        $pet = Factory::pet(Factory::owner(), ['last_weight' => 4.00]);
        Factory::petWeight($pet, 3.0, date('Y-m-d H:i:s', strtotime('-5 days')));
        $newest = Factory::petWeight($pet, 4.0);
        $this->loginAs('vet');

        $this->get('pets/del_weight/' . $newest . '/' . $pet);

        $this->assertEqualsWithDelta(4.0, (float) $this->find('pets', $pet)['last_weight'], 0.001);
    }
}
