<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RoadmapController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReflectionController;
use App\Http\Controllers\Api\MotivatorController;
use App\Http\Controllers\Api\AppConfigController;

/*
|--------------------------------------------------------------------------
| API Routes — Sanctum Protected + 10/min Rate Limiter on mutations
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\AppVersionController;

// ── Public (no auth) ──────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/check-update', [AppConfigController::class, 'checkUpdate']);
Route::get('/check-version', [AppVersionController::class, 'checkVersion']);

// ── Protected (Sanctum token required) ────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::middleware('throttle:tasks')->group(function () {
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::patch('/profile/priority', [ProfileController::class, 'updatePriority']);
    });

    // Reflections
    Route::post('/reflections', [ReflectionController::class, 'store']);

    // Categories — read
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    // Categories — write (throttled)
    Route::middleware('throttle:tasks')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });

    // Tasks — read routes (no throttle)
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/completed', [TaskController::class, 'completed']);
    Route::get('/tasks/trash', [TaskController::class, 'trash']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);

    // Tasks — mutation routes (throttled 10/min)
    Route::middleware('throttle:tasks')->group(function () {
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);
        Route::post('/tasks/motivate', [MotivatorController::class, 'motivate']);
        Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore']);
        Route::delete('/tasks/{id}/force', [TaskController::class, 'forceDelete']);
        Route::patch('/subtasks/{subtask}/toggle', [TaskController::class, 'toggleSubtask']);
    });

    // Attachments
    Route::post('/{type}/{id}/attachments', [App\Http\Controllers\Api\AttachmentController::class, 'store'])->where('type', 'tasks|roadmaps');
    Route::delete('/attachments/{attachment}', [App\Http\Controllers\Api\AttachmentController::class, 'destroy']);
    Route::get('/attachments/{attachment}/view', [App\Http\Controllers\Api\AttachmentController::class, 'view']);

    // Roadmaps — read
    Route::get('/roadmaps', [RoadmapController::class, 'index']);
    Route::get('/roadmaps/trash', [RoadmapController::class, 'trashed']);
    Route::get('/roadmaps/{roadmap}', [RoadmapController::class, 'show']);
    // Roadmaps — write (throttled 20/min)
    Route::middleware('throttle:roadmap')->group(function () {
        Route::post('/roadmaps', [RoadmapController::class, 'store']);
        Route::delete('/roadmaps/{roadmap}', [RoadmapController::class, 'destroy']);
        Route::patch('/roadmaps/{id}/restore', [RoadmapController::class, 'restore']);
        Route::delete('/roadmaps/{id}/force', [RoadmapController::class, 'forceDelete']);
        Route::post('/roadmaps/{roadmap}/steps', [RoadmapController::class, 'storeStep']);
        Route::patch('/roadmap-steps/{step}/toggle', [RoadmapController::class, 'toggleStep']);
        Route::patch('/roadmap-steps/{step}', [RoadmapController::class, 'updateStep']);
        Route::delete('/roadmap-steps/{step}', [RoadmapController::class, 'destroyStep']);
    });
});
