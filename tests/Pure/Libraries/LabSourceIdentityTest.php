<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Lab_source_identity.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LabSourceIdentityTest extends TestCase
{
	private Lab_source_identity $identity;

	protected function setUp(): void
	{
		$this->identity = new Lab_source_identity();
	}

	public function testDeviceIdentityTakesPrecedenceAndIsTrimmed(): void
	{
		$first = $this->identity->derive(' medilab ', 'remote', ' 2652672 ');
		$second = $this->identity->derive('medilab', 'different-source', '2652672');

		$this->assertSame('device', $first['kind']);
		$this->assertSame('medilab', $first['authority']);
		$this->assertSame('2652672', $first['source_id']);
		$this->assertSame($first['hash'], $second['hash']);
	}

	public function testSourceIsUsedOnlyWhenDeviceIsMissing(): void
	{
		$identity = $this->identity->derive(' ', ' remote ', ' S-22 ');

		$this->assertSame('source', $identity['kind']);
		$this->assertSame('remote', $identity['authority']);
		$this->assertSame('S-22', $identity['source_id']);
	}

	public function testMissingStableComponentsReturnNull(): void
	{
		$this->assertNull($this->identity->derive('medilab', 'remote', null));
		$this->assertNull($this->identity->derive('medilab', 'remote', '  '));
		$this->assertNull($this->identity->derive(null, null, '2652672'));
	}

	public function testLengthPrefixesPreventComponentBoundaryAmbiguity(): void
	{
		$first = $this->identity->derive('ab', null, 'c');
		$second = $this->identity->derive('a', null, 'bc');

		$this->assertNotSame($first['canonical'], $second['canonical']);
		$this->assertNotSame($first['hash'], $second['hash']);
	}

	public function testCandidatesPreserveNormalReportSourceFallback(): void
	{
		$candidates = $this->identity->candidates('medilab', 'remote', '2652672');

		$this->assertSame(['device', 'source'], array_column($candidates, 'kind'));
		$this->assertTrue($this->identity->matches($candidates[1], null, 'remote', '2652672'));
	}

	// ---- T022: normalisation, exact format, candidates and matching ---------------------------------

	/** @return array<string, array{mixed, ?string}> */
	public static function normalisation(): array
	{
		return [
			'null' => [null, null],
			'empty string' => ['', null],
			'blank string' => ["  \t\n ", null],
			'array' => [['a'], null],
			'empty array' => [[], null],
			'object' => [new stdClass(), null],
			'text is trimmed' => ['  abc  ', 'abc'],
			'integer' => [5, '5'],
			// zero is a real value, only empty strings are "no value"
			'zero' => [0, '0'],
			'zero as text' => ['0', '0'],
			'float' => [1.5, '1.5'],
			'inner spaces are kept' => ['a b', 'a b'],
		];
	}

	#[DataProvider('normalisation')]
	public function testNormalizeValue(mixed $value, ?string $expected): void
	{
		$this->assertSame($expected, $this->identity->normalize_value($value));
	}

	public function testCanonicalFormatAndHashAreExactlyAsStored(): void
	{
		// the hash is stored in lab_report_pending.identity_hash, so the format must never change:
		// version | length:kind | length:authority | length:source id
		$identity = $this->identity->derive('medilab', null, '2652672');

		$this->assertSame('lab-source-v1|6:device|7:medilab|7:2652672', $identity['canonical']);
		$this->assertSame(hash('sha256', 'lab-source-v1|6:device|7:medilab|7:2652672'), $identity['hash']);
		$this->assertSame(64, strlen($identity['hash']));
		$this->assertSame(['kind', 'authority', 'source_id', 'canonical', 'hash'], array_keys($identity));
	}

	public function testSourceIdentityHasTheSourceKind(): void
	{
		$identity = $this->identity->derive(null, 'remote', 'S-22');

		$this->assertSame('lab-source-v1|6:source|6:remote|4:S-22', $identity['canonical']);
	}

	public function testTheSameDeviceWithAnotherSourceIdIsAnotherIdentity(): void
	{
		$this->assertNotSame(
			$this->identity->derive('medilab', null, '1')['hash'],
			$this->identity->derive('medilab', null, '2')['hash']
		);
	}

	public function testDeviceAndSourceWithTheSameNameAreDifferentKinds(): void
	{
		$this->assertNotSame(
			$this->identity->derive('x', null, '1')['hash'],
			$this->identity->derive(null, 'x', '1')['hash']
		);
	}

	public function testDeriveIgnoresNonScalarComponents(): void
	{
		$this->assertNull($this->identity->derive('medilab', 'remote', ['1']));
		$this->assertNull($this->identity->derive(['medilab'], null, '1'));
		// a device that is not usable falls back to the source
		$this->assertSame('source', $this->identity->derive(['medilab'], 'remote', '1')['kind']);
	}

	/** @return array<string, array{mixed, mixed, mixed, list<string>}> */
	public static function candidateKinds(): array
	{
		return [
			'device and source' => ['medilab', 'remote', '1', ['device', 'source']],
			'only device' => ['medilab', null, '1', ['device']],
			'only source' => [null, 'remote', '1', ['source']],
			'blank device falls away' => ['  ', 'remote', '1', ['source']],
			'neither' => [null, null, '1', []],
			'no source id' => ['medilab', 'remote', null, []],
			'blank source id' => ['medilab', 'remote', ' ', []],
		];
	}

	#[DataProvider('candidateKinds')]
	public function testCandidates(mixed $device, mixed $source, mixed $sourceId, array $kinds): void
	{
		$this->assertSame($kinds, array_column($this->identity->candidates($device, $source, $sourceId), 'kind'));
	}

	public function testMatchesTheIdentityOfAnyCandidate(): void
	{
		$deviceIdentity = $this->identity->derive('medilab', null, '1');
		$sourceIdentity = $this->identity->derive(null, 'remote', '1');

		// a report stored under its device matches a later delivery of the same device, whatever the source
		$this->assertTrue($this->identity->matches($deviceIdentity, 'medilab', 'other', '1'));
		// a report stored under its source matches a delivery that has the same source
		$this->assertTrue($this->identity->matches($sourceIdentity, null, 'remote', '1'));
		$this->assertTrue($this->identity->matches($sourceIdentity, 'medilab', 'remote', '1'));
	}

	public function testDoesNotMatchOtherIdsDevicesOrCase(): void
	{
		$identity = $this->identity->derive('medilab', null, '1');

		$this->assertFalse($this->identity->matches($identity, 'medilab', null, '2'));
		$this->assertFalse($this->identity->matches($identity, 'ikems', null, '1'));
		$this->assertFalse($this->identity->matches($identity, 'MEDILAB', null, '1'));
		$this->assertFalse($this->identity->matches($identity, null, null, '1'));
		$this->assertFalse($this->identity->matches($identity, 'medilab', null, null));
	}
}

