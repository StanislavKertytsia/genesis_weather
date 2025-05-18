<?php

namespace App\Controller;

use App\Enum\Duration;
use App\Enum\WeatherEndpoint;
use App\Service\WeatherAPIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    private WeatherAPIService $weatherAPIService;

    public function __construct(WeatherAPIService $weatherAPIService)
    {
        $this->weatherAPIService = $weatherAPIService;
    }

    #[Route('/weather', name: 'weather')]
    public function getWeather(Request $request): JsonResponse
    {
        $city = $request->query->get('city', '');
        if (!$city) {
            return $this->json(['error' => 'City not found'], 404);
        }

        $queryParams = ['q' => $city];

        $endpoint = WeatherEndpoint::WEATHER->value;

        $ttl = Duration::HOUR->value;

        $weatherData = $this->weatherAPIService->fetchFromWeatherApi(
            new \App\DTO\API_Weather_ForecastDTO($endpoint, 'weather_' . md5($city), 3600), // TTL 1 час
            $queryParams
        );

        return $this->json($weatherData);
    }
}
