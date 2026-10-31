<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T353 Which models use soft deletes.   Source: application/models/*.php
 *
 * Rules in plain words:
 *  - soft_deletes is true for Booking_code, Bills, Owners, Pets, Payment, Stock_location, Procedures, Products, Sticky
 *    and false for every other model. This is the checklist for the CI4 $useSoftDeletes settings.
 *  - Models that do not extend MY_Model (Ion_auth_model, LabReportPending_model, Events_lab_model) have no such
 *    property: counted as "false".
 *  - Procedures_products_model.php is skipped: the file declares class "Procedures", so the loader cannot create
 *    "Procedures_products_model" from it (and the class name would clash with the Procedures controller).
 *  - Events_links_model.php is skipped too: its table events_links does not exist in the schema, and MY_Model dies
 *    (show_error) while it looks up the columns. It is not in the soft delete list.
 *  - LabReport_model declares $soft_delete (typo), so soft deletes are off there although a deleted_at column exists.
 */
final class SoftDeleteInventoryTest extends CodeIgniterDatabaseTestCase
{
    private const SOFT = array(
        'Booking_code_model', 'Bills_model', 'Owners_model', 'Pets_model', 'Payment_model',
        'Stock_location_model', 'Procedures_model', 'Products_model', 'Sticky_model',
    );

    private const SKIPPED = array('Procedures_products_model', 'Events_links_model');

    /** @return array<string, array{0: string}> */
    public static function modelFiles(): array
    {
        $files = array();
        foreach (glob(APPPATH . 'models/*_model.php') as $path) {
            $name = basename($path, '.php');
            if (in_array($name, self::SKIPPED, true)) {
                continue;
            }
            $files[$name] = array($name);
        }

        return $files;
    }

    private function softDeletes(string $class): bool
    {
        $model = $this->model($class, strtolower('inv_' . $class));
        $property = new ReflectionProperty(MY_Model::class, 'soft_deletes');

        if (!$model instanceof MY_Model) {
            return false;
        }

        return $property->getValue($model);
    }

    #[PHPUnit\Framework\Attributes\DataProvider('modelFiles')]
    public function testSoftDeletesFlagMatchesTheInventory(string $class): void
    {
        $expected = in_array($class, self::SOFT, true);

        $this->assertSame($expected, $this->softDeletes($class), $class);
    }

    public function testEveryModelFileIsCoveredAndTheSoftListIsComplete(): void
    {
        $names = array_keys(self::modelFiles());

        $this->assertGreaterThan(50, count($names), 'the provider finds the model files');
        foreach (self::SOFT as $soft) {
            $this->assertContains($soft, $names, $soft . ' exists');
        }
        $this->assertCount(9, self::SOFT);
        foreach (self::SKIPPED as $skipped) {
            $this->assertFileExists(APPPATH . 'models/' . $skipped . '.php', 'the skipped file exists');
        }
    }

    #[Group('known-bug')]
    public function testLabReportModelHasATypoInTheSoftDeleteFlag(): void
    {
        // KNOWN BUG: LabReport_model declares $soft_delete instead of $soft_deletes - deleted_at is never filtered
        $model = $this->model('LabReport_model', 'inv_labreport_typo');
        $this->assertFalse($this->softDeletes('LabReport_model'));
        $this->assertTrue(property_exists($model, 'soft_delete'));
        $this->assertTrue($model->soft_delete);

        // the effect: a deleted report is still returned
        Factory::useMysqli($this->ci->db->conn_id);
        $pet = Factory::pet(Factory::owner());
        $report = Factory::labReport($pet, array(), array('deleted_at' => Factory::raw('NOW()')));

        $row = $model->get($report);

        $this->assertIsArray($row);
        $this->assertSame($report, (int) $row['id']);
        $this->assertNotNull($row['deleted_at']);
    }
}
