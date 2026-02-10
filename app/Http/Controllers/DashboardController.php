<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userid = auth()->id();
        $user = auth()->user();
        $priority = request('priority');
        $status = request('status');
        $recentTasksQuery = Task::where('user_id', $userid);
        $taskPerCategory = Category::where('user_id', auth()->id())
        ->withCount('tasks')
        ->get();

        
        if ($priority && $priority !== 'all') {
            $recentTasksQuery->where('priority', $priority);
            }
            
        if ($status && $status !== 'all') {
            $recentTasksQuery->where('status', $status);
            }

            $totalTasks= Task::where('user_id', $user->id)->count();
            
            $doneTasks = Task::where('user_id', $user->id)
            ->where('status', 'done')
            ->count();
            
            $pendingTasks=Task::where('user_id',$user->id)
            ->where('status', '!=', 'done')
            ->count();
            
            $recentTasks = Task::where('user_id', $user->id)
                ->latest()
                ->take(4)
                ->get();

            $progress = $totalTasks > 0
                ? round(($doneTasks / $totalTasks) * 100)
                : 0;
            
        return view('dashboard', [
            'totalTasks'   => $totalTasks,
            'doneTasks'    => $doneTasks,
            'pendingTasks' => $pendingTasks,
            'recentTasks'  => $recentTasks,
            'progress'     => $progress,
            'taskPerCategory' => $taskPerCategory,
        ]);


    }
    
    
}
