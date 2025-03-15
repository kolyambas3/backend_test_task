<?php

namespace App\Tests\Service;

use App\DTO\CalculatePriceRequest;
use App\Entity\Coupon;
use App\Entity\Product;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $priceCalculator;
    private ProductRepository $productRepository;
    private CouponRepository $couponRepository;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->priceCalculator = new PriceCalculator($this->productRepository, $this->couponRepository);
    }

    public function testCalculatePriceWithTax(): void
    {
        $product = new Product();
        $product->setPrice(100);

        $this->productRepository->method('find')->willReturn($product);

        $dto = new CalculatePriceRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');

        $price = $this->priceCalculator->calculate($dto);
        $this->assertEquals(119.0, $price); // 100 + 19% налог
    }

    public function testCalculatePriceWithCoupon(): void
    {
        $product = new Product();
        $product->setPrice(100);

        $coupon = new Coupon();
        $coupon->setCode('D15');
        $coupon->setType('fixed');
        $coupon->setValue(15);

        $this->productRepository->method('find')->willReturn($product);
        $this->couponRepository->method('findOneBy')->willReturn($coupon);

        $dto = new CalculatePriceRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');

        $price = $this->priceCalculator->calculate($dto);
        $this->assertEquals(101.15, $price);
    }

    public function testCalculatePriceWithInvalidProduct(): void
    {
        $this->productRepository->method('find')->willReturn(null);

        $dto = new CalculatePriceRequest();
        $dto->setProduct(999);
        $dto->setTaxNumber('DE123456789');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found');

        $this->priceCalculator->calculate($dto);
    }
}