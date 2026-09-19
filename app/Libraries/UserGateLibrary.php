<?php

namespace App\Libraries;

use Config\Services;

class UserGateLibrary
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('usergate.url'), '/');
        $this->apiKey  = env('usergate.api_key');
    }

    /**
     * Kirim request ke UserGate API.
     */
    private function request(
        string $method,
        string $endpoint,
        ?string $token = null,
        ?array $body = null
    ): array {
        $client = Services::curlrequest([
            'baseURI' => '',
            'timeout' => 30,
        ]);

        $headers = [
            'X-API-Key' => $this->apiKey,
            'Accept'    => 'application/json',
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $options = [
            'headers' => $headers,
        ];

        if ($body !== null) {
            // Manual encode agar pasti Content-Type benar
            $options['body'] = json_encode($body);
            $options['headers']['Content-Type'] = 'application/json';
        }

        // Bypass SSL untuk localhost (self-signed cert)
        $url = $this->baseUrl . $endpoint;
        if (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
            $options['verify'] = false;
        }

        try {
            $response = $client->request($method, $url, $options);

            $statusCode = $response->getStatusCode();
            $rawBody    = $response->getBody();

            // Log untuk debugging
            log_message('info', "[UserGateLibrary] {$method} {$url} → {$statusCode}");
            log_message('info', "[UserGateLibrary] Response: {$rawBody}");

            $json = json_decode($rawBody, true);

            if ($json === null) {
                return [
                    'status'  => false,
                    'message' => 'Response tidak valid dari UserGate.',
                    'data'    => null,
                    'meta'    => null,
                    'code'    => $statusCode,
                ];
            }

            return [
                'status'  => $json['status'] ?? false,
                'message' => $json['message'] ?? '',
                'data'    => $json['data'] ?? null,
                'meta'    => $json['meta'] ?? null,
                'code'    => $statusCode,
            ];
        } catch (\Exception $e) {
            log_message('error', "[UserGateLibrary] Error: {$e->getMessage()}");

            return [
                'status'  => false,
                'message' => 'Gagal terhubung ke UserGate: ' . $e->getMessage(),
                'data'    => null,
                'meta'    => null,
                'code'    => 0,
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    public function login(string $username, string $password): array
    {
        return $this->request('POST', '/auth/login', null, [
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function refreshToken(string $refreshToken): array
    {
        return $this->request('POST', '/auth/refresh', null, [
            'refresh_token' => $refreshToken,
        ]);
    }

    public function me(string $accessToken): array
    {
        return $this->request('GET', '/auth/me', $accessToken);
    }

    public function logout(string $accessToken): array
    {
        return $this->request('POST', '/auth/logout', $accessToken);
    }

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function getUsers(
        string $accessToken,
        int $page = 1,
        int $perPage = 20,
        string $search = ''
    ): array {
        $params = http_build_query(array_filter([
            'page'     => $page,
            'per_page' => $perPage,
            'search'   => $search,
        ]));

        return $this->request(
            'GET',
            '/users' . ($params ? '?' . $params : ''),
            $accessToken
        );
    }

    public function getUser(string $accessToken, string $id): array
    {
        return $this->request('GET', '/users/' . $id, $accessToken);
    }

    public function createUser(string $accessToken, array $data): array
    {
        return $this->request('POST', '/users', $accessToken, $data);
    }

    public function updateUser(
        string $accessToken,
        string $id,
        array $data
    ): array {
        return $this->request('PUT', '/users/' . $id, $accessToken, $data);
    }

    public function deleteUser(string $accessToken, string $id): array
    {
        return $this->request('DELETE', '/users/' . $id, $accessToken);
    }
}
