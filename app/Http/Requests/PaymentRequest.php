<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'required|string|in:pix,credit_card',
            'qtd_installments' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->input('payment_method') === 'pix' && $value !== 1) {
                        $fail('When payment method is PIX, the number of installments must be 1.');
                    }
                },
            ],
            'card_information' => [
                'required_if:payment_method,credit_card',
                'array',
            ],
            'card_information.card_holder_name' => [
                'required_if:payment_method,credit_card',
                'filled',
                'string',
                'max:255',
            ],
            'card_information.card_number' => [
                'required_if:payment_method,credit_card',
                'filled',
                'string',
                'digits:16',
            ],
            'card_information.expiration_date' => [
                'required_if:payment_method,credit_card',
                'filled',
                'string',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            ],
            'card_information.cvv' => [
                'required_if:payment_method,credit_card',
                'filled',
                'string',
                'digits_between:3,4',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.in' => 'The payment method must be either pix or credit_card.',
            'qtd_installments.min' => 'The number of installments must be at least 1.',
        ];
    }
}
