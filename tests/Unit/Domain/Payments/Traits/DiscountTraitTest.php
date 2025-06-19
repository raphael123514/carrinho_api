<?php

namespace Tests\Unit\Domain\Payments\Traits;

use Tests\TestCase;

/**
 * @group payment_traits
 */
class DiscountTraitTest extends TestCase
{
    /** @test */
    public function it_applies_10_percent_discount_for_cash_payment()
    {
        $testClass = new class {
            use \App\Domain\Payments\Traits\DiscountTrait;
        };

        $total = 100.00;
        $installments = 1;

        $result = $testClass->applyDiscount($total, $installments);

        $this->assertEquals(90.00, $result);
    }

    /** @test */
    public function it_does_not_apply_discount_for_installment_payments()
    {
        $testClass = new class {
            use \App\Domain\Payments\Traits\DiscountTrait;
        };

        $total = 100.00;
        $installments = 2;

        $result = $testClass->applyDiscount($total, $installments);

        $this->assertEquals(100.00, $result);
    }

    /** @test */
    public function it_handles_zero_amount_correctly()
    {
        $testClass = new class {
            use \App\Domain\Payments\Traits\DiscountTrait;
        };

        $total = 0.00;
        $installments = 1;

        $result = $testClass->applyDiscount($total, $installments);

        $this->assertEquals(0.00, $result);
    }

    /** @test */
    public function it_handles_negative_amounts_correctly()
    {
        $testClass = new class {
            use \App\Domain\Payments\Traits\DiscountTrait;
        };

        $total = -100.00;
        $installments = 1;

        $result = $testClass->applyDiscount($total, $installments);

        $this->assertEquals(-90.00, $result);
    }

    /** @test */
    public function it_uses_constant_for_cash_payment_definition()
    {
        $testClass = new class {
            use \App\Domain\Payments\Traits\DiscountTrait;
            public function getCashConstant()
            {
                return self::CASH;
            }
        };

        $this->assertEquals(1, $testClass->getCashConstant());
    }
}
