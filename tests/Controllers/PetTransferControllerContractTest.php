<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PetTransferControllerContractTest extends TestCase
{
    public function testControllerLogsAndRedirectsOnlyAfterCommittedTransfer(): void
    {
        $source = file_get_contents(APPPATH . 'controllers/Pets.php');

        $this->assertMatchesRegularExpression(
            '/change_owner_complete.*?transfer_pet.*?if \(!\$new_pet_id\).*?set_flashdata.*?redirect.*?return;.*?logs->logger.*?redirect/s',
            $source
        );
        $this->assertStringContainsString('pet_transfer_failed', $source);
        $this->assertStringContainsString('successor:', $source);
    }

    public function testChangeOwnerViewRendersEscapedFailureFeedback(): void
    {
        $view = file_get_contents(APPPATH . 'views/pets/change_owner.php');

        $this->assertStringContainsString('alert alert-danger', $view);
        $this->assertStringContainsString('html_escape($transfer_message)', $view);
    }
}
