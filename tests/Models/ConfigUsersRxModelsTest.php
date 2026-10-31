<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T363 Config_model, T364 Users_model::get_active_vets, T365 Rx_model.
 * Sources: application/models/{Config,Users,Rx}_model.php
 *
 * Rules in plain words:
 *  - Config_model::store(name, value) keeps base64(value) in config.value and a second store of the same name
 *    updates that row; get_value(name) decodes it and gives '' for an unknown name (with a PHP notice).
 *  - get_active_vets: active users in group 3 (hard coded), exactly the columns id, username, first_name,
 *    last_name, last_login, image.
 *  - Rx_model::get_images groups the rx rows of a pet per studydate, newest first, with comma separated fields.
 */
final class ConfigUsersRxModelsTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
    }

    private function configRows(string $name): array
    {
        return $this->ci->db->where('name', $name)->get('config')->result_array();
    }

    // ------------------------------------------------------------------ T363

    public function testStoreSavesTheValueBase64Encoded(): void
    {
        $config = $this->model('Config_model', 'config_t363');
        $name = Factory::unique('cfg');

        $this->assertTrue($config->store($name, 'waarde é'));

        $rows = $this->configRows($name);
        $this->assertCount(1, $rows);
        $this->assertSame(base64_encode('waarde é'), $rows[0]['value']);
        $this->assertNotSame('waarde é', $rows[0]['value']);
        $this->assertNotNull($rows[0]['created_at']);
        $this->assertNull($rows[0]['updated_at']);
    }

    public function testASecondStoreUpdatesTheSameRow(): void
    {
        $config = $this->model('Config_model', 'config_t363');
        $name = Factory::unique('cfg');
        $config->store($name, 'een');
        $first = $this->configRows($name)[0];

        $config->store($name, 'twee');

        $rows = $this->configRows($name);
        $this->assertCount(1, $rows);
        $this->assertSame($first['id'], $rows[0]['id']);
        $this->assertSame(base64_encode('twee'), $rows[0]['value']);
        $this->assertNotNull($rows[0]['updated_at']);
    }

    public function testGetValueDecodesTheStoredValue(): void
    {
        $config = $this->model('Config_model', 'config_t363');
        $name = Factory::unique('cfg');
        $config->store($name, 'v');
        $other = Factory::unique('cfg');
        $config->store($other, 'w');

        $this->assertSame('v', $config->get_value($name));
        $this->assertSame('w', $config->get_value($other));
    }

    #[Group('known-bug')]
    public function testGetValueOfAMissingKeyIsAnEmptyStringWithANotice(): void
    {
        // KNOWN BUG: reading $result['value'] of a missing row raises a PHP warning - decide in CI4
        $config = $this->model('Config_model', 'config_t363');
        $messages = array();
        set_error_handler(function (int $no, string $msg) use (&$messages) {
            $messages[] = $msg;

            return true;
        });
        try {
            $value = $config->get_value(Factory::unique('missing'));
        } finally {
            restore_error_handler();
        }

        $this->assertSame('', $value);
        $this->assertNotEmpty($messages, 'a notice or warning is raised');
        $this->assertStringContainsString('offset', implode(' ', $messages));
    }

    public function testANameWithSpacesCanBeStoredAndRead(): void
    {
        $config = $this->model('Config_model', 'config_t363');
        $name = Factory::unique('cfg') . ' met spatie';
        $config->store($name, 'x');

        $this->assertSame('x', $config->get_value($name));
    }

    // ------------------------------------------------------------------ T364

    private function user(string $first, bool $active, ?int $group): int
    {
        $id = Factory::insert('users', array(
            'ip_address' => '127.0.0.1', 'username' => Factory::unique('u'), 'password' => 'x',
            'email' => Factory::unique('m') . '@test.local', 'created_on' => time(),
            'last_login' => 1700000000, 'active' => $active ? 1 : 0, 'first_name' => $first, 'last_name' => 'Achter',
            'current_location' => 0, 'image' => 'img.png', 'sidebar' => '#000000', 'order_nr' => '',
        ));
        if ($group !== null) {
            Factory::insert('users_groups', array('user_id' => $id, 'group_id' => $group));
        }

        return $id;
    }

    public function testGetActiveVetsReturnsActiveUsersOfGroupThreeOnly(): void
    {
        $users = $this->model('Users_model', 'users_t364');
        $vet = $this->user('Dierenarts', true, Fixtures::GROUP_VET);
        $inactive = $this->user('Inactief', false, Fixtures::GROUP_VET);
        $member = $this->user('Lid', true, Fixtures::GROUP_MEMBERS);
        $none = $this->user('Zonder', true, null);

        $ids = array_map('intval', array_column($users->get_active_vets(), 'id'));

        $this->assertContains($vet, $ids);
        $this->assertNotContains($inactive, $ids);
        $this->assertNotContains($member, $ids);
        $this->assertNotContains($none, $ids);
        // the seeded active vets are there too
        $this->assertContains(Fixtures::USER_VET, $ids);
        $this->assertNotContains(Fixtures::USER_INACTIVE, $ids);
        $this->assertNotContains(Fixtures::USER_ADMIN, $ids);
    }

    public function testGetActiveVetsHasExactlyTheDocumentedColumns(): void
    {
        $users = $this->model('Users_model', 'users_t364');
        $vet = $this->user('Kolom', true, Fixtures::GROUP_VET);

        $rows = array_column($users->get_active_vets(), null, 'id');

        $row = $rows[$vet];
        $this->assertSame(array('id', 'username', 'first_name', 'last_name', 'last_login', 'image'), array_keys($row));
        $this->assertSame('Kolom', $row['first_name']);
        $this->assertSame('Achter', $row['last_name']);
        $this->assertSame('1700000000', (string) $row['last_login']);
        $this->assertSame('img.png', $row['image']);
    }

    // ------------------------------------------------------------------ T365

    private function rx(int $pet, string $date, string $description, string $body, ?int $series): int
    {
        return Factory::insert('rx', array(
            'path' => Factory::unique('path/') . '.dcm', 'pet_id' => $pet, 'studydate' => $date,
            'description' => $description, 'bodypart' => $body, 'studydescription' => 'sd-' . $description,
            'series' => $series, 'created_at' => Factory::raw('NOW()'),
        ));
    }

    public function testGetImagesGroupsPerStudyDateNewestFirst(): void
    {
        $rx = $this->model('Rx_model', 'rx_t365');
        $pet = Factory::pet(Factory::owner());
        $this->rx($pet, '2024-03-01', 'oud', 'poot', 1);
        $this->rx($pet, '2025-05-05', 'nieuw1', 'borst', 1);
        $this->rx($pet, '2025-05-05', 'nieuw2', 'buik', 2);
        $this->rx(Factory::pet(Factory::owner()), '2025-05-05', 'andere', 'x', 9); // other pet

        $groups = $rx->get_images($pet);

        $this->assertCount(2, $groups);
        $this->assertSame('2025-05-05', $groups[0]['study_date']);
        $this->assertSame('2024-03-01', $groups[1]['study_date']);

        $newest = $groups[0];
        $this->assertSame(array('images', 'study_date', 'description', 'bodypart', 'study_description', 'series'), array_keys($newest));
        $this->assertCount(2, explode(',', $newest['images']));
        $this->assertEqualsCanonicalizing(array('nieuw1', 'nieuw2'), explode(',', $newest['description']));
        $this->assertEqualsCanonicalizing(array('borst', 'buik'), explode(',', $newest['bodypart']));
        $this->assertEqualsCanonicalizing(array('sd-nieuw1', 'sd-nieuw2'), explode(',', $newest['study_description']));
        $this->assertEqualsCanonicalizing(array('1', '2'), explode(',', $newest['series']));

        $this->assertSame('oud', $groups[1]['description']);
        $this->assertSame('poot', $groups[1]['bodypart']);
    }

    public function testGetImagesOfAPetWithoutRxIsEmpty(): void
    {
        $rx = $this->model('Rx_model', 'rx_t365');

        $this->assertSame(array(), $rx->get_images(Factory::pet(Factory::owner())));
    }

    public function testCountAndHasImagesForPet(): void
    {
        $rx = $this->model('Rx_model', 'rx_t365');
        $pet = Factory::pet(Factory::owner());
        $empty = Factory::pet(Factory::owner());
        $this->rx($pet, '2025-01-01', 'a', 'b', null);
        $this->rx($pet, '2025-01-02', 'a', 'b', null);

        $this->assertSame(2, $rx->count_for_pet($pet));
        $this->assertTrue($rx->has_images_for_pet($pet));
        $this->assertSame(0, $rx->count_for_pet($empty));
        $this->assertFalse($rx->has_images_for_pet($empty));
    }
}
