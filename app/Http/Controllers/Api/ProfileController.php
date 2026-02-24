<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'priority_mode' => $user->priority_mode,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $user->name = strip_tags(trim($request->name));
        $user->email = strtolower(trim($request->email));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated!',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'priority_mode' => $user->priority_mode,
            ],
        ]);
    }

    public function updatePriority(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'priority_mode' => 'required|in:auto,manual',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        Auth::user()->update(['priority_mode' => $request->priority_mode]);

        return response()->json([
            'success' => true,
            'message' => 'Priority mode updated!',
            'data' => ['priority_mode' => $request->priority_mode],
        ]);
    }
}
