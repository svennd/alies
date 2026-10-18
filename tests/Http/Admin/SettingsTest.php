<?php

declare(strict_types=1);

/**
 * T300 - Admin::settings (admin only).
 *
 * Rules: every posted conf_<name> with a non-empty value is stored base64 encoded in config.value
 * (upsert on the unique name); an empty value does not clear a setting; the five check_<name>
 * boxes store 1 when "on" and 0 when left out; nothing happens without the `submit` field.
 * The config table is snapshotted before each test and put back afterwards.
 */
final class SettingsTest extends HttpTestCase
{
    private const CHECKBOXES = ['autodisable', 'invoice_prefix', 'pruning', 'autdeath', 'vamreg_push'];

    /** @var list<array<string, mixed>> */
    private array $snapshot = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshot = $this->rows('SELECT * FROM config');
    }

    protected function tearDown(): void
    {
        $this->db()->exec('DELETE FROM config');
        foreach ($this->snapshot as $row) {
            Factory::insert('config', $row);
        }
        parent::tearDown();
    }

    private function stored(string $name): ?string
    {
        $value = $this->scalar('SELECT value FROM config WHERE name = ?', [$name]);

        return $value === false || $value === null ? null : (string) $value;
    }

    public function testPostedValuesAreStoredBase64Encoded(): void
    {
        $this->loginAs('admin');

        $this->post('admin/settings', ['submit' => 'edit', 'conf_due_date' => '14', 'conf_iban' => 'BE71 0961 2345 6769']);

        $this->assertSame(base64_encode('14'), $this->stored('due_date'));
        $this->assertSame(base64_encode('BE71 0961 2345 6769'), $this->stored('iban'));
    }

    public function testASettingThatDoesNotExistYetIsInserted(): void
    {
        $this->loginAs('admin');
        $name = 'zz_new_' . uniqid();
        $this->assertRowMissing('config', ['name' => $name]);

        $this->post('admin/settings', ['submit' => 'edit', 'conf_' . $name => 'hello']);

        $this->assertSame(base64_encode('hello'), $this->stored($name));
        $this->assertSame(1, $this->countWhere('config', ['name' => $name]));
    }

    public function testPostingAgainUpdatesInsteadOfDuplicating(): void
    {
        $this->loginAs('admin');
        $name = 'zz_twice_' . uniqid();

        $this->post('admin/settings', ['submit' => 'edit', 'conf_' . $name => 'one']);
        $this->post('admin/settings', ['submit' => 'edit', 'conf_' . $name => 'two']);

        $this->assertSame(1, $this->countWhere('config', ['name' => $name]));
        $this->assertSame(base64_encode('two'), $this->stored($name));
    }

    public function testTickedCheckboxesStoreOneAndMissingOnesStoreZero(): void
    {
        $this->loginAs('admin');

        $this->post('admin/settings', ['submit' => 'edit', 'check_pruning' => 'on', 'check_autdeath' => 'on']);

        $this->assertSame(base64_encode('1'), $this->stored('pruning'));
        $this->assertSame(base64_encode('1'), $this->stored('autdeath'));
        foreach (['autodisable', 'invoice_prefix', 'vamreg_push'] as $name) {
            $this->assertSame(base64_encode('0'), $this->stored($name), $name);
        }
    }

    public function testLeavingEveryCheckboxOutSwitchesAllFiveOff(): void
    {
        $this->loginAs('admin');
        foreach (self::CHECKBOXES as $name) {
            Factory::setting($name, '1');
        }

        $this->post('admin/settings', ['submit' => 'edit']);

        foreach (self::CHECKBOXES as $name) {
            $this->assertSame(base64_encode('0'), $this->stored($name), $name);
        }
    }

    public function testACheckboxValueOtherThanOnCountsAsOff(): void
    {
        $this->loginAs('admin');

        $this->post('admin/settings', ['submit' => 'edit', 'check_pruning' => '1']);

        $this->assertSame(base64_encode('0'), $this->stored('pruning'));
    }

    public function testAnEmptyValueDoesNotClearTheStoredSetting(): void
    {
        // pins the behaviour: a text setting cannot be emptied through this form
        $this->loginAs('admin');
        Factory::setting('bic', 'GEBABEBB');

        $this->post('admin/settings', ['submit' => 'edit', 'conf_bic' => '']);

        $this->assertSame(base64_encode('GEBABEBB'), $this->stored('bic'));
    }

    public function testAZeroValueIsStoredBecauseOnlyTheEmptyStringIsSkipped(): void
    {
        $this->loginAs('admin');
        Factory::setting('prune_stock', '5');

        $this->post('admin/settings', ['submit' => 'edit', 'conf_prune_stock' => '0']);

        $this->assertSame(base64_encode('0'), $this->stored('prune_stock'));
    }

    public function testKeysWithoutTheConfPrefixAreIgnored(): void
    {
        $this->loginAs('admin');
        $before = $this->countWhere('config');

        $this->post('admin/settings', ['submit' => 'edit', 'iban' => 'x', 'other' => 'y']);

        $this->assertSame($before, $this->countWhere('config'));
    }

    public function testNothingIsStoredWithoutTheSubmitField(): void
    {
        $this->loginAs('admin');
        Factory::setting('pruning', '1');

        $response = $this->post('admin/settings', ['conf_due_date' => '99', 'conf_zz_nosubmit' => 'x']);

        $this->assertStatus($response, 200);
        $this->assertRowMissing('config', ['name' => 'zz_nosubmit']);
        $this->assertSame(base64_encode('1'), $this->stored('pruning'), 'checkboxes untouched too');
    }

    public function testThePageShowsTheCurrentValuesDecoded(): void
    {
        $this->loginAs('admin');
        Factory::setting('iban', 'BE68 5390 0754 7034');
        Factory::setting('nameiban', 'Dierenarts Test');

        $page = $this->get('admin/settings');

        $this->assertStatus($page, 200);
        $this->assertStringContainsString('value="BE68 5390 0754 7034"', (string) $page->getBody());
        $this->assertStringContainsString('value="Dierenarts Test"', (string) $page->getBody());
        $this->assertStringNotContainsString(base64_encode('Dierenarts Test'), (string) $page->getBody());
    }

    public function testAfterSavingThePageShowsTheNewValue(): void
    {
        $this->loginAs('admin');

        $page = $this->post('admin/settings', ['submit' => 'edit', 'conf_nameiban' => 'Nieuwe Naam']);

        $this->assertStringContainsString('value="Nieuwe Naam"', (string) $page->getBody());
    }

    public function testCheckedBoxesAreRenderedAsChecked(): void
    {
        $this->loginAs('admin');
        Factory::setting('pruning', '1');
        Factory::setting('autdeath', '0');

        $body = (string) $this->get('admin/settings')->getBody();

        $this->assertMatchesRegularExpression('/name="check_pruning"\s+checked/', $body);
        $this->assertDoesNotMatchRegularExpression('/name="check_autdeath"\s+checked/', $body);
    }

    public function testQuotesInAValueAreSafeBecauseItIsBase64Encoded(): void
    {
        // (the setting *name* is concatenated into the SQL unescaped, Config_model::store; not tested)
        $this->loginAs('admin');

        $this->post('admin/settings', ['submit' => 'edit', 'conf_quote_test' => "O'Neil \"x\""]);

        $this->assertSame(base64_encode("O'Neil \"x\""), $this->stored('quote_test'));
    }

    public function testOnlyAdminsMayUseTheSettings(): void
    {
        foreach (['vet', 'acc'] as $role) {
            $this->cookies->clear();
            $this->loginAs($role);
            $response = $this->post('admin/settings', ['submit' => 'edit', 'conf_zz_denied' => 'x']);
            $this->assertRedirectsTo($response, '', $role);
        }
        $this->assertRowMissing('config', ['name' => 'zz_denied']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->post('admin/settings', ['submit' => 'edit', 'conf_zz_denied' => 'x']);
        $this->assertRowExists('config', ['name' => 'zz_denied'], 'positive control');
    }
}
