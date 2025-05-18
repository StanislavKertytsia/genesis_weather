<?php

namespace App\Enum;

enum Frequency: string
{
    case HOURLY = 'hourly';
    case DAILY = 'daily';


    public static function choices(): array
    {
        return [
            'Hourly' => self::HOURLY->value,
            'Daily' => self::DAILY->value,
        ];
    }

}