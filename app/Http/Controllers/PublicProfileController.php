<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Follower;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicProfileController extends Controller
{
    /**
     * Show another user's public profile
     */
    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();

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

        $isFollowing = Follower::where('follower_id', $currentUser->id)
            ->where('following_id', $user->id)
            ->exists();

        return Inertia::render('PublicProfile', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'bio' => $user->bio ?? null,
                'profile_photo_url' => $user->profile_photo_url,
                'posts_count' => $posts->count(),
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
                'is_following' => $isFollowing,
            ],
            'posts' => $posts,
        ]);
    }

    /**
     * Toggle follow / unfollow for a user
     */
    public function toggleFollow(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return back();
        }

        $follower = Follower::where('follower_id', $currentUser->id)
            ->where('following_id', $user->id)
            ->first();

        if ($follower) {
            $follower->delete();
            $isFollowing = false;
        }
        else {
            Follower::create([
                'follower_id' => $currentUser->id,
                'following_id' => $user->id,
            ]);
            $isFollowing = true;
        }

        return back()->with('is_following', $isFollowing);
    }
}