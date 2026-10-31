<?php

declare(strict_types=1);

require_once APPPATH . 'libraries/Vamregclient.php';

use PHPUnit\Framework\TestCase;

/**
 * Vamregclient with the HTTP call replaced: it records what would be sent. The real request() (curl
 * to the authority's server) is never run in tests; its base URL is hard coded, see the notes in
 * docs/testing/00-foundation.md (F07).
 */
final class RecordingVamregclient extends Vamregclient
{
    /** @var list<array{method: string, path: string, payload: ?string}> */
    public array $requests = [];

    public mixed $answer = ['http_code' => 200, 'raw' => '[]', 'response' => []];

    protected function request($method, $path, $payload = null)
    {
        $this->requests[] = ['method' => $method, 'path' => $path, 'payload' => $payload];

        return $this->answer;
    }

    public function urlFor(string $path): string
    {
        return $this->endpoint($path);
    }

    public function header(): string
    {
        return $this->authHeader();
    }
}

/**
 * T025 - application/libraries/Vamregclient.php: client of the Belgian antibiotic register (Vamreg).
 */
final class VamregclientTest extends TestCase
{
    private function production(): RecordingVamregclient
    {
        return new RecordingVamregclient(['apiKey' => 'secret-key', 'prod' => true]);
    }

    private function uat(): RecordingVamregclient
    {
        return new RecordingVamregclient(['apiKey' => 'secret-key']);
    }

    public function testProductionUsesTheProductionServerAndTheSecretKeyHeader(): void
    {
        $client = $this->production();

        $this->assertSame('https://app.fagg-afmps.be/vamreg/api/declaration', $client->urlFor('/declaration'));
        $this->assertSame('FAMHP-SEC-KEY: secret-key', $client->header());
    }

    public function testWithoutTheProdSettingItUsesTheUatServerAndThePublicKeyHeader(): void
    {
        $client = $this->uat();

        $this->assertSame('https://uat.app.fagg-afmps.be/vamreg/api/declaration', $client->urlFor('/declaration'));
        $this->assertSame('FAMHP-PUB-KEY: secret-key', $client->header());
    }

    public function testProdFalseIsTheSameAsLeavingItOut(): void
    {
        $client = new RecordingVamregclient(['apiKey' => 'k', 'prod' => false]);

        $this->assertStringStartsWith('https://uat.', $client->urlFor('/x'));
        $this->assertSame('FAMHP-PUB-KEY: k', $client->header());
    }

    public function testUploadBulkPostsTheDeclarationsAsJson(): void
    {
        $client = $this->uat();
        $declarations = [
            ['internal_id' => 1, 'register' => 'IN', 'cnk' => '1234567', 'inQuantityPackCount' => 2],
            ['internal_id' => 2, 'register' => 'OUT', 'cnk' => '7654321', 'outQuantityPackCount' => 1.5],
        ];

        $client->uploadBulk($declarations);

        $this->assertCount(1, $client->requests);
        $this->assertSame('POST', $client->requests[0]['method']);
        $this->assertSame('/declaration', $client->requests[0]['path']);
        $this->assertSame($declarations, json_decode($client->requests[0]['payload'], true));
    }

    public function testUnicodeIsSentAsIsButSlashesAreEscaped(): void
    {
        $client = $this->uat();

        $client->uploadBulk([['name' => 'Paracétamol 50 mg/ml']]);

        $payload = $client->requests[0]['payload'];
        $this->assertStringContainsString('Paracétamol', $payload);
        $this->assertStringNotContainsString('u00e9', $payload, 'no \\uXXXX escape for the accent');
        // only JSON_UNESCAPED_UNICODE is set, so "/" still becomes "\/" (valid JSON, same meaning)
        $this->assertStringContainsString('50 mg\/ml', $payload);
    }

    public function testUploadingNothingSendsAnEmptyJsonList(): void
    {
        $client = $this->uat();

        $client->uploadBulk([]);

        $this->assertSame('[]', $client->requests[0]['payload']);
    }

    public function testDeleteSendsADeleteForTheDeclarationId(): void
    {
        $client = $this->uat();

        $client->delete('65f0c0ffee0000000000abcd');

        $this->assertSame(
            [['method' => 'DELETE', 'path' => '/declaration/65f0c0ffee0000000000abcd', 'payload' => null]],
            $client->requests
        );
    }

    public function testGetSendsAGetForThePath(): void
    {
        $client = $this->uat();

        $client->get('/medicinal-product');

        $this->assertSame(
            [['method' => 'GET', 'path' => '/medicinal-product', 'payload' => null]],
            $client->requests
        );
    }

    public function testTheResultOfTheRequestIsReturnedUnchanged(): void
    {
        $client = $this->uat();
        $client->answer = ['http_code' => 400, 'raw' => '{"x":1}', 'response' => ['x' => 1]];

        $this->assertSame($client->answer, $client->uploadBulk([]));
        $this->assertSame($client->answer, $client->delete('abc'));
        $this->assertSame($client->answer, $client->get('/x'));
    }

    public function testWithoutAnApiKeyPhpReportsAMissingArrayKey(): void
    {
        // quirk: the constructor reads $config['apiKey'] directly; there is no friendly error
        $messages = [];
        set_error_handler(static function (int $no, string $message) use (&$messages): bool {
            $messages[] = $message;

            return true;
        });
        try {
            new RecordingVamregclient([]);
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('apiKey', $messages[0]);
    }
}
