<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::with('category')->where('user_id', $user->id)->where('status', 'pending');

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                      ->latest()
                      ->get();

        $categories = Category::where('user_id', $user->id)->get();

        return view('tasks.index', compact('tasks', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        return view('tasks.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'nullable|in:low,medium,high',
            'deadline' => 'nullable|date',
        ]);

        $user = Auth::user();
        $priority = $request->priority ?? 'low';

        if ($user->priority_mode === 'auto' && $request->deadline) {
            $priority = $this->calculatePriority($request->deadline);
        }

        Task::create([
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $priority,
            'status' => 'pending',
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function edit(Task $task)
    {
        $this->authorizeOwner($task);
        $categories = Category::where('user_id', Auth::id())->get();
        return view('tasks.create', compact('task', 'categories'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeOwner($task);

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'nullable|in:low,medium,high',
            'deadline' => 'nullable|date',
        ]);

        $user = Auth::user();
        $priority = $request->priority ?? $task->priority;

        if ($user->priority_mode === 'auto' && $request->deadline) {
            $priority = $this->calculatePriority($request->deadline);
        }

        $task->update([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $priority,
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    public function markDone(Task $task)
    {
        $this->authorizeOwner($task);
        $task->update(['status' => 'completed']);
        return redirect()->route('tasks.index')->with('success', 'Task marked as done!');
    }

    public function completed()
    {
        $tasks = Task::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest()
            ->get();
        return view('tasks.completed', compact('tasks'));
    }

    public function destroy(Task $task)
    {
        $this->authorizeOwner($task);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task moved to trash!');
    }

    public function trash()
    {
        $tasks = Task::onlyTrashed()->where('user_id', Auth::id())->get();
        return view('tasks.trash', compact('tasks'));
    }

    public function restore($id)
    {
        $task = Task::onlyTrashed()->where('user_id', Auth::id())->findOrFail($id);
        $task->restore();
        return back()->with('success', 'Task restored!');
    }

    public function forceDelete($id)
    {
        $task = Task::onlyTrashed()->where('user_id', Auth::id())->findOrFail($id);
        $task->forceDelete();
        return back()->with('success', 'Task permanently deleted!');
    }

    private function calculatePriority($deadline)
    {
        $daysLeft = now()->startOfDay()->diffInDays(Carbon::parse($deadline)->startOfDay(), false);
        if ($daysLeft <= 1) return 'high';
        if ($daysLeft <= 3) return 'medium';
        return 'low';
    }

    private function authorizeOwner(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }
    }
}