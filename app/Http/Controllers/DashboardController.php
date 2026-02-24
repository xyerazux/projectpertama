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

        // Hitung statistik dasar
        $totalTasks = Task::where('user_id', $userId)->count();
        $doneTasks = Task::where('user_id', $userId)->where('status', 'completed')->count();
        $pendingTasks = Task::where('user_id', $userId)->where('status', 'pending')->count();

        // Ambil task terbaru yang masih pending dengan subtasks-nya
        $recentTasks = Task::with(['subtasks'])
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->latest()
            ->take(4)
            ->get();

        // Hitung persentase efisiensi (task selesai dibanding total task)
        $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        // Hitung task per kategori (hanya yang pending)
        $taskPerCategory = Category::where('user_id', $userId)
            ->withCount([
                'tasks' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])->get();

        // Cek apakah user sudah punya kategori
        $hasCategories = Category::where('user_id', $userId)->exists();

        return view('dashboard', compact(
            'totalTasks',
            'doneTasks',
            'pendingTasks',
            'recentTasks',
            'progress',
            'taskPerCategory',
            'hasCategories'
        ));
    }
}





