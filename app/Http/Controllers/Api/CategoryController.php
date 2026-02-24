<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())
            ->withCount('tasks')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function show(Category $category)
    {
        if ($category->user_id !== Auth::id())
            abort(403);
        return response()->json(['success' => true, 'data' => $category]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $category = Category::create([
            'user_id' => Auth::id(),
            'name' => strip_tags(trim($request->name)),
        ]);

        return response()->json(['success' => true, 'message' => 'Category created!', 'data' => $category], 201);
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== Auth::id())
            abort(403);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $category->update(['name' => strip_tags(trim($request->name))]);

        return response()->json(['success' => true, 'message' => 'Category updated!', 'data' => $category]);
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== Auth::id())
            abort(403);

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted!']);
    }
}
