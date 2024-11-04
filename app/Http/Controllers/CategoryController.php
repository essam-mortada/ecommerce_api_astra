<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(){

        $categories = Category::all()->map(function ($category) {
            $category->image = url('categories_uploads/' . $category->image);
            return $category;
        });
        return response()->json([
            'status'=>200,
            'message'=>'Categories retrieved successfully',
            'categories' => $categories
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'title'=>'required|string',
            'image'=>'image|mimes:jpeg,png,jpg,svg|max:2048',
            ]);

            $category = new Category();
            $category->title =strip_tags($request->title);
            if ($request->hasFile('image')) {
                $imageName = time().rand(0,500).'.'.$request->image->extension();
                $request->image->move(public_path('categories_uploads'), $imageName);
                $category->image = $imageName;
            }else{
                $category->image = 'default.png';
            }
            $category->save();

            return response()->json([
                'status'=>200,
                'message'=>'Category created successfully',
                'category' => $category
                ]);
    }

    public function show($category){
        $category = Category::find($category);
            if (!$category) {
               return response()->json([
                'status'=>404,
                'message'=>'Category not found',
               ]);
            }
            $products = $category->products->map(function ($product) {
                $product->image = url('products_uploads/' . $product->image);
                return $product;
            });
            $category->image = url('categories_uploads/' . $category->image);

            return response()->json([
                'status'=>200,
                'message'=>'Category retrieved successfully',
                'category' => $category,
                ]);

    }

    public function update(Request $request, $category)
    {
        $category = Category::find($category);
        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }

        $request->validate([
            'title' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Sanitize and update the title
        $category->title = strip_tags($request->title);

        // Check if there's a new image file
        if ($request->hasFile('image')) {
            // Only try to delete the old image if it's not the default
            if ($category->image != 'default.png') {
                $oldImagePath = public_path('categories_uploads/' . $category->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Store the new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('categories_uploads'), $imageName);
            $category->image = $imageName; // Update the image field
        }

        $category->save();

        // Return the updated category data, including the image URL
        $category->image = url('categories_uploads/' . $category->image);

        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

        public function destroy($category)
        {
            $category = Category::find($category);
            if (!$category) {
                return response()->json([
                 'status'=>404,
                 'message'=>'Category not found',
                ]);
             }

            if ($category->image != 'default.png') {
                unlink(public_path('categories_uploads/'.$category->image));

            }
            $category->delete();
            return response()->json([
                'status'=>200,
                'message' => 'Category deleted successfully',
            ]);
        }


}
