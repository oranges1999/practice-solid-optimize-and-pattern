<?php

use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('users',[UserController::class, 'index'])->name('users.index');
    Route::get('users/edit', [UserController::class, 'edit'])->name('users.edit');
});

Route::prefix('api')->as('api.')->middleware('auth')->group(function() {
    Route::get('/users', [ApiUserController::class, 'index'])->name('users.index');
    Route::post('/users/update', [ApiUserController::class, 'massUpdate'])->name('users.mass-update');
});

require __DIR__.'/auth.php';
