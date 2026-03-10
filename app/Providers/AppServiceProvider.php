<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    // Share data global ke semua view (misal user login, setting app, dll)
        View::composer('*', function ($view) {
            $view->with([
                'app_name' => config('app.name'),
                'current_user' => auth()->user(), // kalau pakai auth
                'tahun_berjalan' => date('Y'),
                // tambah data lain yang kamu butuhkan di semua blade
            ]);
        });
    }
}
