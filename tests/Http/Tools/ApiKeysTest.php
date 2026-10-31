<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T312 - Tools::show_api_keys (admin only).
 *
 * Rules: create_key=1 with a device inserts an active api_keys row whose key_hash is a 64 character
 * hex SHA-256 of a random key; the plain key is generated and thrown away (never shown); the page
 * lists active keys only (as hash); revoke_key=1 with key_id sets active = 0.
 */
final class ApiKeysTest extends HttpTestCase
{
    private function createKey(string $device): ?array
    {
        $before = $this->rows('SELECT id FROM api_keys WHERE device = ?', [$device]);
        $this->post('tools/show_api_keys', ['create_key' => '1', 'device' => $device]);
        $after = $this->rows('SELECT * FROM api_keys WHERE device = ? ORDER BY id', [$device]);

        return count($after) === count($before) + 1 ? end($after) : null;
    }

    public function testCreatingAKeyInsertsAnActiveRowWithA64CharacterHexHash(): void
    {
        $this->loginAs('admin');
        $device = Factory::unique('ms4s2-');

        $row = $this->createKey($device);

        $this->assertNotNull($row);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['key_hash']);
        $this->assertSame(1, (int) $row['active']);
        $this->assertSame(60, (int) $row['rate_limit'], 'default rate limit');
        $this->assertNull($row['last_used_at']);
    }

    public function testEveryKeyIsDifferent(): void
    {
        $this->loginAs('admin');
        $device = Factory::unique('dev-');

        $this->post('tools/show_api_keys', ['create_key' => '1', 'device' => $device]);
        $this->post('tools/show_api_keys', ['create_key' => '1', 'device' => $device]);

        $hashes = array_column($this->rows('SELECT key_hash FROM api_keys WHERE device = ?', [$device]), 'key_hash');
        $this->assertCount(2, $hashes);
        $this->assertNotSame($hashes[0], $hashes[1]);
    }

    #[Group('known-bug')]
    public function testThePlainKeyIsNeverShown(): void
    {
        // KNOWN BUG: the page only lists hashes, so a newly created key can never be used by anyone -
        // decide in CI4 whether to show the key once
        $this->loginAs('admin');
        $device = Factory::unique('hidden-');

        $page = $this->post('tools/show_api_keys', ['create_key' => '1', 'device' => $device]);

        $hash = $this->row('SELECT key_hash FROM api_keys WHERE device = ?', [$device])['key_hash'];
        $this->assertStatus($page, 200);
        $this->assertSee($page, $hash, 'the stored hash is listed');
        $rest = str_replace($this->hashesOnPage(), '', (string) $page->getBody());
        $this->assertDoesNotMatchRegularExpression('/[0-9a-f]{64}/', $rest, 'no 64 hex string besides the stored hashes: the plain key is not on the page');
    }

    /** @return list<string> every stored hash, so the test can remove them from a page */
    private function hashesOnPage(): array
    {
        return array_column($this->rows('SELECT key_hash FROM api_keys'), 'key_hash');
    }

    public function testThePageListsOnlyActiveKeysWithTheirDevice(): void
    {
        $this->loginAs('admin');
        $activeHash = Factory::apiKey(str_repeat('a', 63) . '1', true, 'device-active-x');
        $revokedHash = Factory::apiKey(str_repeat('b', 63) . '2', false, 'device-revoked-x');

        $page = $this->get('tools/show_api_keys');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $activeHash);
        $this->assertSee($page, 'device-active-x');
        $this->assertDontSee($page, $revokedHash);
        $this->assertDontSee($page, 'device-revoked-x');
    }

    public function testRevokingDeactivatesOnlyThatKey(): void
    {
        $this->loginAs('admin');
        $a = Factory::apiKey(str_repeat('c', 63) . '3', true, 'rev-a');
        $b = Factory::apiKey(str_repeat('d', 63) . '4', true, 'rev-b');
        $idA = (int) $this->scalar('SELECT id FROM api_keys WHERE key_hash = ?', [$a]);
        $idB = (int) $this->scalar('SELECT id FROM api_keys WHERE key_hash = ?', [$b]);

        $page = $this->post('tools/show_api_keys', ['revoke_key' => '1', 'key_id' => (string) $idA]);

        $this->assertSame(0, (int) $this->find('api_keys', $idA)['active']);
        $this->assertSame(1, (int) $this->find('api_keys', $idB)['active']);
        $this->assertDontSee($page, 'rev-a');
        $this->assertSee($page, 'rev-b');
    }

    public function testRevokingAnUnknownIdChangesNothing(): void
    {
        $this->loginAs('admin');
        $active = $this->countWhere('api_keys', ['active' => 1]);

        $this->post('tools/show_api_keys', ['revoke_key' => '1', 'key_id' => '99999999']);

        $this->assertSame($active, $this->countWhere('api_keys', ['active' => 1]));
    }

    public function testTheCreatedKeyAppearsOnThePageAfterCreation(): void
    {
        $this->loginAs('admin');
        $device = Factory::unique('listed-');

        $page = $this->post('tools/show_api_keys', ['create_key' => '1', 'device' => $device]);

        $this->assertSee($page, $device);
    }

    public function testOnlyAdminsMayManageKeys(): void
    {
        $device = Factory::unique('denied-');
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->post('tools/show_api_keys', ['create_key' => '1', 'device' => $device]), '');
        $this->assertRowMissing('api_keys', ['device' => $device]);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertNotNull($this->createKey($device), 'positive control');
    }
}
