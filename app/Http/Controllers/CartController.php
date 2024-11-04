<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|integer|exists:products,id',
        'quantity' => 'required|integer|min:1'
    ]);

    $product = Product::findOrFail($request->product_id);

    // Check if cart exists for the user or create a new one
    $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

    // Check if item already exists in the cart
    $cartItem = $cart->items()->where('product_id', $product->id)->first();

    if ($cartItem) {
        // Update quantity if the item already exists
        $cartItem->quantity += $request->quantity;
        $cartItem->save();
    } else {
        // Create a new cart item
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $request->quantity,
        ]);
    }

    return response()->json([
        'status' => 200,
        'message' => 'Product added to cart',
        'cart' => $cart->load('items.product')
    ]);
}

public function viewCart()
{
    $cart = Cart::with('items.product')->where('user_id', auth()->id())->first();

    return response()->json([
        'status' => 200,
        'cart' => $cart,
    ]);
}

public function updateCartItem(Request $request, $itemId)
{
    $request->validate([
        'quantity' => 'required|integer|min:1'
    ]);

    $cartItem = CartItem::find($itemId);
    if (!$cartItem) {
        return response()->json([
            'status' => 404,
            'message' => 'Product not found',
        ]);
    }
    $cartItem->quantity = $request->quantity;
    $cartItem->save();

    return response()->json([
        'status' => 200,
        'message' => 'Cart item updated',
        'cartItem' => $cartItem,
    ]);
}

public function removeCartItem($itemId)
{
    $cartItem = CartItem::find($itemId);
    if (!$cartItem) {
        return response()->json([
            'status' => 404,
            'message' => 'Product not found',
        ]);
    }
    $cartItem->delete();

    return response()->json([
        'status' => 200,
        'message' => 'Cart item removed',
    ]);
}


}
