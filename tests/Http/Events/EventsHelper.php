<?php

declare(strict_types=1);

/** Small helpers for the Events tests: a fresh owner, pet and event per call. */
final class EventsHelper
{
    /** @return array{owner: int, pet: int, event: int} */
    public static function openEvent(array $petOverrides = [], array $eventOverrides = [], int $status = 0): array
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner, $petOverrides);
        $event = Factory::event($pet, $status, 0, $eventOverrides);

        return ['owner' => $owner, 'pet' => $pet, 'event' => $event];
    }

    /** The most recent row id of a table (the tests insert through the application and need the new id). */
    public static function lastId(PDO $pdo, string $table): int
    {
        return (int) $pdo->query('SELECT MAX(id) FROM `' . $table . '`')->fetchColumn();
    }

    /** A product with the tiers {1: 10, 5: 8, 10: 6}, 6 % VAT. */
    public static function tieredProduct(array $overrides = []): int
    {
        $product = Factory::product($overrides + ['price' => null]);
        Factory::productPrice($product, 1.0, 10.0);
        Factory::productPrice($product, 5.0, 8.0);
        Factory::productPrice($product, 10.0, 6.0);

        return $product;
    }
}
