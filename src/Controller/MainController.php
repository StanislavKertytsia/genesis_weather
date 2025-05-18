<?php

namespace App\Controller;

use App\DTO\API_Weather_ForecastDTO;
use App\DTO\WeatherDTO;
use App\Repository\SubscriptionRepository;
use App\Service\JwtService;
use App\Service\WeatherAPIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    private JwtService $jwtService;
    private WeatherAPIService $weatherService;
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(
        JwtService             $jwtService,
        SubscriptionRepository $subscriptionRepository,
        WeatherAPIService      $weatherService
    )
    {
        $this->jwtService = $jwtService;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->weatherService = $weatherService;
    }

    #[Route('/', name: 'app_main')]
    public function main(Request $request): Response
    {
        $token = $request->cookies->get('auth_token');
//        dd($token);
        if (!$token) {
            return $this->redirectToRoute('app_login');
        }
        $payload = $this->jwtService->validateToken($token);

        if (isset($payload['email'])) {
            $subscription = $this->subscriptionRepository->findOneBy(['email' => $payload['email']]);

            if ($subscription && $subscription->isConfirmed()) {

                $API_Weather_ForecastDTO = new API_Weather_ForecastDTO($subscription);
                $city = $subscription->getCity();

                $forecast = $this->weatherService->fetchFromWeatherApi(
                    $API_Weather_ForecastDTO,
                    ['q' => $city, 'aqi' => 'no']
                );

                return $this->render('main/main.html.twig', [
                    'subscription' => $subscription,
                    'forecast' => $forecast,
                    'token' => $token,
                ]);
            }
        }

        return $this->redirectToRoute('app_login');
    }
}
