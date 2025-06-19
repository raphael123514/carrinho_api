<?php

namespace App\Http\Controllers;

use App\Domain\Payments\Services\PaymentService;
use App\Http\Requests\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function process(PaymentRequest $request)
    {
        try {
            $response = $this->paymentService->sendMethodPayment($request->validated());
            
            return response()->json([
                'message' => $response['message'],
                'data' => $response['data'],
            ], 200);
        } catch (\Exception $th) {
            Log::error('Payment processing failed', [
                'error' => $th->getMessage(),
                'request' => $request->validated(),
            ]);


            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $th->getMessage(),
            ], 422);
        }
    }
    
}
