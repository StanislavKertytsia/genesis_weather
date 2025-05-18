<?php

namespace App\Enum;

enum Duration: int
{
    case HOUR = 3600;
    case DAY = 86400;
    case WEEK = 604800;
    case MONTH = 2592000;

}