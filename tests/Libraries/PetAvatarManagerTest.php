<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once APPPATH . 'libraries/Pet_avatar_manager.php';

final class PetAvatarManagerTest extends TestCase
{
    public function testInvalidReplacementPreservesReferenceAndWritesNoSuccessLog(): void
    {
        $pets = new PetAvatarManagerFakePets('pet_' . str_repeat('a', 32) . '.jpg');
        $processor = new PetAvatarManagerFakeProcessor(['success' => false, 'error' => 'type']);
        $logs = new PetAvatarManagerFakeLogs();
        $manager = new Pet_avatar_manager(compact('pets', 'processor', 'logs'));

        $result = $manager->save(12, [], 'invalid');

        $this->assertSame('invalid', $result['status']);
        $this->assertSame('type', $result['error']);
        $this->assertSame('pet_' . str_repeat('a', 32) . '.jpg', $pets->avatar);
        $this->assertSame([], $logs->entries);
    }

    public function testSuccessfulReplacementAssociatesNewFileCleansOldFileAndLogsAction(): void
    {
        $old = 'pet_' . str_repeat('b', 32) . '.jpg';
        $new = 'pet_' . str_repeat('c', 32) . '.jpg';
        $pets = new PetAvatarManagerFakePets($old);
        $processor = new PetAvatarManagerFakeProcessor(['success' => true, 'filename' => $new]);
        $logs = new PetAvatarManagerFakeLogs();
        $manager = new Pet_avatar_manager(compact('pets', 'processor', 'logs'));

        $result = $manager->save(12, [], 'crop');

        $this->assertSame('success', $result['status']);
        $this->assertSame($new, $pets->avatar);
        $this->assertSame([$old], $processor->deleted);
        $this->assertSame('pet_avatar_replace', $logs->entries[0]['event']);
        $this->assertStringContainsString('#12', $logs->entries[0]['message']);
    }

    public function testAssociationFailureDeletesNewFileWithoutLoggingSuccess(): void
    {
        $new = 'pet_' . str_repeat('d', 32) . '.jpg';
        $pets = new PetAvatarManagerFakePets(null);
        $pets->failReplacement = true;
        $processor = new PetAvatarManagerFakeProcessor(['success' => true, 'filename' => $new]);
        $logs = new PetAvatarManagerFakeLogs();
        $manager = new Pet_avatar_manager(compact('pets', 'processor', 'logs'));

        $result = $manager->save(12, [], 'crop');

        $this->assertSame('storage', $result['status']);
        $this->assertNull($pets->avatar);
        $this->assertSame([$new], $processor->deleted);
        $this->assertSame([], $logs->entries);
    }

    public function testRemovalKeepsSharedFileAndLogsOnlyARealRemoval(): void
    {
        $shared = 'pet_' . str_repeat('e', 32) . '.jpg';
        $pets = new PetAvatarManagerFakePets($shared);
        $pets->referenceCount = 1;
        $processor = new PetAvatarManagerFakeProcessor(['success' => false]);
        $logs = new PetAvatarManagerFakeLogs();
        $manager = new Pet_avatar_manager(compact('pets', 'processor', 'logs'));

        $result = $manager->remove(12);

        $this->assertSame('success', $result['status']);
        $this->assertNull($pets->avatar);
        $this->assertSame([], $processor->deleted);
        $this->assertSame('pet_avatar_remove', $logs->entries[0]['event']);
    }
}

final class PetAvatarManagerFakePets
{
    public ?string $avatar;
    public bool $failReplacement = false;
    public int $referenceCount = 0;

    public function __construct(?string $avatar)
    {
        $this->avatar = $avatar;
    }

    public function fields(string $fields): self
    {
        return $this;
    }

    public function get(int $petId): array
    {
        return ['id' => $petId, 'avatar' => $this->avatar];
    }

    public function replace_avatar(int $petId, ?string $avatar)
    {
        if ($this->failReplacement) {
            return false;
        }
        $previous = $this->avatar;
        $this->avatar = $avatar;
        return ['previous' => $previous];
    }

    public function avatar_reference_count(string $avatar): int
    {
        return $this->referenceCount;
    }
}

final class PetAvatarManagerFakeProcessor
{
    public array $deleted = [];
    private array $storeResult;

    public function __construct(array $storeResult)
    {
        $this->storeResult = $storeResult;
    }

    public function store(array $source, string $crop): array
    {
        return $this->storeResult;
    }

    public function delete(string $filename): bool
    {
        $this->deleted[] = $filename;
        return true;
    }
}

final class PetAvatarManagerFakeLogs
{
    public array $entries = [];

    public function logger(int $level, string $event, string $message): bool
    {
        $this->entries[] = compact('level', 'event', 'message');
        return true;
    }
}
