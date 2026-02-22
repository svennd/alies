<?php
defined('BASEPATH') OR exit('No direct script access allowed');

# example :
/*
# 7 days before quarter end to quarter end
if (is_within_post_quarter_window(7)) { // compile quarter }
*/
function is_exact_quarter_end_trigger_day($daysBefore = 5)
{
    $year    = date('Y');
    $quarter = ceil(date('n') / 3);

    $endMonth = $quarter * 3;
    $quarterEnd = new DateTime("$year-$endMonth-01");
    $quarterEnd->modify('last day of this month');

    $runDate = (clone $quarterEnd)->modify("-$daysBefore days");

    return (date('Y-m-d') === $runDate->format('Y-m-d'));
}

# example :
/*
if (is_exact_post_quarter_day(7)) { // run accounting compile once }
*/
function is_exact_post_quarter_day($daysAfter = 7)
{
    $year    = date('Y');
    $quarter = ceil(date('n') / 3);

    $endMonth = $quarter * 3;
    $quarterEnd = new DateTime("$year-$endMonth-01");
    $quarterEnd->modify('last day of this month');

    $runDate = (clone $quarterEnd)->modify("+$daysAfter days");

    return (date('Y-m-d') === $runDate->format('Y-m-d'));
}

function quarter_from_date(?string $date = null): int
{
    $month = (int)date('n', $date ? strtotime($date) : time());
    return (int)ceil($month / 3);
}

function quarter_start_end(int $year, int $quarter): array
{
    $startMonth = ($quarter - 1) * 3 + 1;

    $startDate = sprintf('%04d-%02d-01', $year, $startMonth);
    $endDate   = date('Y-m-t', strtotime("$startDate +2 months"));

    return [$startDate, $endDate];
}

function quarter_prev(int $year, int $quarter): array
{
    $quarter--;
    if ($quarter < 1) {
        $quarter = 4;
        $year--;
    }
    return [$year, $quarter];
}

function quarter_next(int $year, int $quarter): array
{
    $quarter++;
    if ($quarter > 4) {
        $quarter = 1;
        $year++;
    }
    return [$year, $quarter];
}

function is_current_quarter(int $year, int $quarter): bool
{
    return $year == date('Y') && $quarter == quarter_from_date();
}