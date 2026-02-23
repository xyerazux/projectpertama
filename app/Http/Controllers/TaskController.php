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
        // PENCEGAHAN SPAM BACKEND VIA SESSION (Lebih Cepat dari Cache Lock untuk form submit)
        if (session()->has('last_task_created_time')) {
            $lastCreated = session('last_task_created_time');
            // Jika request masuk kurang dari 3 detik dari request sebelumnya, tolak!
            if (now()->diffInSeconds($lastCreated) < 3) {
                return back()->with('error', '⚠️ Sedang memproses, jangan tekan tombol berkali-kali.');
            }
        }
        
        // Catat waktu klik saat ini ke session
        session(['last_task_created_time' => now()]);

        try {
            // validasi input - server-side
            $validated = $request->validate([
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

            // sanitasi input
            $validated['title'] = strip_tags(trim($validated['title']));
            $validated['description'] = $validated['description'] 
                ? strip_tags(trim($validated['description'])) 
                : null;
            $validated['link_attachment'] = $validated['link_attachment'] 
                ? filter_var(trim($validated['link_attachment']), FILTER_VALIDATE_URL) 
                : null;

            // auto assign user + status default
            $validated['user_id'] = auth()->id();
            $validated['status'] = 'pending';

            // simpan task
            $task = auth()->user()->tasks()->create($validated);

            // simpan subtasks kalau ada
            if (!empty($validated['subtasks'])) {
                foreach ($validated['subtasks'] as $subtaskTitle) {
                    if (!empty($subtaskTitle)) {
                        $task->subtasks()->create([
                            'title' => strip_tags(trim($subtaskTitle)),
                            'is_completed' => false,
                        ]);
                    }
                }
            }

            return redirect()->route('tasks.index')->with('success', 'Task created!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika validasi gagal, hapus session agar user bisa langsung submit ulang tanpa nunggu 3 detik
            session()->forget('last_task_created_time');
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Task DB error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', ' Database error. Coba lagi.');
            
        } catch (\Illuminate\Database\Eloquent\MassAssignmentException $e) {
            \Log::error('Task MassAssignment error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', ' Configuration error. Hubungi admin.');
            
        } catch (\Exception $e) {
            \Log::error('Task creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', ' Gagal membuat task. Coba lagi.');
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