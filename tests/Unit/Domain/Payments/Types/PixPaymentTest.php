<?php

namespace Tests\Unit\Domain\Payments\Types;

use App\Domain\Payments\Types\PixPayment;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PixPaymentTest extends TestCase
{
    private PixPayment $pixPayment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pixPayment = new PixPayment();
    }

    /** @test */
    public function it_processes_pix_payment_correctly()
    {
        $data = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];
        $total = 100.00;

        // Esperado: 100 - 10% = 90
        $result = $this->pixPayment->processPayment($data, $total);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Pix payment processed successfully', $result['message']);
        $this->assertEquals($data['payment_method'], $result['data']['payment_method']);
        $this->assertEquals($data['qtd_installments'], $result['data']['qtd_installments']);
        $this->assertEquals(90.00, $result['data']['amount']);
    }

    /** @test */
    public function it_applies_10_percent_discount_for_pix_payment()
    {
        $data = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];
        $total = 200.00;

        $result = $this->pixPayment->processPayment($data, $total);
        
        $this->assertEquals(180.00, $result['data']['amount']);
    }

    /** @test */
    public function it_validates_installments_must_be_one_for_pix()
    {
        // Caso válido
        $validData = ['qtd_installments' => 1];
        $this->assertTrue($this->pixPayment->validatePaymentData($validData));

        // Casos inválidos
        $invalidCases = [0, 2, 3, 12];
        
        foreach ($invalidCases as $installments) {
            try {
                $this->pixPayment->validatePaymentData(['qtd_installments' => $installments]);
                $this->fail("Validation should have failed for installments: {$installments}");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('qtd_installments', $e->errors());
                $this->assertEquals(
                    'When payment method is PIX, the number of installments must be 1.',
                    $e->errors()['qtd_installments'][0]
                );
            }
        }
    }

    /** @test */
    public function it_rounds_amount_to_two_decimals()
    {
        $data = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];
        $total = 99.99;

        // 99.99 - 10% = 89.991 → arredondado para 89.99
        $result = $this->pixPayment->processPayment($data, $total);
        
        $this->assertEquals(89.99, $result['data']['amount']);
    }

    /** @test */
    public function it_handles_minimum_payment_amount()
    {
        $data = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];
        $total = 0.01; // Valor mínimo

        $result = $this->pixPayment->processPayment($data, $total);
        
        // 0.01 - 10% = 0.009 → arredondado para 0.01
        $this->assertEquals(0.01, $result['data']['amount']);
    }

    /** @test */
    public function it_returns_correct_response_structure()
    {
        $data = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];
        $total = 50.00;

        $result = $this->pixPayment->processPayment($data, $total);
        
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('payment_method', $result['data']);
        $this->assertArrayHasKey('qtd_installments', $result['data']);
        $this->assertArrayHasKey('amount', $result['data']);
    }

    /** @test */
    public function it_does_not_include_card_information_in_response()
    {
        $data = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];
        $total = 75.50;

        $result = $this->pixPayment->processPayment($data, $total);
        
        $this->assertArrayNotHasKey('card_information', $result['data']);
    }
}