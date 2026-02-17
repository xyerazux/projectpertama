<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // PENTING: Wajib di-import agar tidak error

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
        Schema::defaultStringLength(191);

        if (config('app.env') === 'production' || config('app.env') === 'staging') {
            URL::forceScheme('https');
        }


        View::composer('*', function ($view) {
            if (auth()->check()) {
                $categories = Category::withCount(['tasks' => function($query) {
                    $query->where('status', 'pending'); 
                }])
                ->where('user_id', auth()->id())
                ->get();

                $view->with('categories', $categories);
            } else {
                
                $view->with('categories', collect());
            }
        }); 
    }
}