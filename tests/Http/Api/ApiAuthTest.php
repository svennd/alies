<?php

declare(strict_types=1);

/**
 * T335 - API authentication and rate limit (API_Controller, ApiKey_model, ApiRate_model).
 *
 * Order of operations in the constructor: find the key, touch api_keys.last_used_at, count the hit.
 * The key is read from "Authorization: Bearer <key>" or, when that header is absent or empty, from
 * "X-API-Key". The key is compared as stored (api_keys.key_hash, active = 1), no hashing.
 * Probe endpoint: POST api/lab/import/ms4s2 with a body that is not JSON; reaching the controller
 * action shows as 400 "Invalid JSON payload", an auth failure shows as 401/429 before that.
 */
final class ApiAuthTest extends HttpTestCase
{
    private const URL = 'api/lab/import/ms4s2';

    private function probe(array $headers): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->post(self::URL, ['body' => 'not json', 'headers' => $headers, 'http_errors' => false]);
    }

    private function keyId(string $key): int
    {
        return (int) $this->scalar('SELECT id FROM api_keys WHERE key_hash = ?', [$key]);
    }

    private function assertApiError($response, int $status, string $message): void
    {
        $this->assertSame($status, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame(['status' => 'error', 'message' => $message], json_decode((string) $response->getBody(), true));
    }

    public function testNoKeyIsRefusedWithMissingApiKey(): void
    {
        $this->assertApiError($this->probe([]), 401, 'Missing API key');
    }

    public function testUnknownKeyIsRefused(): void
    {
        $this->assertApiError($this->probe(['Authorization' => 'Bearer nope-' . uniqid()]), 401, 'Invalid API key');
    }

    public function testRevokedKeyIsRefusedAndTheSeededRevokedKeyToo(): void
    {
        $own = Factory::apiKey(null, false);
        $this->assertApiError($this->probe(['Authorization' => 'Bearer ' . $own]), 401, 'Invalid API key');
        $this->assertApiError($this->probe(['X-API-Key' => Fixtures::API_KEY_REVOKED]), 401, 'Invalid API key');
        // positive control: the same request with an active key gets past authentication
        $this->assertSame(400, $this->probe(['X-API-Key' => Fixtures::API_KEY_ACTIVE])->getStatusCode());
    }

    public function testBearerHeaderWorks(): void
    {
        $key = Factory::apiKey();
        $response = $this->probe(['Authorization' => 'Bearer ' . $key]);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid JSON payload', json_decode((string) $response->getBody(), true)['message']);
    }

    public function testXApiKeyHeaderWorks(): void
    {
        $key = Factory::apiKey();
        $response = $this->probe(['X-API-Key' => $key]);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid JSON payload', json_decode((string) $response->getBody(), true)['message']);
    }

    public function testTokenSchemeIsRefusedEvenWhenXApiKeyIsValid(): void
    {
        $key = Factory::apiKey();
        // KNOWN BUG: the whole "Token <key>" value is used as the key and X-API-Key is never looked at - decide in CI4 whether to keep
        $this->assertApiError(
            $this->probe(['Authorization' => 'Token ' . $key, 'X-API-Key' => $key]),
            401,
            'Invalid API key'
        );
        $this->assertSame(400, $this->probe(['Authorization' => 'Bearer ' . $key, 'X-API-Key' => $key])->getStatusCode());
    }

    public function testKeyIsComparedAsStoredWithoutHashing(): void
    {
        $key = Factory::apiKey('plain-' . uniqid());
        $this->assertSame($key, $this->scalar('SELECT key_hash FROM api_keys WHERE key_hash = ?', [$key]));
        $this->assertSame(400, $this->probe(['X-API-Key' => $key])->getStatusCode());
        $this->assertApiError($this->probe(['X-API-Key' => sha1($key)]), 401, 'Invalid API key');
    }

    public function testASuccessfulCallUpdatesLastUsedAt(): void
    {
        $key = Factory::apiKey();
        $id = $this->keyId($key);
        $this->assertNull($this->scalar('SELECT last_used_at FROM api_keys WHERE id = ?', [$id]));

        $this->probe(['X-API-Key' => $key]);

        $used = $this->scalar('SELECT last_used_at FROM api_keys WHERE id = ?', [$id]);
        $this->assertNotNull($used);
        $this->assertEqualsWithDelta(time(), strtotime((string) $used), 5);
    }

    public function testAFailedAuthenticationTouchesNothing(): void
    {
        $key = Factory::apiKey(null, false);
        $this->probe(['X-API-Key' => $key]);
        $this->assertNull($this->scalar('SELECT last_used_at FROM api_keys WHERE key_hash = ?', [$key]));
        $this->assertSame(0, $this->countWhere('api_rate_limit', ['api_key_id' => $this->keyId($key)]));
    }

    public function testEachCallCountsAsOneHitInTheCurrentMinute(): void
    {
        $key = Factory::apiKey();
        $id = $this->keyId($key);
        $this->probe(['X-API-Key' => $key]);
        $this->probe(['X-API-Key' => $key]);
        $this->probe(['X-API-Key' => $key]);

        $total = (int) $this->scalar('SELECT SUM(count) FROM api_rate_limit WHERE api_key_id = ?', [$id]);
        $this->assertSame(3, $total);
    }

    public function testTheSixtyFirstCallInAMinuteIsRateLimited(): void
    {
        $key = Factory::apiKey();
        $id = $this->keyId($key);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $minute = date('YmdHi');
            Factory::insert('api_rate_limit', ['api_key_id' => $id, 'minute' => $minute, 'count' => 60]);
            $response = $this->probe(['X-API-Key' => $key]);
            if (date('YmdHi') !== $minute) {
                // the minute rolled over during the request: start again with a clean slate
                $this->db()->prepare('DELETE FROM api_rate_limit WHERE api_key_id = ?')->execute([$id]);
                continue;
            }
            $this->assertApiError($response, 429, 'Rate limit exceeded');
            $this->assertSame(61, (int) $this->scalar('SELECT count FROM api_rate_limit WHERE api_key_id = ? AND minute = ?', [$id, $minute]));
            // the key was touched before the limit was checked
            $this->assertNotNull($this->scalar('SELECT last_used_at FROM api_keys WHERE id = ?', [$id]));
            return;
        }
        $this->markTestSkipped('the minute rolled over twice');
    }

    public function testTheSixtiethCallStillPassesAndAnotherKeyIsNotAffected(): void
    {
        $key = Factory::apiKey();
        $other = Factory::apiKey();
        $id = $this->keyId($key);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $minute = date('YmdHi');
            Factory::insert('api_rate_limit', ['api_key_id' => $id, 'minute' => $minute, 'count' => 59]);
            $last = $this->probe(['X-API-Key' => $key]);
            $over = $this->probe(['X-API-Key' => $key]);
            $otherResponse = $this->probe(['X-API-Key' => $other]);
            if (date('YmdHi') !== $minute) {
                $this->db()->prepare('DELETE FROM api_rate_limit WHERE api_key_id = ?')->execute([$id]);
                continue;
            }
            $this->assertSame(400, $last->getStatusCode(), 'call 60 is allowed');
            $this->assertSame(429, $over->getStatusCode(), 'call 61 is refused');
            $this->assertSame(400, $otherResponse->getStatusCode());
            return;
        }
        $this->markTestSkipped('the minute rolled over twice');
    }

    public function testAnOldMinuteDoesNotCount(): void
    {
        $key = Factory::apiKey();
        Factory::insert('api_rate_limit', ['api_key_id' => $this->keyId($key), 'minute' => date('YmdHi', strtotime('-5 minutes')), 'count' => 500]);
        $this->assertSame(400, $this->probe(['X-API-Key' => $key])->getStatusCode());
    }
}
