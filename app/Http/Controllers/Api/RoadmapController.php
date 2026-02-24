<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoadmapController extends Controller
{
    public function index()
    {
        $roadmaps = Auth::user()->roadmaps()
            ->with('steps')
            ->orderBy('target_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                $r->completion_percentage = $r->completion_percentage;
                return $r;
            });

        return response()->json(['success' => true, 'data' => $roadmaps]);
    }

    public function show(Roadmap $roadmap)
    {
        if ($roadmap->user_id !== Auth::id())
            abort(403);
        $roadmap->load('steps');
        $roadmap->completion_percentage = $roadmap->completion_percentage;

        return response()->json(['success' => true, 'data' => $roadmap]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:planned,in_progress,on_hold,completed',
            'target_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $validated['user_id'] = Auth::id();
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = !empty($validated['description']) ? strip_tags(trim($validated['description'])) : null;

        $roadmap = Auth::user()->roadmaps()->create($validated);

        return response()->json(['success' => true, 'message' => 'Roadmap created!', 'data' => $roadmap], 201);
    }

    public function destroy(Roadmap $roadmap)
    {
        if ($roadmap->user_id !== Auth::id())
            abort(403);

        $roadmap->delete(); // soft delete

        return response()->json(['success' => true, 'message' => 'Roadmap moved to trash.']);
    }

    public function trashed()
    {
        $roadmaps = Auth::user()->roadmaps()
            ->onlyTrashed()
            ->with('steps')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $roadmaps]);
    }

    public function restore($id)
    {
        $roadmap = Auth::user()->roadmaps()->onlyTrashed()->findOrFail($id);
        $roadmap->restore();

        return response()->json(['success' => true, 'message' => 'Roadmap restored.']);
    }

    public function forceDelete($id)
    {
        $roadmap = Auth::user()->roadmaps()->onlyTrashed()->findOrFail($id);
        $roadmap->steps()->delete();
        $roadmap->forceDelete();

        return response()->json(['success' => true, 'message' => 'Roadmap permanently deleted.']);
    }

    public function storeStep(Request $request, Roadmap $roadmap)
    {
        if ($roadmap->user_id !== Auth::id())
            abort(403);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2|max:255',
            'priority' => 'nullable|in:high,medium,low',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = !empty($validated['description']) ? strip_tags(trim($validated['description'])) : null;
        $validated['category'] = !empty($validated['category']) ? ucfirst(trim($validated['category'])) : null;

        $step = $roadmap->steps()->create([
            'title' => $validated['title'],
            'is_completed' => false,
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'progress' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Step added!', 'data' => $step], 201);
    }

    public function toggleStep(RoadmapStep $step)
    {
        if ($step->roadmap->user_id !== Auth::id())
            abort(403);

        $step->update([
            'is_completed' => !$step->is_completed,
            'progress' => $step->is_completed ? 0 : 100,
        ]);

        // Auto-complete roadmap if all steps are done
        $roadmap = $step->roadmap;
        $total = $roadmap->steps()->count();
        $done = $roadmap->steps()->where('is_completed', true)->count();
        if ($total > 0 && $total === $done) {
            $roadmap->update(['status' => 'completed']);
        } elseif ($roadmap->status === 'completed' && $done < $total) {
            $roadmap->update(['status' => 'in_progress']);
        }

        return response()->json(['success' => true, 'message' => 'Step toggled.', 'data' => $step]);
    }

    public function updateStep(Request $request, RoadmapStep $step)
    {
        if ($step->roadmap->user_id !== Auth::id())
            abort(403);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|min:2|max:255',
            'priority' => 'nullable|in:high,medium,low',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $updateData = [];
        if ($request->has('title')) {
            $updateData['title'] = strip_tags(trim($validated['title']));
        }
        if ($request->has('priority')) {
            $updateData['priority'] = $validated['priority'];
        }
        if ($request->has('due_date')) {
            $updateData['due_date'] = $validated['due_date'];
        }
        if ($request->has('description')) {
            $updateData['description'] = !empty($validated['description']) ? strip_tags(trim($validated['description'])) : null;
        }
        if ($request->has('category')) {
            $updateData['category'] = !empty($validated['category']) ? ucfirst(trim($validated['category'])) : null;
        }
        if ($request->has('progress')) {
            $updateData['progress'] = $validated['progress'];
            $updateData['is_completed'] = $validated['progress'] == 100;
        }

        $step->update($updateData);

        // Auto-complete roadmap if all steps are done
        $roadmap = $step->roadmap;
        $total = $roadmap->steps()->count();
        $done = $roadmap->steps()->where('is_completed', true)->count();
        if ($total > 0 && $total === $done) {
            $roadmap->update(['status' => 'completed']);
        } elseif ($roadmap->status === 'completed' && $done < $total) {
            $roadmap->update(['status' => 'in_progress']);
        }

        return response()->json(['success' => true, 'message' => 'Step updated!', 'data' => $step]);
    }

    public function destroyStep(RoadmapStep $step)
    {
        if ($step->roadmap->user_id !== Auth::id())
            abort(403);

        $step->delete();

        return response()->json(['success' => true, 'message' => 'Step deleted!']);
    }
}
