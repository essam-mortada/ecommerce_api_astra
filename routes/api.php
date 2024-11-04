<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);


Route::middleware('auth:sanctum','admin')->group(function(){
    Route::post('/logout',[UserController::class,'logout']);

    //products
    Route::get('/products',[ProductController::class,'index']);
    Route::get('/products/{product}',[ProductController::class,'show']);
    Route::post('/products',[ProductController::class,'store']);
    Route::put('/products/{product}',[ProductController::class,'update']);
    Route::delete('/products/{product}',[ProductController::class,'destroy']);

    //categories
    Route::get('/categories',[CategoryController::class,'index']);
    Route::get('/categories/{category}',[CategoryController::class,'show']);
    Route::post('/categories',[CategoryController::class,'store']);
    Route::put('/categories/{category}',[CategoryController::class,'update']);
    Route::delete('/categories/{category}',[CategoryController::class,'destroy']);



});

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/cart',[CartController::class,'addToCart']);
    Route::get('/cart',[CartController::class,'viewCart']);
    Route::put('/cart/{itemId}',[CartController::class,'updateCartItem']);
    Route::delete('/cart/{itemId}',[CartController::class,'removeCartItem']);

    Route::post('/checkout',[OrderController::class,'checkout']);

});

