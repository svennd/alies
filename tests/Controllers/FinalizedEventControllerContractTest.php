<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FinalizedEventControllerContractTest extends TestCase
{
    private string $events;
    private string $files;

    protected function setUp(): void
    {
        $this->events = file_get_contents(APPPATH . 'controllers/Events.php');
        $this->files = file_get_contents(APPPATH . 'controllers/Files.php');
    }

    public function testLabMutationsAreAuthenticatedPostOnlyAndUsePersistedEventPet(): void
    {
        $this->assertStringContainsString('class Events extends Vet_Controller', $this->events);
        $this->assertMatchesRegularExpression('/function link_lab\(int \$event_id\).*?require_lab_post\(\).*?fields\(\'id, pet, status\'\)->get\(\$event_id\).*?events_lab->link\(\$event_id, \$lab_id, \(int\) \$event\[\'pet\'\]\)/s', $this->events);
        $this->assertMatchesRegularExpression('/function unlink_lab\(int \$event_id, int \$lab_id\).*?require_lab_post\(\).*?events_lab->unlink\(\$event_id, \$lab_id\)/s', $this->events);
        $this->assertStringContainsString("method(true) !== 'POST'", $this->events);
        $this->assertStringContainsString("(int) \$event['status'] === STATUS_OPEN", $this->events);
        $this->assertStringNotContainsString("input->post('pet_id')", $this->events);
    }

    public function testLabDataIsAssembledOnlyInTheFinalizedBranch(): void
    {
        $this->assertMatchesRegularExpression('/if \(\$event_info\[\'status\'\] == STATUS_OPEN \).*?main_open.*?else.*?get_linked_for_event.*?get_linkable_for_event.*?main_report/s', $this->events);
    }

    public function testPreviewUsesAuthenticatedSafeInlineDelivery(): void
    {
        $this->assertStringContainsString('class Files extends Vet_Controller', $this->files);
        $this->assertMatchesRegularExpression('/function preview\(int \$id\).*?events_upload->get\(\$id\).*?event_attachment_preview->inspect/s', $this->files);
        $this->assertStringContainsString('Content-Disposition: inline;', $this->files);
        $this->assertStringContainsString('X-Content-Type-Options: nosniff', $this->files);
        $this->assertMatchesRegularExpression('/function preview.*?show_404\(\).*?show_404\(\)/s', $this->files);
    }
}
