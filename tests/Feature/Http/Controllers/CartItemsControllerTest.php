<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @group cart_items
 */
class CartItemsControllerTest extends TestCase
{
    private array $validData = [];

    protected function setUp(): void
    {
        $this->validData = [
            'name' => 'test',
            'price' => 1,
            'quantity' => 1
        ];
        
        parent::setUp();
    }

    public function test_create_item_successfully(): void
    {
        $response = $this->postJson('/api/cart-items', $this->validData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                    'quantity',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_create_returns_correct_location_header(): void
    {
        $response = $this->postJson('/api/cart-items', $this->validData);

        $responseData = $response->json();
        $itemId = $responseData['data']['id'];

        $response->assertHeader('Location', route('cart-items.show', $itemId));
    }
    
     /**
     * @dataProvider requiredFieldsProvider
     */
    public function test_create_validation_requires_fields(string $field, mixed $invalidValue): void
    {
        $invalidData = $this->validData;
        $invalidData[$field] = $invalidValue;

        $response = $this->postJson('/api/cart-items', $invalidData);

        $response->assertStatus(422) // HTTP 422 Unprocessable Entity
            ->assertJsonValidationErrors([$field]);
    }

    public static function requiredFieldsProvider(): array
    {
        return [
            'name is required' => ['name', null],
            'name must be string' => ['name', 123],
            'price is required' => ['price', null],
            'price must be numeric' => ['price', 'texto'],
            'quantity is required' => ['quantity', null],
            'quantity must be integer' => ['quantity', 'texto'],
            'quantity must be positive' => ['quantity', -1],
        ];
    }

    public function test_show_returns_item_successfully(): void
    {
        // Create an item first
        $response = $this->postJson('/api/cart-items', $this->validData);
        $itemId = $response->json('data.id');
    
        $showResponse = $this->getJson('/api/cart-items/' . $itemId);
    
        $showResponse->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                    'quantity',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertHeader('Location', route('cart-items.show', $itemId));
    }
    
    public function test_show_returns_404_for_nonexistent_item(): void
    {
        $nonExistentId = 999999;
        $response = $this->getJson('/api/cart-items/' . $nonExistentId);
    
        $response->assertStatus(404);
    }

    public function test_update_item_successfully(): void
    {
        // Create an item first
        $createResponse = $this->postJson('/api/cart-items', $this->validData);
        $itemId = $createResponse->json('data.id');
    
        $updateData = [
            'name' => 'updated name',
            'price' => 10.5,
            'quantity' => 2
        ];
    
        $response = $this->putJson('/api/cart-items/' . $itemId, $updateData);
    
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $itemId,
                    'name' => 'updated name',
                    'price' => 10.5,
                    'quantity' => 2,
                ]
            ])
            ->assertHeader('Location', route('cart-items.show', $itemId));
    }
    
    public function test_update_returns_404_for_nonexistent_item(): void
    {
        $nonExistentId = 999999;
        $updateData = [
            'name' => 'does not matter',
            'price' => 5,
            'quantity' => 1
        ];
    
        $response = $this->putJson('/api/cart-items/' . $nonExistentId, $updateData);
    
        $response->assertStatus(404);
    }
    
    /**
     * @dataProvider updateRequiredFieldsProvider
     */
    public function test_update_validation_requires_fields(string $field, mixed $invalidValue): void
    {
        // Create an item first
        $createResponse = $this->postJson('/api/cart-items', $this->validData);
        $itemId = $createResponse->json('data.id');

        $updateData = $this->validData;
        $updateData[$field] = $invalidValue;

        $response = $this->putJson('/api/cart-items/' . $itemId, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }

    public static function updateRequiredFieldsProvider(): array
    {
        return [
            'name is required' => ['name', null],
            'name must be string' => ['name', 123],
            'price is required' => ['price', null],
            'price must be numeric' => ['price', 'texto'],
            'quantity is required' => ['quantity', null],
            'quantity must be integer' => ['quantity', 'texto'],
            'quantity must be positive' => ['quantity', -1],
        ];
    }
}


