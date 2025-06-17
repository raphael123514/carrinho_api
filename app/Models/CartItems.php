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
}
