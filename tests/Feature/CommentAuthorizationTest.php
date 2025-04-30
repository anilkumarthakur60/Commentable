<?php

namespace Anil\Comments\Tests\Feature;

use Anil\Comments\Tests\TestSetup\Models\PostModel;
use Anil\Comments\Tests\TestSetup\Models\UserModel;

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->otherUser = UserModel::factory()->create();
    $this->post = PostModel::factory()->create();
});

test('it cannot update other users comment', function () {
    $comment = $this->post->comments()->create([
        'comment' => 'Original comment',
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/comments/{$comment->id}", [
            'message' => 'Updated comment',
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'comment' => 'Original comment',
    ]);
});

test('it cannot delete other users comment', function () {
    $comment = $this->post->comments()->create([
        'comment' => 'Comment to delete',
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/comments/{$comment->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'comment' => 'Comment to delete',
    ]);
});

test('it can only view approved comments when configured', function () {
    config(['comments.approval_required' => true]);

    $approvedComment = $this->post->comments()->create([
        'comment' => 'Approved comment',
        'user_id' => $this->user->id,
        'approved' => true,
    ]);

    $unapprovedComment = $this->post->comments()->create([
        'comment' => 'Unapproved comment',
        'user_id' => $this->user->id,
        'approved' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('Approved comment')
        ->assertDontSee('Unapproved comment');
});

test('it can comment as guest when enabled', function () {
    config(['comments.guest_commenting' => true]);

    $response = $this->postJson('/comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'comment' => 'Guest comment',
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'comment' => 'Guest comment',
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
    ]);
});

test('it cannot comment as guest when disabled', function () {
    config(['comments.guest_commenting' => false]);

    $response = $this->postJson('/comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'comment' => 'Guest comment',
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('comments', [
        'comment' => 'Guest comment',
    ]);
});
