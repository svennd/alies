<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Opt-in: run as the PHP service user against the local development server. */
final class DocumentHttpTest extends TestCase
{
    private array $sessions = [];
    private string $base;
    private $ci;

    protected function setUp(): void
    {
        $this->base = getenv('DOCUMENT_HTTP_BASE') ?: '';
        if ($this->base === '') self::markTestSkipped('Set DOCUMENT_HTTP_BASE to the local application URL.');
        $this->ci = get_instance();
    }

    protected function tearDown(): void
    {
        foreach ($this->sessions as $file) if (is_file($file)) unlink($file);
    }

    private function session(string $group): string
    {
        $query = $this->ci->db->select('u.*')->from('users u')->join('users_groups ug', 'ug.user_id=u.id')
            ->join('groups g', 'g.id=ug.group_id')->where('g.name', $group)->where('u.active', 1);
        if ($group !== 'admin') $query->where('NOT EXISTS (SELECT 1 FROM users_groups x JOIN groups y ON y.id=x.group_id WHERE x.user_id=u.id AND y.name="admin")', null, false);
        $user = $query->get()->row();
        self::assertNotNull($user, 'A development ' . $group . ' account is required');
        $this->ci->config->load('ion_auth', true);
        $identity = $this->ci->config->item('identity', 'ion_auth');
        $data = ['__ci_last_regenerate' => time(), 'identity' => $user->{$identity}, $identity => $user->{$identity},
            'email' => $user->email, 'user_id' => $user->id, 'location' => 0, 'last_check' => time(),
            'ion_auth_session_hash' => $this->ci->config->item('session_hash', 'ion_auth')];
        $length = max((int) ini_get('session.sid_length'), (int) ceil(160 / (int) ini_get('session.sid_bits_per_character')));
        $id = substr(bin2hex(random_bytes($length)), 0, $length);
        $cookie = $this->ci->config->item('sess_cookie_name');
        $file = $this->ci->config->item('sess_save_path') . '/' . $cookie . $id;
        $encoded = '';
        foreach ($data as $key => $value) $encoded .= $key . '|' . serialize($value);
        file_put_contents($file, $encoded);
        chmod($file, 0600);
        $this->sessions[] = $file;
        return $cookie . '=' . $id;
    }

    private function request(string $path, string $cookie = '', ?array $post = null, bool $multipart = false): array
    {
        $curl = curl_init($this->base . '/' . $path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 30,
            CURLOPT_COOKIE => $cookie, CURLOPT_HTTPHEADER => ['Host: localhost:8080']]);
        if ($post !== null) curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $multipart ? $post : http_build_query($post)]);
        $response = curl_exec($curl);
        self::assertNotFalse($response, curl_error($curl));
        $length = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        return [curl_getinfo($curl, CURLINFO_HTTP_CODE), substr($response, 0, $length), substr($response, $length)];
    }

    private function token(string $html): string
    {
        self::assertSame(1, preg_match('/name="document_token" value="([a-f0-9]+)"/', $html, $match), substr(strip_tags($html), 0, 500));
        return $match[1];
    }

    public function testAccessCsrfUnsavedPreviewsAndDownloads(): void
    {
        foreach (['document_center', 'documents/invoice/1', 'documents/overview/1', 'documents/reminders/0'] as $path) {
            [, $headers, $body] = $this->request($path);
            self::assertStringContainsString('auth/login', $headers . $body);
            self::assertStringNotContainsString('%PDF-', $body);
        }
        $admin = $this->session('admin');
        $vet = $this->session('vet');
        foreach (['document_center', 'document_center/logo', 'document_center/index/reminder'] as $path) {
            [$status, , $body] = $this->request($path, $vet);
            self::assertContains($status, [302, 303, 307]);
            self::assertStringNotContainsString('document_token', $body);
        }
        [$status] = $this->request('documents/reminders/3', $vet);
        self::assertSame(403, $status);
        $member = $this->session('members');
        foreach (['documents/invoice/1', 'documents/overview/1', 'documents/reminders/0'] as $path) {
            [$status, , $body] = $this->request($path, $member);
            self::assertSame(500, $status);
            self::assertStringNotContainsString('%PDF-', $body);
        }
        [, , $form] = $this->request('document_center', $admin);
        $token = $this->token($form);
        $settingsBefore = $this->ci->db->get('document_settings')->result_array();
        [$status] = $this->request('document_center/index/invoice', $admin, ['action' => 'save', 'fields' => ['footer' => 'forbidden']]);
        self::assertSame(403, $status);
        foreach (['invoice', 'overview', 'reminder'] as $type) {
            [$status, $headers, $pdf] = $this->request('document_center/index/' . $type, $admin,
                ['document_token' => $token, 'action' => 'preview', 'fields' => ['font_size' => 11]]);
            self::assertSame(200, $status);
            self::assertStringContainsString('application/pdf', $headers);
            self::assertStringStartsWith('%PDF-', $pdf);
            self::assertStringContainsString('no-store', $headers);
        }
        self::assertSame($settingsBefore, $this->ci->db->get('document_settings')->result_array());
        [$status, , $error] = $this->request('document_center/index/reminder', $admin,
            ['document_token' => $token, 'action' => 'save', 'fields' => ['font_size' => 99]]);
        self::assertStringContainsString('font_size', $error);
        self::assertSame($settingsBefore, $this->ci->db->get('document_settings')->result_array());
        [$status] = $this->request('documents/invoice/9999999999', $admin);
        self::assertSame(422, $status);
        $pet = $this->ci->db->select('p.id')->from('pets p')->join('owners o', 'o.id=p.owner')->where('p.deleted_at', null)->limit(1)->get()->row()->id;
        [$status, $headers, $pdf] = $this->request('documents/overview/' . $pet, $admin);
        self::assertSame(200, $status);
        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertStringContainsString('attachment;', $headers);
        [$status, $headers, $pdf] = $this->request('vaccine/export_vaccine/' . $pet, $admin);
        self::assertSame(200, $status);
        self::assertStringStartsWith('%PDF-', $pdf);
        [, , $selection] = $this->request('documents/reminders/0', $admin);
        $token = $this->token($selection);
        foreach ([[], [1, 1], range(1, 11), [9999999999]] as $ids) {
            [, $headers, $body] = $this->request('documents/reminders/0', $admin,
                ['document_token' => $token, 'action' => 'generate', 'rows' => $ids]);
            self::assertStringNotContainsString('application/pdf', $headers);
            self::assertStringContainsString('alert-danger', $body);
        }
        $this->ci->load->model('Document_data_model', 'document_data');
        $rows = $this->ci->document_data->reminder_rows(0);
        self::assertGreaterThan(10, count($rows), 'Development fixture month must have >10 eligible reminders');
        self::assertSame(10, preg_match_all('/class="document-row"[^>]+ checked/', $selection));
        foreach ([1, 5, 10] as $count) {
            $ids = array_map('intval', array_column(array_slice($rows, 0, $count), 'id'));
            $before = $this->ci->db->where_in('id', $ids)->get('vaccine_pet')->result_array();
            [$status, $headers, $pdf] = $this->request('documents/reminders/0', $admin,
                ['document_token' => $token, 'action' => 'generate', 'rows' => array_reverse($ids)]);
            self::assertSame(200, $status);
            self::assertStringStartsWith('%PDF-', $pdf);
            self::assertSame($before, $this->ci->db->where_in('id', $ids)->get('vaccine_pet')->result_array());
        }
        $bill = $this->ci->db->where('status', BILL_PAID)->where('invoice_id IS NOT NULL', null, false)->where('deleted_at', null)->limit(1)->get('bills')->row_array();
        self::assertNotNull($bill);
        [$status, , $pdf] = $this->request('documents/invoice/' . $bill['id'], $admin);
        self::assertSame(200, $status);
        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertSame($bill, $this->ci->db->where('id', $bill['id'])->get('bills')->row_array());
        foreach (['invoice' => $bill['id'], 'overview' => $pet, 'reminder' => $rows[0]['id']] as $type => $record) {
            [$status, , $pdf] = $this->request('document_center/index/' . $type, $admin,
                ['document_token' => $token, 'action' => 'preview', 'record_id' => $record, 'month' => 0, 'fields' => ['font_size' => 11]]);
            self::assertSame(200, $status);
            self::assertStringStartsWith('%PDF-', $pdf);
        }
        self::assertSame($settingsBefore, $this->ci->db->get('document_settings')->result_array());
        $unpaid = $this->ci->db->select('b.*')->from('bills b')->join('events e', 'e.payment=b.id')->where('b.status', BILL_PAID)
            ->where('b.transfer >', 0)->where('b.transfer_verified', 0)->where('b.total_brut >', 0)->where('b.deleted_at', null)->limit(1)->get()->row_array();
        self::assertNotNull($unpaid);
        [$status, , $pdf] = $this->request('documents/invoice/' . $unpaid['id'], $admin);
        self::assertSame(200, $status);
        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertSame($unpaid, $this->ci->db->where('id', $unpaid['id'])->get('bills')->row_array());
        $this->ci->config->load('documents', true);
        $root = $this->ci->config->item('temporary_path', 'documents');
        $lock = fopen($root . '/render.lock', 'c');
        flock($lock, LOCK_EX);
        try {
            [$status, , $body] = $this->request('documents/overview/' . $pet, $admin);
            self::assertSame(429, $status);
            self::assertStringNotContainsString('%PDF-', $body);
        } finally { flock($lock, LOCK_UN); fclose($lock); }
        self::assertSame(['render.lock'], array_values(array_diff(scandir($root), ['.', '..'])));
    }

    public function testSettingsSaveLogoAccessAndScopedRestore(): void
    {
        if (getenv('DOCUMENT_HTTP_ALLOW_SETTINGS_WRITE') !== '1') {
            self::markTestSkipped('Enable settings-write checks only on an idle, isolated development instance.');
        }
        $admin = $this->session('admin');
        $original = $this->ci->db->get('document_settings')->result_array();
        $logoPath = null;
        $source = tempnam(sys_get_temp_dir(), 'document-http-logo');
        $image = imagecreatetruecolor(30, 30);
        imagepng($image, $source);
        try {
            [, , $form] = $this->request('document_center/index/branding', $admin);
            $token = $this->token($form);
            [$status, , $body] = $this->request('document_center/index/branding', $admin,
                ['document_token' => $token, 'action' => 'save', 'fields[name]' => 'HTTP QA practice',
                    'logo' => new CURLFile($source, 'image/png', 'logo.png')], true);
            self::assertSame(200, $status);
            self::assertStringContainsString('alert-success', $body);
            $this->ci->load->library('Document_settings');
            $settings = $this->ci->document_settings->all();
            $name = $settings['branding']['logo'];
            $logoPath = $this->ci->document_settings->logo_path($name);
            self::assertSame('HTTP QA practice', $settings['branding']['name']);
            [$status, $headers, $bytes] = $this->request('document_center/logo', $admin);
            self::assertSame(200, $status);
            self::assertStringContainsString('image/png', $headers);
            self::assertSame(IMAGETYPE_PNG, getimagesizefromstring($bytes)[2]);
            [, , $direct] = $this->request('data/documents/' . $name);
            self::assertStringNotContainsString('PNG', $direct);
            [, $headers] = $this->request('document_center/logo');
            self::assertStringNotContainsString('image/png', $headers);
            [$status, , $pdf] = $this->request('document_center/index/branding', $admin,
                ['document_token' => $token, 'action' => 'preview', 'fields' => ['name' => 'Unsaved QA practice']]);
            self::assertSame(200, $status);
            self::assertStringStartsWith('%PDF-', $pdf);
            self::assertSame($settings, $this->ci->document_settings->all());
            $this->request('document_center/index/reminder', $admin, ['document_token' => $token, 'action' => 'restore']);
            self::assertSame($settings['branding'], $this->ci->document_settings->all()['branding']);
        } finally {
            $this->ci->db->where('name', Document_settings::KEY)->delete('document_settings');
            foreach ($original as $row) if ($row['name'] === Document_settings::KEY) $this->ci->db->insert('document_settings', $row);
            if ($logoPath && is_file($logoPath)) unlink($logoPath);
            unlink($source);
        }
    }
}
