<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Api\ChatController as ApiChatController;
use App\Http\Controllers\Api\SendNotiWhenLeaveController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get(
    '/',
    function () {
        return Inertia::render(
            'Welcome',
            [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            ]
        );
    }
);

Route::get('/delete-all-conversation', function() {
    $conversations = Conversation::all();
    foreach ($conversations as $key => $conversation) {
        $conversation->users()->detach();
        $conversation->delete();
    }
    $messages = Message::all();
    foreach ($messages as $key => $message) {
        $message->delete();
    }
});

Route::get('/dashboard', [ChatController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('/profile')
        ->as('profile.')
        ->controller(ProfileController::class)
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

    Route::prefix('/users')
        ->as('users.')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'edit')->name('edit');
            Route::get('/create', 'create')->name('create');
            Route::get('/import', 'import')->name('import');
            Route::get('/download-sample', 'downloadSampleFile')->name('download-sample');
            Route::get('/{user}', 'specificEdit')->name('show');
        });
});

Route::prefix('api')->as('api.')->middleware('auth')->group(function () {
    Route::post('/users/leave', [SendNotiWhenLeaveController::class, 'sendNotification'])->name('users.leave');

    Route::prefix('/users')
        ->as('users.')
        ->controller(ApiUserController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/update', 'massUpdate')->name('mass-update');
            Route::delete('/delete', 'massDelete')->name('mass-delete');
            Route::post('/create', 'createUser')->name('create-user');
            Route::post('/load-user', 'loadingUserFromFile')->name('load-user');
            Route::post('/import-user', 'importData')->name('import-user');
            Route::post('/export-users', 'exportUsers')->name('export-users');
            Route::get('/{user}', 'getUserData')->name('show');
            Route::put('/{user}/update', 'updateSpecificUser')->name('update-specific');
            Route::delete('/{user}/delete', 'deleteSpecificUser')->name('delete-specific');
        });

    Route::prefix('/chats')
    ->as('chats.')
        ->controller(ApiChatController::class)
        ->group(function () {
            Route::post('/message-store', 'storeMessage')->name('message.store');
            Route::get('message-get/{conversation}', 'getConversation')->name('message.get');
            Route::get('mark-as-read/{conversation}', 'markAsRead')->name('message.mark-as-read');
        });
});

require __DIR__ . '/auth.php';
