<?php

namespace App\DTO\Interface;

interface API_Weather_Interface
{
    public function getEndpoint(): string;

    public function getCacheKey(): string;

    public function getTtl(): int;
}