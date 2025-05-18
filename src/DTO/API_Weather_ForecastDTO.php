<?php

namespace App\DTO;

use App\DTO\Interface\API_Weather_Interface;
use App\Entity\Subscription;
use App\Enum\CacheKey;
use App\Enum\Duration;
use App\Enum\Frequency;
use App\Enum\WeatherEndpoint;

class API_Weather_ForecastDTO implements API_Weather_Interface
{
    private string $city;
    private Frequency $frequency;
    private WeatherEndpoint $endPoint;
    private CacheKey $cacheKey;
    private int $ttl;

    public function __construct(Subscription $subscription)
    {
        $this->city = $subscription->getCity();
        $this->frequency = $subscription->getFrequency();
        $this->endPoint = WeatherEndpoint::WEATHER;
        $this->cacheKey = CacheKey::getCacheFromFrequency($this->frequency);
        $this->ttl = Duration::WEEK->value;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getFrequency(): Frequency
    {
        return $this->frequency;
    }

    public function getEndPoint(): string
    {
        return $this->endPoint->value;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey->value . md5(mb_strtolower(trim($this->city)));
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
