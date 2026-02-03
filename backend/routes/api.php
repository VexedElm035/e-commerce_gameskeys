<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameKeyController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\IGDBController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MessageController;


use App\Http\Controllers\CartController;
use App\Http\Controllers\SellerController; // Assuming we might move seller stats here or keep in User/GameKey

// Public Routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/users', [UserController::class, 'store']); // Register

// IGDB (assuming these are public for browsing game info)
Route::get('/igdb/games', [IGDBController::class, 'getPopularGames']);
Route::get('/igdb/search-games', [IGDBController::class, 'searchGames']);

// Reviews
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

// Public Resources (Index & Show)
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{id}', [GameController::class, 'show']);
Route::get('/gamekeys', [GameKeyController::class, 'index']);
Route::get('/gamekeys/{id}', [GameKeyController::class, 'show']);
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{id}', [GenreController::class, 'show']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Support
    // Support
    Route::apiResource('support', SupportController::class);
    
    // Resources (Protected actions only)
    Route::apiResource('games', GameController::class)->except(['index', 'show']);
    Route::apiResource('gamekeys', GameKeyController::class)->except(['index', 'show']);
    Route::apiResource('genres', GenreController::class)->except(['index', 'show']);
    
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('reviews', ReviewController::class);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('supports', SupportController::class);
    
    // Users (Store is public for registration, handled above)
    Route::apiResource('users', UserController::class)->except(['store', 'create', 'edit']);

    // Dedicated routes for logic previously in closures or bad methods
    // Moving gamekeys-s (index2) filtering logic to standard index in GameKeyController
    
    // Uploads
    Route::get('/uploads', [FileUploadController::class, 'listUploads']);
    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::delete('/uploads/{filename}', [FileUploadController::class, 'deleteFile']);
    Route::get('/uploads/{file}', [FileUploadController::class, 'getFile']); // Might need to be public?

    // IGDB Sync
    Route::post('/igdb/sync-game', [IGDBController::class, 'syncGame']);

    // Admin
    Route::get('/admin/earnings', [AdminController::class, 'getEarnings']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    Route::put('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount']);

    // Cart
    Route::apiResource('cart', CartController::class)->only(['index', 'store', 'destroy']);
    Route::get('cart/count', [CartController::class, 'count']);
    Route::delete('cart/clear', [CartController::class, 'clear']);

    // Seller Stats (Refactoring the closure route)
    // Assuming we can move this to a controller, e.g., SellerController or UserController
    Route::get('/sellers/{seller}/stats', [UserController::class, 'sellerStats']);
});
