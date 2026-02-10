<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    View::composer('*', function ($view) {
        if (auth()->check()) {
            $categories = Category::withCount('tasks')
                ->where('user_id', auth()->id())
                ->get();

            $view->with('categories', $categories);
        }
    });

    \Illuminate\Support\Facades\Artisan::call('config:clear');
}

}
