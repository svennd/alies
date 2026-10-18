<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Bcrypt.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T020 - application/libraries/Bcrypt.php.
 *
 * NOTE: nothing in the application uses this class; Ion_auth hashes passwords itself with
 * password_hash(). It is a candidate for deletion in the CI4 port. The tests document what it does
 * until then. Hashes are generated with a low cost (4) where speed matters.
 */
final class BcryptTest extends TestCase
{
    private function bcrypt(int $rounds = 4, string $prefix = '$2y$'): Bcrypt
    {
        return new Bcrypt(['rounds' => $rounds, 'salt_prefix' => $prefix]);
    }

    public function testHashIsA60CharacterBcryptStringWithTheConfiguredCost(): void
    {
        $hash = $this->bcrypt(4)->hash('secret');

        $this->assertSame(60, strlen($hash));
        $this->assertStringStartsWith('$2y$04$', $hash);
        $this->assertMatchesRegularExpression('#^\$2y\$04\$[./A-Za-z0-9]{53}$#', $hash);
    }

    public function testTheDefaultCostIsSeven(): void
    {
        $this->assertStringStartsWith('$2y$07$', (new Bcrypt())->hash('secret'));
    }

    /** @return array<string, array{int}> */
    public static function costs(): array
    {
        return ['4' => [4], '5' => [5], '10' => [10]];
    }

    #[DataProvider('costs')]
    public function testTheCostIsWrittenWithTwoDigits(int $cost): void
    {
        $this->assertStringStartsWith(sprintf('$2y$%02d$', $cost), $this->bcrypt($cost)->hash('x'));
    }

    public function testTheSaltPrefixIsConfigurable(): void
    {
        $this->assertStringStartsWith('$2a$04$', $this->bcrypt(4, '$2a$')->hash('secret'));
    }

    public function testEveryHashOfTheSameInputUsesAnotherSalt(): void
    {
        $bcrypt = $this->bcrypt();

        $this->assertNotSame($bcrypt->hash('secret'), $bcrypt->hash('secret'));
    }

    public function testVerifyAcceptsTheRightInputAndRejectsOthers(): void
    {
        $bcrypt = $this->bcrypt();
        $hash = $bcrypt->hash('correct horse');

        $this->assertTrue($bcrypt->verify('correct horse', $hash));
        $this->assertFalse($bcrypt->verify('correct horse ', $hash));
        $this->assertFalse($bcrypt->verify('Correct horse', $hash));
        $this->assertFalse($bcrypt->verify('', $hash));
    }

    public function testVerifyRejectsAnythingThatIsNotAHash(): void
    {
        $this->assertFalse($this->bcrypt()->verify('secret', 'garbage'));
        $this->assertFalse($this->bcrypt()->verify('secret', ''));
    }

    public function testHashesAreCompatibleWithPhpsPasswordFunctions(): void
    {
        $bcrypt = $this->bcrypt();

        $this->assertTrue(password_verify('secret', $bcrypt->hash('secret')));
        $this->assertTrue($bcrypt->verify('secret', password_hash('secret', PASSWORD_BCRYPT, ['cost' => 4])));
    }

    public function testOnlyTheFirst72BytesOfTheInputCount(): void
    {
        // a property of bcrypt itself, worth knowing when long passwords are allowed
        $bcrypt = $this->bcrypt();
        $hash = $bcrypt->hash(str_repeat('a', 72) . 'ONE');

        $this->assertTrue($bcrypt->verify(str_repeat('a', 72) . 'TWO', $hash));
    }
}
