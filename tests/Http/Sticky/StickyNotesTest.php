<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T090 - Sticky notes (Sticky controller).
 *
 * Rules: `add` stores the escaped text with the user and the current location (and answers with an
 * empty body); the list shows every sticky (private ones too); `delete` soft-deletes: a vet only
 * his own notes, an admin any note; both end on the list.
 */
final class StickyNotesTest extends HttpTestCase
{
    private function sticky(int $user, string $note, array $extra = []): int
    {
        return Factory::insert('sticky', $extra + [
            'user_id' => $user,
            'location' => Fixtures::LOCATION_PRACTICE,
            'note' => $note,
            'private' => 0,
            'created_at' => Factory::raw('NOW()'),
            'updated_at' => Factory::raw('NOW()'),
        ]);
    }

    public function testAddingAStickyStoresTheEscapedTextTheUserAndTheLocation(): void
    {
        $this->loginAs('vet');
        $before = $this->countWhere('sticky');

        $response = $this->post('sticky/add', ['content' => '<b>x</b> unique1', 'private' => 1]);

        $this->assertStatus($response, 200);
        $this->assertSame('', trim((string) $response->getBody()));
        $this->assertSame($before + 1, $this->countWhere('sticky'));
        $row = $this->row("SELECT * FROM sticky WHERE note LIKE '%unique1'");
        $this->assertNotNull($row);
        $this->assertSame('&lt;b&gt;x&lt;/b&gt; unique1', $row['note']);
        $this->assertSame(Fixtures::USER_VET, (int) $row['user_id']);
        $this->assertSame(Fixtures::LOCATION_PRACTICE, (int) $row['location']);
        $this->assertSame(1, (int) $row['private']);
    }

    public function testTheLocationIsTheOneTheUserIsWorkingAt(): void
    {
        $this->loginAs('vet');
        $this->get('welcome/change_location/' . Fixtures::LOCATION_CAR);

        $this->post('sticky/add', ['content' => 'from the car unique2']);

        $row = $this->row("SELECT * FROM sticky WHERE note = 'from the car unique2'");
        $this->assertNotNull($row);
        $this->assertSame(Fixtures::LOCATION_CAR, (int) $row['location']);
        $this->assertSame(0, (int) $row['private'], 'private defaults to 0');
    }

    public function testAnEmptyContentStoresNothing(): void
    {
        $this->loginAs('vet');
        $before = $this->countWhere('sticky');

        $this->post('sticky/add', ['content' => '']);
        $this->post('sticky/add');

        $this->assertSame($before, $this->countWhere('sticky'));
    }

    #[Group('known-bug')]
    public function testThePrivateFlagDoesNotHideAStickyFromOtherUsers(): void
    {
        // KNOWN BUG: private stickies are listed to everyone - decide in CI4 whether to keep
        $this->sticky(Fixtures::USER_VET2, 'secret note unique3', ['private' => 1]);
        $this->sticky(Fixtures::USER_VET2, 'public note unique4');
        $this->loginAs('vet');

        $page = $this->get('sticky');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'public note unique4');
        $this->assertSee($page, 'secret note unique3');
        $this->assertSee($page, 'Test sticky', 'the seeded note');
    }

    public function testADeletedStickyDisappearsFromTheList(): void
    {
        $id = $this->sticky(Fixtures::USER_VET, 'to be removed unique5', ['deleted_at' => Factory::raw('NOW()')]);
        $this->loginAs('vet');

        $this->assertDontSee($this->get('sticky'), 'to be removed unique5');
        $this->assertNotNull($this->find('sticky', $id)['deleted_at']);
    }

    public function testAVetSoftDeletesHisOwnStickyOnly(): void
    {
        $own = $this->sticky(Fixtures::USER_VET, 'own unique6');
        $foreign = $this->sticky(Fixtures::USER_VET2, 'foreign unique7');
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('sticky/delete/' . $own), 'sticky');
        $this->assertNotNull($this->find('sticky', $own)['deleted_at'], 'positive control: own note is deleted');

        $this->assertRedirectsTo($this->get('sticky/delete/' . $foreign), 'sticky');
        $this->assertNull($this->find('sticky', $foreign)['deleted_at'], 'someone else\'s note stays');
    }

    public function testAnAdminSoftDeletesAnyStickyAndTheRowIsKept(): void
    {
        $foreign = $this->sticky(Fixtures::USER_VET2, 'foreign unique8');
        $this->loginAs('admin');

        $this->assertRedirectsTo($this->get('sticky/delete/' . $foreign), 'sticky');

        $row = $this->find('sticky', $foreign);
        $this->assertNotNull($row, 'soft delete, not a hard delete');
        $this->assertNotNull($row['deleted_at']);
    }
}
