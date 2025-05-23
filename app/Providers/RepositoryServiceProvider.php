<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Repositories\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Message\MessageRepositoryInterface::class,
            \App\Repositories\Message\MessageRepository::class
        );
        $this->app->bind(
            \App\Repositories\Conversation\ConversationRepositoryInterface::class,
            \App\Repositories\Conversation\ConversationRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
