<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EditProfileController extends Controller
{
    /**
     * Show the edit profile form
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia::render('EditProfile', [
            'user' => [
                'name' => $user->name,
                'bio' => $user->bio ?? '',
                'profile_photo_url' => $user->profile_photo_url,
            ],
        ]);
    }

    /**
     * Update name, bio, and optionally the profile photo
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|file|image|max:5120',
        ]);

        $user->name = $validated['name'];
        $user->bio = $validated['bio'] ?? null;

        if ($request->hasFile('photo')) {
            // Delete old photo if it was not the default
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return redirect()->route('profile.detail')->with('success', 'Profile updated!');
    }
}