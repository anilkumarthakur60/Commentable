<?php

use Anil\Comments\Tests\TestSetup\Models\PostModel;
use Anil\Comments\Tests\TestSetup\Models\UserModel;

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->post = PostModel::factory()->create();
});

test('it can create a comment', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/comments', [
            'commentable_type' => PostModel::class,
            'commentable_id' => $this->post->id,
            'message' => 'Test comment',
        ]);

    $response->assertStatus(200);
    expect($response->json())->toBeArray();
    $this->assertDatabaseHas('comments', [
        'commentable_type' => PostModel::class,
        'commentable_id' => $this->post->id,
        'message' => 'Test comment',
        'user_id' => $this->user->id,
    ]);
});

test('it can update a comment', function () {
    $comment = $this->post->comments()->create([
        'message' => 'Original comment',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/comments/{$comment->id}", [
            'message' => 'Updated comment',
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'message' => 'Updated comment',
    ]);
});

test('it can delete a comment', function () {
    $comment = $this->post->comments()->create([
        'message' => 'Comment to delete',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/comments/{$comment->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

test('it can reply to a comment', function () {
    $parentComment = $this->post->comments()->create([
        'message' => 'Parent comment',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/comments/{$parentComment->id}", [
            'message' => 'Reply comment',
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', [
        'parent_id' => $parentComment->id,
        'message' => 'Reply comment',
        'user_id' => $this->user->id,
    ]);
});

test('it validates required fields when creating comment', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/comments', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['commentable_type', 'commentable_id', 'message']);
});

test('it validates required fields when updating comment', function () {
    $comment = $this->post->comments()->create([
        'message' => 'Original comment',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->putJson("/comments/{$comment->id}", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});
