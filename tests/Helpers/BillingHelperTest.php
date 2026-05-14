<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BillingHelperTest extends TestCase
{
    public function testGetBillIdPadsToSixDigits(): void
    {
        $this->assertSame('000042', get_bill_id(42));
    }

    public function testGetInvoiceIdOptionallyPrefixesTheYear(): void
    {
        $this->assertSame(
            '202500042',
            get_invoice_id(42, '2025-03-14 12:00:00', base64_encode('1'))
        );
        $this->assertSame(
            '00042',
            get_invoice_id(42, '2025-03-14 12:00:00', '')
        );
    }

    public function testGenerateStructMessageSupportsConfiguredModes(): void
    {
        $this->assertSame(
            '+++123/4000/05660+++',
            generate_struct_message(1234, 56, CLIENT_BILL)
        );
        $this->assertSame(
            '+++123/4000/00047+++',
            generate_struct_message(1234, 56, CLIENT)
        );
        $this->assertSame(
            '+++123/4000/05660+++',
            generate_struct_message(1234, 56, CLIENT_3DIGIT_BILL)
        );
    }
}
