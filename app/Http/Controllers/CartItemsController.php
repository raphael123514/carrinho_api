<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCartItemRequest;
use App\Http\Requests\CreateCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartItemCollection;
use App\Http\Resources\CartItemResource;
use App\Models\CartItems;

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
    public function index(IndexCartItemRequest $request)
    {
        $filters = $request->validated();
        $perPage = (int) $request->input('per_page', 15);
        $page = $request->input('page') ? (int) $request->input('page') : null;
        
        $items = $this->cartItems->getAllItems(
            filters: array_filter($filters, fn($value) => $value !== null),
            perPage: $perPage,
            page: $page
        );
        
        return new CartItemCollection($items);
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
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        $this->cartItems->deleteItem($id);
        
        return response()->noContent();
    }
}
