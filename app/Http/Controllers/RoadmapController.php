<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse; 
use Illuminate\View\View; 

class RoadmapController extends Controller
{
    public function index(): View
    {
        $roadmaps = auth()->user()->roadmaps()->with('steps')->orderBy('target_date', 'asc')->get();
        return view('roadmaps.index', compact('roadmaps'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:planned,in_progress,completed,on_hold',
            'target_date' => 'nullable|date',
        ]);

        auth()->user()->roadmaps()->create($request->all());

        return back()->with('success', 'Goal berhasil ditambahkan!');
    }

    public function destroy(Roadmap $roadmap): RedirectResponse
    {
        if ($roadmap->user_id !== auth()->id()) {
            abort(403);
        }

        $roadmap->delete();
        return back()->with('success', 'Goal berhasil dihapus');
    }

    public function storeStep(Request $request, Roadmap $roadmap): RedirectResponse
{
    $request->validate([
        'title' => 'required|string|max:255',
        'priority' => 'nullable|in:high,medium,low',
        'due_date' => 'nullable|date',
        'description' => 'nullable|string|max:1000',
        'category' => 'nullable|string|max:50',
    ]);
    
    $roadmap->steps()->create([
        'title' => $request->title,
        'is_completed' => false,
        'priority' => $request->priority ?? 'medium',
        'due_date' => $request->due_date,
        'description' => $request->description,
        'category' => $request->category,
        'progress' => 0,
    ]);

    return back()->with('success', 'Task berhasil ditambahkan!');
}

    public function toggleStep(RoadmapStep $step): RedirectResponse
{
    $step->update([
        'is_completed' => !$step->is_completed,
        'progress' => $step->is_completed ? 0 : 100,
    ]);

    return back()->with('success', 'Task updated!');
}
public function updateStep(Request $request, RoadmapStep $step): RedirectResponse
{
    // Security: Pastikan user hanya bisa edit task miliknya
    if ($step->roadmap->user_id !== auth()->id()) {
        abort(403);
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'priority' => 'nullable|in:high,medium,low',
        'due_date' => 'nullable|date',
        'description' => 'nullable|string|max:1000',
        'category' => 'nullable|string|max:50',
        'progress' => 'nullable|integer|min:0|max:100',
    ]);

    $step->update($request->only([
        'title', 'priority', 'due_date', 'description', 'category', 'progress'
    ]));

    return back()->with('success', 'Task updated successfully!');
}
// ✅ Method untuk hapus task/step saja
public function destroyStep(RoadmapStep $step): RedirectResponse
{
    if ($step->roadmap->user_id !== auth()->id()) {
        abort(403);
    }

    $step->delete();

    return back()->with('success', 'Task deleted successfully!');
}
           
}