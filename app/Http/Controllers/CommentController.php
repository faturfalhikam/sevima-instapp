<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Get all comments for a post (with replies and like data)
     */
    public function getComments(Post $post)
    {
        $user = auth()->user();

        $comments = Comment::where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with([
            'user:id,name,profile_photo_path',
            'replies.user:id,name,profile_photo_path',
            'replies.likes',
            'likes',
        ])
            ->latest()
            ->get();

        $transformed = $comments->map(fn($comment) => $this->transformComment($comment, $user));

        return response()->json([
            'comments' => $transformed,
            'post' => [
                'id' => $post->id,
                'caption' => $post->caption,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'profile_photo_url' => $post->user->profile_photo_path
                    ? asset('storage/' . $post->user->profile_photo_path)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($post->user->name),
                ],
                'created_at' => $post->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Add a comment or reply to a post
     */
    public function addComment(Post $post, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load(['user:id,name,profile_photo_path', 'likes']);

        return response()->json([
            'comment' => $this->transformComment($comment, auth()->user()),
            'comments_count' => $post->comments()->whereNull('parent_id')->count(),
        ]);
    }

    /**
     * Toggle like on a comment
     */
    public function likeComment(Comment $comment)
    {
        $user = auth()->user();

        $like = CommentLike::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        }
        else {
            CommentLike::create([
                'user_id' => $user->id,
                'comment_id' => $comment->id,
            ]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }

    /**
     * Transform a comment model into the API response shape
     */
    private function transformComment(Comment $comment, $user): array
    {
        $likesCount = $comment->likes->count();
        $isLiked = $user ? $comment->likes->where('user_id', $user->id)->count() > 0 : false;

        $profileUrl = $comment->user->profile_photo_path
            ? asset('storage/' . $comment->user->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name);

        $replies = $comment->replies->map(function ($reply) use ($user) {
            $replyProfileUrl = $reply->user->profile_photo_path
                ? asset('storage/' . $reply->user->profile_photo_path)
                : 'https://ui-avatars.com/api/?name=' . urlencode($reply->user->name);

            return [
            'id' => $reply->id,
            'user' => [
            'id' => $reply->user->id,
            'name' => $reply->user->name,
            'profile_photo_url' => $replyProfileUrl,
            ],
            'content' => $reply->content,
            'created_at' => $reply->created_at->diffForHumans(),
            'likes_count' => $reply->likes->count(),
            'is_liked' => $user ? $reply->likes->where('user_id', $user->id)->count() > 0 : false,
            'parent_id' => $reply->parent_id,
            ];
        });

        return [
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'profile_photo_url' => $profileUrl,
            ],
            'content' => $comment->content,
            'created_at' => $comment->created_at->diffForHumans(),
            'likes_count' => $likesCount,
            'is_liked' => $isLiked,
            'parent_id' => $comment->parent_id,
            'replies' => $replies,
        ];
    }
}