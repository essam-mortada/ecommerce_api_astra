<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index()
    {

        $products = product::with('category')->get()->map(function ($product) {
            $product->image = url('products_uploads/' . $product->image);
          
            return $product;
        });


        return response()->json([
            'status' => 200,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }



    public function store(Request $request)
    {
        //
        $request->validate([
            'title' => 'required|string',
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'description' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,svg|max:2048',
            'category_id' => 'required|integer|exists:categories,id',
            'quantity' => 'required|integer|min:0'
        ]);


        $product = new product();
        $product->title = strip_tags($request->title);
        $product->price = strip_tags($request->price);
        $product->description = strip_tags($request->description);
        $product->category_id = strip_tags($request->category_id);
        $product->quantity = strip_tags($request->quantity);
        if ($request->hasFile('image')) {
            $imageName = time() . rand(0, 500) . '.' . $request->image->extension();
            $request->image->move(public_path('products_uploads'), $imageName);
            $product->image = $imageName;
        } else {
            $product->image = 'default.png';
        }
        $product->save();

        $product->image = url('products_uploads/' . $product->image);
        $product->load('category');

        if ($product->category && $product->category->image) {
            $product->category->image = url('categories_uploads/' . $product->category->image);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product created successfully',
            'data' => $product
        ]);
    }


    public function show($product)
    {
        $product = Product::find($product);
        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found',
            ]);
        }

        $product->image = url('products_uploads/' . $product->image);

        $product->load('category');

        if ($product->category && $product->category->image) {
            $product->category->image = url('categories_uploads/' . $product->category->image);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product found',
            'data' => $product


        ]);
    }


    public function update(Request $request, $product)
    {
        $product = Product::find($product);
        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found',
            ]);
        }

        $request->validate([
            'title' => 'required|string',
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'description' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,svg|max:2048',
            'category_id' => 'required|integer|exists:categories,id',
            'quantity' => 'required|integer|min:0'
        ]);
        if ($request->hasFile('image')) {
            if ($product->image != 'default.png') {
                unlink(public_path('products_uploads/' . $product->image));
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('products_uploads'), $imageName);
            $product->image = $imageName;
        }

        $product->title = strip_tags($request->title);
        $product->price = strip_tags($request->price);
        $product->description = strip_tags($request->description);
        $product->category_id = strip_tags($request->category_id);
        $product->quantity = strip_tags($request->quantity);
        $product->save();


        $product->image = url('products_uploads/' . $product->image);
        $product->load('category');
        if ($product->category && $product->category->image) {
            $product->category->image = url('categories_uploads/' . $product->category->image);
        }

        return response()->json([
            'status' => 200,
            'message' => 'product updated successfully',
            'product' => $product,

        ]);
    }

    public function destroy($product)
    {
        $product = Product::find($product);
        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found',
            ]);
        }

        if ($product->image != 'default.png') {
            unlink(public_path('products_uploads/' . $product->image));
        }
        $product->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Product deleted successfully',
        ]);
    }
}
