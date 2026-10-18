<?php

declare(strict_types=1);

/** Complete pets/edit form submission, as the browser sends it (own helper for the Pets tests). */
final class PetFormHelper
{
    /** @return array<string, string> */
    public static function edit(array $pet, array $overrides = []): array
    {
        return array_merge([
            'submit' => '1',
            'type' => (string) $pet['type'],
            'name' => (string) $pet['name'],
            'gender' => (string) $pet['gender'],
            'birth' => (string) $pet['birth'],
            'breed' => '',
            'current_breed' => (string) $pet['breed'],
            'breed2' => '',
            'current_breed2' => $pet['breed2'] === null ? '-1' : (string) $pet['breed2'],
            'color' => (string) $pet['color'],
            'hairtype' => (string) $pet['hairtype'],
            'companion' => '',
            'chip' => (string) ($pet['chip'] ?? ''),
            'weight' => (string) $pet['last_weight'],
            'vacbook' => '',
            'nutritional_advice' => '',
            'medication' => '',
            'msg' => '',
        ], $overrides);
    }
}
