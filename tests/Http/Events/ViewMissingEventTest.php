<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/** T103 - the event page of an id that does not exist. */
final class ViewMissingEventTest extends HttpTestCase
{
    #[Group('known-bug')]
    public function testAMissingEventIsNotAProperNotFound(): void
    {
        // KNOWN BUG: no existence check; the page breaks instead of answering 404 - CI4 should return 404
        $this->loginAs('vet');

        $response = $this->get('events/event/99999999');

        $this->assertSame(500, $response->getStatusCode());
    }
}
