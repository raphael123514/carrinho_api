<?php

namespace App\Domain\Payments\Types;

use App\Domain\Payments\Contracts\PaymentMethodInterface;
use App\Domain\Payments\Traits\DiscountTrait;
use App\Models\CartItems;
use Illuminate\Validation\ValidationException;

class PixPayment implements PaymentMethodInterface
{
    use DiscountTrait;

    /**
     * Processes a Pix payment for the given cart items and payment data.
     *
     * Calculates the payment amount considering possible discounts based on the number of installments,
     * and returns a response array with the payment status, message, and relevant payment data.
     *
     * @param array $data Associative array containing payment data, including 'payment_method' and 'qtd_installments'.
     * @param CartItems $cartItem The cart items to be processed for payment.
     * @return array Response array containing the status, message, and payment details.
     */
    public function processPayment(array $data, float $total)
    {
        $amount = $this->applyDiscount($total, $data['qtd_installments']);
     
        return [
            'status' => 'success',
            'message' => 'Pix payment processed successfully',
            'data' => [
                'payment_method' => $data['payment_method'],
                'qtd_installments' => $data['qtd_installments'],
                'amount' => round($amount, 2),
            ]
        ];
    }

    /**
     * Validates the payment data for PIX payment method.
     *
     * Ensures that the number of installments (`qtd_installments`) is exactly 1,
     * as PIX payments do not support multiple installments.
     *
     * @param array $data The payment data to validate. Must include 'qtd_installments'.
     * @return bool Returns true if the payment data is valid.
     * @throws \Illuminate\Validation\ValidationException If the number of installments is not 1.
     */
    public function validatePaymentData(array $data): bool
    {
        if ($data['qtd_installments'] !== 1) {
            throw ValidationException::withMessages([
                'qtd_installments' => 'When payment method is PIX, the number of installments must be 1.'
            ]);
        }

        return true;
    }
}