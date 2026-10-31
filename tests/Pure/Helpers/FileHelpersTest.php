<?php

declare(strict_types=1);

require_once __DIR__ . '/../stubs.php';
require_once APPPATH . 'helpers/file_helper.php';
require_once APPPATH . 'helpers/base64_to_img_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T014 - application/helpers/file_helper.php (move_file) and base64_to_img_helper.php (base64_to_image).
 *
 * Works in a private temp directory. base64_to_image() uses CodeIgniter's write_file(); a stand-in
 * from tests/Pure/stubs.php is used when the framework is not loaded.
 */
final class FileHelpersTest extends TestCase
{
    /** a 1x1 pixel PNG */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/alies-file-helpers-' . bin2hex(random_bytes(4)) . '/';
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->dir);
    }

    /** Runs the call with PHP warnings turned into a counter, and returns [result, warnings]. */
    private function withWarnings(callable $call): array
    {
        $warnings = 0;
        set_error_handler(static function () use (&$warnings): bool {
            $warnings++;

            return true;
        });
        try {
            $result = $call();
        } finally {
            restore_error_handler();
        }

        return [$result, $warnings];
    }

    // ---- move_file --------------------------------------------------------------------------

    public function testMoveFileCopiesTheContentAndRemovesTheSource(): void
    {
        file_put_contents($this->dir . 'a.txt', 'hello');

        $this->assertTrue(move_file($this->dir . 'a.txt', $this->dir . 'b.txt'));

        $this->assertFileDoesNotExist($this->dir . 'a.txt');
        $this->assertSame('hello', file_get_contents($this->dir . 'b.txt'));
    }

    public function testMoveFileOverwritesAnExistingTarget(): void
    {
        file_put_contents($this->dir . 'a.txt', 'new');
        file_put_contents($this->dir . 'b.txt', 'old');

        $this->assertTrue(move_file($this->dir . 'a.txt', $this->dir . 'b.txt'));

        $this->assertSame('new', file_get_contents($this->dir . 'b.txt'));
    }

    public function testMoveFileOfAMissingSourceFailsAndCreatesNothing(): void
    {
        [$result, $warnings] = $this->withWarnings(fn () => move_file($this->dir . 'missing.txt', $this->dir . 'target.txt'));

        $this->assertFalse($result);
        $this->assertGreaterThan(0, $warnings, 'copy() warns about the missing source');
        $this->assertFileDoesNotExist($this->dir . 'target.txt');
    }

    public function testMoveFileToAMissingDirectoryFailsAndKeepsTheSource(): void
    {
        file_put_contents($this->dir . 'a.txt', 'hello');

        [$result] = $this->withWarnings(fn () => move_file($this->dir . 'a.txt', $this->dir . 'no-such-dir/b.txt'));

        $this->assertFalse($result);
        $this->assertFileExists($this->dir . 'a.txt');
    }

    // ---- base64_to_image --------------------------------------------------------------------

    public function testAPngDataUriIsWrittenAndDescribed(): void
    {
        $result = base64_to_image('data:image/png;base64,' . self::PNG, $this->dir, 'avatar');

        $bytes = base64_decode(self::PNG);
        $this->assertSame(['avatar.png', 'image/png', strlen($bytes)], $result);
        $this->assertSame($bytes, file_get_contents($this->dir . 'avatar.png'));
    }

    /** @return array<string, array{string, string}> */
    public static function acceptedTypes(): array
    {
        return [
            'png' => ['png', 'png'],
            'jpg' => ['jpg', 'jpg'],
            'jpeg' => ['jpeg', 'jpeg'],
            'gif' => ['gif', 'gif'],
            'upper case is lowered' => ['PNG', 'png'],
            'mixed case is lowered' => ['JpEg', 'jpeg'],
        ];
    }

    #[DataProvider('acceptedTypes')]
    public function testAcceptedImageTypes(string $typeInUri, string $extension): void
    {
        $result = base64_to_image('data:image/' . $typeInUri . ';base64,' . self::PNG, $this->dir, 'pic');

        $this->assertSame('pic.' . $extension, $result[0]);
        $this->assertSame('image/' . $extension, $result[1]);
        $this->assertFileExists($this->dir . 'pic.' . $extension);
    }

    public function testOtherImageTypesAreRejected(): void
    {
        $this->expectExceptionObject(new Exception('invalid image type'));

        base64_to_image('data:image/webp;base64,' . self::PNG, $this->dir, 'pic');
    }

    public function testSvgIsNotEvenRecognisedAsAnImageUri(): void
    {
        // "svg+xml" contains a plus, which the \w+ in the pattern does not match
        $this->expectExceptionObject(new Exception('did not match data URI with image data'));

        base64_to_image('data:image/svg+xml;base64,' . self::PNG, $this->dir, 'pic');
    }

    public function testATextThatIsNoDataUriIsRejected(): void
    {
        $this->expectExceptionObject(new Exception('did not match data URI with image data'));

        base64_to_image('just some text', $this->dir, 'pic');
    }

    public function testADataUriWithoutBase64IsRejected(): void
    {
        $this->expectExceptionObject(new Exception('did not match data URI with image data'));

        base64_to_image('data:image/png,abc', $this->dir, 'pic');
    }

    public function testSpacesInThePayloadAreReadAsPluses(): void
    {
        // a "+" turns into a space in form posts; "++++" is the base64 of the three bytes FB EF BE
        $result = base64_to_image('data:image/png;base64,    ', $this->dir, 'plus');

        $this->assertSame(['plus.png', 'image/png', 3], $result);
        $this->assertSame("\xFB\xEF\xBE", file_get_contents($this->dir . 'plus.png'));
    }

    public function testAnUnwritableLocationGivesAnException(): void
    {
        $this->expectExceptionObject(new Exception('Could not write file.'));

        $this->withWarnings(fn () => base64_to_image('data:image/png;base64,' . self::PNG, $this->dir . 'no-such-dir/', 'pic'));
    }

    public function testTheLocationIsPrefixedAsIsSoItNeedsATrailingSlash(): void
    {
        $dirWithoutSlash = rtrim($this->dir, '/');

        base64_to_image('data:image/png;base64,' . self::PNG, $dirWithoutSlash, 'pic');

        // written next to the directory, not inside it
        $this->assertFileExists($dirWithoutSlash . 'pic.png');
        @unlink($dirWithoutSlash . 'pic.png');
    }
}
