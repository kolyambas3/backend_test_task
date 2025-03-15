<?php

namespace App\Service;

use App\Dto\PurchaseRequest;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PurchaseService
{
    private const STRIPE_PAYMENT_PROCESSOR = 'stripe';
    private const PAYPAL_PAYMENT_PROCESSOR = 'paypal';
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {}

    public function purchase(
        PurchaseRequest $dto,
    ): void {
        $price = $this->priceCalculator->calculate($dto);

        switch ($dto->getPaymentProcessor()) {
            case self::PAYPAL_PAYMENT_PROCESSOR:
                $processor = new PaypalPaymentProcessor();
                $processor->pay($price);
                break;
            case self::STRIPE_PAYMENT_PROCESSOR:
                $processor = new StripePaymentProcessor();
                if (!$processor->processPayment($price)) {
                    throw new \InvalidArgumentException('Payment failed');
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid payment processor');
        }
    }
}