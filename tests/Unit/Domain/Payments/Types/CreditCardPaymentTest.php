<?php

namespace Tests\Unit\Domain\Payments\Types;

use App\Domain\Payments\Types\CreditCardPayment;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreditCardPaymentTest extends TestCase
{
    private CreditCardPayment $creditCardPayment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditCardPayment = new CreditCardPayment();
    }

    /** @test */
    public function it_processes_credit_card_payment_correctly()
    {
        $data = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 3,
            'card_information' => [
                'number' => '4111111111111111',
                'expiry' => '12/25'
            ]
        ];
        $total = 100.00;

        $expectedAmount = round(100 * pow(1.03, 3), 2);

        $result = $this->creditCardPayment->processPayment($data, $total);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Credit card payment processed successfully', $result['message']);
        $this->assertEquals($data['payment_method'], $result['data']['payment_method']);
        $this->assertEquals($data['qtd_installments'], $result['data']['qtd_installments']);
        $this->assertEquals($data['card_information'], $result['data']['card_information']);
        $this->assertEquals($expectedAmount, $result['data']['amount']);
    }

    /** @test */
    public function it_applies_discount_for_cash_payment()
    {
        $data = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 1,
            'card_information' => []
        ];
        $total = 200.00;

        $result = $this->creditCardPayment->processPayment($data, $total);
        
        $this->assertEquals(180.00, $result['data']['amount']);
    }

    /** @test */
    public function it_validates_installments_correctly()
    {
        $validData = ['qtd_installments' => 6];
        $this->assertTrue($this->creditCardPayment->validatePaymentData($validData));

        $invalidCases = [
            ['qtd_installments' => 0],
            ['qtd_installments' => 13],
            ['qtd_installments' => -1]
        ];
        
        foreach ($invalidCases as $case) {
            try {
                $this->creditCardPayment->validatePaymentData($case);
                $this->fail("Validation should have failed for installments: {$case['qtd_installments']}");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('qtd_installments', $e->errors());
            }
        }
    }

   /** @test */
    public function it_rounds_amount_to_two_decimals()
    {
        $data = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 5,
            'card_information' => []
        ];
        $total = 99.99;

        $expectedAmount = round(99.99 * pow(1.05, 5), 2);
        
        $result = $this->creditCardPayment->processPayment($data, $total);
        
        $this->assertEqualsWithDelta(127.63, $result['data']['amount'], 0.01);
    }

    /** @test */
    public function it_handles_minimum_payment_amount()
    {
        $data = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 2,
            'card_information' => []
        ];
        $total = 0.01;

        $result = $this->creditCardPayment->processPayment($data, $total);
        
        $this->assertEquals(0.01, $result['data']['amount']);
    }

    /** @test */
    public function it_returns_correct_response_structure()
    {
        $data = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 1,
            'card_information' => [
                'number' => '5555555555554444',
                'expiry' => '06/26'
            ]
        ];
        $total = 50.00;

        $result = $this->creditCardPayment->processPayment($data, $total);
        
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('payment_method', $result['data']);
        $this->assertArrayHasKey('qtd_installments', $result['data']);
        $this->assertArrayHasKey('card_information', $result['data']);
        $this->assertArrayHasKey('amount', $result['data']);
    }

    /** @test */
    public function it_handles_missing_card_information_gracefully()
    {
        $data = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 1
        ];
        $total = 75.50;

        $result = $this->creditCardPayment->processPayment($data, $total);
        
        $this->assertArrayHasKey('card_information', $result['data']);
        $this->assertEquals([], $result['data']['card_information']);
    }
}