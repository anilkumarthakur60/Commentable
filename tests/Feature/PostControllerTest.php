<?php

use Anil\Comments\Tests\TestSetup\Models\PostModel;
use Anil\Comments\Tests\TestSetup\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Honeypot\ProtectAgainstSpam;

describe('testing comments on routes', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $this->post = PostModel::factory()->create([
            'name' => 'Test Post',
        ]);

        // Disable Honeypot middleware for tests
        $this->withoutMiddleware([ProtectAgainstSpam::class]);
    });

    it('can store a comment as authenticated user', function () {
        Gate::define('create-comment', fn($user) => true);

        $response = $this->actingAs($this->user)
            ->postJson('/comments', [
                'commentable_type' => PostModel::class,
                'commentable_id' => $this->post->id,
                'message' => 'Test comment',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'comment' => 'Test comment',
                    'commenter_id' => $this->user->id,
                    'commenter_type' => UserModel::class,
                    'commentable_id' => $this->post->id,
                    'commentable_type' => PostModel::class,
                    'approved' => true,
                    'child_id' => null,
                    'guest_name' => null,
                    'guest_email' => null,
                    'commenter' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ],
                    'commentable' => [
                        'id' => $this->post->id,
                        'name' => $this->post->name,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'Test comment',
            'commenter_id' => $this->user->id,
            'commenter_type' => UserModel::class,
            'commentable_id' => $this->post->id,
            'commentable_type' => PostModel::class,
            'approved' => true,
        ]);
    });

    it('can store a comment as guest when guest commenting is enabled', function () {
        Config::set('comments.guest_commenting', true);

        $response = $this->postJson('/comments', [
            'commentable_type' => PostModel::class,
            'commentable_id' => $this->post->id,
            'message' => 'Guest comment',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'comment' => 'Guest comment',
                    'guest_name' => 'John Doe',
                    'guest_email' => 'john@example.com',
                    'commentable_id' => $this->post->id,
                    'commentable_type' => PostModel::class,
                    'commenter_id' => null,
                    'commenter_type' => null,
                    'approved' => true,
                    'child_id' => null,
                    'commenter' => null,
                    'commentable' => [
                        'id' => $this->post->id,
                        'name' => $this->post->name,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'Guest comment',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'commentable_id' => $this->post->id,
            'commentable_type' => PostModel::class,
            'approved' => true,
        ]);
    });

    it('cannot store a comment as guest when guest commenting is disabled', function () {
        Config::set('comments.guest_commenting', false);

        $response = $this->postJson('/comments', [
            'commentable_type' => PostModel::class,
            'commentable_id' => $this->post->id,
            'message' => 'Guest comment',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
        ]);

        $response->assertStatus(403);
    });

    it('validates required fields for authenticated user', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/comments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'commentable_type',
                'commentable_id',
                'message',
            ]);
    });

    it('validates required fields for guest user', function () {
        Config::set('comments.guest_commenting', true);

        $response = $this->postJson('/comments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'commentable_type',
                'commentable_id',
                'message',
                'guest_name',
                'guest_email',
            ]);
    });

    it('validates guest email format', function () {
        Config::set('comments.guest_commenting', true);

        $response = $this->postJson('/comments', [
            'commentable_type' => PostModel::class,
            'commentable_id' => $this->post->id,
            'message' => 'Guest comment',
            'guest_name' => 'John Doe',
            'guest_email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_email']);
    });

    it('validates commentable model exists', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/comments', [
                'commentable_type' => PostModel::class,
                'commentable_id' => 999999,
                'message' => 'Test comment',
            ]);

        $response->assertStatus(404);
    });

    it('sets comment as approved when approval is not required', function () {
        Config::set('comments.approval_required', false);

        $response = $this->actingAs($this->user)
            ->postJson('/comments', [
                'commentable_type' => PostModel::class,
                'commentable_id' => $this->post->id,
                'message' => 'Test comment',
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['approved' => true]]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'Test comment',
            'approved' => true,
        ]);
    });

    it('sets comment as unapproved when approval is required', function () {
        Config::set('comments.approval_required', true);

        $response = $this->actingAs($this->user)
            ->postJson('/comments', [
                'commentable_type' => PostModel::class,
                'commentable_id' => $this->post->id,
                'message' => 'Test comment',
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['approved' => false]]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'Test comment',
            'approved' => false,
        ]);
    });

    it('authorizes comment creation when guest commenting is disabled', function () {
        Config::set('comments.guest_commenting', false);

        Gate::define('create-comment', function ($user) {
            return false;
        });

        $response = $this->actingAs($this->user)
            ->postJson('/comments', [
                'commentable_type' => PostModel::class,
                'commentable_id' => $this->post->id,
                'message' => 'Test comment',
            ]);

        $response->assertStatus(403);
    });

    it('runs afterCreate on model if method exists', function () {
        $post = new class extends PostModel {
            public function afterCreate()
            {
                $this->update(['name' => 'Updated after comment']);
            }
        };
        $post->fill(['name' => 'Original Name']);
        $post->save();

        $response = $this->actingAs($this->user)
            ->postJson('/comments', [
                'commentable_type' => get_class($post),
                'commentable_id' => $post->id,
                'message' => 'Test comment',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'name' => 'Updated after comment',
        ]);
    });

    // it('rolls back transaction on error', function () {
    //     // Mock DB transaction to throw an exception
    //     DB::shouldReceive('beginTransaction')->once();
    //     DB::shouldReceive('rollBack')->once();
    //     DB::shouldReceive('commit')->never();

    //     $response = $this->actingAs($this->user)
    //         ->postJson('/comments', [
    //             'commentable_type' => 'akjs',
    //             'commentable_id' => $this->post->id,
    //             'message' => str_repeat('a', 4294), // 4GB + 1 byte
    //         ]);

    //     $response->assertStatus(500);
    // });
});
