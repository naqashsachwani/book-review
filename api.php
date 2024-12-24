<?php
use App\Http\Controllers\Api\ReviewApiController;
use App\Http\Controllers\Api\HomeApiController;


Route::prefix('reviews')->group(function() {
    Route::get('/', [ReviewApiController::class, 'index']); // Get all reviews with optional search
    Route::get('{id}', [ReviewApiController::class, 'show']); // Get a single review
    Route::put('{id}', [ReviewApiController::class, 'update']); // Update a review
    Route::delete('{id}', [ReviewApiController::class, 'destroy']); // Delete a review
});

Route::prefix('home')->group(function() {
    Route::get('books', [HomeApiController::class, 'index']); // Get list of books
    Route::get('books/{id}', [HomeApiController::class, 'detail']); // Get book details
    Route::post('books/review', [HomeApiController::class, 'saveReview']); // Save a review for a book
});
