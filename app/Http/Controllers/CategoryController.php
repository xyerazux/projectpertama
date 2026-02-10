<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', auth()->id())
        ->WithCount('tasks')
        ->latest()
        ->get();

        $taskPerCategory = Category::where('user_id', auth()->id())
            ->withCount('tasks')
            ->get();
        
        return view('categories.index', compact('categories'));

        
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|max:100'
        ]);

        Category::create([
            'user_id'=>auth()->id(),
            'name'=>$request->name
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function edit(Category $category)
    {
        if($category->user_id !== auth()->id()) {
            abort(403);
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name'=>'required|max:100'
        ]);

        $category->update([
            'name'=>$request->name
        ])->id(); {
            abort(403);
        }

        $category->delete();
        
        return back()->with('success', 'Kategori berhasil dihapus');

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diupdate');
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $category->delete();
        
        return back()->with('success', 'Kategori berhasil dihapus');
    }
}
