<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->priority_mode === 'auto') {
            $allTasks = Task::where('user_id', $user->id)
                            ->where('status', 'pending')
                            ->get();

            foreach ($allTasks as $t) {
                $newPriority = $this->calculatePriority($t->deadline);
                if ($t->priority !== $newPriority) {
                    $t->update(['priority' => $newPriority]);
                }
            }
        }

        $query = Task::with(['category', 'subtasks'])->where('user_id', $user->id)->where('status', 'pending');

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
        $is_manual = Auth::user()->priority_mode === 'manual';
        return view('tasks.create', compact('categories', 'is_manual'));
    }

    private function calculatePriority($deadline)
    {
        if (!$deadline) return 'low';
        $today = Carbon::today();
        $target = Carbon::parse($deadline);
        $diff = $today->diffInDays($target, false);

        if ($diff <= 2) return 'high';
        if ($diff <= 5) return 'medium';
        return 'low';
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'deadline' => 'required|date',
            'link_attachment' => 'nullable|url',
        ]);

        $user = Auth::user();
        $priority = ($user->priority_mode === 'manual') 
                    ? ($request->priority ?? 'medium') 
                    : $this->calculatePriority($request->deadline);

        $task = Task::create([
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'link_attachment' => $request->link_attachment,
            'priority' => $priority,
            'priority_color' => $request->priority_color,
            'status' => 'pending',
            'deadline' => $request->deadline,
        ]);

        if ($request->has('subtasks')) {
            foreach ($request->subtasks as $subTitle) {
                if (!empty(trim($subTitle))) {
                    $task->subtasks()->create([
                        'title' => $subTitle, 
                        'is_completed' => false
                    ]);
                }
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function edit(Task $task)
    {
        $this->authorizeOwner($task);
        $categories = Category::where('user_id', Auth::id())->get();
        $is_manual = Auth::user()->priority_mode === 'manual';
        return view('tasks.edit', compact('task', 'categories', 'is_manual'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeOwner($task);
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'deadline' => 'required|date',
            'status' => 'required|in:pending,completed',
            'link_attachment' => 'nullable|url',
        ]);

        $priority = ($user->priority_mode === 'manual') 
                    ? ($request->priority ?? $task->priority) 
                    : $this->calculatePriority($request->deadline);

        $task->update([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'deadline' => $request->deadline,
            'description' => $request->description,
            'link_attachment' => $request->link_attachment,
            'priority' => $priority,
            'priority_color' => ($user->priority_mode === 'manual') ? $request->priority_color : $task->priority_color,
            'status' => $request->status,
        ]);

        if ($request->has('existing_subtasks')) {
            foreach ($request->existing_subtasks as $subId => $title) {
                $subtask = Subtask::where('id', $subId)->where('task_id', $task->id)->first();
                if ($subtask) {
                    $subtask->update([
                        'title' => $title,
                        'is_completed' => isset($request->subtasks_status[$subId]) && $request->subtasks_status[$subId] == '1'
                    ]);
                }
            }
        }

        if ($request->has('subtasks')) {
            foreach ($request->subtasks as $newSubTitle) {
                if (!empty(trim($newSubTitle))) {
                    $task->subtasks()->create([
                        'title' => $newSubTitle,
                        'is_completed' => false
                    ]);
                }
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    public function complete(Task $task)
    {
        $this->authorizeOwner($task);
        $task->update(['status' => 'completed']);
        return redirect()->route('tasks.index')->with('success', 'Task marked as done!');
    }

    public function destroy(Task $task)
    {
        $this->authorizeOwner($task);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task moved to trash!');
    }

    public function trash()
    {
        $tasks = Task::onlyTrashed()->where('user_id', Auth::id())->latest()->get();
        return view('tasks.trash', compact('tasks'));
    }

    public function restore($id)
    {
        $task = Task::onlyTrashed()->where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $task->restore();
        return redirect()->route('tasks.trash')->with('success', 'Task restored successfully!');
    }

    public function forceDelete($id)
    {
        $task = Task::onlyTrashed()->where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $task->forceDelete();
        return redirect()->route('tasks.trash')->with('success', 'Task permanently deleted!');
    }

    private function authorizeOwner(Task $task)
    {
        if ($task->user_id !== Auth::id()) abort(403);
    }
}