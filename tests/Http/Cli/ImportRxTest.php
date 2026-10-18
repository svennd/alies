<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T236 - Rx import from the command line (`php index.php cli import_rx [full]`).
 * Source: application/controllers/Cli.php::import_rx
 *
 * The command scans data/stored/rx/. Without an argument it only looks at folders whose name starts with last or
 * this month (YYYY-MM...); any argument turns on a full scan of every folder, which would also import the real
 * X-rays lying in that directory, so the tests use the default scan with a unique folder named after today.
 * For every `<name>.jpg` that is not in the rx table yet (key: `<folder>/<name>`) the sidecar `<name>.txt`
 * (note: NOT `<name>.jpg.txt`) is read as `Key:Value` lines and one row is inserted; files without a sidecar are
 * skipped. PatientName `CLIENT^PET` is split, PatientSex F gives FEMALE (1), anything else MALE (0).
 */
final class ImportRxTest extends HttpTestCase
{
    private string $root;
    /** @var list<string> */
    private array $dirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 3) . '/data/stored/rx/';
    }

    protected function tearDown(): void
    {
        foreach ($this->dirs as $dir) {
            foreach (glob($dir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
        parent::tearDown();
    }

    /** @param array<string, string|null> $files file name => content (null = empty image only) */
    private function folder(array $files): string
    {
        $name = date('Y-m-d') . '_t236_' . uniqid();
        $dir = $this->root . $name;
        mkdir($dir, 0775, true);
        $this->dirs[] = $dir;
        foreach ($files as $file => $content) {
            file_put_contents($dir . '/' . $file, $content ?? 'jpgdata');
        }

        return $name;
    }

    private function sidecar(int $pet, array $over = []): string
    {
        $lines = $over + [
            'PatientID' => (string) $pet,
            'PatientName' => 'JANSSENS^REX',
            'PatientSex' => 'F',
            'PatientBirthDate' => '20200304',
            'StudyDate' => '2026-03-04',
            'StudyDescription' => 'Study thorax',
            'SeriesDescription' => 'Thorax LL',
            'BodyPartExamined' => 'THORAX',
            'SeriesNumber' => '3',
        ];
        $out = [];
        foreach ($lines as $key => $value) {
            if ($value !== null) {
                $out[] = $key . ':' . $value;
            }
        }

        return implode("\n", $out) . "\n";
    }

    private function runImport(): void
    {
        [$exit, $out, $err] = $this->cli(['cli', 'import_rx']);
        $this->assertSame(0, $exit, $out . $err);
    }

    public function testAnImageWithItsSidecarIsImportedWithAllMappedFields(): void
    {
        $pet = Factory::pet(Factory::owner());
        $dir = $this->folder(['a.jpg' => null, 'a.txt' => $this->sidecar($pet)]);

        $this->runImport();

        $row = $this->row('SELECT * FROM rx WHERE path = ?', [$dir . '/a']);
        $this->assertNotNull($row, 'the path is <folder>/<name> without extension');
        $this->assertSame($pet, (int) $row['pet_id']);
        $this->assertSame('2026-03-04', $row['studydate']);
        $this->assertSame('Thorax LL', $row['description']);
        $this->assertSame('THORAX', $row['bodypart']);
        $this->assertSame('JANSSENS', $row['client']);
        $this->assertSame('REX', $row['petname']);
        $this->assertSame(1, (int) $row['gender'], 'F = FEMALE');
        $this->assertSame('Study thorax', $row['studydescription']);
        $this->assertSame(3, (int) $row['series']);
        $this->assertNotEmpty($row['petbirthdate']);
    }

    public function testRunningItAgainInsertsNothing(): void
    {
        $pet = Factory::pet(Factory::owner());
        $dir = $this->folder(['a.jpg' => null, 'a.txt' => $this->sidecar($pet)]);
        $this->runImport();
        $before = $this->countWhere('rx');

        $this->runImport();

        $this->assertSame($before, $this->countWhere('rx'));
        $this->assertSame(1, $this->countWhere('rx', ['path' => $dir . '/a']));
    }

    public function testAMaleAndAnUnsplittableOwnerNameAreMapped(): void
    {
        $pet = Factory::pet(Factory::owner());
        $dir = $this->folder([
            'm.jpg' => null,
            'm.txt' => $this->sidecar($pet, ['PatientSex' => 'M', 'PatientName' => 'ONLYCLIENT']),
        ]);

        $this->runImport();

        $row = $this->row('SELECT * FROM rx WHERE path = ?', [$dir . '/m']);
        $this->assertSame(0, (int) $row['gender']);
        $this->assertSame('ONLYCLIENT', $row['client']);
        $this->assertSame('', (string) $row['petname']);
    }

    public function testAnImageWithoutSidecarAndOtherFilesAreSkipped(): void
    {
        $pet = Factory::pet(Factory::owner());
        $dir = $this->folder(['x.jpg' => null, 'y.png' => null, 'y.txt' => $this->sidecar($pet)]);

        $this->runImport();

        $this->assertSame(0, $this->countWhere('rx', ['pet_id' => $pet]));
        $this->assertNull($this->row('SELECT id FROM rx WHERE path LIKE ?', [$dir . '/%']));
    }

    public function testFoldersOfOtherMonthsAreNotScannedWithoutTheFullScanFlag(): void
    {
        $pet = Factory::pet(Factory::owner());
        $old = '2019-01-01_t236_' . uniqid();
        mkdir($this->root . $old, 0775, true);
        $this->dirs[] = $this->root . $old;
        file_put_contents($this->root . $old . '/o.jpg', 'x');
        file_put_contents($this->root . $old . '/o.txt', $this->sidecar($pet));

        $this->runImport();

        $this->assertNull($this->row('SELECT id FROM rx WHERE path = ?', [$old . '/o']));
    }

    #[Group('known-bug')]
    public function testMetadataLeaksFromTheFirstFileIntoASecondOneThatLacksAKey(): void
    {
        $pet = Factory::pet(Factory::owner());
        $dir = $this->folder([
            'a.jpg' => null, 'a.txt' => $this->sidecar($pet, ['SeriesDescription' => 'Leaky description', 'BodyPartExamined' => 'SKULL']),
            // second sidecar misses SeriesDescription and BodyPartExamined
            'b.jpg' => null, 'b.txt' => $this->sidecar($pet, ['SeriesDescription' => null, 'BodyPartExamined' => null, 'SeriesNumber' => '9']),
        ]);

        $this->runImport();

        $a = $this->row('SELECT * FROM rx WHERE path = ?', [$dir . '/a']);
        $b = $this->row('SELECT * FROM rx WHERE path = ?', [$dir . '/b']);
        $this->assertSame('Leaky description', $a['description']);
        // KNOWN BUG: $meta is not reset between files, so b inherits a's description and body part -- decide in CI4 whether to keep
        $this->assertSame('Leaky description', $b['description']);
        $this->assertSame('SKULL', $b['bodypart']);
        $this->assertSame(9, (int) $b['series'], 'control: the keys b does have are its own');
    }
}
