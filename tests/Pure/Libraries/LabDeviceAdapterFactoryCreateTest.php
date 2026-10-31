<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Lab_device_adapter_factory.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T022 - application/libraries/Lab_device_adapter_factory.php: device name => adapter.
 * (The check that the API controller uses the factory is a source-pattern test and lives in
 * tests/Libraries/LabDeviceAdapterFactoryTest.php.)
 */
final class LabDeviceAdapterFactoryCreateTest extends TestCase
{
    /** @return array<string, array{string, class-string}> */
    public static function devices(): array
    {
        return [
            'ms4s2' => ['ms4s2', Ms4s2::class],
            'ikems' => ['ikems', Ikems::class],
            'lmscan' => ['lmscan', Lmscan::class],
            'medilab' => ['medilab', Medilab::class],
            'upper case' => ['MS4S2', Ms4s2::class],
            'mixed case' => ['MeDiLab', Medilab::class],
            'capitalised' => ['Ikems', Ikems::class],
        ];
    }

    #[DataProvider('devices')]
    public function testEverySupportedDeviceGetsItsAdapter(string $device, string $class): void
    {
        $adapter = (new Lab_device_adapter_factory())->create($device);

        $this->assertInstanceOf($class, $adapter);
        $this->assertInstanceOf(DeviceAdapterInterface::class, $adapter);
    }

    /** @return array<string, array{mixed}> */
    public static function unknown(): array
    {
        return [
            'unknown name' => ['unknown'],
            'empty' => [''],
            'null' => [null],
            'surrounding space is not trimmed' => [' ms4s2'],
            'path like' => ['../ms4s2'],
            'class name of the interface' => ['DeviceAdapterInterface'],
        ];
    }

    #[DataProvider('unknown')]
    public function testUnknownDevicesGiveNull(mixed $device): void
    {
        $this->assertNull((new Lab_device_adapter_factory())->create($device));
    }

    public function testEveryCallGivesANewAdapter(): void
    {
        $factory = new Lab_device_adapter_factory();

        $this->assertNotSame($factory->create('lmscan'), $factory->create('lmscan'));
    }
}
