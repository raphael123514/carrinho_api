<?php

namespace App\Domain\Payments;

use App\Domain\Payments\Contracts\PaymentMethodInterface;

class PaymentProcessor
{
    public function __construct(private PaymentMethodInterface $paymentMethod)
    {
    }

    public function setPaymentMethod(PaymentMethodInterface $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function process(array $data, float $total): array
    {
        if (empty($this->paymentMethod)) {
            throw new \Exception('Payment method not set.');
        }

        $this->paymentMethod->validatePaymentData($data);
        return $this->paymentMethod->processPayment($data, $total);
    }
}
