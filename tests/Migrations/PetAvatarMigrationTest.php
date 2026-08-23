<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once BASEPATH . 'libraries/Migration.php';
require_once APPPATH . 'migrations/052_pet_avatar.php';

final class PetAvatarMigrationTest extends TestCase
{
    public function testMigrationAddsAndRemovesOnlyTheAvatarColumn(): void
    {
        $ci = get_instance();
        $database = $ci->db;
        $fake = new PetAvatarMigrationFakeDatabase();
        $ci->db = $fake;

        try {
            $migration = new Migration_pet_avatar();
            $before = $fake->rows;

            $this->assertSame('052', $migration->up());
            $this->assertTrue($fake->field_exists('avatar', 'pets'));
            $this->assertSame($before, $fake->rows);

            $this->assertSame('051', $migration->down());
            $this->assertFalse($fake->field_exists('avatar', 'pets'));
            $this->assertSame($before, $fake->rows);
        } finally {
            $ci->db = $database;
        }
    }
}

final class PetAvatarMigrationFakeDatabase
{
    public array $rows = [['id' => 1, 'name' => 'Existing pet']];
    private array $fields = [];

    public function field_exists(string $field, string $table): bool
    {
        return $table === 'pets' && !empty($this->fields[$field]);
    }

    public function query(string $sql): bool
    {
        if (stripos($sql, 'ADD `avatar`') !== false) {
            $this->fields['avatar'] = true;
        }
        if (stripos($sql, 'DROP `avatar`') !== false) {
            unset($this->fields['avatar']);
        }
        return true;
    }
}
