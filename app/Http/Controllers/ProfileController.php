<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the current authenticated user's profile page
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Load posts with their first image, sorted newest first
        $posts = Post::where('user_id', $user->id)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->latest()
            ->get()
            ->map(fn($post) => [
        'id' => $post->id,
        'caption' => $post->caption,
        'created_at' => $post->created_at->diffForHumans(),
        'images' => $post->images->map(fn($img) => [
        'url' => asset('storage/' . $img->image_path),
        ])->values(),
        ]);

        return Inertia::render('UserProfile', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? explode(' ', $user->name)[0],
                'bio' => $user->bio ?? null,
                'profile_photo_url' => $user->profile_photo_url,
                'posts_count' => $posts->count(),
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ],
            'posts' => $posts,
        ]);
    }
}