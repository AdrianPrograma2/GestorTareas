<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inicia la sesión PHP nativa (usada en lugar del sistema de sesiones de Laravel)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
