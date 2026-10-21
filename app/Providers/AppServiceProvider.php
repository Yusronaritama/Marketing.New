<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider; // <-- ini WAJIB ADA
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kirim data user ke semua view yang ada di folder layouts/admin/*
        View::composer('layouts.admin.*', function ($view) {
            $view->with('user', Auth::user());
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
