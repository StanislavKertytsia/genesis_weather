<?php

namespace App\Controller;

use App\DTO\API_Weather_CitySearchDTO;
use App\DTO\APIWeatherCitySearchDTO;
use App\Enum\CacheKey;
use App\Enum\Duration;
use App\Enum\WeatherEndpoint;
use App\Service\CitySearchService;
use App\Service\WeatherAPIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    #[Route('/city/search', name: 'city_search')]
    public function search(Request $request, WeatherAPIService $weather): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }
        $cacheKey = CacheKey::CITY->value . md5(mb_strtolower(trim($query)));
        $endpoint = WeatherEndpoint::CITY_SEARCH->value;
        $ttl = Duration::MONTH->value;
        $API_Weather_CitySearchDTO = new API_Weather_CitySearchDTO(
            $endpoint,
            $cacheKey,
            $ttl
        );

        $cities = $weather->fetchFromWeatherApi($API_Weather_CitySearchDTO, ['q' => $query]);
        return new JsonResponse($cities);
    }
}
