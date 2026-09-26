<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Document_service
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
        $this->ci->lang->load('documents', $this->ci->config->item('language'));
        $this->ci->load->library('Document_settings');
        $this->ci->load->library('Typst_renderer');
        $this->ci->load->model('Document_data_model', 'document_data');
    }

    public function token(): string
    {
        $token = $this->ci->session->userdata('document_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $this->ci->session->set_userdata('document_token', $token);
        }
        return $token;
    }

    public function verify_post(): void
    {
        $sent = $this->ci->input->post('document_token');
        if ($this->ci->input->method() !== 'post' || !is_string($sent) || !hash_equals($this->token(), $sent)) { show_error('Invalid request token', 403); }
    }

    public function month($value): int
    {
        if (!is_scalar($value) || filter_var($value, FILTER_VALIDATE_INT) === false) { throw new InvalidArgumentException('month'); }
        $month = (int) $value;
        if (!$this->ci->ion_auth->in_group('admin') && abs($month) >= 3) { show_error('Month access denied', 403); }
        Document_data_model::month($month);
        return $month;
    }

    public function pdf(string $type, array $content, array $settings, ?string $previewLogo = null): string
    {
        $labels = [];
        foreach ($this->ci->lang->language as $key => $value) {
            if (str_starts_with($key, 'doc_')) { $labels[substr($key, 4)] = $value; }
        }
        if ($type === 'reminder') {
            foreach ($content['letters'] as &$row) {
                foreach (['subject', 'greeting', 'body', 'closing'] as $key) { $row[$key] = Document_settings::wording($settings['reminder'][$key], $row); }
            }
            unset($row);
        }
        $qr = null;
        if ($type === 'invoice' && $settings['invoice']['show_qr'] && $content['qr_amount'] > 0.01 && $content['iban'] !== '' && $content['bank_name'] !== '') {
            require_once FCPATH . 'vendor/autoload.php';
            // Reuse the installed EPC/QR libraries with explicit PNG output for Typst.
            // Qr.php retains its existing output behavior for legacy consumers.
            try {
                $payment = (new \SepaQr\SepaQrData())->setName($content['bank_name'])->setIban($content['iban'])
                    ->setRemittanceText($content['reference'])->setAmount($content['qr_amount']);
                $options = new \chillerlan\QRCode\QROptions([
                    'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                    'outputBase64' => false, 'addQuietzone' => true, 'quietzoneSize' => 4, 'scale' => 8,
                    'versionMax' => 13, 'eccLevel' => \chillerlan\QRCode\Common\EccLevel::Q,
                ]);
                $qr = (new \chillerlan\QRCode\QRCode($options))->render($payment);
            } catch (Throwable $error) { throw new RuntimeException('qr', 0, $error); }
        }
        $logo = $previewLogo;
        if ($logo === null && $settings['branding']['logo'] !== '') {
            $logo = $this->ci->document_settings->logo_bytes($settings['branding']['logo']);
            if ($logo === null) { throw new RuntimeException('storage'); }
        }
        return $this->ci->typst_renderer->render($type, [
            'branding' => $settings['branding'], 'settings' => $settings[$type], 'content' => $content,
            'language' => $this->ci->config->item('language') === 'english' ? 'en' : 'nl', 'labels' => $labels,
        ], $logo, $qr);
    }

    public function download(string $pdf, string $filename, bool $preview = false): void
    {
        $this->ci->output->set_content_type('application/pdf')->set_header('Cache-Control: no-store, private')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('Content-Disposition: ' . ($preview ? 'inline' : 'attachment') . '; filename="' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename) . '"')
            ->set_output($pdf);
    }

    public function error(Throwable $error): string
    {
        if ($error instanceof InvalidArgumentException) {
            return $this->ci->lang->line('doc_invalid') . ': ' . $error->getMessage();
        }
        if (preg_match('/^overflow:(\d+)$/', $error->getMessage(), $match)) {
            return sprintf($this->ci->lang->line('doc_error_overflow'), (int) $match[1]);
        }
        $key = 'doc_error_' . $error->getMessage();
        return $this->ci->lang->language[$key] ?? $this->ci->lang->line('doc_error_compile');
    }

    public function sample(string $type): array
    {
        $recipient = ['recipient' => 'De Smet Zoë', 'address' => "Voorbeeldstraat 12\n9000 Gent"];
        if ($type === 'reminder') {
            return ['letters' => [$recipient + ['id' => 1, 'pet' => 'Milo', 'vaccine' => 'Vaccin A', 'disease' => 'ziekte A', 'due_date' => date('d-m-Y', strtotime('+1 month'))]]];
        }
        if ($type === 'overview') {
            return $recipient + ['pet' => 'Milo', 'pet_id' => '123', 'type' => 'Kat', 'gender' => 'Male', 'birth' => '01-05-2020', 'breed' => 'Europese korthaar', 'chip' => '967000000000001', 'weight' => '4.2', 'vaccines' => [['vaccine' => 'Vaccin A', 'date' => '20-09-2025', 'vet' => 'Alex Example', 'location' => 'Praktijk', 'due' => '20-09-2026']]];
        }
        return $recipient + ['title' => $this->ci->lang->line('doc_invoice'), 'number' => 'VOORBEELD-001', 'date' => date('d-m-Y'), 'due' => date('d-m-Y', strtotime('+30 days')), 'client_id' => '123', 'vat_number' => '', 'location' => 'Praktijk', 'message' => '',
            'lines' => [['group' => 'Milo', 'description' => 'Consultatie', 'quantity' => '1,00', 'unit_price' => '40,00', 'tax' => '21%', 'total' => '40,00']],
            'tax_rows' => [['21%', '40,00', '8,40']], 'net' => '40,00', 'tax_total' => '8,40', 'total' => '48,40', 'cash' => '0,00', 'card' => '0,00', 'transfer' => '0,00', 'payment_status' => $this->ci->lang->line('doc_unpaid'), 'reference' => '+++000/0000/00101+++', 'iban' => '', 'bic' => '', 'bank_name' => '', 'qr_amount' => 0];
    }
}
