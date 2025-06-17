<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItems;
use App\Models\Items;
use Illuminate\Http\Request;

class CartItemsController extends Controller
{
    public function __construct(
        private CartItems $cartItems
    ) 
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCartItemRequest $request)
    {
        $item = $this->cartItems->createItem($request->validated());

        return (new CartItemResource($item))
            ->response()
            ->header('Location', route('cart-items.show', $item->id))
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = $this->cartItems->findItem($id);

        return (new CartItemResource($item))
            ->response()
            ->header('Location', route('cart-items.show', $item->id))
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartItemRequest $request, string $id)
    {
        $item = $this->cartItems->updateItem($id, $request->validated());

        return (new CartItemResource($item))
            ->response()
            ->header('Location', route('cart-items.show', $item->id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
