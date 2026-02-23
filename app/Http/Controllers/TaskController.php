<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    // list tasks
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // auto update priority kalau mode auto
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

        $query = Task::with(['category', 'subtasks'])
                     ->where('user_id', $user->id)
                     ->where('status', 'pending');

        // filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
               ->latest()
               ->paginate(10)
               ->withQueryString();

        $categories = Category::where('user_id', $user->id)->get();
        
        return view('tasks.index', compact('tasks', 'categories'));
    }

    // task selesai
    public function completed()
    {
        $tasks = Task::where('user_id', Auth::id())
                     ->where('status', 'completed')
                     ->latest()
                     ->get();
                     
        return view('tasks.completed', compact('tasks'));
    }

    // form create
    public function create()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        $is_manual = Auth::user()->priority_mode === 'manual';
        
        return view('tasks.create', compact('categories', 'is_manual'));
    }

    // hitung priority auto
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

    // simpan task baru
    public function store(Request $request)
    {
        // 1. PENCEGAHAN SPAM MENGGUNAKAN INTEGER TIMESTAMP (Aman dari Error 500 Object Serialization)
        $lastCreated = session('last_task_time', 0);
        
        // Jika selisih waktu dari request terakhir kurang dari 3 detik, tolak!
        if (time() - $lastCreated < 3) {
            return back()->with('error', '⚠️ Sedang memproses, jangan tekan tombol berkali-kali.');
        }
        
        // Langsung set session lock saat request masuk
        session(['last_task_time' => time()]);

        // 2. VALIDASI MANUAL (Lebih aman dari try-catch exception)
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'link_attachment' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
            'deadline' => 'required|date',
            'priority' => 'nullable|in:low,medium,high',
            'subtasks' => 'nullable|array',
            'subtasks.*' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Task title wajib diisi',
            'title.min' => 'Title minimal 2 karakter',
            'category_id.exists' => 'Kategori tidak valid',
            'deadline.date' => 'Format tanggal tidak valid',
        ]);

        if ($validator->fails()) {
            // Jika form salah (misal judul kosong), hapus kunci session agar user bisa langsung revisi
            session()->forget('last_task_time');
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            // 3. SANITASI INPUT YANG AMAN (Mencegah "Undefined Array Key" Error 500)
            $validated['title'] = strip_tags(trim($validated['title']));
            
            if (!empty($validated['description'])) {
                $validated['description'] = strip_tags(trim($validated['description']));
            } else {
                $validated['description'] = null;
            }

            if (!empty($validated['link_attachment'])) {
                $validated['link_attachment'] = filter_var(trim($validated['link_attachment']), FILTER_VALIDATE_URL) ?: null;
            } else {
                $validated['link_attachment'] = null;
            }

            // Auto assign user + status default
            $validated['user_id'] = auth()->id();
            $validated['status'] = 'pending';

            // 4. SIMPAN TASK KE DATABASE
            $task = auth()->user()->tasks()->create($validated);

            // 5. SIMPAN SUBTASKS (Jika ada)
            if (!empty($validated['subtasks'])) {
                foreach ($validated['subtasks'] as $subtaskTitle) {
                    if (!empty(trim($subtaskTitle))) {
                        $task->subtasks()->create([
                            'title' => strip_tags(trim($subtaskTitle)),
                            'is_completed' => false,
                        ]);
                    }
                }
            }

            return redirect()->route('tasks.index')->with('success', 'Task created!');

        } catch (\Exception $e) {
            // Jika terjadi error sistem/database, catat errornya dan buka kembali kuncinya
            session()->forget('last_task_time');
            
            \Log::error('Task creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Gagal menyimpan ke database. Hubungi admin atau coba lagi.');
        }
    }

    // form edit
    public function edit(Task $task)
    {
        $this->authorizeOwner($task);
        
        $categories = Category::where('user_id', Auth::id())->get();
        $is_manual = Auth::user()->priority_mode === 'manual';
        
        return view('tasks.edit', compact('task', 'categories', 'is_manual'));
    }

    // update task
    public function update(Request $request, Task $task)
    {
        $this->authorizeOwner($task);
        $user = Auth::user();

        // validasi
        $validated = $request->validate([
            'title' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'deadline' => 'required|date',
            'status' => 'required|in:pending,completed',
            'link_attachment' => 'nullable|url',
            'priority' => 'nullable|in:low,medium,high',
            'existing_subtasks' => 'nullable|array',
            'existing_subtasks.*' => 'nullable|string|max:255',
            'subtasks_status' => 'nullable|array',
            'subtasks' => 'nullable|array',
            'subtasks.*' => 'nullable|string|max:255',
        ]);

        // sanitasi
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = $validated['description'] 
            ? strip_tags(trim($validated['description'])) 
            : null;

        // priority: manual atau auto
        $priority = ($user->priority_mode === 'manual') 
            ? ($validated['priority'] ?? $task->priority) 
            : $this->calculatePriority($validated['deadline']);

        // update task
        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'deadline' => $validated['deadline'],
            'link_attachment' => $validated['link_attachment'],
            'priority' => $priority,
            'status' => $validated['status'],
        ]);

        // update existing subtasks
        if (!empty($validated['existing_subtasks'])) {
            foreach ($validated['existing_subtasks'] as $subId => $title) {
                $subtask = $task->subtasks()->find($subId);
                if ($subtask) {
                    $isCompleted = !empty($validated['subtasks_status'][$subId]);
                    $subtask->update([
                        'title' => strip_tags(trim($title)),
                        'is_completed' => $isCompleted,
                    ]);
                }
            }
        }

        // tambah subtasks baru
        if (!empty($validated['subtasks'])) {
            foreach ($validated['subtasks'] as $newSubTitle) {
                if (!empty(trim($newSubTitle))) {
                    $task->subtasks()->create([
                        'title' => strip_tags(trim($newSubTitle)),
                        'is_completed' => false,
                    ]);
                }
            }
        }

        return redirect()->route('tasks.index')->with('success', '✏️ Task updated!');
    }

    // mark as done
    public function complete(Task $task)
    {
        $this->authorizeOwner($task);
        
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        
        return redirect()->route('tasks.index')->with('success', '✅ Task marked as done!');
    }

    // hapus task (soft delete)
    public function destroy(Task $task)
    {
        $this->authorizeOwner($task);
        
        $task->delete();
        
        return redirect()->route('tasks.index')->with('success', '🗑️ Task moved to trash!');
    }

    // list task di trash
    public function trash(Request $request)
    {
        $tasks = Task::onlyTrashed()
                     ->where('user_id', auth()->id())
                     ->paginate(10);
        
        return view('tasks.trash', compact('tasks'));
    }

    // restore task dari trash
    public function restore($id)
    {
        $task = Task::onlyTrashed()
                    ->where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
                    
        $task->restore();
        
        return redirect()->route('tasks.trash')->with('success', 'Task restored!');
    }

    // hapus permanent dari trash
    public function forceDelete($id)
    {
        $task = Task::onlyTrashed()
                    ->where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
                    
        $task->forceDelete();
        
        return redirect()->route('tasks.trash')->with('success', 'Task permanently deleted!');
    }

    // cek ownership - jangan sampai user lain bisa akses
    private function authorizeOwner(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}