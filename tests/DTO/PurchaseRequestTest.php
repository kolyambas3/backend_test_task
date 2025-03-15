<?php

namespace App\Tests\DTO;

use App\DTO\PurchaseRequest;
use Symfony\Component\Validator\Validation;
use PHPUnit\Framework\TestCase;

class PurchaseRequestTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    public function testValidData(): void
    {
        $request = new PurchaseRequest();
        $request->setProduct(1);
        $request->setTaxNumber('DE123456789');
        $request->setCouponCode('D15');
        $request->setPaymentProcessor('paypal');

        $errors = $this->validator->validate($request);
        $this->assertCount(0, $errors);
    }

    public function testInvalidPaymentProcessor(): void
    {
        $request = new PurchaseRequest();
        $request->setProduct(1);
        $request->setTaxNumber('DE123456789');
        $request->setCouponCode('D15');
        $request->setPaymentProcessor('invalid');

        $errors = $this->validator->validate($request);
        $this->assertCount(1, $errors);
        $this->assertEquals('Invalid payment processor.', $errors[0]->getMessage());
    }

    public function testMissingPaymentProcessor(): void
    {
        $request = new PurchaseRequest();
        $request->setProduct(1);
        $request->setTaxNumber('DE123456789');
        $request->setCouponCode('D15');

        $errors = $this->validator->validate($request);
        $this->assertCount(1, $errors);
        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
    }
}