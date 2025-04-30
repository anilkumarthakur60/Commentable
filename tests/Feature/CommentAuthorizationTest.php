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
        'message' => 'Original comment',
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/comments/{$comment->id}", [
            'message' => 'Updated comment',
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'message' => 'Original comment',
    ]);
});

test('it cannot delete other users comment', function () {
    $comment = $this->post->comments()->create([
        'message' => 'Comment to delete',
        'user_id' => $this->otherUser->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/comments/{$comment->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'message' => 'Comment to delete',
    ]);
});

test('it can only view approved comments when configured', function () {
    config(['comments.approval_required' => true]);

    $approvedComment = $this->post->comments()->create([
        'message' => 'Approved comment',
        'user_id' => $this->user->id,
        'approved' => true,
    ]);

    $unapprovedComment = $this->post->comments()->create([
        'message' => 'Unapproved comment',
        'user_id' => $this->user->id,
        'approved' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/comments?model='.PostModel::class.'&id='.$this->post->id.'&approved=true');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['message' => 'Approved comment'])
        ->assertJsonMissing(['message' => 'Unapproved comment']);
});

test('it can comment as guest when enabled', function () {
    config(['comments.guest_commenting' => true]);

    $response = $this->postJson('/comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'message' => 'Guest comment',
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'message' => 'Guest comment',
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
    ]);
});

test('it cannot comment as guest when disabled', function () {
    config(['comments.guest_commenting' => false]);

    $response = $this->postJson('/comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'message' => 'Guest comment',
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('comments', [
        'message' => 'Guest comment',
    ]);
});
