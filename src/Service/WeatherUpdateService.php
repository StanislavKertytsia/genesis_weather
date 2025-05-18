<?php

namespace App\Service;

use App\DTO\API_Weather_ForecastDTO;
use App\Repository\SubscriptionRepository;

class WeatherUpdateService
{
    private SubscriptionRepository $subscriptionRepository;
    private WeatherAPIService $weatherAPIService;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        WeatherAPIService      $weatherAPIService,
    )
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->weatherAPIService = $weatherAPIService;
    }

    public function updateByFrequency(string $frequency): void
    {
        $subscriptions = $this->subscriptionRepository->findBy(['frequency' => $frequency]);

        foreach ($subscriptions as $subscription) {
            if (!$subscription || !$subscription->isConfirmed()) {
                continue;
            }
            try {
                $city = $subscription->getCity();
                $API_Weather_ForecastDTO = new API_Weather_ForecastDTO($subscription);;
                $this->weatherAPIService->fetchFromWeatherApi($API_Weather_ForecastDTO, ['q' => $city, 'aqi' => 'no'], true);
            } catch (\Exception $e) {
                continue;
            }
        }

    }
}
