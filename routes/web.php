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
    Route::get('users/{user}', [UserController::class, 'specificEdit'])->name('users.show');
    Route::get('user/create', [UserController::class, 'create'])->name('users.create');
});

Route::prefix('api')->as('api.')->middleware('auth')->group(function() {
    Route::get('/users', [ApiUserController::class, 'index'])->name('users.index');
    Route::patch('/users/update', [ApiUserController::class, 'massUpdate'])->name('users.mass-update');
    Route::delete('/users/delete', [ApiUserController::class, 'massDelete'])->name('users.mass-delete');
    Route::get('/users/{user}', [ApiUserController::class, 'getUserData'])->name('users.show');
    Route::put('/users/{user}/update', [ApiUserController::class, 'updateSpecificUser'])->name('users.update-specific');
    Route::delete('/users/{user}/delete', [ApiUserController::class, 'deleteSpecificUser'])->name('users.delete-specific');
    Route::post('/users/create', [ApiUserController::class, 'createUser'])->name('users.create-user');
});

require __DIR__.'/auth.php';
