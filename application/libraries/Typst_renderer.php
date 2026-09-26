<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Independent of Pdf.php and its archive/cache semantics. */
class Typst_renderer
{
    private array $options;

    public function __construct(array $options = [])
    {
        $ci = get_instance();
        $ci->config->load('documents', true);
        $this->options = $options + $ci->config->item('documents');
    }

    public function render(string $type, array $payload, ?string $logo = null, ?string $qr = null): string
    {
        if (!in_array($type, ['invoice', 'overview', 'reminder'], true)) { throw new InvalidArgumentException('type'); }
        $root = $this->options['temporary_path'];
        if (!is_dir($root) && !@mkdir($root, 0700, true) && !is_dir($root)) { throw new RuntimeException('storage'); }
        if (is_link($root) || !is_writable($root)) { throw new RuntimeException('storage'); }
        $lock = fopen($root . '/render.lock', 'c');
        if (!$lock) { throw new RuntimeException('storage'); }
        if (!flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock); throw new RuntimeException('busy'); }
        $workspace = $root . '/' . bin2hex(random_bytes(16));
        $start = microtime(true);
        $deadline = $start + $this->options['request_timeout'];
        $status = 'error';
        try {
            if (!is_executable($this->options['binary'])) { throw new RuntimeException('binary'); }
            if (!mkdir($workspace, 0700)) { throw new RuntimeException('storage'); }
            $fontArgs = $this->options['font_path'] !== '' ? ['--font-path', $this->options['font_path']] : [];
            $fonts = $this->run(['fonts', ...$fontArgs, '--variants'], $workspace, $deadline);
            if (!preg_match('/^Noto Sans\R((?:-.*\R?)+)/m', $fonts, $match)
                || !str_contains($match[1], 'Style: Normal, Weight: 400, Stretch: FontStretch(1000)')
                || !str_contains($match[1], 'Style: Normal, Weight: 700, Stretch: FontStretch(1000)')) {
                throw new RuntimeException('font');
            }
            foreach (['common.typ', $type . '.typ'] as $file) {
                if (!copy(APPPATH . 'documents/' . $file, $workspace . '/' . $file)) { throw new RuntimeException('storage'); }
            }
            $payload['logo'] = '';
            $payload['qr'] = '';
            if ($logo !== null) {
                if (file_put_contents($workspace . '/logo.png', $logo) === false) { throw new RuntimeException('storage'); }
                $payload['logo'] = 'logo.png';
            }
            if ($qr !== null) {
                if (file_put_contents($workspace . '/qr.png', $qr) === false) { throw new RuntimeException('storage'); }
                $payload['qr'] = 'qr.png';
            }
            $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($workspace . '/data.json', $json) === false) { throw new RuntimeException('storage'); }
            $this->run(['compile', '--root', $workspace, ...$fontArgs, $type . '.typ', 'output.pdf'], $workspace, $deadline);
            $output = $workspace . '/output.pdf';
            if (!is_file($output) || filesize($output) > 20 * 1024 * 1024) { throw new RuntimeException('output'); }
            $pdf = file_get_contents($output);
            if (!str_starts_with($pdf, '%PDF-') || !str_contains(substr($pdf, -100), '%%EOF')) { throw new RuntimeException('output'); }
            $status = 'ok';
            return $pdf;
        } finally {
            foreach (glob($workspace . '/*') ?: [] as $file) { if (is_file($file)) { unlink($file); } }
            if (is_dir($workspace)) { rmdir($workspace); }
            flock($lock, LOCK_UN);
            fclose($lock);
            log_message('info', sprintf('Typst type=%s status=%s count=%d seconds=%.3f', $type, $status, count($payload['content']['letters'] ?? [1]), microtime(true) - $start));
        }
    }

    private function run(array $arguments, string $workspace, float $deadline): string
    {
        $pipes = [];
        $process = @proc_open([$this->options['binary'], ...$arguments], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $workspace);
        if (!is_resource($process)) { throw new RuntimeException('binary'); }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $out = ''; $err = '';
        $end = min($deadline, microtime(true) + $this->options['compile_timeout']);
        try {
            do {
                $out .= stream_get_contents($pipes[1]);
                $err .= stream_get_contents($pipes[2]);
                if (strlen($out) + strlen($err) > 512000) { throw new RuntimeException('output'); }
                $state = proc_get_status($process);
                if (!$state['running']) { break; }
                if (microtime(true) >= $end) { throw new RuntimeException('timeout'); }
                usleep(10000);
            } while (true);
            $out .= stream_get_contents($pipes[1]);
            $err .= stream_get_contents($pipes[2]);
            if ($state['exitcode'] !== 0) {
                if (preg_match('/LETTER_OVERFLOW_(\d+)/', $err, $match)) { throw new RuntimeException('overflow:' . $match[1]); }
                throw new RuntimeException('compile');
            }
            if (str_contains($err, 'unknown font family')) { throw new RuntimeException('font'); }
            return $out;
        } finally {
            $state = proc_get_status($process);
            if ($state['running']) { proc_terminate($process, 9); }
            fclose($pipes[1]); fclose($pipes[2]);
            proc_close($process);
        }
    }
}
