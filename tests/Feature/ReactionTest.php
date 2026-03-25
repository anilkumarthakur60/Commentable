<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Models\CommentReaction;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

describe('Reactions', function () {
    beforeEach(function () {
        $this->withoutMiddleware([ProtectAgainstSpam::class]);
        $this->user = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();

        $this->comment = new Comment();
        $this->comment->comment = 'Test comment';
        $this->comment->commenter()->associate($this->user);
        $this->comment->commentable()->associate($this->post);
        $this->comment->approved = true;
        $this->comment->save();
    });

    it('allows authenticated user to react to a comment', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", [
                'type' => 'like',
            ]);

        $response->assertOk()
            ->assertJson([
                'reactions'     => ['like' => 1, 'dislike' => 0],
                'user_reaction' => 'like',
            ]);

        $this->assertDatabaseHas('comment_reactions', [
            'comment_id' => $this->comment->id,
            'reactor_id' => $this->user->id,
            'type'       => 'like',
        ]);
    });

    it('toggles off when same reaction type is sent again', function () {
        // First, create a like
        $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", ['type' => 'like']);

        // Toggle off
        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", ['type' => 'like']);

        $response->assertOk()
            ->assertJson([
                'reactions'     => ['like' => 0, 'dislike' => 0],
                'user_reaction' => null,
            ]);

        $this->assertDatabaseMissing('comment_reactions', [
            'comment_id' => $this->comment->id,
            'reactor_id' => $this->user->id,
        ]);
    });

    it('switches reaction type when different type is sent', function () {
        // First, like
        $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", ['type' => 'like']);

        // Switch to dislike
        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", ['type' => 'dislike']);

        $response->assertOk()
            ->assertJson([
                'reactions'     => ['like' => 0, 'dislike' => 1],
                'user_reaction' => 'dislike',
            ]);

        expect(CommentReaction::where('comment_id', $this->comment->id)->count())->toBe(1);
    });

    it('rejects invalid reaction types', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", [
                'type' => 'love',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    it('returns 401 for unauthenticated users via JSON', function () {
        $response = $this->postJson("/comments/{$this->comment->id}/react", [
            'type' => 'like',
        ]);

        $response->assertStatus(403);
    });

    it('returns 404 when reactions are disabled', function () {
        Config::set('comments.reactions.enabled', false);

        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", [
                'type' => 'like',
            ]);

        $response->assertStatus(404);
    });

    it('supports custom reaction types from config', function () {
        Config::set('comments.reactions.types', ['like', 'dislike', 'love', 'laugh']);

        $response = $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", [
                'type' => 'love',
            ]);

        $response->assertOk()
            ->assertJson([
                'user_reaction' => 'love',
            ]);
    });

    it('tracks reactions from multiple users independently', function () {
        $otherUser = UserModel::factory()->create();

        $this->actingAs($this->user)
            ->postJson("/comments/{$this->comment->id}/react", ['type' => 'like']);

        $response = $this->actingAs($otherUser)
            ->postJson("/comments/{$this->comment->id}/react", ['type' => 'like']);

        $response->assertOk()
            ->assertJson([
                'reactions'     => ['like' => 2, 'dislike' => 0],
                'user_reaction' => 'like',
            ]);
    });
});
