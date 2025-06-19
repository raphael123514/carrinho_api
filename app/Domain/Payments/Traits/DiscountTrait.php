<?php

namespace App\Domain\Payments\Traits;

trait DiscountTrait
{
    private const CASH = 1;

    /**
     * Calculates the total amount after applying a discount based on the number of installments.
     *
     * If the number of installments is 1, applies a 10% discount.
     *
     * @param float $total The original total amount of the purchase.
     * @param int $qtd_installments The number of installments.
     * @return float The total amount after applying the discount.
     */
    public function applyDiscount(float $total, int $qtd_installments): float
    {
        if ($qtd_installments === self::CASH) {
            return $total - ($total * 0.10);
        }
        return $total;
    }
}
