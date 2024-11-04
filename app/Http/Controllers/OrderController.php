<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function checkout()
    {
        $cart = Cart::with('items.product')->where('user_id', auth()->id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cart is empty',
            ], 400);
        }

        $order = DB::transaction(function () use ($cart) {
            $totalPrice = 0;

            $order = Order::create([
                'user_id' => auth()->id(),
                'total_price' => 0,
                'status' => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$product->title}");
                }

                $product->quantity -= $item->quantity;
                $product->save();

                $itemTotal = $product->price * $item->quantity;
                $totalPrice += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $itemTotal,
                ]);
            }

            $order->total_price = $totalPrice;
            $order->save();

            $cart->items()->delete();
            $cart->delete();

            return $order;
        });

        return response()->json([
            'status' => 200,
            'message' => 'Order placed successfully',
            'order' => $order->load('orderItems.product'),
        ]);
    }

}
