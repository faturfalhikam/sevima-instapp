<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Show the Create Post page
     */
    public function create()
    {
        return Inertia::render('CreatePost');
    }

    /**
     * Store a new post with images
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => 'nullable|string|max:2200',
            'location' => 'nullable|string|max:255',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|file|image|max:10240',
        ]);

        // Create the post
        $post = Post::create([
            'user_id' => auth()->id(),
            'caption' => $validated['caption'] ?? null,
            'location' => $validated['location'] ?? null,
        ]);

        // Store each image
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('posts', 'public');

            PostImage::create([
                'post_id' => $post->id,
                'image_path' => $path,
                'order' => $index,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Post created!');
    }
}