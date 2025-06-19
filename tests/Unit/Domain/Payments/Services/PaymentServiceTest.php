<?php

namespace Tests\Unit\Domain\Payments\Services;

use App\Domain\Payments\Services\PaymentService;
use App\Models\CartItems;
use Tests\TestCase;
use Mockery;

/**
 * @group payment_service
 */
class PaymentServiceTest extends TestCase
{

    private PaymentService $paymentService;
    private $mockCartItems;
    private $mockProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock do CartItems
        $this->mockCartItems = Mockery::mock(CartItems::class);

        // Instância do serviço com o mock
        $this->paymentService = new PaymentService($this->mockCartItems);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_when_payment_method_is_missing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment method is required.');

        $this->paymentService->sendMethodPayment([]);
    }

    /** @test */
    public function it_processes_pix_payment_successfully()
    {
        // Configuração do mock
        $total = 100.50;
        $request = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];

        $expectedResponse = [
            'status' => 'success',
            'message' => 'Pix payment processed successfully',
            'data' => [
                'payment_method' => 'pix',
                'qtd_installments' => 1,
                'amount' => 90.45 // Com 10% de desconto
            ]
        ];

        // Mock do CartItems
        $this->mockCartItems->shouldReceive('getTotalPrice')
            ->once()
            ->andReturn($total);

        // Mock do processor via container
        $this->mockProcessor = Mockery::mock('payment_processor');
        $this->mockProcessor->shouldReceive('process')
            ->with($request, $total)
            ->andReturn($expectedResponse);

        app()->instance('payment.processor', $this->mockProcessor);

        // Execução
        $response = $this->paymentService->sendMethodPayment($request);

        // Asserts
        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function it_processes_credit_card_payment_successfully()
    {
        // Configuração
        $total = 200.00;
        $request = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 3,
            'card_information' => [
                'number' => '4111111111111111',
                'expiry' => '12/25'
            ]
        ];

        $expectedResponse = [
            'status' => 'success',
            'message' => 'Credit card payment processed successfully',
            'data' => [
                'payment_method' => 'creditcard',
                'qtd_installments' => 3,
                'card_information' => $request['card_information'],
                'amount' => 218.55
            ]
        ];

        // Mock do CartItems
        $this->mockCartItems->shouldReceive('getTotalPrice')
            ->once()
            ->andReturn($total);

        // Mock do processor
        $this->mockProcessor = Mockery::mock('payment_processor');
        $this->mockProcessor->shouldReceive('process')
            ->with($request, $total)
            ->andReturn($expectedResponse);

        app()->instance('payment.processor', $this->mockProcessor);

        // Execução e asserts
        $response = $this->paymentService->sendMethodPayment($request);
        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function it_propagates_exceptions_from_payment_processor()
    {
        // Configuração
        $request = [
            'payment_method' => 'creditcard',
            'qtd_installments' => 15 // Inválido
        ];

        $exception = new \Illuminate\Validation\ValidationException(
            validator()->make([], []),
            response()->json(['error' => 'Invalid installments'])
        );

        // Mock do CartItems
        $this->mockCartItems->shouldReceive('getTotalPrice')
            ->once()
            ->andReturn(100.00);

        // Mock do processor para lançar exceção
        $this->mockProcessor = Mockery::mock('payment_processor');
        $this->mockProcessor->shouldReceive('process')
            ->andThrow($exception);

        app()->instance('payment.processor', $this->mockProcessor);

        // Verificação
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->paymentService->sendMethodPayment($request);
    }

    /** @test */
    public function it_calculates_total_price_before_processing_payment()
    {
        // Configuração
        $request = [
            'payment_method' => 'pix',
            'qtd_installments' => 1
        ];

        $total = 150.75;
        $expectedProcessedAmount = 135.68; // Com 10% de desconto

        $this->mockCartItems->shouldReceive('getTotalPrice')
            ->once()
            ->andReturn($total);

        $this->mockProcessor = Mockery::mock('payment_processor');
        $this->mockProcessor->shouldReceive('process')
            ->withArgs(function ($requestData, $receivedTotal) use ($total) {
                $this->assertEquals($total, $receivedTotal);
                return true;
            })
            ->andReturn([
                'status' => 'success',
                'data' => ['amount' => $expectedProcessedAmount]
            ]);

        app()->instance('payment.processor', $this->mockProcessor);

        $response = $this->paymentService->sendMethodPayment($request);

        $this->assertEquals($expectedProcessedAmount, $response['data']['amount']);
    }
}
