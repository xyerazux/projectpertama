<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updatePriority(Request $request)
{
    $request->validate([
        'priority_mode' => 'required|in:auto,manual'
    ]);

    auth()->user()->update([
        'priority_mode' => $request->priority_mode
    ]);

    return back()->with('success', 'Mode prioritas berhasil diubah');
}

public function updatePhoto(Request $request)
{
    $request->validate([
        'photo' => 'required|image|max:2048',
    ]);

    $path = $request->file('photo')->store('profile-photos', 'public');

    $request->user()->update([
        'profile_photo' => $path,
    ]);

    return back()->with('status', 'Profile photo updated!');
}



}
