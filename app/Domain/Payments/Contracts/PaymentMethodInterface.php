<?php

namespace App\Domain\Payments\Contracts;

interface PaymentMethodInterface
{
    public function processPayment(array $data,  float $total);

    public function validatePaymentData(array $data): bool;

}
