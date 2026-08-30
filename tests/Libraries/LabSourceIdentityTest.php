<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Lab_source_identity.php';

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
}
