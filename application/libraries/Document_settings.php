<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Presentation data only. Financial settings remain owned by the application. */
class Document_settings
{
    public const KEY = 'document_center_v1';
    public const TYPES = ['invoice', 'reminder', 'overview'];
    public const PLACEHOLDERS = ['recipient', 'pet', 'vaccine', 'disease', 'due_date'];
    private const LOGO_GUARD = "<?php exit; ?>\n";
    private $ci;
    private string $assets;

    public function __construct(array $options = [])
    {
        $this->ci = get_instance();
        $this->ci->config->load('documents', true);
        $this->assets = $options['asset_path'] ?? $this->ci->config->item('asset_path', 'documents');
    }

    public function defaults(): array
    {
        $en = $this->ci->config->item('language') === 'english';
        $layout = ['font_size' => 10, 'margin' => 20, 'spacing' => 4];
        return [
            'version' => 1,
            'branding' => ['name' => '', 'address' => '', 'contact' => '', 'accent' => '#25636b', 'logo' => ''],
            'invoice' => $layout + ['footer' => '', 'payment_text' => $en ? 'Please use the payment reference below.' : 'Gelieve de onderstaande gestructureerde mededeling te gebruiken.', 'show_qr' => true],
            'reminder' => $layout + [
                'subject' => $en ? 'Vaccination reminder for {pet}' : 'Vaccinatieherinnering voor {pet}',
                'greeting' => $en ? 'Dear {recipient},' : 'Beste {recipient},',
                'body' => $en ? 'The vaccination of {pet} against {disease} is due on {due_date}. Please contact our practice to arrange an appointment.' : 'De vaccinatie van {pet} tegen {disease} is aan herhaling toe op {due_date}. Neem gerust contact op met onze praktijk om een afspraak te maken.',
                'closing' => $en ? 'Kind regards,' : 'Met vriendelijke groeten,',
            ],
            'overview' => $layout + ['title' => $en ? 'Vaccination overview' : 'Vaccinatieoverzicht', 'introduction' => '', 'footer' => '', 'show_vet' => true, 'show_location' => false, 'show_due' => false],
        ];
    }

    public function all(): array
    {
        if (!$this->ci->db->table_exists('document_settings')) { throw new RuntimeException('migration'); }
        $row = $this->ci->db->where('name', self::KEY)->get('document_settings')->row_array();
        $stored = $row ? json_decode($row['payload'], true) : [];
        return array_replace_recursive($this->defaults(), is_array($stored) ? $stored : []);
    }

    public function validate(string $scope, array $input, array $current): array
    {
        $defaults = $this->defaults();
        if (!isset($defaults[$scope]) || !is_array($defaults[$scope])) {
            throw new InvalidArgumentException('scope');
        }
        $errors = [];
        $values = $defaults[$scope];
        foreach ($values as $key => $default) {
            if ($key === 'logo') {
                $values[$key] = $current['branding']['logo'];
                continue;
            }
            $v = $input[$key] ?? (is_bool($default) ? false : $default);
            if (is_bool($default)) {
                if (!in_array($v, [true, false, '1', '0', 1, 0], true)) { $errors[] = $key; }
                $values[$key] = in_array($v, [true, '1', 1], true);
            } elseif (is_int($default)) {
                $bounds = ['font_size' => [8, 14], 'margin' => [12, 30], 'spacing' => [1, 10]][$key];
                if (!is_scalar($v) || filter_var($v, FILTER_VALIDATE_INT) === false || $v < $bounds[0] || $v > $bounds[1]) { $errors[] = $key; }
                else { $values[$key] = (int) $v; }
            } else {
                if (!is_string($v) || !mb_check_encoding($v, 'UTF-8') || mb_strlen($v) > ($key === 'body' ? 4000 : 600)) { $errors[] = $key; continue; }
                $v = trim(str_replace("\r\n", "\n", $v));
                if ($key === 'accent' && !preg_match('/^#[a-f0-9]{6}$/iD', $v)) { $errors[] = $key; }
                if ($scope === 'reminder') {
                    $remaining = str_replace(array_map(fn($p) => '{' . $p . '}', self::PLACEHOLDERS), '', $v);
                    if (str_contains($remaining, '{') || str_contains($remaining, '}')) { $errors[] = $key; }
                }
                $values[$key] = $v;
            }
        }
        if ($errors) { throw new InvalidArgumentException(implode(', ', array_unique($errors))); }
        $current[$scope] = $values;
        return $current;
    }

    public function save(array $settings): void
    {
        if (!$this->ci->db->query('INSERT INTO document_settings (name, payload, updated_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE payload = VALUES(payload), updated_at = VALUES(updated_at)',
            [self::KEY, json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), date('Y-m-d H:i:s')])) {
            throw new RuntimeException('save');
        }
    }

    public function restore(string $scope): void
    {
        $defaults = $this->defaults();
        if (!isset($defaults[$scope]) || !is_array($defaults[$scope])) { throw new InvalidArgumentException('scope'); }
        $settings = $this->all();
        $settings[$scope] = $defaults[$scope];
        $this->save($settings);
    }

    public function store_logo(array $upload): string
    {
        $bytes = $this->logo_data($upload);
        if (!is_dir($this->assets) && !mkdir($this->assets, 0700, true)) { throw new RuntimeException('storage'); }
        // Keep persistent assets on the data volume, but never serve their bytes directly.
        $name = bin2hex(random_bytes(24)) . '.php';
        if (file_put_contents($this->assets . '/' . $name, self::LOGO_GUARD . $bytes) === false) { throw new RuntimeException('storage'); }
        chmod($this->assets . '/' . $name, 0600);
        return $name;
    }

    public function logo_data(array $upload): string
    {
        $path = $upload['tmp_name'] ?? '';
        if (($upload['error'] ?? -1) !== UPLOAD_ERR_OK || !is_file($path) || filesize($path) > 2 * 1024 * 1024) { throw new InvalidArgumentException('logo'); }
        $info = @getimagesize($path);
        if (!$info || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true) || $info[0] > 4096 || $info[1] > 4096 || $info[0] * $info[1] > 12000000) { throw new InvalidArgumentException('logo'); }
        $image = @imagecreatefromstring(file_get_contents($path));
        if (!$image) { throw new InvalidArgumentException('logo'); }
        try {
            imagesavealpha($image, true);
            ob_start();
            imagepng($image);
            return ob_get_clean();
        } finally { imagedestroy($image); }
    }

    public function logo_path(string $name): ?string
    {
        if (!preg_match('/^[a-f0-9]{48}\.php$/D', $name)) { return null; }
        $path = $this->assets . '/' . $name;
        return is_file($path) && !is_link($path) ? $path : null;
    }

    public function logo_bytes(string $name): ?string
    {
        $path = $this->logo_path($name);
        if (!$path) { return null; }
        $stored = file_get_contents($path);
        return str_starts_with($stored, self::LOGO_GUARD) ? substr($stored, strlen(self::LOGO_GUARD)) : null;
    }

    public static function wording(string $text, array $row): string
    {
        $replace = [];
        foreach (self::PLACEHOLDERS as $key) { $replace['{' . $key . '}'] = (string) ($row[$key] ?? ''); }
        return strtr($text, $replace);
    }
}
