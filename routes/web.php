<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', [\App\Http\Controllers\HomeController::class, 'index'])->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::prefix('safe')->group(function() {
        Route::get('create/{type}', [\App\Http\Controllers\SafeController::class, 'create'])->name('safe.create');
        Route::post('store', [\App\Http\Controllers\SafeController::class, 'store'])->name('safe.store');
        Route::get('exchange/create/{type}', [\App\Http\Controllers\SafeController::class, 'createExchange'])
            ->name('safe.exchange.create');
        Route::post('exchange/store', [\App\Http\Controllers\SafeController::class, 'storeExchange'])
            ->name('safe.exchange.store');
        Route::get('transfer/create/{type}', [\App\Http\Controllers\SafeController::class, 'createTransfer'])
            ->name('safe.transfer.create');
        Route::post('transfer/store', [\App\Http\Controllers\SafeController::class, 'storeTransfer'])
            ->name('safe.transfer.store');
        Route::get('{safe}/edit', [\App\Http\Controllers\SafeController::class, 'edit'])->name('safe.edit');
        Route::match(['put','patch'],'{safe}', [\App\Http\Controllers\SafeController::class, 'update'])->name('safe.update');
        Route::delete('{safe}', [\App\Http\Controllers\SafeController::class, 'destroy'])->name('safe.destroy');

    });
    Route::prefix('ajax')->group(function() {
        Route::get('customer', [\App\Http\Controllers\AjaxController::class, 'customer'])->name('ajax.customer');

    });
    Route::get('/222', [\App\Http\Controllers\HomeController::class, 'index'])->name('222');

    Route::get('/333', [\App\Http\Controllers\HomeController::class, 'index'])->name('333');

    Route::resource('currency', \App\Http\Controllers\CurrencyController::class);
    Route::resource('wallet', \App\Http\Controllers\WalletController::class);
    Route::resource('customer', \App\Http\Controllers\CustomerController::class);
    Route::resource('user', \App\Http\Controllers\UserController::class);
});
