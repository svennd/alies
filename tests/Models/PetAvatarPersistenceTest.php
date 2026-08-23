<?php

declare(strict_types=1);

final class PetAvatarPersistenceTest extends CodeIgniterDatabaseTestCase
{
    public function testReplacementAlwaysReturnsTheLockedCurrentReference(): void
    {
        $pets = $this->model('Pets_model', 'avatar_pets_test');
        $petId = $this->petIds(1)[0];

        $this->ci->db->where('id', $petId)->update('pets', ['avatar' => 'pet_' . str_repeat('a', 32) . '.jpg']);
        $first = $pets->replace_avatar($petId, 'pet_' . str_repeat('b', 32) . '.jpg');
        $second = $pets->replace_avatar($petId, 'pet_' . str_repeat('c', 32) . '.jpg');

        $this->assertSame('pet_' . str_repeat('a', 32) . '.jpg', $first['previous']);
        $this->assertSame('pet_' . str_repeat('b', 32) . '.jpg', $second['previous']);
        $this->assertFalse($pets->replace_avatar(PHP_INT_MAX, null));
    }

    public function testReferenceCountIncludesTransferredAndSoftDeletedRows(): void
    {
        $pets = $this->model('Pets_model', 'avatar_reference_pets_test');
        [$firstId, $secondId] = $this->petIds(2);
        $shared = 'pet_' . str_repeat('d', 32) . '.jpg';

        $this->ci->db->where_in('id', [$firstId, $secondId])->update('pets', ['avatar' => $shared]);
        $this->ci->db->where('id', $secondId)->update('pets', ['deleted_at' => date('Y-m-d H:i:s'), 'transfered' => 1]);

        $this->assertSame(2, $pets->avatar_reference_count($shared));
        $pets->replace_avatar($firstId, null);
        $this->assertSame(1, $pets->avatar_reference_count($shared));
        $pets->replace_avatar($secondId, null);
        $this->assertSame(0, $pets->avatar_reference_count($shared));
    }

    public function testTransferCopiesAvatarAndLaterReplacementKeepsHistoricalReference(): void
    {
        $pets = $this->model('Pets_model', 'avatar_transfer_pets_test');
        $petId = $this->petIds(1)[0];
        $ownerIds = $this->ownerIds(2);
        $shared = 'pet_' . str_repeat('e', 32) . '.jpg';
        $replacement = 'pet_' . str_repeat('f', 32) . '.jpg';

        $this->ci->db->where('id', $petId)->update('pets', ['owner' => $ownerIds[0], 'avatar' => $shared]);
        $before = (int) $this->ci->db->select_max('id')->get('pets')->row()->id;
        $pets->transfer_pet($petId, $ownerIds[1]);

        $successor = $this->ci->db
            ->where('id >', $before)
            ->where('owner', $ownerIds[1])
            ->where('avatar', $shared)
            ->order_by('id', 'DESC')
            ->get('pets')
            ->row_array();

        $this->assertNotNull($successor);
        $pets->replace_avatar((int) $successor['id'], $replacement);
        $historical = $this->ci->db->select('avatar')->where('id', $petId)->get('pets')->row_array();

        $this->assertSame($shared, $historical['avatar']);
        $this->assertSame(1, $pets->avatar_reference_count($shared));
        $this->assertSame(1, $pets->avatar_reference_count($replacement));
    }

    private function petIds(int $limit): array
    {
        $rows = $this->ci->db
            ->select('id')
            ->where('deleted_at IS NULL', null, false)
            ->order_by('id', 'ASC')
            ->limit($limit)
            ->get('pets')
            ->result_array();
        $this->assertCount($limit, $rows, 'Not enough pet fixtures for avatar persistence tests.');
        return array_map('intval', array_column($rows, 'id'));
    }

    private function ownerIds(int $limit): array
    {
        $rows = $this->ci->db->select('id')->order_by('id', 'ASC')->limit($limit)->get('owners')->result_array();
        $this->assertCount($limit, $rows, 'Not enough owner fixtures for avatar transfer tests.');
        return array_map('intval', array_column($rows, 'id'));
    }
}
