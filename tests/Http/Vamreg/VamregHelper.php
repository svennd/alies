<?php

declare(strict_types=1);

/**
 * Rows for the Vamreg tests (tables vamreg_in_buffer and vamreg_out_buffer). Works in both layers
 * because it only uses Factory.
 */
final class VamregHelper
{
    private static int $line = 900000;

    /** An IN declaration (delivery of an antibiotic pack count). */
    public static function in(string $delivery, array $over = []): int
    {
        return Factory::insert('vamreg_in_buffer', $over + [
            'cnk' => '1234567',
            'wholesale_id' => null,
            'in_quantity_pack_count' => 5,
            'delivery' => $delivery,
            'product_type' => 'BE',
            'provider_type' => 'DIST_BE',
            'status' => 'DRAFT',
            'created_at' => Factory::raw('NOW()'),
        ]);
    }

    /** An OUT declaration (use of an antibiotic in a consultation). */
    public static function out(string $date, array $over = []): int
    {
        self::$line++;

        return Factory::insert('vamreg_out_buffer', $over + [
            'event' => 6001,
            'event_line' => self::$line + random_int(0, 99999) * 10,
            'cnk' => '1234567',
            'out_quantity_type' => 'PACKS',
            'out_quantity_pack_count' => 1,
            'out_quantity_unit_count' => null,
            'out_quantity_unit' => null,
            'out_date' => $date,
            'product_type' => 'BE',
            'target_species' => 'DOG',
            'indication' => 'RESP',
            'vet' => 2,
            'status' => 'DRAFT',
            'created_at' => Factory::raw('NOW()'),
        ]);
    }
}
