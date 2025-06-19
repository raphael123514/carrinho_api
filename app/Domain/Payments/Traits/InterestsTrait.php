<?php

namespace App\Domain\Payments\Traits;

trait InterestsTrait
{
    /**
     * Calculates the total amount including interests based on the number of installments.
     *
     * If the number of installments is greater than 1, applies a compounded interest calculation
     * where the monthly interest rate is 1% per installment.
     *
     * @param float $total The original total amount of the purchase.
     * @param int $qtd_installments The number of installments.
     * @return float The total amount including interests.
     */
    public function applyInterests(float $total, int $qtd_installments): float
    {
        if ($qtd_installments > 1) {
            $P = $total;                    // Total purchase amount
            $i = $qtd_installments * 0.01;  // Monthly interest rate
            $n = $qtd_installments;         // Number of installments
            return $P * pow((1 + ($i)), $n);
        }

        return $total;
    }
}
