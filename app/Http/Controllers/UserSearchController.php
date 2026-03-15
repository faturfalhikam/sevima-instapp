<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    /**
     * Search users by name or email, excluding the current user
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', $request->user()->id)
            ->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->select('id', 'name', 'email', 'profile_photo_path')
            ->limit(15)
            ->get()
            ->map(fn($user) => [
        'id' => $user->id,
        'name' => $user->name,
        'profile_photo_url' => $user->profile_photo_path
        ? asset('storage/' . $user->profile_photo_path)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
        ]);

        return response()->json($users);
    }
}