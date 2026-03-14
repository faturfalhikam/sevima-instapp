<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Follower;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedController extends Controller
{
    /**
     * Display the home feed page
     */
    public function index()
    {
        return Inertia::render('Dashboard');
    }

    /**
     * Get feed posts with infinite scroll
     */
    public function getFeed(Request $request)
    {
        $user = auth()->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to view the feed'
            ], 401);
        }

        $page = $request->query('page', 1);
        $perPage = 10;

        // Get all users that current user follows
        $followingIds = Follower::where('follower_id', $user->id)
            ->pluck('following_id')
            ->toArray();

        // Include own posts too
        $followingIds[] = $user->id;

        // Get posts from following + own posts
        $posts = Post::whereIn('user_id', $followingIds)
            ->with([
                'user:id,name,profile_photo_path',
                'images',
                'likes:id,user_id,post_id',
                'comments:id,user_id,post_id,content,created_at',
                'comments.user:id,name,profile_photo_path'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform posts for frontend
        $transformedPosts = $posts->map(function ($post) use ($user) {
            return [
                'id' => $post->id,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'profile_photo_url' => $post->user->profile_photo_path ?
                        asset('storage/' . $post->user->profile_photo_path) :
                        'https://ui-avatars.com/api/?name=' . urlencode($post->user->name),
                ],
                'caption' => $post->caption,
                'location' => $post->location,
                'images' => $post->images->map(fn($img) => [
                    'url' => asset('storage/' . $img->image_path),
                    'path' => $img->image_path,
                ])->toArray(),
                'likes_count' => $post->likes->count(),
                'is_liked' => $post->likes->where('user_id', $user->id)->count() > 0,
                'comments_count' => $post->comments->count(),
                'comments' => $post->comments->map(fn($comment) => [
                    'id' => $comment->id,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                ])->toArray(),
                'created_at' => $post->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'data' => $transformedPosts,
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'has_more' => $posts->hasMorePages(),
            ]
        ]);
    }

    /**
     * Like a post
     */
    public function likePost(Post $post)
    {
        $user = auth()->user();

        // Check if already liked
        $like = Like::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();
            return response()->json([
                'liked' => false,
                'likes_count' => $post->likes()->count()
            ]);
        }

        Like::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return response()->json([
            'liked' => true,
            'likes_count' => $post->likes()->count()
        ]);
    }

    /**
     * Add comment to post
     */
    public function addComment(Post $post, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user:id,name,profile_photo_path');

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
            'comments_count' => $post->comments()->count()
        ]);
    }

    /**
     * Follow a user
     */
    public function followUser($userId)
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $userId) {
            return response()->json([
                'message' => 'Cannot follow yourself'
            ], 422);
        }

        $follower = Follower::where('follower_id', $currentUser->id)
            ->where('following_id', $userId)
            ->first();

        if ($follower) {
            $follower->delete();
            return response()->json([
                'following' => false,
            ]);
        }

        Follower::create([
            'follower_id' => $currentUser->id,
            'following_id' => $userId,
        ]);

        return response()->json([
            'following' => true,
        ]);
    }
}
