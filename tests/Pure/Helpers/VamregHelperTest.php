<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/vamreg_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T011 - application/helpers/vamreg_helper.php: building the quantity fields, species and indication
 * of a Vamreg (antibiotic register) declaration. Pet types: DOG 0, CAT 1, HORSE 2, BIRD 3, OTHER 4, RABBIT 5.
 */
final class VamregHelperTest extends TestCase
{
    public function testPacksAreCountedInPacksWithoutAUnit(): void
    {
        $this->assertSame(
            [
                'out_quantity_type' => 'PACKS',
                'out_quantity_pack_count' => 2.0,
                'out_quantity_unit_count' => null,
                'out_quantity_unit' => null,
            ],
            get_vamreg_out_unit('PACKS', 2)
        );
    }

    /** @return array<string, array{string}> */
    public static function units(): array
    {
        return ['millilitre' => ['ML'], 'gram' => ['G'], 'piece' => ['PIECE'], 'tube' => ['TUBE'], 'prestation' => ['PRESTATION']];
    }

    #[DataProvider('units')]
    public function testAnyOtherUnitIsCountedInUnits(string $unit): void
    {
        $this->assertSame(
            [
                'out_quantity_type' => 'UNITS',
                'out_quantity_pack_count' => null,
                'out_quantity_unit_count' => 5.5,
                'out_quantity_unit' => $unit,
            ],
            get_vamreg_out_unit($unit, 5.5)
        );
    }

    public function testTheUnitIsCaseSensitiveSoLowercasePacksBecomesUnits(): void
    {
        // quirk: the register only accepts upper case units, "packs" is sent on as a (wrong) unit
        $result = get_vamreg_out_unit('packs', 1);

        $this->assertSame('UNITS', $result['out_quantity_type']);
        $this->assertSame('packs', $result['out_quantity_unit']);
    }

    public function testAnEmptyUnitIsSentOnAsAnEmptyUnit(): void
    {
        $result = get_vamreg_out_unit('', 3);

        $this->assertSame('UNITS', $result['out_quantity_type']);
        $this->assertSame('', $result['out_quantity_unit']);
    }

    public function testTheUnitMustBeAString(): void
    {
        $this->expectException(TypeError::class);

        get_vamreg_out_unit(null, 1);
    }

    /** @return array<string, array{int, string}> */
    public static function species(): array
    {
        return [
            'dog' => [0, 'DOG'],
            'cat' => [1, 'CAT'],
            'horse' => [2, 'HORSE'],
            'bird' => [3, 'OTHER_NON_FOOD'],
            'other' => [4, 'OTHER_NON_FOOD'],
            'rabbit' => [5, 'OTHER_NON_FOOD'],
            'unknown' => [99, 'OTHER_NON_FOOD'],
            'negative' => [-1, 'OTHER_NON_FOOD'],
        ];
    }

    #[DataProvider('species')]
    public function testTargetSpecies(int $petType, string $expected): void
    {
        $this->assertSame($expected, get_vamreg_target_species($petType));
    }

    /** @return array<string, array{string}> */
    public static function validIndications(): array
    {
        $codes = ['DIGEST', 'EYE', 'LOCO', 'MAST', 'NERVE', 'PERI_OP', 'RESP', 'DERMA', 'SYST', 'URO_GEN'];

        return array_combine($codes, array_map(static fn (string $c): array => [$c], $codes));
    }

    #[DataProvider('validIndications')]
    public function testValidIndicationsAreReturnedUnchanged(string $indication): void
    {
        $this->assertSame($indication, valid_vamreg_indication($indication));
    }

    /** @return array<string, array{?string}> */
    public static function invalidIndications(): array
    {
        return [
            'lowercase' => ['eye'],
            'NONE is a product default, not a declaration value' => ['NONE'],
            'empty string' => [''],
            'null' => [null],
            'with a space' => [' EYE'],
            'unknown code' => ['HEART'],
        ];
    }

    #[DataProvider('invalidIndications')]
    public function testInvalidIndicationsBecomeNull(?string $indication): void
    {
        $this->assertNull(valid_vamreg_indication($indication));
    }
}
