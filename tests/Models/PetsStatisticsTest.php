<?php

declare(strict_types=1);

/**
 * T361 Pets statistics, duplicate chips and format_transfer_history.   Source: application/models/Pets_model.php
 *
 * Rules in plain words:
 *  - duplicate_chips lists chips used by more than one pet; empty chips and transferred pets are ignored.
 *  - format_transfer_history appends "Overgenomen producten/procedures" lists to a report: quantity 1.50 -> 1.5,
 *    2.00 -> 2, -0 -> 0; names and units are HTML escaped; an <hr> separates it from a report that is not blank.
 */
final class PetsStatisticsTest extends CodeIgniterDatabaseTestCase
{
    private $pets;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->pets = $this->model('Pets_model', 'pets_stat');
    }

    public function testDuplicateChipsListsSharedChipsAndIgnoresTransferredPets(): void
    {
        $chip = '98100' . random_int(1000000000, 9999999999);
        $owner = Factory::owner();
        $a = Factory::pet($owner, ['chip' => $chip]);
        $b = Factory::pet($owner, ['chip' => $chip]);
        Factory::pet($owner, ['chip' => $chip, 'transfered' => 1]);
        // a chip shared by a live and a transferred pet is not a duplicate
        $single = '98200' . random_int(1000000000, 9999999999);
        Factory::pet($owner, ['chip' => $single]);
        Factory::pet($owner, ['chip' => $single, 'transfered' => 1]);

        $rows = $this->pets->duplicate_chips();

        $byChip = array_column($rows, null, 'chip');
        $this->assertArrayHasKey($chip, $byChip);
        $this->assertSame(2, (int) $byChip[$chip]['cnt']);
        $ids = array_map('intval', explode(',', $byChip[$chip]['pet_ids']));
        sort($ids);
        $this->assertSame([$a, $b], $ids);
        $this->assertArrayNotHasKey($single, $byChip);
    }

    public function testDuplicateChipsIgnoresEmptyChips(): void
    {
        $owner = Factory::owner();
        Factory::pet($owner, ['chip' => '']);
        Factory::pet($owner, ['chip' => '']);
        Factory::pet($owner, ['chip' => null]);
        Factory::pet($owner, ['chip' => null]);

        $chips = array_column($this->pets->duplicate_chips(), 'chip');

        $this->assertNotContains('', $chips);
        $this->assertNotContains(null, $chips);
    }

    public function testDuplicateChipsAlsoCountsSoftDeletedPets(): void
    {
        // pinned: the raw SQL has no deleted_at filter
        $chip = '98300' . random_int(1000000000, 9999999999);
        $owner = Factory::owner();
        Factory::pet($owner, ['chip' => $chip]);
        Factory::pet($owner, ['chip' => $chip, 'deleted_at' => Factory::raw('NOW()')]);

        $byChip = array_column($this->pets->duplicate_chips(), null, 'chip');

        $this->assertSame(2, (int) $byChip[$chip]['cnt']);
    }

    // ------------------------------------------------------------------ format_transfer_history

    public function testQuantitiesAreShownWithoutUselessZeros(): void
    {
        $html = $this->pets->format_transfer_history('', array(
            array('name' => 'A', 'volume' => '1.50'),
            array('name' => 'B', 'volume' => '2.00'),
            array('name' => 'C', 'volume' => '-0.001'),
            array('name' => 'D', 'volume' => 0),
            array('name' => 'E', 'volume' => '100.00'),
            array('name' => 'F', 'volume' => '0.25'),
        ), array());

        $this->assertSame(
            '<h4>Overgenomen producten</h4><ul>'
            . '<li><strong>A</strong>: 1.5</li>'
            . '<li><strong>B</strong>: 2</li>'
            . '<li><strong>C</strong>: 0</li>'
            . '<li><strong>D</strong>: 0</li>'
            . '<li><strong>E</strong>: 100</li>'
            . '<li><strong>F</strong>: 0.25</li>'
            . '</ul>',
            $html
        );
    }

    public function testNamesAndUnitsAreEscapedAndUnitIsAppended(): void
    {
        $html = $this->pets->format_transfer_history('', array(
            array('name' => '<script>x</script> & "q"', 'volume' => 1, 'unit' => ' <ml> '),
            array('name' => 'Leeg', 'volume' => 1, 'unit' => '  '),
        ), array());

        $this->assertStringContainsString('<li><strong>&lt;script&gt;x&lt;/script&gt; &amp; &quot;q&quot;</strong>: 1 &lt;ml&gt;</li>', $html);
        $this->assertStringContainsString('<li><strong>Leeg</strong>: 1</li>', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function testProductsAndProceduresGetTheirOwnSections(): void
    {
        $html = $this->pets->format_transfer_history('', array(array('name' => 'P', 'volume' => 1)), array(array('name' => 'Q', 'volume' => 3)));

        $this->assertSame(
            '<h4>Overgenomen producten</h4><ul><li><strong>P</strong>: 1</li></ul>'
            . '<h4>Overgenomen procedures</h4><ul><li><strong>Q</strong>: 3</li></ul>',
            $html
        );
        $onlyProcedures = $this->pets->format_transfer_history('', array(), array(array('name' => 'Q', 'volume' => 3)));
        $this->assertSame('<h4>Overgenomen procedures</h4><ul><li><strong>Q</strong>: 3</li></ul>', $onlyProcedures);
    }

    public function testAnHrIsOnlyAddedWhenTheReportIsNotBlank(): void
    {
        $items = array(array('name' => 'P', 'volume' => 1));
        $section = '<h4>Overgenomen producten</h4><ul><li><strong>P</strong>: 1</li></ul>';

        $this->assertSame('verslag<hr>' . $section, $this->pets->format_transfer_history('verslag', $items, array()));
        $this->assertSame($section, $this->pets->format_transfer_history('', $items, array()));
        $this->assertSame(" \n " . $section, $this->pets->format_transfer_history(" \n ", $items, array()));
    }

    public function testWithoutItemsTheReportIsReturnedUnchanged(): void
    {
        $this->assertSame('verslag', $this->pets->format_transfer_history('verslag', array(), array()));
        $this->assertSame('', $this->pets->format_transfer_history('', array(), array()));
    }
}
