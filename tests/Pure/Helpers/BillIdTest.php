<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/generate_bill_id_helper.php';

use PHPUnit\Framework\TestCase;

/**
 * T001 - application/helpers/generate_bill_id_helper.php: get_bill_id() and get_invoice_id().
 */
final class BillIdTest extends TestCase
{
    public function testBillIdIsPaddedWithZerosToSixDigits(): void
    {
        $this->assertSame('000042', get_bill_id(42));
        $this->assertSame('000000', get_bill_id(0));
        $this->assertSame('999999', get_bill_id(999999));
    }

    public function testBillIdLongerThanSixDigitsIsNotTruncated(): void
    {
        $this->assertSame('1234567', get_bill_id(1234567));
    }

    public function testInvoiceIdWithPrefixSettingStartsWithTheYearOfTheInvoiceDate(): void
    {
        // the setting is stored base64 encoded; any truthy decoded value switches the year prefix on
        $this->assertSame('202500012', get_invoice_id(12, '2025-03-01 10:00:00', base64_encode('1')));
        $this->assertSame('199912345', get_invoice_id(12345, '1999-12-31 23:59:59', base64_encode('1')));
    }

    public function testInvoiceIdWithoutPrefixSettingIsOnlyThePaddedNumber(): void
    {
        $this->assertSame('00012', get_invoice_id(12, '2025-03-01 10:00:00', ''));
        $this->assertSame('00012', get_invoice_id(12, '2025-03-01 10:00:00'));
    }

    public function testInvoiceIdPrefixSettingThatDecodesToZeroCountsAsOff(): void
    {
        // base64('0') decodes to "0", which PHP treats as false: so "0" means no year prefix
        $this->assertSame('00012', get_invoice_id(12, '2025-03-01 10:00:00', base64_encode('0')));
    }

    public function testInvoiceIdWithoutDateUsesTheCurrentYear(): void
    {
        $this->assertSame(date('Y') . '00012', get_invoice_id(12, false, base64_encode('1')));
    }

    public function testInvoiceIdLongerThanFiveDigitsIsNotTruncated(): void
    {
        $this->assertSame('123456', get_invoice_id(123456, '2025-03-01 10:00:00', ''));
        $this->assertSame('2025123456', get_invoice_id(123456, '2025-03-01 10:00:00', base64_encode('1')));
    }
}
