<?php

namespace App\Domain\Payments\Services;

use App\Models\CartItems;

class PaymentService
{

    public function __construct(private CartItems $cartItems)
    {
    }

    /**
     * Processes the payment according to the specified payment method.
     *
     * @param array $request The request data, including the payment method and other required parameters.
     *
     * @return array The result of the payment processing.
     *
     * @throws \InvalidArgumentException If the payment method is not provided.
     * @throws \Exception May throw other exceptions depending on the payment processor implementation.
     */
    public function sendMethodPayment(array $request): array
    {
        $paymentMethod = $request['payment_method'] ?? null;
        
        if (!$paymentMethod) {
            throw new \InvalidArgumentException('Payment method is required.');
        }

        $total = $this->cartItems->getTotalPrice();
        
        $processor = app('payment.processor', ['method' => $paymentMethod]);
        
        return $processor->process($request, $total);
    }
}
