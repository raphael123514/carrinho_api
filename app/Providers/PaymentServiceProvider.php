<?php

namespace App\Providers;

use App\Domain\Payments\Types\CreditCardPayment;
use App\Domain\Payments\Types\PixPayment;
use App\Domain\Payments\PaymentProcessor;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar as implementações de pagamento
        $this->app->bind('payment.pix', function() {
            return new PixPayment();
        });

        $this->app->bind('payment.creditcard', function() {
            return new CreditCardPayment();
        });

        // Registrar o processador com injeção de dependência
        $this->app->bind('payment.processor', function($app, $params) {
            $method = $params['method'] ?? 'creditcard';
            $paymentMethod = $app->make("payment.{$method}");
            return new PaymentProcessor($paymentMethod);
        });

        $this->app->bind(
            \App\Domain\Payments\Contracts\PaymentMethodInterface::class,
            \App\Domain\Payments\Types\CreditCardPayment::class // Default
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
