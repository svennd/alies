<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T272 (pin) - Reports::clients_list when there are no owners to export.
 * Separate class because the test soft-deletes every owner of the (per class) database.
 */
final class ClientListEmptyXlsxTest extends HttpTestCase
{
    #[Group('known-bug')]
    public function testAnEmptyClientListIsATypeErrorAndNotAnEmptySheet(): void
    {
        $this->loginAs('admin');
        $this->assertStatus($this->get('reports/clients_list'), 200); // positive control: seeded owners exist

        $this->db()->exec('UPDATE owners SET deleted_at = NOW()');

        // KNOWN BUG: Owners_model::get_all() returns false for "nothing"; ExcelExporter::exportToExcel(array $data)
        // then fails with a TypeError, so the user gets a 500 instead of an empty sheet.
        $this->assertStatus($this->get('reports/clients_list'), 500);
        $this->assertStatus($this->get('reports/clients_list/1'), 500);
    }
}
