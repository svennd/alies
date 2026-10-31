<?php

declare(strict_types=1);

/** Small helpers for the product tests (not part of the shared support code). */
final class ProductsHelper
{
    /** A valid 14 digit GTIN (GS1 mod 10 check digit) built from 13 random digits. */
    public static function gtin(): string
    {
        $base = '0' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        $sum = 0;
        // weights 3,1,3,1... from the right of the 13 digits
        for ($i = 0; $i < 13; $i++) {
            $digit = (int) $base[12 - $i];
            $sum += ($i % 2 === 0) ? $digit * 3 : $digit;
        }

        return $base . (string) ((10 - $sum % 10) % 10);
    }

    /** GS1 element string: GTIN, expiry (YYMMDD), lot. */
    public static function gs1(string $gtin, string $yymmdd, string $lot): string
    {
        return '01' . $gtin . '17' . $yymmdd . '10' . $lot;
    }
}
