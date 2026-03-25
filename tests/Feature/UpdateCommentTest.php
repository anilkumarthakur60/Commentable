<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

describe('Update comment', function () {
    beforeEach(function () {
        $this->withoutMiddleware([ProtectAgainstSpam::class]);
        $this->user = UserModel::factory()->create();
        $this->otherUser = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();

        $this->comment = new Comment;
        $this->comment->comment = 'Original comment';
        $this->comment->commenter()->associate($this->user);
        $this->comment->commentable()->associate($this->post);
        $this->comment->approved = true;
        $this->comment->save();
    });

    it('allows the author to update their comment', function () {
        $response = $this->actingAs($this->user)
            ->putJson("/comments/{$this->comment->id}", [
                'message' => 'Updated comment',
            ]);

        $response->assertStatus(200)
            ->assertJson(['data' => ['comment' => 'Updated comment']]);

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'comment' => 'Updated comment',
        ]);
    });

    it('prevents other users from updating the comment', function () {
        $response = $this->actingAs($this->otherUser)
            ->putJson("/comments/{$this->comment->id}", [
                'message' => 'Hacked comment',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'comment' => 'Original comment',
        ]);
    });

    it('prevents unauthenticated users from updating', function () {
        $response = $this->putJson("/comments/{$this->comment->id}", [
            'message' => 'Anonymous edit',
        ]);

        $response->assertStatus(403);
    });

    it('validates that message is required', function () {
        $response = $this->actingAs($this->user)
            ->putJson("/comments/{$this->comment->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('uses custom validation rules from config', function () {
        Config::set('comments.validation.message', ['required', 'string', 'min:10']);

        $response = $this->actingAs($this->user)
            ->putJson("/comments/{$this->comment->id}", [
                'message' => 'Short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('returns redirect for non-JSON requests', function () {
        $response = $this->actingAs($this->user)
            ->from('/posts/1')
            ->put("/comments/{$this->comment->id}", [
                'message' => 'Updated via form',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    });
});
