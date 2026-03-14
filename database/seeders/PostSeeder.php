<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create data
        $users = \App\Models\User::factory(5)->create();

        // Create posts for each user
        foreach ($users as $user) {
            \App\Models\Post::factory(3)
                ->for($user)
                ->create()
                ->each(function ($post) {
                    // Create 1-2 images per post
                    \App\Models\PostImage::factory(rand(1, 2))
                        ->for($post)
                        ->create();

                    // Create 2-5 comments per post
                    $commentUsers = \App\Models\User::all()->random(rand(2, min(5, count(\App\Models\User::all()))));
                    foreach ($commentUsers as $commentUser) {
                        \App\Models\Comment::create([
                            'post_id' => $post->id,
                            'user_id' => $commentUser->id,
                            'content' => fake()->sentences(1, true),
                        ]);
                    }

                    // Create 3-10 likes per post
                    $likeUsers = \App\Models\User::all()->random(rand(3, min(10, count(\App\Models\User::all()))));
                    foreach ($likeUsers as $likeUser) {
                        \App\Models\Like::create([
                            'post_id' => $post->id,
                            'user_id' => $likeUser->id,
                        ]);
                    }
                });
        }

        // Create follow relationships
        foreach ($users as $user) {
            $followingUsers = $users->where('id', '!=', $user->id)->random(rand(2, 4));
            foreach ($followingUsers as $followingUser) {
                \App\Models\Follower::create([
                    'follower_id' => $user->id,
                    'following_id' => $followingUser->id,
                ]);
            }
        }
    }
}
