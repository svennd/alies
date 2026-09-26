<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require_once APPPATH . 'libraries/Typst_renderer.php';

final class TypstFailureTest extends TestCase
{
    public function testMissingFontBinaryExitTimeoutAndInvalidOutputAreControlled(): void
    {
        $root = sys_get_temp_dir() . '/typst-failure-' . bin2hex(random_bytes(5));
        mkdir($root, 0700);
        $binary = $root . '/compiler';
        $fontResponse = "if [ \"\$1\" = fonts ]; then printf 'Noto Sans\\n- Style: Normal, Weight: 400, Stretch: FontStretch(1000)\\n- Style: Normal, Weight: 700, Stretch: FontStretch(1000)\\n'; exit 0; fi\n";
        $cases = [
            'binary' => null,
            'font' => "#!/bin/sh\nprintf 'Other font\\n'\n",
            'compile' => "#!/bin/sh\n" . $fontResponse . "exit 1\n",
            'timeout' => "#!/bin/sh\n" . $fontResponse . "exec sleep 3\n",
            'output' => "#!/bin/sh\n" . $fontResponse . "printf 'garbage' > output.pdf\n",
        ];
        try {
            foreach ($cases as $expected => $script) {
                if ($script !== null) { file_put_contents($binary, $script); chmod($binary, 0700); }
                $renderer = new Typst_renderer(['binary' => $binary, 'temporary_path' => $root . '/work', 'compile_timeout' => 0.15]);
                try { $renderer->render('reminder', []); self::fail('Expected ' . $expected); }
                catch (RuntimeException $error) { self::assertSame($expected, $error->getMessage()); }
                self::assertSame(['render.lock'], array_values(array_diff(scandir($root . '/work'), ['.', '..'])));
            }
        } finally {
            if (is_file($binary)) unlink($binary);
            unlink($root . '/work/render.lock'); rmdir($root . '/work'); rmdir($root);
        }
    }
}
