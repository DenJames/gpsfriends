<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\MessageController;
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

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::post('/location/store', [LocationController::class, 'store'])->name('location.store');
    Route::get('/fetch/users', [LocationController::class, 'fetchUsers'])->name('fetch.users');

    Route::post('/message/send', [MessageController::class, 'store'])->name('message.store');
    Route::post('/messages/fetch', [MessageController::class, 'fetch'])->name('messages.fetch');
});



require __DIR__.'/auth.php';
