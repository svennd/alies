<?php

declare(strict_types=1);

require_once __DIR__ . '/../stubs.php';
require_once APPPATH . 'helpers/javascript_helper.php';
require_once APPPATH . 'helpers/inject_helper.php';

use PHPUnit\Framework\TestCase;

/**
 * T015 - application/helpers/javascript_helper.php (javascript) and inject_helper.php (inject_trumbowyg).
 *
 * base_url() comes from CodeIgniter (or a stand-in), so the host is never asserted, only the path.
 * composer.json in the project root serves as the "existing asset".
 */
final class AssetHelpersTest extends TestCase
{
    public function testAnExistingAssetGetsItsModificationTimeAsVersion(): void
    {
        $mtime = filemtime(FCPATH . 'composer.json');

        $url = javascript('composer.json');

        $this->assertSame(base_url('composer.json?v=' . $mtime), $url);
        $this->assertStringEndsWith('composer.json?v=' . $mtime, $url);
    }

    public function testAMissingAssetGivesAnEmptyString(): void
    {
        $this->assertSame('', javascript('assets/js/does-not-exist.js'));
    }

    public function testAnAssetInASubdirectoryKeepsItsPath(): void
    {
        $this->assertStringContainsString('application/config/constants.php?v=', javascript('application/config/constants.php'));
    }

    public function testTheHeaderIsOneStylesheetLink(): void
    {
        $html = inject_trumbowyg('header');

        $this->assertSame(1, substr_count($html, '<link '));
        $this->assertSame(0, substr_count($html, '<script'));
        $this->assertStringContainsString('node_modules/trumbowyg/dist/ui/trumbowyg.min.css', $html);
        $this->assertStringContainsString('rel="stylesheet"', $html);
    }

    public function testTheFooterIsTheEditorAndTheTemplatePluginScripts(): void
    {
        $html = inject_trumbowyg();

        $this->assertSame(2, substr_count($html, '<script src='));
        $this->assertSame(0, substr_count($html, '<link '));
        $this->assertStringContainsString('node_modules/trumbowyg/dist/trumbowyg.min.js', $html);
        $this->assertStringContainsString('node_modules/trumbowyg/dist/plugins/template/trumbowyg.template.min.js', $html);
    }

    public function testAnyArgumentOtherThanHeaderGivesTheScripts(): void
    {
        $this->assertSame(inject_trumbowyg('footer'), inject_trumbowyg('anything else'));
        $this->assertSame(inject_trumbowyg(), inject_trumbowyg('footer'));
    }

    public function testTheInjectedFilesExistInTheProject(): void
    {
        // the url points to node_modules, so a missing "npm install" would break the editor silently
        foreach (['ui/trumbowyg.min.css', 'trumbowyg.min.js', 'plugins/template/trumbowyg.template.min.js'] as $file) {
            $this->assertFileExists(FCPATH . 'node_modules/trumbowyg/dist/' . $file);
        }
    }
}
