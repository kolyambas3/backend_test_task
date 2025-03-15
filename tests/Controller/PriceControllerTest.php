<?php

namespace App\Tests\Controller;

use App\DTO\CalculatePriceRequest;
use App\Service\PriceCalculator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;

class PriceControllerTest extends KernelTestCase
{
    private PriceCalculator $priceCalculator;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->priceCalculator = $this->createMock(PriceCalculator::class);
    }

    public function testCalculatePriceSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
        ]));

        $dto = new CalculatePriceRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');

        $this->serializer
            ->method('deserialize')
            ->with(
                $request->getContent(),
                CalculatePriceRequest::class,
                'json'
            )
            ->willReturn($dto);

        $this->validator
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $this->priceCalculator
            ->method('calculate')
            ->with($dto)
            ->willReturn(106.4);

        $controller = new \App\Controller\PriceController(
            $this->priceCalculator,
            $this->validator,
            $this->serializer
        );

        $container = self::getContainer();
        $controller->setContainer($container);

        $response = $controller->__invoke($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('{"price":106.4}', $response->getContent());
    }

    public function testCalculatePriceValidationError(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'INVALID',
            'couponCode' => 'D15',
        ]));

        $dto = new CalculatePriceRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('INVALID');
        $dto->setCouponCode('D15');

        $this->serializer
            ->method('deserialize')
            ->willReturn($dto);

        $violation = new ConstraintViolation(
            'Invalid tax number.',
            null,
            [],
            $dto,
            'taxNumber',
            'INVALID'
        );

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $controller = new \App\Controller\PriceController(
            $this->priceCalculator,
            $this->validator,
            $this->serializer
        );

        $container = self::getContainer();
        $controller->setContainer($container);

        $response = $controller->__invoke($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('{"errors":{"taxNumber":"Invalid tax number."}}', $response->getContent());
    }

    public function testCalculatePriceInvalidArgumentException(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
        ]));

        $dto = new CalculatePriceRequest();
        $dto->setProduct(1);
        $dto->setTaxNumber('DE123456789');
        $dto->setCouponCode('D15');

        $this->serializer
            ->method('deserialize')
            ->willReturn($dto);

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->priceCalculator
            ->method('calculate')
            ->with($dto)
            ->willThrowException(new \InvalidArgumentException('Product not found'));

        $controller = new \App\Controller\PriceController(
            $this->priceCalculator,
            $this->validator,
            $this->serializer
        );

        $container = self::getContainer();
        $controller->setContainer($container);

        $response = $controller->__invoke($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('{"error":"Product not found"}', $response->getContent());
    }
}