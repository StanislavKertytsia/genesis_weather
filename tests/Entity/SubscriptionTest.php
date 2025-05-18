<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Enum\Frequency;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testInitialIdIsGenerated()
    {
        $subscription = new Subscription();
        $this->assertNotEmpty($subscription->getId());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $subscription->getId()
        );
    }

    public function testEmailSetterAndGetter()
    {
        $subscription = new Subscription();
        $subscription->setEmail('user@example.com');
        $this->assertEquals('user@example.com', $subscription->getEmail());
    }

    public function testCitySetterAndGetter()
    {
        $subscription = new Subscription();
        $subscription->setCity('Kyiv');
        $this->assertEquals('Kyiv', $subscription->getCity());
    }

    public function testFrequencySetterAndGetter()
    {
        $subscription = new Subscription();
        $subscription->setFrequency(Frequency::DAILY);
        $this->assertEquals(Frequency::DAILY, $subscription->getFrequency());
    }

    public function testConfirmedSetterAndGetter()
    {
        $subscription = new Subscription();
        $this->assertFalse($subscription->isConfirmed());

        $subscription->setConfirmed(true);
        $this->assertTrue($subscription->isConfirmed());
    }
}
