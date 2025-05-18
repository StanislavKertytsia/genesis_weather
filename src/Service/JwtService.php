<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secretKey;
    private string $algo;

    public function __construct(string $secretKey, string $algo = 'HS256')
    {
        $this->secretKey = $secretKey;
        $this->algo = $algo;
    }

    public function generateToken(array $payload, int $expireSeconds): string
    {
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $expireSeconds,
        ]);
        return JWT::encode($payload, $this->secretKey, $this->algo);
    }

    public function validateToken(string $token): ?array
    {
        try {
            return (array)JWT::decode($token, new Key($this->secretKey, $this->algo));
        } catch (\Exception $e) {
            return null;
        }
    }
}
