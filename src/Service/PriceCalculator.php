<?php

namespace App\Service;

use App\Dto\CalculatePriceRequest;
use App\Repository\ProductRepository;
use App\Repository\CouponRepository;

class PriceCalculator
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly CouponRepository  $couponRepository,
    ) {}

    public function calculate(
        CalculatePriceRequest $dto,
    ): float {
        $product = $this->productRepository->find($dto->getProduct());
        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $price = $product->getPrice();

        $couponCode = $dto->getCouponCode();
        if ($couponCode) {
            $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);
            if (!$coupon) {
                throw new \InvalidArgumentException('Invalid coupon code');
            }

            if ($coupon->getType() === 'fixed') {
                $price -= $coupon->getValue();
            } elseif ($coupon->getType() === 'percent') {
                $price *= (1 - $coupon->getValue() / 100);
            }
        }

        $taxRate = $this->getTaxRate($dto->getTaxNumber());
        $price *= (1 + $taxRate / 100);

        return round($price, 2);
    }

    private function getTaxRate(
        string $taxNumber,
    ): float {
        $countryCode = substr($taxNumber, 0, 2);
        switch ($countryCode) {
            case 'DE': return 19;
            case 'IT': return 22;
            case 'FR': return 20;
            case 'GR': return 24;
            default: throw new \InvalidArgumentException('Invalid tax number');
        }
    }
}