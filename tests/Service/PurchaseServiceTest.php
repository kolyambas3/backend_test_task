<?php

namespace App\Tests\Service;

use App\DTO\PurchaseRequest;
use App\Service\PurchaseService;
use App\Service\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PurchaseServiceTest extends TestCase
{
    private PurchaseService $purchaseService;
    private PriceCalculator $priceCalculator;

    protected function setUp(): void
    {
        $this->priceCalculator = $this->createMock(PriceCalculator::class);
        $this->purchaseService = new PurchaseService($this->priceCalculator);
    }

    public function testPurchaseWithPaypal(): void
    {
        $dto = new PurchaseRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');
        $dto->setPaymentProcessor('paypal');

        $this->priceCalculator->method('calculate')->willReturn(100.0);

        $this->purchaseService->purchase($dto);

        $this->assertTrue(true);
    }

    public function testPurchaseWithStripe(): void
    {
        $dto = new PurchaseRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');
        $dto->setPaymentProcessor('stripe');

        $this->priceCalculator->method('calculate')->willReturn(100.0);

        $this->purchaseService->purchase($dto);

        $this->assertTrue(true);
    }

    public function testPurchaseWithInvalidPaymentProcessor(): void
    {
        $dto = new PurchaseRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');
        $dto->setPaymentProcessor('invalid');

        $this->priceCalculator->method('calculate')->willReturn(100.0);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid payment processor');

        $this->purchaseService->purchase($dto);
    }
}