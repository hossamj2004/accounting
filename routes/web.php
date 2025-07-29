<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes from Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // App routes
    Route::resource('accounts', AccountController::class)->only(['store', 'show']);
    Route::resource('transactions', TransactionController::class)->only(['store']);

    // Client Sharing Routes
    Route::post('/client-share/generate', [ClientShareController::class, 'generate'])->name('client.share.generate');
});

// Public client routes
Route::get('/client/transaction/{account}', [ClientShareController::class, 'create'])
    ->middleware('signed')
    ->name('client.transaction.create');
Route::post('/client/transaction/{account}', [ClientShareController::class, 'store'])
    ->middleware('signed')
    ->name('client.transaction.store');
Route::get('/client/success', [ClientShareController::class, 'success'])->name('client.transaction.success');


require __DIR__.'/auth.php';
