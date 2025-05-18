<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use App\Enum\Frequency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SubscriptionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $subscriptions = [
            ['alice@example.com', 'Kyiv', 'daily'],
            ['bob@example.com', 'Lviv', 'hourly'],
            ['carol@example.com', 'Odesa', 'daily'],
            ['dave@example.com', 'Dnipro', 'hourly'],
            ['eve@example.com', 'Kharkiv', 'daily'],
            ['frank@example.com', 'Zaporizhzhia', 'hourly'],
            ['grace@example.com', 'Vinnytsia', 'daily'],
            ['heidi@example.com', 'Ternopil', 'daily'],
            ['ivan@example.com', 'Poltava', 'hourly'],
            ['judy@example.com', 'Chernihiv', 'daily'],
        ];


        foreach ($subscriptions as [$email, $city, $frequency]) {
            $subscription = new Subscription();
            $subscription->setEmail($email);
            $subscription->setCity($city);
            $subscription->setFrequency(Frequency::from($frequency));
            $subscription->setConfirmed(true);

            $manager->persist($subscription);
        }

        $manager->flush();
    }
}
