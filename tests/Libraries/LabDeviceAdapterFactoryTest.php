<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabDeviceAdapterFactoryTest extends TestCase
{
    public function testFactoryResolvesEverySupportedDeviceAndRejectsUnknownDevice(): void
    {
        require_once APPPATH . 'libraries/Lab_device_adapter_factory.php';
        $factory = new Lab_device_adapter_factory();

        $this->assertInstanceOf(Ms4s2::class, $factory->create('ms4s2'));
        $this->assertInstanceOf(Ikems::class, $factory->create('ikems'));
        $this->assertInstanceOf(Lmscan::class, $factory->create('lmscan'));
        $this->assertInstanceOf(Medilab::class, $factory->create('medilab'));
        $this->assertNull($factory->create('unknown'));
    }

    public function testApiControllerUsesSharedFactory(): void
    {
        $source = file_get_contents(APPPATH . 'controllers/api/Lab.php');

        $this->assertStringContainsString("load->library('lab_device_adapter_factory')", $source);
        $this->assertStringContainsString('lab_device_adapter_factory->create($device)', $source);
        $this->assertStringNotContainsString('function adapterFactory', $source);
    }
}
