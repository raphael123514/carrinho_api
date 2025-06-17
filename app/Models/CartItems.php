<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItems extends Model
{
    use HasFactory;
    protected $table = 'cart_items';

    protected $fillable = [
        'name',
        'price',
        'quantity'
    ];

    /**
     * Find a cart item by ID or throw an exception
     *
     * @param string $id
     * @return \App\Models\CartItems
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findItem(string $id): self
    {
        return static::findOrFail($id);
    }

    /**
     * Create a new cart item
     *
     * @param array $validatedData
     * @return \App\Models\CartItems
     */
    public function createItem(array $validatedData): self
    {
        return static::create($validatedData);
    }

    /**
     * Update a cart item
     *
     * @param string $id
     * @param array $validatedData
     * @return \App\Models\CartItems
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateItem(string $id, array $validatedData): self
    {
        $item = $this->findItem($id);
        $item->update($validatedData);
        return $item->fresh();
    }

    /**
     * Delete a cart item
     *
     * @param string $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteItem(string $id): bool
    {
        $item = $this->findItem($id);
        return $item->delete();
    }

    /**
     * Get all cart items with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    /**
     * Apply filters to the query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilters($query, array $filters)
    {
        $filterableFields = [
            'name' => fn($value) => ['like', "%{$value}%"],
            'price' => fn($value) => ['=', $value],
            'quantity' => fn($value) => ['=', $value],
            'created_at' => fn($value) => ['>=', $value],
        ];

        foreach ($filters as $field => $value) {
            if (isset($filterableFields[$field])) {
                [$operator, $filterValue] = $filterableFields[$field]($value);
                $query->where($field, $operator, $filterValue);
            }
        }

        return $query;
    }

    /**
     * Get all cart items with filters
     *
     * @param array $filters
     * @param int $perPage
     * @param int|null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllItems(array $filters = [], int $perPage = 15, ?int $page = null)
    {
        $query = static::query();
        return $this->applyFilters($query, $filters)->paginate(
            $perPage,
            ['*'],
            'page',
            $page
        );
    }
}
