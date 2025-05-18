<?php

namespace App\Tests\Controller;

use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testRedirectsToLoginIfNoToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }

    public function testRedirectsToLoginIfInvalidToken(): void
    {
        $client = static::createClient();

        $jwtService = $this->createMock(JwtService::class);
        $jwtService->method('validateToken')->willReturn(null);

        static::getContainer()->set(JwtService::class, $jwtService);

        $client->request('GET', '/', [], [], [
            'HTTP_COOKIE' => 'auth_token=invalidtoken',
        ]);

        $this->assertResponseRedirects('/login');
    }

    public function testRendersMainPageWithForecast()
    {
        $client = static::createClient();

        $cookie = new \Symfony\Component\BrowserKit\Cookie('auth_token', 'validtoken');
        $client->getCookieJar()->set($cookie);

        $jwtService = $this->createMock(\App\Service\JwtService::class);
        $jwtService->method('validateToken')->willReturn(['email' => 'user@example.com']);
        static::getContainer()->set(\App\Service\JwtService::class, $jwtService);

        $subscription = new \App\Entity\Subscription();
        $subscription->setEmail('user@example.com')
            ->setCity('Kyiv')
            ->setFrequency(\App\Enum\Frequency::DAILY)
            ->setConfirmed(true);

        $subscriptionRepo = $this->createMock(\App\Repository\SubscriptionRepository::class);
        $subscriptionRepo->method('findOneBy')->with(['email' => 'user@example.com'])->willReturn($subscription);
        static::getContainer()->set(\App\Repository\SubscriptionRepository::class, $subscriptionRepo);

        $weatherService = $this->createMock(\App\Service\WeatherAPIService::class);
        $weatherService->method('fetchFromWeatherApi')->willReturn([
            'current' => [
                'temp_c' => 20,
                'humidity' => 55,
                'wind_kph' => 10,
                'condition' => [
                    'text' => 'Sunny',
                    'icon' => '//cdn.weatherapi.com/weather/64x64/day/113.png',
                ],
            ]
        ]);
        static::getContainer()->set(\App\Service\WeatherAPIService::class, $weatherService);

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Kyiv');
        $this->assertSelectorTextContains('body', 'Sunny');
    }

}
