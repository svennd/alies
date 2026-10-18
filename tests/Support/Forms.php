<?php

declare(strict_types=1);

/**
 * Complete form submissions, the way the browser sends them.
 *
 * The application inserts NULL for every field a form leaves out, and several columns are NOT NULL,
 * so posting only the interesting fields is a database error (see OwnerFormRequiresAllFieldsTest).
 * Use these defaults and override what the test cares about.
 */
final class Forms
{
    /**
     * owners/add and owners/edit. Checkboxes (low_budget, debts, contact, disabled) are only sent when
     * checked, so they are not in the defaults: pass 'low_budget' => '1' to tick one.
     *
     * @return array<string, string>
     */
    public static function owner(array $overrides = []): array
    {
        return array_merge([
            'submit' => '1',
            'first_name' => 'Test',
            'last_name' => Factory::unique('Owner'),
            'street' => 'Teststraat',
            'nr' => '1',
            'zip' => '2000',
            'city' => 'Antwerpen',
            'main_city' => 'Antwerpen',
            'province' => 'Antwerpen',
            'phone' => '',
            'mobile' => '',
            'phone2' => '',
            'phone3' => '',
            'mail' => '',
            'msg' => '',
            'btw_nr' => '',
            'invoice_addr' => '',
            'invoice_contact' => '',
            'invoice_tel' => '',
        ], $overrides);
    }

    /**
     * pets/add/{owner}. `owner` is read from the form, not from the url.
     *
     * @return array<string, string>
     */
    public static function pet(int $ownerId, array $overrides = []): array
    {
        return array_merge([
            'submit' => '1',
            'owner' => (string) $ownerId,
            'type' => '0',
            'name' => Factory::unique('Pet'),
            'gender' => '0',
            'birth' => '2020-01-15',
            'breed' => (string) Fixtures::BREED_UNKNOWN,
            'breed2' => '',
            'color' => 'bruin',
            'hairtype' => 'kort',
            'companion' => '',
            'chip' => '',
            'weight' => '',
            'vacbook' => '',
            'msg' => '',
        ], $overrides);
    }
}
