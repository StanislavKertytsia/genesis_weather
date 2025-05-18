<?php

namespace App\DTO;

use App\DTO\Interface\API_Weather_Interface;

class API_Weather_CitySearchDTO implements API_Weather_Interface
{
    private string $endpoint;
    private string $cacheKey;
    private int $ttl;

    public function __construct(string $endpoint, string $cacheKey, int $ttl)
    {
        $this->endpoint = $endpoint;
        $this->cacheKey = $cacheKey;
        $this->ttl = $ttl;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
