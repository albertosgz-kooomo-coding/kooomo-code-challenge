<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListCommentsForGivenPostPublicTest extends TestCase
{
    use RefreshDatabase;

    const URL_HOSTNAME = 'http://kooomo-code-challenge.test';

    /**
     * @group post
     * @group comment
     */
    public function test_see_comments_of_public_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comments = Comment::factory()
            ->count(10)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments')
            ->assertStatus(200)
            ->assertFetchedMany($comments);
    }

    /**
     * @group post
     * @group comment
     */
    public function test_see_comments_of_public_post_paginated()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comments = Comment::factory()
            ->count(4)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments?page[number]=2&page[size]=2')
            ->assertStatus(200)
            ->assertFetchedMany($comments->slice(2));
    }

    /**
     * @group post
     * @group comment
     */
    public function test_cannot_see_unpublished_comments_of_public_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comments = Comment::factory()
            ->count(10)
            ->for($user, 'author')
            ->for($post)
            ->sequence(fn($sequence) => [
                'is_published' => $sequence->index > 4,
            ])
            ->create();

        $this
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments?page[number]=1&page[size]=10')
            ->assertStatus(200)
            ->assertFetchedMany($comments->slice(5));
    }

    /**
     * @group post
     * @group comment
     */
    public function test_cannot_see_comments_of_protected_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => false]);
        $comments = Comment::factory()
            ->count(10)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments?page[number]=1&page[size]=10')
            ->assertStatus(401);
    }

    /**
     * @group post
     * @group comment
     */
    public function test_cannot_see_comments_from_other_public_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        $comments = Comment::factory()
            ->count(2)
            ->for($user, 'author')
            ->for($post)
            ->create(['is_published' => true]);
        $otherPost = Post::factory()
            ->for($user, 'author')
            ->create(['is_published' => true]);
        Comment::factory()
            ->count(2)
            ->for($user, 'author')
            ->for($otherPost)
            ->create(['is_published' => true]);

        $this
            ->jsonApi()
            ->expects('comments')
            ->get('/api/v1/posts/' . $post->getRouteKey() . '/relationships/comments?page[number]=1&page[size]=10')
            ->assertStatus(200)
            ->assertFetchedManyExact($comments);
    }
}
