<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Category;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskController extends Controller
{
    // ──────────────────────────────────────────────
    // LIST — pending tasks (with filters)
    // ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = Auth::user();

        // Auto-update priorities when mode is "auto"
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

        $query = Task::with(['category', 'subtasks', 'attachments'])
            ->where('user_id', $user->id)
            ->where('status', 'pending');

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $tasks = $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    // ──────────────────────────────────────────────
    // SHOW — single task detail
    // ──────────────────────────────────────────────
    public function show(Task $task)
    {
        $this->authorizeOwner($task);
        $task->load(['category', 'subtasks', 'attachments']);

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    // ──────────────────────────────────────────────
    // STORE — create new task
    // ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'link_attachment' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
            'deadline' => 'required|date',
            'priority' => 'nullable|in:low,medium,high',
            'subtasks' => 'nullable|array',
            'subtasks.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Verify category belongs to user
        $category = Category::where('id', $validated['category_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category does not belong to you.',
            ], 403);
        }

        // Sanitize
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = !empty($validated['description'])
            ? strip_tags(trim($validated['description']))
            : null;
        $validated['link_attachment'] = !empty($validated['link_attachment'])
            ? (filter_var(trim($validated['link_attachment']), FILTER_VALIDATE_URL) ?: null)
            : null;

        // Priority: auto or manual
        $user = Auth::user();
        if ($user->priority_mode === 'manual') {
            $validated['priority'] = $validated['priority'] ?? 'low';
        } else {
            $validated['priority'] = $this->calculatePriority($validated['deadline']);
        }

        $validated['user_id'] = $user->id;
        $validated['status'] = 'pending';

        $task = $user->tasks()->create($validated);

        // Subtasks
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

        $task->load(['category', 'subtasks', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => 'Task created!',
            'data' => $task,
        ], 201);
    }

    // ──────────────────────────────────────────────
    // UPDATE — edit task
    // ──────────────────────────────────────────────
    public function update(Request $request, Task $task)
    {
        $this->authorizeOwner($task);
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'deadline' => 'required|date',
            'status' => 'nullable|in:pending,completed',
            'link_attachment' => 'nullable|url',
            'priority' => 'nullable|in:low,medium,high',
            'existing_subtasks' => 'nullable|array',
            'existing_subtasks.*' => 'nullable|string|max:255',
            'subtasks_status' => 'nullable|array',
            'subtasks' => 'nullable|array',
            'subtasks.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Sanitize
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = !empty($validated['description'])
            ? strip_tags(trim($validated['description']))
            : null;

        // Priority: manual or auto
        $priority = ($user->priority_mode === 'manual')
            ? ($validated['priority'] ?? $task->priority)
            : $this->calculatePriority($validated['deadline']);

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'deadline' => $validated['deadline'],
            'link_attachment' => $validated['link_attachment'] ?? null,
            'priority' => $priority,
            'status' => $validated['status'] ?? $task->status,
        ]);

        // Update existing subtasks
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

        // Add new subtasks
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

        $task->load(['category', 'subtasks', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => 'Task updated!',
            'data' => $task,
        ]);
    }

    // ──────────────────────────────────────────────
    // COMPLETE — mark task as done
    // ──────────────────────────────────────────────
    public function complete(Task $task)
    {
        $this->authorizeOwner($task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task marked as done!',
            'data' => $task,
        ]);
    }

    // ──────────────────────────────────────────────
    // DESTROY — soft delete
    // ──────────────────────────────────────────────
    public function destroy(Task $task)
    {
        $this->authorizeOwner($task);
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task moved to trash!',
        ]);
    }

    // ──────────────────────────────────────────────
    // COMPLETED — list completed tasks
    // ──────────────────────────────────────────────
    public function completed()
    {
        $tasks = Task::with(['category', 'subtasks', 'attachments'])
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    // ──────────────────────────────────────────────
    // TRASH — list soft-deleted tasks
    // ──────────────────────────────────────────────
    public function trash()
    {
        $tasks = Task::with(['category', 'subtasks', 'attachments'])
            ->onlyTrashed()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    // ──────────────────────────────────────────────
    // RESTORE — restore from trash
    // ──────────────────────────────────────────────
    public function restore($id)
    {
        $task = Task::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $task->restore();

        return response()->json([
            'success' => true,
            'message' => 'Task restored!',
            'data' => $task,
        ]);
    }

    // ──────────────────────────────────────────────
    // FORCE DELETE — permanent delete
    // ──────────────────────────────────────────────
    public function forceDelete($id)
    {
        $task = Task::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $task->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Task permanently deleted!',
        ]);
    }

    // ──────────────────────────────────────────────
    // CATEGORIES — list user categories
    // ──────────────────────────────────────────────
    public function categories()
    {
        $categories = Category::where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // ──────────────────────────────────────────────
    // TOGGLE SUBTASK
    // ──────────────────────────────────────────────
    public function toggleSubtask(Subtask $subtask)
    {
        if ($subtask->task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $subtask->update([
            'is_completed' => !$subtask->is_completed,
        ]);

        return response()->json([
            'success' => true,
            'is_completed' => $subtask->is_completed,
        ]);
    }

    // ──────────────────────────────────────────────
    // Auto-calculate priority based on deadline
    // ──────────────────────────────────────────────
    private function calculatePriority($deadline)
    {
        if (!$deadline)
            return 'low';

        $today = Carbon::today();
        $target = Carbon::parse($deadline);
        $diff = $today->diffInDays($target, false);

        if ($diff <= 2)
            return 'high';
        if ($diff <= 5)
            return 'medium';
        return 'low';
    }

    // ──────────────────────────────────────────────
    // Ownership check
    // ──────────────────────────────────────────────
    private function authorizeOwner(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
