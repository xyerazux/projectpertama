<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReflection;
use Illuminate\Http\Request;

class ReflectionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|string|in:bad,okay,great',
            'note' => 'nullable|string|max:1000',
        ]);

        $reflection = DailyReflection::create([
            'user_id' => $request->user()->id,
            'mood' => $request->mood,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Reflection saved successfully',
            'data' => $reflection
        ], 201);
    }
}
