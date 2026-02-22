<?php

function isValidCNK(string $cnk): bool
{
    # veterinary CNK format validation
    if (preg_match('/^V\d{6}$/', $cnk)) {
        return true;
    }

    if (!preg_match('/^\d{7}$/', $cnk)) {
        return false;
    }

    $sum = 0;
    $double = false;

    for ($i = 6; $i >= 0; $i--) {
        $d = (int)$cnk[$i];
        if ($double) {
            $d *= 2;
            if ($d > 9) $d -= 9;
        }
        $sum += $d;
        $double = !$double;
    }

    return ($sum % 10) === 0;
}
