<?php

namespace Tests\Unit\Domain\Payments;

use App\Domain\Payments\Contracts\PaymentMethodInterface;
use App\Domain\Payments\PaymentProcessor;
use Tests\TestCase;
use Mockery;

class PaymentProcessorTest extends TestCase
{
    private $mockPaymentMethod;
    private PaymentProcessor $paymentProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockPaymentMethod = Mockery::mock(PaymentMethodInterface::class);
        $this->paymentProcessor = new PaymentProcessor($this->mockPaymentMethod);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_processes_payment_successfully()
    {
        $requestData = ['payment_method' => 'pix', 'qtd_installments' => 1];
        $total = 100.00;
        $expectedResponse = [
            'status' => 'success',
            'message' => 'Payment processed',
            'amount' => 100.00
        ];

        $this->mockPaymentMethod->shouldReceive('validatePaymentData')
            ->once()
            ->with($requestData);
            
        $this->mockPaymentMethod->shouldReceive('processPayment')
            ->once()
            ->with($requestData, $total)
            ->andReturn($expectedResponse);

        $result = $this->paymentProcessor->process($requestData, $total);

        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_throws_exception_when_payment_processor_not_initialized()
    {
        $this->expectException(\TypeError::class);
        
        // Isso vai falhar porque o construtor requer um PaymentMethodInterface
        new PaymentProcessor(null);
    }

    /** @test */
    public function it_validates_data_before_processing()
    {
        $invalidData = ['payment_method' => 'pix', 'qtd_installments' => 0];
        
        $this->mockPaymentMethod->shouldReceive('validatePaymentData')
            ->once()
            ->with($invalidData)
            ->andThrow(new \InvalidArgumentException('Invalid installments'));

        $this->expectException(\InvalidArgumentException::class);

        $this->paymentProcessor->process($invalidData, 100.00);
    }

    /** @test */
    public function it_allows_changing_payment_method()
    {
        $newPaymentMethod = Mockery::mock(PaymentMethodInterface::class);
        $requestData = ['payment_method' => 'creditcard'];
        $total = 200.00;
        
        $newPaymentMethod->shouldReceive('validatePaymentData')
            ->once()
            ->with($requestData);
            
        $newPaymentMethod->shouldReceive('processPayment')
            ->once()
            ->with($requestData, $total)
            ->andReturn(['status' => 'success']);

        $this->paymentProcessor->setPaymentMethod($newPaymentMethod);
        $result = $this->paymentProcessor->process($requestData, $total);

        $this->assertEquals(['status' => 'success'], $result);
    }

    /** @test */
    public function it_uses_correct_payment_method_for_processing()
    {
        $requestData = ['payment_method' => 'pix'];
        $total = 150.00;
        
        $this->mockPaymentMethod->shouldReceive('validatePaymentData')
            ->once()
            ->with($requestData);
            
        $this->mockPaymentMethod->shouldReceive('processPayment')
            ->once()
            ->with($requestData, $total)
            ->andReturn(['status' => 'processed']);

        $result = $this->paymentProcessor->process($requestData, $total);

        $this->assertEquals(['status' => 'processed'], $result);
    }
}