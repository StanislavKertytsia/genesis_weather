<?php

namespace App\Enum;

enum CacheKey: string
{
    case CITY = 'city_';
    case DAILY = 'weather_daily_';
    case HOURLY = 'weather_hourly_';


    public static function getCacheFromFrequency(Frequency $frequency): self
    {
        return match ($frequency) {
            Frequency::DAILY => self::DAILY,
            Frequency::HOURLY => self::HOURLY,
        };
    }
}