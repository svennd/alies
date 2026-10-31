<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Source-pattern test, only meaningful for the CodeIgniter 3 controller. The factory itself is
 * tested in tests/Pure/Libraries/LabDeviceAdapterFactoryCreateTest.php.
 */
#[Group('ci3-only')]
final class LabDeviceAdapterFactoryTest extends TestCase
{
    public function testApiControllerUsesSharedFactory(): void
    {
        $source = file_get_contents(APPPATH . 'controllers/api/Lab.php');

        $this->assertStringContainsString("load->library('lab_device_adapter_factory')", $source);
        $this->assertStringContainsString('lab_device_adapter_factory->create($device)', $source);
        $this->assertStringNotContainsString('function adapterFactory', $source);
    }
}
