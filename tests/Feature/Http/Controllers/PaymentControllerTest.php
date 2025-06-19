<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Payments\Services\PaymentService;
use App\Models\CartItems;
use Tests\TestCase;

/**
 * @group payment
 */
class PaymentControllerTest extends TestCase
{

    private const PAYMENT_ENDPOINT = '/api/payment';
    private array $validData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validData = [
            'payment_method' => 'pix',
            'qtd_installments' => 1,
            'card_information' => [
                'card_holder_name' => 'Test User',
                'card_number' => '4111111111111111',
                'expiration_date' => '12/25',
                'cvv' => '123',
            ],
        ];

    }

    public function test_process_payment_successfully(): void
    {
        $cartItems = CartItems::factory(5)->create();
        $total = $cartItems->sum('price');
        
        $mockResponse = [
            'message' => 'Pix payment processed successfully',
            'data' => [
                'payment_method' => 'pix',
                'qtd_installments' => 1,
                'amount' => $total * 0.10, // Assuming a 10% discount for Pix payments
            ]
        ];
        
        $this->mock(PaymentService::class, function ($mock) use ($mockResponse) {
            $mock->shouldReceive('sendMethodPayment')
                ->once()
                ->andReturn($mockResponse);
        });

        $response = $this->postJson(self::PAYMENT_ENDPOINT, $this->validData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'payment_method',
                    'qtd_installments',
                    'amount'
                ]
            ])
            ->assertJsonFragment([
                'message' => 'Pix payment processed successfully',
                'payment_method' => 'pix'
            ]);
    }

    /**
     * @dataProvider requiredFieldsProvider
     */
    public function test_create_validation_requires_fields(string $field, mixed $invalidValue): void
    {
        $data = $this->validData;
        $data[$field] = $invalidValue;

        $this->postJson(self::PAYMENT_ENDPOINT, $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public static function requiredFieldsProvider(): array
    {
        return [
            ['payment_method', null],
            ['payment_method', 123],
            ['payment_method', 'teste'],
            ['qtd_installments', null],
            ['qtd_installments', 'texto'],
            ['qtd_installments', -1],
            ['qtd_installments', 0],
            ['qtd_installments', 1.5],
            ['card_information', null],
            ['card_information', ''],
            ['card_information', 123],
        ];
    }

    public function test_pix_requires_single_installment(): void
    {
        $data = $this->validData;
        $data['payment_method'] = 'pix';
        $data['qtd_installments'] = 2;

        $this->postJson(self::PAYMENT_ENDPOINT, $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('qtd_installments')
            ->assertJsonPath('errors.qtd_installments.0', 'When payment method is PIX, the number of installments must be 1.');
    }

    public function test_process_payment_does_not_return_card_information(): void
    {
        CartItems::factory(2)->create();
        $response = $this->postJson(self::PAYMENT_ENDPOINT, $this->validData);
    
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('card_information', $response->json('data'));
    }

    public function test_payment_processing_fails(): void
    {
        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('sendMethodPayment')
                ->once()
                ->andThrow(new \Exception('Gateway timeout'));
        });

        $response = $this->postJson(self::PAYMENT_ENDPOINT, $this->validData);
        
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Payment processing failed',
                'error' => 'Gateway timeout'
            ]);
    }

    /**
     * @dataProvider invalidCreditCardProvider
     */
    public function test_credit_card_requires_card_information_fields(array $override, string $expectedField): void
    {
        $data = array_replace_recursive($this->validData, $override);
        $data['payment_method'] = 'credit_card';
        $data['qtd_installments'] = 2;
        $response = $this->postJson(self::PAYMENT_ENDPOINT, $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($expectedField);
    }

    public static function invalidCreditCardProvider(): array
    {
        return [
            'missing card_information' => [
                ['card_information' => null], 'card_information'
            ],
            'missing card_holder_name' => [
                ['card_information' => [
                    'card_holder_name' => null,
                    'card_number' => '4111111111111111',
                    'expiration_date' => '12/25',
                    'cvv' => '123',
                ]], 'card_information.card_holder_name'
            ],
            'missing card_number' => [
                ['card_information' => [
                    'card_holder_name' => 'Test User',
                    'card_number' => null,
                    'expiration_date' => '12/25',
                    'cvv' => '123',
                ]], 'card_information.card_number'
            ],
            'missing expiration_date' => [
                ['card_information' => [
                    'card_holder_name' => 'Test User',
                    'card_number' => '4111111111111111',
                    'expiration_date' => null,
                    'cvv' => '123',
                ]], 'card_information.expiration_date'
            ],
            'missing cvv' => [
                ['card_information' => [
                    'card_holder_name' => 'Test User',
                    'card_number' => '4111111111111111',
                    'expiration_date' => '12/25',
                    'cvv' => null,
                ]], 'card_information.cvv'
            ],
            'invalid card_number length' => [
                ['card_information' => [
                    'card_holder_name' => 'Test User',
                    'card_number' => '123',
                    'expiration_date' => '12/25',
                    'cvv' => '123',
                ]], 'card_information.card_number'
            ],
            'invalid expiration_date format' => [
                ['card_information' => [
                    'card_holder_name' => 'Test User',
                    'card_number' => '4111111111111111',
                    'expiration_date' => '2025-12',
                    'cvv' => '123',
                ]], 'card_information.expiration_date'
            ],
            'invalid cvv length' => [
                ['card_information' => [
                    'card_holder_name' => 'Test User',
                    'card_number' => '4111111111111111',
                    'expiration_date' => '12/25',
                    'cvv' => '1',
                ]], 'card_information.cvv'
            ],
        ];
    }
}

