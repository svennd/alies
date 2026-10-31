<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T311 - Tools::merge_clients/{A}/{B} (admin only).
 *
 * Rules: owner A is disabled and gets msg "merge_id_disable:{B}:{timestamp}" (an existing msg is
 * overwritten); every pet of A is transferred to B the way Pets_model::transfer_pet does it (the
 * source pet is hidden, a copy is made for B); then redirect to tools/duplicate_clients. It is a
 * plain GET with no confirmation.
 */
final class MergeClientsTest extends HttpTestCase
{
    public function testMergingDisablesTheFirstOwnerAndMovesItsPetsToTheSecond(): void
    {
        $a = Factory::owner(['msg' => 'oude opmerking']);
        $b = Factory::owner();
        $chip = '9813' . random_int(10000000000, 99999999999);
        $rex = Factory::pet($a, ['name' => 'Mergerex', 'chip' => $chip]);
        $mimi = Factory::pet($a, ['name' => 'Mergemimi']);
        $own = Factory::pet($b, ['name' => 'Alreadyhere']);
        $this->loginAs('admin');

        $response = $this->get('tools/merge_clients/' . $a . '/' . $b);

        $this->assertRedirectsTo($response, 'tools/duplicate_clients');
        $owner = $this->find('owners', $a);
        $this->assertSame(1, (int) $owner['disabled']);
        $this->assertMatchesRegularExpression('/^merge_id_disable:' . $b . ':\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $owner['msg'], 'the old msg is overwritten');
        $this->assertSame(0, (int) $this->find('owners', $b)['disabled']);

        foreach ([$rex, $mimi] as $source) {
            $row = $this->find('pets', $source);
            $this->assertSame(1, (int) $row['transfered'], 'source hidden');
            $this->assertSame('[transfer:send:' . $b . ']', $row['note']);
            $this->assertNull($row['chip']);
        }
        $this->assertRowCount(1, 'pets', ['owner' => $b, 'name' => 'Mergerex', 'transfered' => 0]);
        $this->assertRowCount(1, 'pets', ['owner' => $b, 'name' => 'Mergemimi', 'transfered' => 0]);
        $this->assertSame($chip, $this->row('SELECT chip FROM pets WHERE owner = ? AND name = ?', [$b, 'Mergerex'])['chip'], 'the chip moves to the copy');
        $this->assertSame(0, (int) $this->find('pets', $own)['transfered'], 'pets of B untouched');
        $this->assertRowCount(0, 'pets', ['owner' => $a, 'transfered' => 0]);
    }

    public function testAnOwnerWithoutPetsIsJustDisabled(): void
    {
        $a = Factory::owner();
        $b = Factory::owner();
        $this->loginAs('admin');

        $response = $this->get('tools/merge_clients/' . $a . '/' . $b);

        $this->assertRedirectsTo($response, 'tools/duplicate_clients');
        $this->assertSame(1, (int) $this->find('owners', $a)['disabled']);
    }

    public function testAlreadyTransferredPetsAreNotMovedAgain(): void
    {
        $a = Factory::owner();
        $b = Factory::owner();
        $gone = Factory::pet($a, ['name' => 'Alreadygone', 'transfered' => 1, 'note' => '[transfer:send:1002]']);
        $live = Factory::pet($a, ['name' => 'Stillthere']);
        $this->loginAs('admin');

        $this->get('tools/merge_clients/' . $a . '/' . $b);

        $this->assertSame('[transfer:send:1002]', $this->find('pets', $gone)['note'], 'left as it was');
        $this->assertSame(1, (int) $this->find('pets', $live)['transfered']);
        $this->assertRowCount(1, 'pets', ['owner' => $b, 'name' => 'Stillthere']);
        $this->assertRowCount(0, 'pets', ['owner' => $b, 'name' => 'Alreadygone']);
    }

    #[Group('known-bug')]
    public function testMergingAnOwnerWithItselfDisablesItAndMovesNothing(): void
    {
        // KNOWN BUG: no check that A != B. The owner is disabled first, so the transfer then refuses
        // ("target owner is missing or disabled") and the pets stay: a plain GET disables a client
        $a = Factory::owner();
        $pet = Factory::pet($a);
        $this->loginAs('admin');

        $response = $this->get('tools/merge_clients/' . $a . '/' . $a);

        $this->assertRedirectsTo($response, 'tools/duplicate_clients');
        $owner = $this->find('owners', $a);
        $this->assertSame(1, (int) $owner['disabled']);
        $this->assertStringStartsWith('merge_id_disable:' . $a . ':', $owner['msg']);
        $this->assertSame(0, (int) $this->find('pets', $pet)['transfered']);
        $this->assertSame($a, (int) $this->find('pets', $pet)['owner']);
    }

    #[Group('known-bug')]
    public function testMergingIntoADisabledOrMissingOwnerStillDisablesTheFirst(): void
    {
        // KNOWN BUG: the first owner is disabled before the target is checked; the pets stay behind
        $a = Factory::owner();
        $pet = Factory::pet($a);
        $disabledTarget = Factory::owner(['disabled' => 1]);
        $this->loginAs('admin');

        $this->get('tools/merge_clients/' . $a . '/' . $disabledTarget);

        $this->assertSame(1, (int) $this->find('owners', $a)['disabled']);
        $this->assertSame(0, (int) $this->find('pets', $pet)['transfered']);

        $b = Factory::owner();
        $petB = Factory::pet($b);
        $this->get('tools/merge_clients/' . $b . '/99999999');
        $this->assertSame(1, (int) $this->find('owners', $b)['disabled']);
        $this->assertSame(0, (int) $this->find('pets', $petB)['transfered']);
    }

    #[Group('known-bug')]
    public function testMergeNeedsNoConfirmationOrPost(): void
    {
        // KNOWN BUG: a state changing GET (a link prefetch or a crawler is enough)
        $a = Factory::owner();
        $b = Factory::owner();
        $this->loginAs('admin');

        $response = $this->get('tools/merge_clients/' . $a . '/' . $b);

        $this->assertSame(1, (int) $this->find('owners', $a)['disabled']);
        $this->assertContains($response->getStatusCode(), [200, 302, 303, 307]);
    }

    public function testOnlyAdminsMayMerge(): void
    {
        $a = Factory::owner();
        $b = Factory::owner();
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('tools/merge_clients/' . $a . '/' . $b), '');
        $this->assertSame(0, (int) $this->find('owners', $a)['disabled']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->get('tools/merge_clients/' . $a . '/' . $b);
        $this->assertSame(1, (int) $this->find('owners', $a)['disabled'], 'positive control');
    }
}
