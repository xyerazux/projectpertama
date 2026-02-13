<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totalTasks = Task::where('user_id', $userId)->count();
        $doneTasks = Task::where('user_id', $userId)->where('status', 'completed')->count();
        $pendingTasks = Task::where('user_id', $userId)->where('status', 'pending')->count();

        // Urutkan recent tasks berdasarkan prioritas juga agar konsisten
        $recentTasks = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->latest()
            ->take(4)
            ->get();

        $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        $taskPerCategory = Category::where('user_id', $userId)
            ->withCount(['tasks' => function($query) {
                $query->where('status', 'pending');
            }])->get();

        return view('dashboard', compact('totalTasks', 'doneTasks', 'pendingTasks', 'recentTasks', 'progress', 'taskPerCategory'));
    }
}