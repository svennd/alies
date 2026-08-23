<?php

declare(strict_types=1);

final class PetAvatarViewTest extends CodeIgniterDatabaseTestCase
{
    public function testProfileCardUsesSpeciesFallbackAndAccessibleUploadControl(): void
    {
        $html = $this->renderCard(null);

        $this->assertStringContainsString('id="petAvatarModal"', $html);
        $this->assertStringContainsString('data-target="#petAvatarModal"', $html);
        $this->assertStringContainsString('name="pet_avatar_source"', $html);
        $this->assertStringNotContainsString('pets/avatar_file/123?v=', $html);
        $this->assertStringContainsString('Test Pet', $html);
        $this->assertStringContainsString('#123', $html);
        $this->assertStringContainsString('Test Owner', $html);
    }

    public function testProfileCardUsesPrivateVersionedAvatarAndRemovalForm(): void
    {
        $avatar = 'pet_' . str_repeat('a', 32) . '.jpg';
        $html = $this->renderCard($avatar);

        $this->assertStringContainsString('pets/avatar_file/123?v=', $html);
        $this->assertStringContainsString('class="pet-profile-avatar"', $html);
        $this->assertStringContainsString('pets/remove_avatar/123', $html);
        $this->assertStringNotContainsString('data/stored/pets', $html);
    }

    public function testEveryAvatarLanguageKeyExistsInEnglishAndDutch(): void
    {
        $keys = [
            'pet_avatar_add', 'pet_avatar_change', 'pet_avatar_title', 'pet_avatar_choose',
            'pet_avatar_rotate_left', 'pet_avatar_rotate_right', 'pet_avatar_save',
            'pet_avatar_cancel', 'pet_avatar_remove', 'pet_avatar_remove_confirm',
            'pet_avatar_uploaded', 'pet_avatar_replaced', 'pet_avatar_removed',
            'pet_avatar_too_large', 'pet_avatar_invalid_type', 'pet_avatar_invalid_image',
            'pet_avatar_invalid_dimensions', 'pet_avatar_invalid_crop',
            'pet_avatar_processing_error', 'pet_avatar_storage_error',
            'pet_avatar_unknown_pet', 'pet_avatar_post_only',
        ];

        foreach (['english', 'dutch'] as $language) {
            $translations = $this->loadLanguage($language);
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $translations, $language . ' misses ' . $key);
                $this->assertNotSame('', trim((string) $translations[$key]));
            }
        }
    }

    public function testClientInteractionWiresCropRotationCancellationAndCacheSizedOutput(): void
    {
        $script = file_get_contents(FCPATH . 'assets/js/pet-avatar.js');

        $this->assertStringContainsString("$('.pet-avatar-rotate').on('click'", $script);
        $this->assertStringContainsString("croppie('rotate'", $script);
		$this->assertStringContainsString("\$modal.on('hidden.bs.modal', resetEditor)", $script);
        $this->assertStringContainsString("size: { width: 512, height: 512 }", $script);
        $this->assertStringContainsString("document.getElementById('petAvatarUploadForm').submit()", $script);
    }

    public function testAvatarControlsAdaptTheirCropBoundaryToNarrowViewports(): void
    {
        $view = file_get_contents(APPPATH . 'views/pets/fiche/pet_info.php');
        $script = file_get_contents(FCPATH . 'assets/js/pet-avatar.js');

        $this->assertStringContainsString('@media (max-width: 575.98px)', $view);
		$this->assertStringContainsString('Math.min(300, Math.max(220, Math.floor($editor.width())))', $script);
        $this->assertStringContainsString('white-space: normal', $view);
        $this->assertStringContainsString('modal-footer d-flex flex-wrap', $view);
    }

    private function renderCard(?string $avatar): string
    {
        $pet = [
            'id' => 123,
            'type' => DOG,
            'name' => 'Test Pet',
            'avatar' => $avatar,
            'last_weight' => '12.50',
            'birth' => '2022-01-01',
            'death' => 0,
            'chip' => '981234567890123',
            'gender' => MALE,
            'color' => '',
            'hairtype' => '',
            'nr_vac_book' => '',
            'note' => '',
            'breeds' => ['name' => 'Mixed'],
            'breeds2' => null,
        ];

        return $this->ci->load->view('pets/fiche/pet_info', [
            'pet' => $pet,
            'owner' => ['id' => 456, 'last_name' => 'Test Owner'],
            'user' => (object) ['user_date' => 'Y-m-d'],
            'pet_has_rx' => false,
            'pet_has_lab' => false,
            'pet_avatar_message' => null,
            'pet_avatar_message_type' => null,
			'pet_avatar_available' => $avatar !== null,
        ], true);
    }

    private function loadLanguage(string $language): array
    {
        return (static function (string $path): array {
            $lang = [];
            require $path;
            return $lang;
        })(APPPATH . 'language/' . $language . '/vet_lang.php');
    }
}
