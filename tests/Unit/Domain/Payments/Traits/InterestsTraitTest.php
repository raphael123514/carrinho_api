<?php

namespace Tests\Unit\Domain\Payments\Traits;

use Tests\TestCase;

/**
 * @group payment_traits
 */
class InterestsTraitTest extends TestCase
{
    /** @test */
    public function it_does_not_apply_interests_for_single_installment()
    {
        // Arrange
        $testClass = new class {
            use \App\Domain\Payments\Traits\InterestsTrait;
        };
        
        $total = 100.00;
        $installments = 1; // À vista

        // Act
        $result = $testClass->applyInterests($total, $installments);

        // Assert
        $this->assertEquals(100.00, $result);
    }

    /** @test */
    public function it_applies_compound_interests_for_multiple_installments()
    {
        // Arrange
        $testClass = new class {
            use \App\Domain\Payments\Traits\InterestsTrait;
        };
        
        $total = 100.00;
        $installments = 3; // Parcelado

        // Cálculo esperado: 100 * (1 + (3*0.01))^3 = 100 * 1.03^3 ≈ 109.2727
        $expected = 100 * pow(1.03, 3);

        // Act
        $result = $testClass->applyInterests($total, $installments);

        // Assert
        $this->assertEqualsWithDelta($expected, $result, 0.0001);
    }

    /** @test */
    public function it_handles_zero_amount_correctly()
    {
        // Arrange
        $testClass = new class {
            use \App\Domain\Payments\Traits\InterestsTrait;
        };
        
        $total = 0.00;
        $installments = 3;

        // Act
        $result = $testClass->applyInterests($total, $installments);

        // Assert
        $this->assertEquals(0.00, $result);
    }

    /** @test */
    public function it_handles_negative_amounts_correctly()
    {
        // Arrange
        $testClass = new class {
            use \App\Domain\Payments\Traits\InterestsTrait;
        };
        
        $total = -100.00;
        $installments = 2;

        // Cálculo esperado: -100 * (1 + (2*0.01))^2 = -100 * 1.02^2 ≈ -104.04
        $expected = -100 * pow(1.02, 2);

        // Act
        $result = $testClass->applyInterests($total, $installments);

        // Assert
        $this->assertEqualsWithDelta($expected, $result, 0.0001);
    }

    /** @test */
    public function it_calculates_correctly_for_different_installment_values()
    {
        // Arrange
        $testClass = new class {
            use \App\Domain\Payments\Traits\InterestsTrait;
        };
        
        $testCases = [
            [100.00, 2, 100 * pow(1.02, 2)],  // 2 parcelas: 2% por mês
            [500.00, 4, 500 * pow(1.04, 4)],  // 4 parcelas: 4% por mês
            [250.00, 6, 250 * pow(1.06, 6)],  // 6 parcelas: 6% por mês
            [1000.00, 12, 1000 * pow(1.12, 12)] // 12 parcelas: 12% por mês
        ];

        foreach ($testCases as $case) {
            [$total, $installments, $expected] = $case;
            
            // Act
            $result = $testClass->applyInterests($total, $installments);

            // Assert
            $this->assertEqualsWithDelta(
                $expected,
                $result,
                0.0001,
                "Failed for {$installments} installments on amount {$total}"
            );
        }
    }

    /** @test */
    public function it_returns_same_value_when_installments_less_than_two()
    {
        // Arrange
        $testClass = new class {
            use \App\Domain\Payments\Traits\InterestsTrait;
        };
        
        $total = 150.00;

        // Testando 0 e 1 parcelas (não deve aplicar juros)
        foreach ([0, 1] as $installments) {
            // Act
            $result = $testClass->applyInterests($total, $installments);

            // Assert
            $this->assertEquals($total, $result);
        }
    }
}