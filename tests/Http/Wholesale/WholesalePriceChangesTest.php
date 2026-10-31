<?php

declare(strict_types=1);

/**
 * T211 - Wholesale::pricing, accept, accept_all, ignore, unignore (Wholesale_model::get_bruto_increase,
 * accept_price, accept_all). Admin only.
 *
 * Rules: pricing lists articles that are linked to an internal product, have a last_bruto and whose
 * bruto is higher than last_bruto, with the increase in percent (1 decimal). accept copies bruto to
 * last_bruto and stamps today. accept_all does that for every row. ignore/unignore only toggle
 * ignore_change. All four redirect: to wholesale/index, or to wholesale/get_history/{id} when the
 * second segment is "history" (any other value means index; accept_all goes to wholesale/pricing).
 */
final class WholesalePriceChangesTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    /** A wholesale row linked to a product, with the given prices. @return array{0:int,1:int,2:string} */
    private function linked(float $bruto, ?float $last): array
    {
        $name = Factory::unique('PriceProd');
        $wh = Factory::wholesale(['bruto' => $bruto, 'last_bruto' => $last, 'last_bruto_date' => '2020-01-01']);
        $pid = Factory::product(['name' => $name, 'wholesale' => $wh]);

        return [$wh, $pid, $name];
    }

    public function testNonAdminsAreSentHome(): void
    {
        [$wh] = $this->linked(11.0, 10.0);
        $this->cookies->clear();
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->get('wholesale/pricing'), '');
        $this->assertRedirectsTo($this->get('wholesale/accept/' . $wh), '');
        $this->assertRedirectsTo($this->get('wholesale/accept_all'), '');
        $this->assertRedirectsTo($this->get('wholesale/ignore/' . $wh), '');
        $this->assertEqualsWithDelta(10.0, (float) $this->find('wholesale', $wh)['last_bruto'], 0.001);

        // positive control
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertRedirectsTo($this->get('wholesale/accept/' . $wh), 'wholesale/index');
        $this->assertEqualsWithDelta(11.0, (float) $this->find('wholesale', $wh)['last_bruto'], 0.001);
    }

    public function testPricingListsOnlyLinkedArticlesWithAnIncrease(): void
    {
        [, , $up] = $this->linked(11.50, 10.00);          // +15.0 %
        [, , $down] = $this->linked(8.00, 10.00);         // decrease
        [, , $same] = $this->linked(10.00, 10.00);        // unchanged
        [, , $noLast] = $this->linked(10.00, null);       // no last price
        $unlinked = Factory::wholesale(['description' => 'UnlinkedIncrease', 'bruto' => 20, 'last_bruto' => 10]);

        $response = $this->get('wholesale/pricing');

        $this->assertStatus($response, 200);
        $this->assertSee($response, $up);
        $this->assertDontSee($response, $down);
        $this->assertDontSee($response, $same);
        $this->assertDontSee($response, $noLast);
        $this->assertDontSee($response, 'UnlinkedIncrease');
        $this->assertGreaterThan(0, $unlinked);
    }

    public function testPricingShowsOldPriceNewPriceAndPercentage(): void
    {
        [$wh, $pid, $name] = $this->linked(11.50, 10.00);

        $text = $this->text($this->get('wholesale/pricing'));

        $row = strstr($text, $name);
        $this->assertNotFalse($row);
        $row = substr($row, 0, 700);
        $this->assertStringContainsString('10.00', $row);
        $this->assertStringContainsString('11.50', $row);
        // (11.50 - 10.00) / 10.00 * 100 = 15.0
        $this->assertStringContainsString('+15.0 %', $row);
        $this->assertStringContainsString('pricing/prod/' . $pid, (string) $this->get('wholesale/pricing')->getBody());
        $this->assertStringContainsString('wholesale/get_history/' . $wh, (string) $this->get('wholesale/pricing')->getBody());
    }

    public function testThePercentageIsRoundedToOneDecimal(): void
    {
        // (10.10 - 9.70) / 9.70 * 100 = 4.1237 -> 4.1
        [, , $name] = $this->linked(10.10, 9.70);

        $text = $this->text($this->get('wholesale/pricing'));

        $this->assertStringContainsString('+4.1 %', substr((string) strstr($text, $name), 0, 700));
    }

    public function testIgnoredChangesAreStillListedOnThePricingPage(): void
    {
        [$wh, , $name] = $this->linked(12.00, 10.00);
        $this->get('wholesale/ignore/' . $wh);

        $this->assertSee($this->get('wholesale/pricing'), $name);
    }

    public function testAcceptCopiesBrutoToLastBrutoAndStampsToday(): void
    {
        [$wh, , $name] = $this->linked(11.50, 10.00);

        $response = $this->get('wholesale/accept/' . $wh);

        $this->assertRedirectsTo($response, 'wholesale/index');
        $row = $this->find('wholesale', $wh);
        $this->assertEqualsWithDelta(11.50, (float) $row['last_bruto'], 0.001);
        $this->assertEqualsWithDelta(11.50, (float) $row['bruto'], 0.001);
        $this->assertSame(date('Y-m-d'), $row['last_bruto_date']);
        $this->assertDontSee($this->get('wholesale/pricing'), $name, 'an accepted increase leaves the pricing page');
    }

    public function testAcceptOnlyTouchesThatRow(): void
    {
        [$wh] = $this->linked(11.50, 10.00);
        [$other] = $this->linked(13.00, 10.00);

        $this->get('wholesale/accept/' . $wh);

        $this->assertEqualsWithDelta(10.0, (float) $this->find('wholesale', $other)['last_bruto'], 0.001);
        $this->assertSame('2020-01-01', $this->find('wholesale', $other)['last_bruto_date']);
    }

    public function testAcceptFromHistoryReturnsToTheHistoryPage(): void
    {
        [$wh] = $this->linked(11.50, 10.00);

        $this->assertRedirectsTo($this->get('wholesale/accept/' . $wh . '/history'), 'wholesale/get_history/' . $wh);
        $this->assertEqualsWithDelta(11.50, (float) $this->find('wholesale', $wh)['last_bruto'], 0.001);
    }

    public function testAnyOtherSecondSegmentMeansIndex(): void
    {
        [$wh] = $this->linked(11.50, 10.00);

        $this->assertRedirectsTo($this->get('wholesale/accept/' . $wh . '/whatever'), 'wholesale/index');
    }

    public function testAcceptingAnUnknownIdDoesNothingAndRedirects(): void
    {
        $before = $this->rows('SELECT id, last_bruto FROM wholesale ORDER BY id');

        $this->assertRedirectsTo($this->get('wholesale/accept/99999999'), 'wholesale/index');

        $this->assertSame($before, $this->rows('SELECT id, last_bruto FROM wholesale ORDER BY id'));
    }

    public function testIgnoreAndUnignoreToggleOnlyTheFlag(): void
    {
        [$wh] = $this->linked(11.50, 10.00);
        [$other] = $this->linked(11.50, 10.00);

        $this->assertRedirectsTo($this->get('wholesale/ignore/' . $wh), 'wholesale/index');
        $this->assertSame(1, (int) $this->find('wholesale', $wh)['ignore_change']);
        $this->assertSame(0, (int) $this->find('wholesale', $other)['ignore_change']);
        $this->assertEqualsWithDelta(10.0, (float) $this->find('wholesale', $wh)['last_bruto'], 0.001, 'ignore does not accept the price');

        $this->assertRedirectsTo($this->get('wholesale/unignore/' . $wh), 'wholesale/index');
        $this->assertSame(0, (int) $this->find('wholesale', $wh)['ignore_change']);
    }

    public function testIgnoreAndUnignoreFromHistoryReturnToTheHistoryPage(): void
    {
        [$wh] = $this->linked(11.50, 10.00);

        $this->assertRedirectsTo($this->get('wholesale/ignore/' . $wh . '/history'), 'wholesale/get_history/' . $wh);
        $this->assertSame(1, (int) $this->find('wholesale', $wh)['ignore_change']);
        $this->assertRedirectsTo($this->get('wholesale/unignore/' . $wh . '/history'), 'wholesale/get_history/' . $wh);
        $this->assertSame(0, (int) $this->find('wholesale', $wh)['ignore_change']);
    }

    public function testAcceptAllUpdatesEveryRowIncludingUnlinkedOnes(): void
    {
        $this->linked(11.50, 10.00);
        $this->linked(13.00, null);
        $free = Factory::wholesale(['bruto' => 20, 'last_bruto' => 10, 'last_bruto_date' => '2020-01-01']);

        $response = $this->get('wholesale/accept_all');

        $this->assertRedirectsTo($response, 'wholesale/pricing');
        $this->assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM wholesale WHERE last_bruto IS NULL OR last_bruto <> bruto'));
        $this->assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM wholesale WHERE last_bruto_date <> ?', [date('Y-m-d')]));
        $this->assertEqualsWithDelta(20.0, (float) $this->find('wholesale', $free)['last_bruto'], 0.001);
        $this->assertDontSee($this->get('wholesale/pricing'), 'PriceProd');
        $this->assertSee($this->get('wholesale/pricing'), 'no increases detected.');
    }
}
