<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $coupons = [
            ['code' => 'D15', 'type' => 'fixed', 'value' => 15],
            ['code' => 'P10', 'type' => 'percent', 'value' => 10],
            ['code' => 'P100', 'type' => 'percent', 'value' => 100],
        ];

        foreach ($coupons as $couponData) {
            $coupon = new Coupon();
            $coupon->setCode($couponData['code']);
            $coupon->setType($couponData['type']);
            $coupon->setValue($couponData['value']);
            $manager->persist($coupon);
        }

        $manager->flush();
    }
}