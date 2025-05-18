<?php

namespace App\Enum;

enum WeatherEndpoint: string
{
    case CITY_SEARCH = 'search.json';
    case WEATHER = 'current.json';
}