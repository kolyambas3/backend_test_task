<?php

namespace App\Tests\Controller;

use App\Controller\PurchaseController;
use App\DTO\PurchaseRequest;
use App\Service\PurchaseService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PurchaseControllerTest extends KernelTestCase
{
    private PurchaseController $controller;
    private PurchaseService $purchaseService;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->purchaseService = $this->createMock(PurchaseService::class);

        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->controller = new PurchaseController(
            $this->purchaseService,
            $this->validator,
            $this->serializer
        );

        $this->controller->setContainer($container);
    }

    public function testPurchaseSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
            'paymentProcessor' => 'paypal',
        ]));

        $dto = new PurchaseRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');
        $dto->setPaymentProcessor('paypal');

        $this->serializer
            ->method('deserialize')
            ->with(
                $request->getContent(),
                PurchaseRequest::class,
                'json'
            )
            ->willReturn($dto);

        $this->validator
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $response = $this->controller->__invoke($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('{"status":"success"}', $response->getContent());
    }

    public function testPurchaseValidationError(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
            'paymentProcessor' => 'invalid',
        ]));

        $dto = new PurchaseRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');
        $dto->setPaymentProcessor('invalid');

        $this->serializer
            ->method('deserialize')
            ->willReturn($dto);

        $violation = new ConstraintViolation(
            'Invalid payment processor.',
            null,
            [],
            $dto,
            'paymentProcessor',
            'invalid'
        );

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $response = $this->controller->__invoke($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('{"errors":{"paymentProcessor":"Invalid payment processor."}}', $response->getContent());
    }

    public function testPurchaseInvalidArgumentException(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
            'paymentProcessor' => 'paypal',
        ]));

        $dto = new PurchaseRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');
        $dto->setPaymentProcessor('paypal');

        $this->serializer
            ->method('deserialize')
            ->willReturn($dto);

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->purchaseService
            ->method('purchase')
            ->with($dto)
            ->willThrowException(new \InvalidArgumentException('Payment failed'));

        $response = $this->controller->__invoke($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('{"error":"Payment failed"}', $response->getContent());
    }
}