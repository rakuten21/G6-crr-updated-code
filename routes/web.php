<?php

    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Artisan;
    use App\Http\Controllers\ProductReviewController;

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */

// Product Review
    Route::post('product/{slug}/review', [ProductReviewController::class, 'store'])->name('review.store');
    
// Backend section start
        // Review
        Route::get('review', [ProductReviewController::class, 'index'])->name('review.index');
        Route::get('review/edit/{id}', [ProductReviewController::class, 'edit'])->name('review.edit');
        Route::delete('review/destroy/{id}', [ProductReviewController::class, 'destroy'])->name('review.destroy');
        Route::patch('/review/update/{id}', [HomeController::class, 'productReviewUpdate'])->name('review.update');

        // Product Review
        Route::get('/user-review', [HomeController::class, 'productReviewIndex'])->name('user.productreview.index');
        Route::delete('/user-review/delete/{id}', [HomeController::class, 'productReviewDelete'])->name('user.productreview.delete');
        Route::get('/user-review/edit/{id}', [HomeController::class, 'productReviewEdit'])->name('user.productreview.edit');
        Route::patch('/user-review/update/{id}', [HomeController::class, 'productReviewUpdate'])->name('user.productreview.update');