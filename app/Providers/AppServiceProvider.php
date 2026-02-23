<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; 
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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


        // custom rate limiter untuk tasks - limit per user diubah ke 10 agar menghindari burst click yang berlebihan
RateLimiter::for('tasks', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});

// custom rate limiter untuk roadmap - limit per user
RateLimiter::for('roadmap', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
});

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

         if (env('APP_ENV') === 'production') {
        \URL::forceScheme('https');
    }
    }

    
}