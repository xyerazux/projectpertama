<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoadmapController extends Controller
{
    /**
     * Display all roadmaps for authenticated user
     */
    public function index(): View
    {
        $roadmaps = Auth::user()->roadmaps()
            ->with('steps')
            ->orderBy('target_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('roadmap.index', compact('roadmaps'));
    }

    /**
     * Store new roadmap - ✅ Essential validation only
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ ESSENTIAL: Input validation
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:planned,in_progress,on_hold,completed',
            'target_date' => 'nullable|date|after_or_equal:today',
        ], [
            'title.required' => 'Goal title is required',
            'title.min' => 'Title must be at least 3 characters',
            'status.required' => 'Status is required',
        ]);

        // ✅ ESSENTIAL: Auto-assign user_id + sanitize
        $validated['user_id'] = Auth::id();
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = $validated['description'] ? strip_tags(trim($validated['description'])) : null;

        try {
            Auth::user()->roadmaps()->create($validated);
            return back()->with('success', 'Goal created successfully.');
        } catch (\Exception $e) {
            Log::error('Roadmap creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to create goal. Please try again.');
        }
    }

    /**
     * Store new step/task - ✅ Essential validation only
     */
    public function storeStep(Request $request, Roadmap $roadmap): RedirectResponse
    {
        // ✅ ESSENTIAL: Ownership check
        if ($roadmap->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // ✅ ESSENTIAL: Input validation
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'priority' => 'nullable|in:high,medium,low',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
        ]);

        // ✅ ESSENTIAL: Sanitize
        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = $validated['description'] ? strip_tags(trim($validated['description'])) : null;
        $validated['category'] = $validated['category'] ? ucfirst(trim($validated['category'])) : null;

        try {
            $roadmap->steps()->create([
                'title' => $validated['title'],
                'is_completed' => false,
                'priority' => $validated['priority'] ?? 'medium',
                'due_date' => $validated['due_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'progress' => 0,
            ]);

            return back()->with('success', ' Task added successfully!');
        } catch (\Exception $e) {
            Log::error('Step creation failed', [
                'user_id' => Auth::id(),
                'roadmap_id' => $roadmap->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to add task.');
        }
    }

    public function destroy(Roadmap $roadmap): RedirectResponse
    {
        // ✅ ESSENTIAL: Ownership check
        if ($roadmap->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $roadmap->steps()->delete();
            $roadmap->delete();
            return back()->with('success', 'Roadmap deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Roadmap delete failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete roadmap.');
        }
    }

    public function toggleStep(RoadmapStep $step): RedirectResponse
    {
        // ✅ ESSENTIAL: Ownership check
        if ($step->roadmap->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $step->update([
                'is_completed' => !$step->is_completed,
                'progress' => $step->is_completed ? 0 : 100,
            ]);
            return back()->with('success', 'Task status updated.');
        } catch (\Exception $e) {
            Log::error('Step toggle failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update task.');
        }
    }

    public function updateStep(Request $request, RoadmapStep $step): RedirectResponse
    {
        // ✅ ESSENTIAL: Ownership check
        if ($step->roadmap->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // ✅ ESSENTIAL: Input validation
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'priority' => 'nullable|in:high,medium,low',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $validated['title'] = strip_tags(trim($validated['title']));
        $validated['description'] = $validated['description'] ? strip_tags(trim($validated['description'])) : null;
        $validated['category'] = $validated['category'] ? ucfirst(trim($validated['category'])) : null;

        try {
            $step->update($validated);
            return back()->with('success', ' Task updated!');
        } catch (\Exception $e) {
            Log::error('Step update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', ' Failed to update task.');
        }
    }

    public function destroyStep(RoadmapStep $step): RedirectResponse
    {
        // ✅ ESSENTIAL: Ownership check
        if ($step->roadmap->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $step->delete();
            return back()->with('success', 'Task deleted.');
        } catch (\Exception $e) {
            Log::error('Step delete failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete task.');
        }
    }
}