<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/get_symbol_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T009 - application/helpers/get_symbol_helper.php.
 *
 * Values used (application/config/constants.php): pet types DOG 0, CAT 1, HORSE 2, BIRD 3, OTHER 4,
 * RABBIT 5; genders MALE 0, FEMALE 1, MALE_NEUTERED 2, FEMALE_NEUTERED 3; event types DISEASE 0,
 * OPERATION 1, MEDICINE 2, LAB 3; bill status -1..7; stock state 0..4; log levels FATAL 1 .. DEBUG 5.
 * They are stored in the database, so the numbers are part of the contract: literals are used here.
 *
 * The helpers return HTML; only the parts that matter (icon class, colour, label) are asserted.
 */
final class GetSymbolHelperTest extends TestCase
{
    // ---- pet type ---------------------------------------------------------------------------

    /** @return array<string, array{int, string, string, string}> type, icon class, colour, name */
    public static function petTypes(): array
    {
        return [
            'dog' => [0, 'fa-dog', '#628395', 'Dog'],
            'cat' => [1, 'fa-cat', '#96897B', 'Cat'],
            'horse' => [2, 'fa-horse', '#CF995F', 'Horse'],
            'bird' => [3, 'fa-dove', '#DBAD6A', 'Bird'],
            'rabbit' => [5, 'fa-paw', '#dec5a1', 'Rabbit'],
            'other' => [4, 'fa-ghost', '#DFD5A5', 'Other'],
            'unknown type' => [99, 'fa-ghost', '#DFD5A5', 'Other'],
        ];
    }

    #[DataProvider('petTypes')]
    public function testGetSymbolHasTheIconAndColourOfThePetType(int $type, string $icon, string $colour, string $name): void
    {
        $html = get_symbol($type);

        $this->assertStringContainsString($icon, $html);
        $this->assertStringContainsString($colour, $html);
    }

    #[DataProvider('petTypes')]
    public function testGetSymbolAppendsTheNameOnlyWhenAsked(int $type, string $icon, string $colour, string $name): void
    {
        $this->assertStringEndsNotWith($name, get_symbol($type));
        $this->assertStringEndsWith(' ' . $name, get_symbol($type, true));
    }

    #[DataProvider('petTypes')]
    public function testGetNameIsTheEnglishNameOfThePetType(int $type, string $icon, string $colour, string $name): void
    {
        $this->assertSame($name, get_name($type));
    }

    // ---- gender -----------------------------------------------------------------------------

    /** @return array<string, array{int, string, string}> */
    public static function genders(): array
    {
        return [
            'male' => [0, 'fa-mars', ' Male'],
            'female' => [1, 'fa-venus', ' Female'],
            'male neutered' => [2, 'fa-mars', ' Male neutered'],
            'female neutered' => [3, 'fa-venus', ' Female neutered'],
            'other' => [4, 'fa-genderless', ' Other'],
            'unknown' => [99, 'fa-genderless', ' Other'],
        ];
    }

    #[DataProvider('genders')]
    public function testGetGender(int $gender, string $icon, string $label): void
    {
        $html = get_gender($gender);

        $this->assertStringContainsString($icon, $html);
        $this->assertStringEndsWith($label, $html);
    }

    // ---- event type -------------------------------------------------------------------------

    /** @return array<string, array{int, string, string, string}> type, icon, data-name, label with name */
    public static function eventTypes(): array
    {
        return [
            'disease' => [0, 'fa-virus', 'disease', ' disease'],
            // the data-name of an operation is the Dutch word, of a lab it is "medicine" (copy paste)
            'operation' => [1, 'fa-hand-holding-medical', 'operatie', ' operation'],
            'medicine' => [2, 'fa-prescription-bottle-alt', 'medicine', ' medicine'],
            'lab' => [3, 'fa-flask', 'medicine', ' lab'],
        ];
    }

    #[DataProvider('eventTypes')]
    public function testGetEventType(int $type, string $icon, string $dataName, string $label): void
    {
        $html = get_event_type($type);

        $this->assertStringContainsString($icon, $html);
        $this->assertStringContainsString('data-name="' . $dataName . '"', $html);
        $this->assertStringEndsNotWith($label, $html);
        $this->assertStringEndsWith($label, get_event_type($type, true));
    }

    public function testUnknownEventTypeIsAnEmptyIconWithoutNameEvenWhenAsked(): void
    {
        $html = get_event_type(99, true);

        $this->assertStringContainsString('data-name="other"', $html);
        $this->assertStringEndsWith('</span>', $html);
    }

    // ---- bill status ------------------------------------------------------------------------

    /** @return array<string, array{int, string}> */
    public static function billStatusText(): array
    {
        return [
            'invalid' => [-1, 'invalid'],
            'draft' => [0, 'draft'],
            'pending' => [1, 'pending'],
            'unpaid' => [2, 'unpaid'],
            'incomplete' => [3, 'incomplete'],
            // note the capital: the only status with one
            'paid' => [4, 'Paid'],
            'overdue' => [5, 'overdue'],
            'onhold' => [6, 'onhold'],
            'historical' => [7, 'historical'],
            'unknown' => [8, 'unknown'],
            'far out' => [99, 'unknown'],
        ];
    }

    #[DataProvider('billStatusText')]
    public function testGetBillStatusText(int $status, string $expected): void
    {
        $this->assertSame($expected, get_bill_status($status));
    }

    /** @return array<string, array{int, string}> */
    public static function billStatusIcons(): array
    {
        return [
            'invalid spins' => [-1, 'fa-arrows-spin'],
            'draft spins' => [0, 'fa-arrows-spin'],
            'pending spins' => [1, 'fa-arrows-spin'],
            'unpaid spins' => [2, 'fa-arrows-spin'],
            'incomplete spins' => [3, 'fa-arrows-spin'],
            'paid is a check' => [4, 'fa-check'],
            // overdue is not in the list of the icon switch, so it gets the "unknown" icon
            'overdue is a question mark' => [5, 'fa-clipboard-question'],
            'onhold spins' => [6, 'fa-arrows-spin'],
            'historical is an import' => [7, 'fa-file-import'],
            'unknown is a question mark' => [99, 'fa-clipboard-question'],
        ];
    }

    #[DataProvider('billStatusIcons')]
    public function testGetBillStatusIcon(int $status, string $iconClass): void
    {
        $this->assertStringContainsString($iconClass, get_bill_status($status, true));
    }

    // ---- stock state ------------------------------------------------------------------------

    /** @return array<string, array{int, string}> */
    public static function stockStates(): array
    {
        return [
            'check' => [0, 'check'],
            'in use' => [1, 'in_use'],
            'history' => [2, 'history'],
            'error' => [3, 'error'],
            'merged' => [4, 'stock_merged'],
            'unknown' => [5, 'unknown'],
            'negative' => [-1, 'unknown'],
        ];
    }

    #[DataProvider('stockStates')]
    public function testStockState(int $state, string $expected): void
    {
        $this->assertSame($expected, stock_state($state));
    }

    // ---- log level --------------------------------------------------------------------------

    /** @return array<string, array{int, string, string}> level, text, icon class */
    public static function errorLevels(): array
    {
        return [
            'fatal' => [1, 'FATAL', 'fa-skull-crossbones'],
            'error' => [2, 'ERROR', 'fa-circle-exclamation'],
            'warn' => [3, 'WARN', 'fa-triangle-exclamation'],
            'info' => [4, 'INFO', 'fa-info'],
            'debug' => [5, 'DEBUG', 'fa-bug'],
            'zero' => [0, 'unknown', 'fa-question'],
            'too high' => [6, 'unknown', 'fa-question'],
        ];
    }

    #[DataProvider('errorLevels')]
    public function testGetErrorLevelText(int $level, string $text, string $iconClass): void
    {
        $this->assertSame($text, get_error_level($level));
    }

    #[DataProvider('errorLevels')]
    public function testGetErrorLevelIcon(int $level, string $text, string $iconClass): void
    {
        $this->assertStringContainsString($iconClass, get_error_level($level, true));
    }
}
