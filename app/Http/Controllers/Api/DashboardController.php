<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totalTasks = Task::where('user_id', $userId)->count();
        $doneTasks = Task::where('user_id', $userId)->where('status', 'completed')->count();
        $pendingTasks = Task::where('user_id', $userId)->where('status', 'pending')->count();
        $trashedTasks = Task::onlyTrashed()->where('user_id', $userId)->count();

        $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        $recentTasks = Task::with(['subtasks', 'category'])
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->latest()
            ->take(4)
            ->get();

        $taskPerCategory = Category::where('user_id', $userId)
            ->withCount([
                'tasks' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->get();

        $activeRoadmaps = \App\Models\Roadmap::with('steps')
            ->where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($rm) {
                $totalArgs = $rm->steps->count();
                $doneArgs = $rm->steps->where('is_completed', true)->count();
                $pct = $totalArgs > 0 ? round(($doneArgs / $totalArgs) * 100) : 0;
                return [
                    'id' => $rm->id,
                    'title' => $rm->title,
                    'status' => $rm->status,
                    'progress_percent' => $pct,
                    'total_steps' => $totalArgs,
                    'completed_steps' => $doneArgs,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_tasks' => $totalTasks,
                'done_tasks' => $doneTasks,
                'pending_tasks' => $pendingTasks,
                'trashed_tasks' => $trashedTasks,
                'progress' => $progress,
                'recent_tasks' => $recentTasks,
                'task_per_category' => $taskPerCategory,
                'roadmaps_summary' => $activeRoadmaps,
            ],
        ]);
    }
}
