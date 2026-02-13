<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // DASHBOARD & PROFILE
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/priority', [ProfileController::class, 'updatePriority'])->name('profile.priority');
    
    // 1. STATIS ROUTES (Wajib di atas rute yang ada parameter {task})
    Route::get('/tasks/completed', [TaskController::class, 'completed'])->name('tasks.completed');
    Route::get('/tasks/trash', [TaskController::class, 'trash'])->name('tasks.trash');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

    // 2. ACTION ROUTES
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::delete('/tasks/{id}/force', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');
    Route::patch('/tasks/{task}/done', [TaskController::class, 'markDone'])->name('tasks.markDone');

    // 3. RESOURCE ROUTES (Parameter {task} paling bawah)
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // CATEGORIES
    Route::resource('categories', CategoryController::class);
});

// EMERGENCY OPTIMIZE
Route::get('/optimize', function() {
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    return "Application Cleaned and Optimized!";
});

require __DIR__.'/auth.php';