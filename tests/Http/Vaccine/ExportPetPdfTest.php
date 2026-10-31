<?php

declare(strict_types=1);

/**
 * T222 - Pet vaccination PDF (vaccine/export_vaccine/{pet}).
 * Source: application/controllers/Vaccine.php::export_vaccine, libraries/Pdf.php
 *
 * A known pet gives a PDF attachment named export_{pet}_vaccines_{m.d.y}.pdf. A pet that does not exist
 * redirects to the home page.
 */
final class ExportPetPdfTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    public function testAPetWithVaccinesGivesAPdfAttachment(): void
    {
        $pet = Factory::pet(Factory::owner());
        Factory::vaccine($pet);

        $response = $this->get('vaccine/export_vaccine/' . $pet);

        $this->assertDownload($response, 'application/pdf', 'export_' . $pet . '_vaccines_' . date('m.d.y'));
        $this->assertStringStartsWith('%PDF', (string) $response->getBody());
    }

    public function testAPetWithoutVaccinesStillGivesAPdf(): void
    {
        $pet = Factory::pet(Factory::owner());

        $response = $this->get('vaccine/export_vaccine/' . $pet);

        $this->assertDownload($response, 'application/pdf', 'export_' . $pet . '_vaccines_');
    }

    public function testAMissingPetRedirectsHome(): void
    {
        $this->assertRedirectsTo($this->get('vaccine/export_vaccine/99999999'), '');
    }
}
