<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function toggle(Subtask $subtask)
    {
        // Pastikan yang punya task yang bisa ganti
        if ($subtask->task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subtask->update([
            'is_completed' => !$subtask->is_completed
        ]);

        // Jika request datang dari AJAX/React, kirim JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $subtask->is_completed
            ]);
        }

        return back();
    }
}