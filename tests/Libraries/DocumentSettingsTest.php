<?php
declare(strict_types=1);

require_once APPPATH . 'libraries/Document_settings.php';

final class DocumentSettingsTest extends CodeIgniterDatabaseTestCase
{
    public function testPersistRestoreAndIsolateScopes(): void
    {
        $settings = new Document_settings();
        $original = $settings->defaults();
        $legacy = $this->ci->db->order_by('id')->get('config')->result_array();
        $changed = $settings->validate('reminder', ['body' => str_repeat('Hello {pet}. ', 100), 'font_size' => '12'], $original);
        $changed = $settings->validate('branding', ['name' => 'Test practice'], $changed);
        $settings->save($changed);
        self::assertSame($changed, $settings->all());
        self::assertSame($legacy, $this->ci->db->order_by('id')->get('config')->result_array());
        $settings->restore('reminder');
        self::assertSame($original['reminder'], $settings->all()['reminder']);
        self::assertSame('Test practice', $settings->all()['branding']['name']);
        self::assertSame($original['invoice'], $settings->all()['invoice']);
        $settings->restore('branding');
        self::assertSame($original['branding'], $settings->all()['branding']);
    }

    public function testInvalidFieldsDoNotPersistAndSubstitutionIsNotRecursive(): void
    {
        $settings = new Document_settings();
        $before = $settings->all();
        foreach ([['body' => 'Hello {unknown}'], ['font_size' => '100'], ['margin' => []], ['body' => str_repeat('a', 4001)]] as $input) {
            try { $settings->validate('reminder', $input, $before); self::fail('Expected field rejection'); }
            catch (InvalidArgumentException $e) { self::assertNotSame('', $e->getMessage()); }
            self::assertSame($before, $settings->all());
        }
        self::assertSame('{vaccine}', Document_settings::wording('{pet}', ['pet' => '{vaccine}', 'vaccine' => 'must not substitute']));
    }

    public function testLogoValidationPersistenceAndPathRejection(): void
    {
        $root = sys_get_temp_dir() . '/doc-logo-' . bin2hex(random_bytes(6));
        $settings = new Document_settings(['asset_path' => $root]);
        $source = tempnam(sys_get_temp_dir(), 'doc-logo');
        $image = imagecreatetruecolor(20, 20);
        imagepng($image, $source);
        imagedestroy($image);
        try {
            $name = $settings->store_logo(['error' => UPLOAD_ERR_OK, 'tmp_name' => $source]);
            $reloaded = new Document_settings(['asset_path' => $root]);
            self::assertNotNull($reloaded->logo_path($name));
            self::assertSame(IMAGETYPE_PNG, getimagesizefromstring($reloaded->logo_bytes($name))[2]);
            self::assertNull($reloaded->logo_path('../' . $name));
            self::assertNull($reloaded->logo_path('anything.svg'));
            file_put_contents($source, 'not an image');
            try { $settings->store_logo(['error' => UPLOAD_ERR_OK, 'tmp_name' => $source]); self::fail('Invalid logo accepted'); }
            catch (InvalidArgumentException $error) { self::assertSame('logo', $error->getMessage()); }
            file_put_contents($source, str_repeat('x', 2097153));
            try { $settings->store_logo(['error' => UPLOAD_ERR_OK, 'tmp_name' => $source]); self::fail('Oversized logo accepted'); }
            catch (InvalidArgumentException $error) { self::assertSame('logo', $error->getMessage()); }
        } finally {
            unlink($source);
            foreach (glob($root . '/*') ?: [] as $file) unlink($file);
            if (is_dir($root)) rmdir($root);
        }
    }

    public function testDutchEnglishLabelsAndDefaults(): void
    {
        $read = static function (string $language): array {
            $lang = [];
            require APPPATH . 'language/' . $language . '/documents_lang.php';
            return $lang;
        };
        self::assertSame(array_keys($read('dutch')), array_keys($read('english')));
        $language = $this->ci->config->item('language');
        try {
            $this->ci->config->set_item('language', 'english');
            $settings = new Document_settings();
            self::assertSame('Vaccination reminder for {pet}', $settings->defaults()['reminder']['subject']);
        } finally { $this->ci->config->set_item('language', $language); }
    }
}
