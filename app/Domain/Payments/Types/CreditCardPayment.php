<?php

namespace App\Domain\Payments\Types;

use App\Domain\Payments\Contracts\PaymentMethodInterface;
use App\Domain\Payments\Traits\DiscountTrait;
use App\Domain\Payments\Traits\InterestsTrait;
use App\Models\CartItems;
use Illuminate\Validation\ValidationException;

class CreditCardPayment implements PaymentMethodInterface
{
    use InterestsTrait, DiscountTrait;
    
    /**
     * Processes a credit card payment for the given cart items and payment data.
     *
     * Calculates the total amount including interests and discounts based on the number of installments.
     * Returns an array containing the status, message, and payment details.
     *
     * @param array $data Associative array containing payment details:
     *                    - 'payment_method': string, the payment method used.
     *                    - 'qtd_installments': int, the number of installments.
     *                    - 'card_information': array, the credit card information.
     * @param CartItems $cartItem The cart items object containing the total amount.
     *
     * @return array An array with the following structure:
     *               - 'status': string, the result status.
     *               - 'message': string, a descriptive message.
     *               - 'data': array, payment details including method, installments, card info, and amount.
     */
    public function processPayment(array $data, float $total)
    {
        $amount = $this->applyInterests($total, $data['qtd_installments']);
        $amount = $this->applyDiscount($amount, $data['qtd_installments']);
        
        return [
            'status' => 'success',
            'message' => 'Credit card payment processed successfully',
            'data' => [
                'payment_method' => $data['payment_method'],
                'qtd_installments' => $data['qtd_installments'],
                'card_information' => $data['card_information'] ?? [],
                'amount' => round($amount, 2)
            ]
        ];
    }

    /**
     * Validates the payment data for credit card payments.
     *
     * Checks if the number of installments ('qtd_installments') is between 1 and 12.
     * Throws a ValidationException if the value is out of bounds.
     *
     * @param array $data The payment data to validate.
     * @return bool Returns true if the data is valid.
     * @throws \Illuminate\Validation\ValidationException If the number of installments is invalid.
     */
    public function validatePaymentData(array $data): bool
    {
        if ($data['qtd_installments'] < 1 || $data['qtd_installments'] > 12) {
            throw ValidationException::withMessages([
                'qtd_installments' => 'The number of installments must be at least 1 and at most 12.'
            ]);
        }

        return true;
    }
}