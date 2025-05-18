<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CityControllerTest extends WebTestCase
{
    public function testCitySearchWithValidQuery(): void
    {
        $client = static::createClient();

        $client->request('GET', '/city/search?q=Kyiv');
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testCitySearchWithShortQueryReturnsEmptyArray(): void
    {
        $client = static::createClient();

        $client->request('GET', '/city/search?q=k');
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame([], $data);
    }

    public function testCitySearchWithMissingQueryReturnsEmptyArray(): void
    {
        $client = static::createClient();

        $client->request('GET', '/city/search');
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame([], $data);
    }
}
