<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

describe('Reply to comment', function () {
    beforeEach(function () {
        $this->withoutMiddleware([ProtectAgainstSpam::class]);
        $this->user = UserModel::factory()->create();
        $this->otherUser = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();

        $this->comment = new Comment;
        $this->comment->comment = 'Parent comment';
        $this->comment->commenter()->associate($this->user);
        $this->comment->commentable()->associate($this->post);
        $this->comment->approved = true;
        $this->comment->save();
    });

    it('allows another user to reply to a comment', function () {
        $response = $this->actingAs($this->otherUser)
            ->postJson("/comments/{$this->comment->id}", [
                'message' => 'Reply text',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'comment' => 'Reply text',
                    'child_id' => $this->comment->id,
                    'commenter_id' => $this->otherUser->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'Reply text',
            'child_id' => $this->comment->id,
            'commentable_id' => $this->post->id,
            'commentable_type' => PostModel::class,
        ]);
    });

    it('prevents self-reply when allow_self_reply is false', function () {
        Config::set('comments.allow_self_reply', false);

        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}", [
                'message' => 'Self-reply',
            ]);

        $response->assertStatus(403);
    });

    it('allows self-reply when allow_self_reply is true', function () {
        Config::set('comments.allow_self_reply', true);

        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}", [
                'message' => 'Self-reply allowed',
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['comment' => 'Self-reply allowed']]);
    });

    it('validates that message is required', function () {
        $response = $this->actingAs($this->otherUser)
            ->postJson("/comments/{$this->comment->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('sets reply as unapproved when approval is required', function () {
        Config::set('comments.approval_required', true);

        $response = $this->actingAs($this->otherUser)
            ->postJson("/comments/{$this->comment->id}", [
                'message' => 'Reply needing approval',
            ]);

        $response->assertStatus(201)
            ->assertJson(['data' => ['approved' => false]]);
    });

    it('associates reply with the same commentable as parent', function () {
        $response = $this->actingAs($this->otherUser)
            ->postJson("/comments/{$this->comment->id}", [
                'message' => 'Reply text',
            ]);

        $response->assertStatus(201);

        $reply = Comment::query()->where('child_id', $this->comment->id)->first();

        expect($reply->commentable_id)->toBe($this->post->id)
            ->and($reply->commentable_type)->toBe(PostModel::class);
    });

    it('returns redirect for non-JSON requests', function () {
        $response = $this->actingAs($this->otherUser)
            ->from('/posts/1')
            ->post("/comments/{$this->comment->id}", [
                'message' => 'Reply via form',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    });
});
