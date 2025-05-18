<?php

namespace App\Service;

use App\DTO\Interface\API_Weather_Interface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherAPIService
{
    private $client;
    private $apiKey;
    private CacheInterface $cache;

    public function __construct(
        HttpClientInterface $client,
        string              $apiKey,
        CacheInterface      $cache
    )
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->cache = $cache;
    }

    public function fetchFromWeatherApi(
        API_Weather_Interface $API_Weather_Interface,
        array                 $queryParams,
        bool                $cron = false
    ): array
    {
        $endpoint = $API_Weather_Interface->getEndpoint();
        $cacheKey = $API_Weather_Interface->getCacheKey();
        $ttl = $API_Weather_Interface->getTtl();

        return $cron
            ? (function () use ($cacheKey, $endpoint, $queryParams, $ttl) {
                $response = $this->client->request('GET', "http://api.weatherapi.com/v1/{$endpoint}", [
                    'query' => array_merge(['key' => $this->apiKey], $queryParams)
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException("API request failed: {$response->getStatusCode()}");
                }

                $data = $response->toArray();

                $item = $this->cache->getItem($cacheKey);
                $item->set($data);
                $item->expiresAfter($ttl);
                $this->cache->save($item);

                return $data;
            })()
        : $this->cache->get($cacheKey, function (ItemInterface $item) use ($endpoint, $queryParams, $ttl, $cron, $cacheKey) {
            $item->expiresAfter($ttl);

            $response = $this->client->request('GET', "http://api.weatherapi.com/v1/{$endpoint}", [
                'query' => array_merge([
                    'key' => $this->apiKey
                ], $queryParams)
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("API request failed: {$response->getStatusCode()}");
            }
            return $response->toArray();
        });


    }
}
