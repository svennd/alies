<?php

function calc_pct($value, $low, $high) {
    // handle unknown values ("-")
    if ($low === "-" || $high === "-") {
        return null;
    }

    $v  = floatval($value);
    $lo = floatval($low);
    $hi = floatval($high);

    if ($lo == 0.0 && $hi == 0.0) {
        return null;
    }

    // special case: low == high
    if ($hi == $lo) {
        if ($v == $lo) {
            return 0.5;
        } elseif ($v < $lo) {
            return 0.0;
        } else {
            return 1.0;
        }
    }

    $span = $hi - $lo;

    // clamp far outside
    if ($v <= $lo - $span) {
        return 0.0;
    }
    if ($v >= $hi + $span) {
        return 1.0;
    }

    // below range
    if ($v < $lo) {
        return ($v - ($lo - $span)) / $span * (1.0/3.0);
    }

    // above range
    if ($v > $hi) {
        return (2.0/3.0) + ($v - $hi) / $span * (1.0/3.0);
    }

    // inside range
    return (1.0/3.0) + ($v - $lo) / $span * (1.0/3.0);
}