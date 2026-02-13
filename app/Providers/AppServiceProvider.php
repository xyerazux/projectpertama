<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

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
        // Menangani panjang string database untuk versi MySQL lama jika perlu
        Schema::defaultStringLength(191);

        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // View Composer untuk membagikan data kategori ke semua view
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $categories = Category::withCount(['tasks' => function($query) {
                    $query->where('status', 'pending'); // Hanya hitung task yang belum selesai
                }])
                ->where('user_id', auth()->id())
                ->get();

                $view->with('categories', $categories);
            } else {
                // Berikan array kosong jika belum login agar tidak error undefined variable
                $view->with('categories', collect());
            }
        }); 
    }
}