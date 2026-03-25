<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

describe('Delete comment', function () {
    beforeEach(function () {
        $this->withoutMiddleware([ProtectAgainstSpam::class]);
        $this->user = UserModel::factory()->create();
        $this->otherUser = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();

        $this->comment = new Comment();
        $this->comment->comment = 'To be deleted';
        $this->comment->commenter()->associate($this->user);
        $this->comment->commentable()->associate($this->post);
        $this->comment->approved = true;
        $this->comment->save();
    });

    it('allows the author to delete their comment', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson("/comments/{$this->comment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'id' => $this->comment->id,
        ]);
    });

    it('allows admin to delete any comment', function () {
        $admin = UserModel::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->deleteJson("/comments/{$this->comment->id}");

        $response->assertStatus(200);
    });

    it('prevents other non-admin users from deleting', function () {
        $response = $this->actingAs($this->otherUser)
            ->deleteJson("/comments/{$this->comment->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
        ]);
    });

    it('soft deletes when soft_deletes config is enabled', function () {
        Config::set('comments.soft_deletes', true);

        $response = $this->actingAs($this->user)
            ->deleteJson("/comments/{$this->comment->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('comments', [
            'id' => $this->comment->id,
        ]);
    });

    it('hard deletes when soft_deletes config is disabled', function () {
        Config::set('comments.soft_deletes', false);

        $response = $this->actingAs($this->user)
            ->deleteJson("/comments/{$this->comment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'id' => $this->comment->id,
        ]);
    });

    it('returns redirect for non-JSON requests', function () {
        $response = $this->actingAs($this->user)
            ->from('/posts/1')
            ->delete("/comments/{$this->comment->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
    });

    it('uses configurable admin attribute', function () {
        // By default 'is_admin' is used — otherUser is not admin
        $response = $this->actingAs($this->otherUser)
            ->deleteJson("/comments/{$this->comment->id}");

        $response->assertStatus(403);

        // Set admin_attribute to null to disable admin check entirely
        Config::set('comments.admin_attribute', null);
        $admin = UserModel::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->deleteJson("/comments/{$this->comment->id}");

        // Even with is_admin=true, should fail because admin detection is disabled
        $response->assertStatus(403);
    });
});
