<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\RoadmapController;

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
    
    // 1. STATIC ROUTES (Wajib di atas parameter {task})
    Route::get('/tasks/completed', [TaskController::class, 'completed'])->name('tasks.completed');
    Route::get('/tasks/trash', [TaskController::class, 'trash'])->name('tasks.trash');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');

    // 2. ACTION ROUTES
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::delete('/tasks/{id}/force', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');
    Route::patch('/subtasks/{subtask}/toggle', [SubtaskController::class, 'toggle'])->name('subtasks.toggle');

    // 3. RESOURCE ROUTES
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // CATEGORIES
    Route::resource('categories', CategoryController::class);

    // ROADMAP
    Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');
    Route::post('/roadmap', [RoadmapController::class, 'store'])->name('roadmap.store');
    Route::delete('/roadmap/{roadmap}', [RoadmapController::class, 'destroy'])->name('roadmap.destroy');
    
    // Roadmap Steps
    Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');
    Route::post('/roadmap/{roadmap}/steps', [RoadmapController::class, 'storeStep'])->name('roadmap.steps.store');
    Route::patch('/roadmap-steps/{step}/toggle', [RoadmapController::class, 'toggleStep'])->name('roadmap.steps.toggle');
    Route::patch('/roadmap-steps/{step}', [RoadmapController::class, 'updateStep'])->name('roadmap.steps.update');
    Route::delete('/roadmap-steps/{step}', [RoadmapController::class, 'destroyStep'])->name('roadmap.steps.destroy');
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