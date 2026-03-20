<?php

class Vamregclient
{
    protected $apiKey;
    protected $prod;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['apiKey'];
        $this->prod   = $config['prod'] ?? false;
    }

    protected function endpoint($path)
    {
        $base = $this->prod
            ? 'https://app.fagg-afmps.be/vamreg/api'
            : 'https://uat.app.fagg-afmps.be/vamreg/api';

        return $base . $path;
    }

    protected function authHeader()
    {
        return $this->prod
            ? 'FAMHP-SEC-KEY: ' . $this->apiKey
            : 'FAMHP-PUB-KEY: ' . $this->apiKey;
    }

    public function uploadBulk(array $declarations)
    {
        return $this->request(
            'POST',
            '/declaration',
            json_encode($declarations, JSON_UNESCAPED_UNICODE)
        );
    }

    public function delete(string $id)
    {
        return $this->request('DELETE', '/declaration/' . $id);
    }

    public function get($path)
    {
        return $this->request('GET', $path);
    }

    protected function request($method, $path, $payload = null)
    {
        $ch = curl_init($this->endpoint($path));

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            $this->authHeader(),
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_CONNECTTIMEOUT => 3,
			CURLOPT_TIMEOUT        => 10
        ]);

        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);

        return [
            'http_code' => $code,
            'raw'       => $raw,
            'response'  => json_decode($raw, true),
        ];
    }
}
