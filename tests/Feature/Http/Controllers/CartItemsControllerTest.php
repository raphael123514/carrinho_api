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
    use RefreshDatabase, WithFaker;

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
    
    public function test_index_returns_paginated_items(): void
    {
        // Create multiple items
        $items = [
            ['name' => 'item1', 'price' => 10, 'quantity' => 1],
            ['name' => 'item2', 'price' => 20, 'quantity' => 2],
            ['name' => 'item3', 'price' => 30, 'quantity' => 3],
        ];
    
        foreach ($items as $item) {
            $this->postJson('/api/cart-items', $item);
        }
    
        $response = $this->getJson('/api/cart-items?per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'quantity',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2);
    }
    
    /**
     * @dataProvider filterProvider
     */
    public function test_index_applies_filters(
        array $items,
        array $filter,
        array $expectedPresent,
        array $expectedMissing
    ): void {
        // Create test items
        foreach ($items as $item) {
            $this->postJson('/api/cart-items', $item);
        }
    
        // Apply filter
        $queryString = http_build_query($filter);
        $response = $this->getJson("/api/cart-items?{$queryString}");
    
        // Assert response
        $response->assertStatus(200);
        
        // Assert items that should be present
        foreach ($expectedPresent as $present) {
            $response->assertJsonFragment($present);
        }
        
        // Assert items that should be missing
        foreach ($expectedMissing as $missing) {
            $response->assertJsonMissing($missing);
        }
    }
    
    public static function filterProvider(): array
    {
        return [
            'filter by name' => [
                'items' => [
                    [
                        'name' => 'Apple',
                        'price' => 5,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Banana',
                        'price' => 3,
                        'quantity' => 2
                    ]
                ],
                'filter' => ['name' => 'Apple'],
                'expectedPresent' => [['name' => 'Apple']],
                'expectedMissing' => [['name' => 'Banana']]
            ],
            'filter by price' => [
                'items' => [
                    [
                        'name' => 'Apple',
                        'price' => 5,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Banana',
                        'price' => 3,
                        'quantity' => 2
                    ]
                ],
                'filter' => ['price' => 5],
                'expectedPresent' => [['name' => 'Apple', 'price' => 5]],
                'expectedMissing' => [['name' => 'Banana', 'price' => 3]]
            ],
            'filter by quantity' => [
                'items' => [
                    [
                        'name' => 'Apple',
                        'price' => 5,
                        'quantity' => 1
                    ],
                    [
                        'name' => 'Banana',
                        'price' => 3,
                        'quantity' => 2
                    ]
                ],
                'filter' => ['quantity' => 2],
                'expectedPresent' => [['name' => 'Banana', 'quantity' => 2]],
                'expectedMissing' => [['name' => 'Apple', 'quantity' => 1]]
            ]
        ];
    }
    
    public function test_destroy_deletes_item_successfully(): void
    {
        // Create an item first
        $createResponse = $this->postJson('/api/cart-items', $this->validData);
        $itemId = $createResponse->json('data.id');
    
        // Delete the item
        $deleteResponse = $this->deleteJson('/api/cart-items/' . $itemId);
    
        $deleteResponse->assertNoContent();
    
        // Ensure the item no longer exists
        $this->getJson('/api/cart-items/' . $itemId)
            ->assertStatus(404);
    }
    
    public function test_destroy_returns_404_for_nonexistent_item(): void
    {
        $nonExistentId = 999999;
        $response = $this->deleteJson('/api/cart-items/' . $nonExistentId);
    
        $response->assertStatus(404);
    }
}




