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

    private array $validData;
    private const CART_ITEMS_ENDPOINT = '/api/cart-items';

    protected function setUp(): void
    {
        parent::setUp();

        $this->validData = [
            'name' => 'test',
            'price' => 1,
            'quantity' => 1,
        ];
    }

    private function createItem(array $data = null): int
    {
        $response = $this->postJson(self::CART_ITEMS_ENDPOINT, $data ?? $this->validData);
        return $response->json('data.id');
    }

    public function test_create_item_successfully(): void
    {
        $this->postJson(self::CART_ITEMS_ENDPOINT, $this->validData)
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'price', 'quantity', 'created_at', 'updated_at']
            ]);
    }

    public function test_create_returns_correct_location_header(): void
    {
        $response = $this->postJson(self::CART_ITEMS_ENDPOINT, $this->validData);
        $itemId = $response->json('data.id');

        $response->assertHeader('Location', route('cart-items.show', $itemId));
    }

    /**
     * @dataProvider requiredFieldsProvider
     */
    public function test_create_validation_requires_fields(string $field, mixed $invalidValue): void
    {
        $data = $this->validData;
        $data[$field] = $invalidValue;

        $this->postJson(self::CART_ITEMS_ENDPOINT, $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public static function requiredFieldsProvider(): array
    {
        return [
            ['name', null],
            ['name', 123],
            ['price', null],
            ['price', 'texto'],
            ['quantity', null],
            ['quantity', 'texto'],
            ['quantity', -1],
        ];
    }

    public function test_show_returns_item_successfully(): void
    {
        $itemId = $this->createItem();

        $this->getJson(self::CART_ITEMS_ENDPOINT. "/{$itemId}")
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'price', 'quantity', 'created_at', 'updated_at']
            ])
            ->assertHeader('Location', route('cart-items.show', $itemId));
    }

    public function test_show_returns_404_for_nonexistent_item(): void
    {
        $this->getJson(self::CART_ITEMS_ENDPOINT . '/999999')->assertNotFound();
    }

    public function test_update_item_successfully(): void
    {
        $itemId = $this->createItem();

        $updateData = [
            'name' => 'updated name',
            'price' => 10.5,
            'quantity' => 2,
        ];

        $this->putJson(self::CART_ITEMS_ENDPOINT. "/{$itemId}", $updateData)
            ->assertOk()
            ->assertJson(['data' => ['id' => $itemId] + $updateData])
            ->assertHeader('Location', route('cart-items.show', $itemId));
    }

    public function test_update_returns_404_for_nonexistent_item(): void
    {
        $this->putJson(self::CART_ITEMS_ENDPOINT.'/999999', $this->validData)->assertNotFound();
    }

    /**
     * @dataProvider updateRequiredFieldsProvider
     */
    public function test_update_validation_requires_fields(string $field, mixed $invalidValue): void
    {
        $itemId = $this->createItem();
        $data = $this->validData;
        $data[$field] = $invalidValue;

        $this->putJson(self::CART_ITEMS_ENDPOINT . "/{$itemId}", $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public static function updateRequiredFieldsProvider(): array
    {
        return self::requiredFieldsProvider();
    }

    public function test_index_returns_paginated_items(): void
    {
        foreach ([
            ['name' => 'item1', 'price' => 10, 'quantity' => 1],
            ['name' => 'item2', 'price' => 20, 'quantity' => 2],
            ['name' => 'item3', 'price' => 30, 'quantity' => 3],
        ] as $item) {
            $this->postJson(self::CART_ITEMS_ENDPOINT, $item);
        }

        $this->getJson(self::CART_ITEMS_ENDPOINT. '?per_page=2')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'price', 'quantity', 'created_at', 'updated_at']],
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
        foreach ($items as $item) {
            $this->postJson(self::CART_ITEMS_ENDPOINT, $item);
        }

        $query = http_build_query($filter);
        $response = $this->getJson(self::CART_ITEMS_ENDPOINT. "?{$query}");

        $response->assertOk();

        foreach ($expectedPresent as $present) {
            $response->assertJsonFragment($present);
        }

        foreach ($expectedMissing as $missing) {
            $response->assertJsonMissing($missing);
        }
    }

    public static function filterProvider(): array
    {
        return [
            'filter by name' => [
                'items' => [
                    ['name' => 'Apple', 'price' => 5, 'quantity' => 1],
                    ['name' => 'Banana', 'price' => 3, 'quantity' => 2],
                ],
                'filter' => ['name' => 'Apple'],
                'expectedPresent' => [['name' => 'Apple']],
                'expectedMissing' => [['name' => 'Banana']],
            ],
            'filter by price' => [
                'items' => [
                    ['name' => 'Apple', 'price' => 5, 'quantity' => 1],
                    ['name' => 'Banana', 'price' => 3, 'quantity' => 2],
                ],
                'filter' => ['price' => 5],
                'expectedPresent' => [['name' => 'Apple', 'price' => 5]],
                'expectedMissing' => [['name' => 'Banana', 'price' => 3]],
            ],
            'filter by quantity' => [
                'items' => [
                    ['name' => 'Apple', 'price' => 5, 'quantity' => 1],
                    ['name' => 'Banana', 'price' => 3, 'quantity' => 2],
                ],
                'filter' => ['quantity' => 2],
                'expectedPresent' => [['name' => 'Banana', 'quantity' => 2]],
                'expectedMissing' => [['name' => 'Apple', 'quantity' => 1]],
            ]
        ];
    }

    public function test_destroy_deletes_item_successfully(): void
    {
        $itemId = $this->createItem();

        $this->deleteJson(self::CART_ITEMS_ENDPOINT. "/{$itemId}")->assertNoContent();
        $this->getJson(self::CART_ITEMS_ENDPOINT. "/{$itemId}")->assertNotFound();
    }

    public function test_destroy_returns_404_for_nonexistent_item(): void
    {
        $this->deleteJson(self::CART_ITEMS_ENDPOINT. '/999999')->assertNotFound();
    }
}
