<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Category;

class TaskController extends Controller
{
    // =======================
    // LIST TASK
    // =======================
    public function index(Request $request)
    {
        $query = Task::where('user_id', auth()->id())
            ->where('status', '!=', 'done');

    if ($request->priority) {
        $query->where('priority', $request->priority);
    }

    if ($request->category_id) {
        $query->where('category_id', $request->category_id);
    }

    $tasks = $query->latest()->paginate(10);
    $categories = Category::where('user_id', auth()->id())->get();

    return view('tasks.index', compact('tasks', 'categories'));
    }

    // =======================
    // FORM CREATE
    // =======================
    public function create()
    {
        $categories = Category::where('user_id', auth()->id())->get();
        return view('tasks.create', compact('categories'));
    }

    // =======================
    // STORE TASK
    // =======================
    public function store(Request $request)
    {
        $priorityMode = auth()->user()->priority_mode;

        $rules = [
            'title'       => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ];

        if ($priorityMode === 'manual') {
            $rules['priority'] = 'required|in:low,medium,high';
        }

        $request->validate($rules);

        $priority = $priorityMode === 'manual'
            ? $request->priority
            : $this->calculatePriority($request->deadline);

        Task::create([
            'user_id'     => auth()->id(),
            'title'       => $request->title,
            'category_id' => $request->category_id,
            'description'=> $request->description,
            'deadline'   => $request->deadline,
            'priority'   => $priority,
            'status'     => 'todo',
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil ditambahkan');
    }

    // =======================
    // FORM EDIT
    // =======================
    public function edit(Task $task)
    {
        $this->authorizeTask($task);

        $categories = Category::where('user_id', auth()->id())->get();

        return view('tasks.edit', compact('task', 'categories'));
    }

    // =======================
    // UPDATE TASK
    // =======================
    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $priorityMode = auth()->user()->priority_mode;

        $rules = [
            'title'       => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ];

        if ($priorityMode === 'manual') {
            $rules['priority'] = 'required|in:low,medium,high';
        }

        $request->validate($rules);

        $priority = $priorityMode === 'manual'
            ? $request->priority
            : $this->calculatePriority($request->deadline);

        $task->update([
            'title'       => $request->title,
            'category_id' => $request->category_id,
            'description'=> $request->description,
            'deadline'   => $request->deadline,
            'priority'   => $priority,
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil diupdate');
    }

    // =======================
    // DELETE
    // =======================
    public function destroy(Task $task)
{
    // Pastikan hanya pemilik task yang bisa hapus
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    $task->delete(); // Ini akan memindah ke trash jika pakai SoftDeletes, atau hapus permanen jika tidak.

    return redirect()->back()->with('status', 'Task deleted successfully');
}
    // =======================
    // MARK DONE
    // =======================
    public function markDone(Task $task)
{
    // Pastikan hanya pemilik task yang bisa akses
    if ($task->user_id !== auth()->id()) {
        abort(403);
    }

    // Update status task. Sesuaikan nama kolomnya (misal: is_completed atau status)
    $task->update([
        'status' => 'done',
        // 'status' => 'completed' // Jika kamu pakai kolom status
    ]);

    return redirect()->back()->with('status', 'Task marked as completed!');
}

    // =======================
    // COMPLETED TASK
    // =======================
    public function completed()
    {
        $tasks = Task::with('category')
            ->where('user_id', auth()->id())
            ->where('status', 'done')
            ->latest()
            ->get();

        return view('tasks.completed', compact('tasks'));
    }

    // =======================
    // RESTORE
    // =======================

    // TRASH
public function trash()
{
    $tasks = Task::onlyTrashed()
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return view('tasks.trash', compact('tasks'));
}

// RESTORE
public function restore($id)
{
    $task = Task::onlyTrashed()
        ->where('user_id', auth()->id())
        ->findOrFail($id);

    $task->restore();

    return back()->with('success', 'Task berhasil direstore');
}

// FORCE DELETE
public function forceDelete($id)
{
    $task = Task::onlyTrashed()
        ->where('user_id', auth()->id())
        ->findOrFail($id);

    $task->forceDelete();

    return back()->with('success', 'Task dihapus permanen');
}


    // =======================
    // AUTO PRIORITY
    // =======================
    private function calculatePriority($deadline)
    {
        if (!$deadline) return 'low';

        $daysLeft = now()->diffInDays($deadline, false);

        if ($daysLeft <= 2) return 'high';
        if ($daysLeft <= 5) return 'medium';

        return 'low';
    }

    // =======================
    // SECURITY CHECK
    // =======================
    private function authorizeTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
