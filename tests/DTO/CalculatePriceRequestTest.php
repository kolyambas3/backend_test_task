<?php

namespace App\Tests\DTO;

use App\DTO\CalculatePriceRequest;
use Symfony\Component\Validator\Validation;
use PHPUnit\Framework\TestCase;

class CalculatePriceRequestTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    public function testValidData(): void
    {
        $request = new CalculatePriceRequest();
        $request->setProduct(1);
        $request->setTaxNumber('DE123456789');
        $request->setCouponCode('D15');

        $errors = $this->validator->validate($request);
        $this->assertCount(0, $errors);
    }

    public function testInvalidTaxNumber(): void
    {
        $request = new CalculatePriceRequest();
        $request->setProduct(1);
        $request->setTaxNumber('INVALID');
        $request->setCouponCode('D15');

        $errors = $this->validator->validate($request);
        $this->assertCount(1, $errors);
        $this->assertEquals('Invalid tax number format.', $errors[0]->getMessage());
    }

    public function testMissingProduct(): void
    {
        $request = new CalculatePriceRequest();
        $request->setTaxNumber('DE123456789');
        $request->setCouponCode('D15');

        $errors = $this->validator->validate($request);
        $this->assertCount(1, $errors);
        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
    }
}