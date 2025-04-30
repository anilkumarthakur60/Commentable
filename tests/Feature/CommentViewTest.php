<?php

use Anil\Comments\Tests\TestCase;
use Anil\Comments\Tests\TestSetup\Models\PostModel;
use Anil\Comments\Tests\TestSetup\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class)->in('Feature');
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->post = PostModel::factory()->create();
});

test('it displays comments component for guest', function () {
    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post', $this->post)
        ->assertSee('@comments')
        ->assertSee('comments-container')
        ->assertSee('comments-form');
});

test('it displays comments component for authenticated user', function () {
    $response = $this->actingAs($this->user)
        ->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post', $this->post)
        ->assertSee('@comments')
        ->assertSee('comments-container')
        ->assertSee('comments-form')
        ->assertSee('user-avatar');
});

test('it displays only approved comments when configured', function () {
    config(['comments.approval_required' => true]);

    // Create approved and unapproved comments
    $this->post->comments()->create([
        'message' => 'Approved comment',
        'user_id' => $this->user->id,
        'approved' => true,
    ]);

    $this->post->comments()->create([
        'message' => 'Unapproved comment',
        'user_id' => $this->user->id,
        'approved' => false,
    ]);

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('Approved comment')
        ->assertDontSee('Unapproved comment');
});

test('it displays paginated comments', function () {
    config(['comments.pagination' => true]);

    // Create multiple comments
    for ($i = 1; $i <= 5; $i++) {
        $this->post->comments()->create([
            'message' => "Comment {$i}",
            'user_id' => $this->user->id,
        ]);
    }

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('Comment 1')
        ->assertSee('Comment 2')
        ->assertSee('Comment 3')
        ->assertDontSee('Comment 4')
        ->assertDontSee('Comment 5')
        ->assertSee('pagination');
});

test('it displays nested comments with proper indentation', function () {
    // Create a comment chain
    $parent = $this->post->comments()->create([
        'message' => 'Parent comment',
        'user_id' => $this->user->id,
    ]);

    $child = $this->post->comments()->create([
        'message' => 'Child comment',
        'user_id' => $this->user->id,
        'parent_id' => $parent->id,
    ]);

    $grandchild = $this->post->comments()->create([
        'message' => 'Grandchild comment',
        'user_id' => $this->user->id,
        'parent_id' => $child->id,
    ]);

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('Parent comment')
        ->assertSee('Child comment')
        ->assertSee('Grandchild comment')
        ->assertSee('comment-replies')
        ->assertSee('comment-nested');
});

test('it respects maximum indentation level', function () {
    // Create a deep comment chain
    $level1 = $this->post->comments()->create([
        'message' => 'Level 1',
        'user_id' => $this->user->id,
    ]);

    $level2 = $this->post->comments()->create([
        'message' => 'Level 2',
        'user_id' => $this->user->id,
        'parent_id' => $level1->id,
    ]);

    $level3 = $this->post->comments()->create([
        'message' => 'Level 3',
        'user_id' => $this->user->id,
        'parent_id' => $level2->id,
    ]);

    $level4 = $this->post->comments()->create([
        'message' => 'Level 4',
        'user_id' => $this->user->id,
        'parent_id' => $level3->id,
    ]);

    $response = $this->get(route('posts.show', [
        'post' => $this->post,
        'maxIndentationLevel' => 2,
    ]));

    $response->assertStatus(200)
        ->assertSee('Level 1')
        ->assertSee('Level 2')
        ->assertSee('Level 3')
        ->assertDontSee('Level 4');
});

test('it displays edit form for comment owner', function () {
    $comment = $this->post->comments()->create([
        'message' => 'Original comment',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('edit-comment-form')
        ->assertSee('Original comment')
        ->assertSee('Update Comment');
});

test('it does not display edit form for non owners', function () {
    $otherUser = UserModel::factory()->create();
    $comment = $this->post->comments()->create([
        'message' => 'Original comment',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($otherUser)
        ->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertDontSee('edit-comment-form')
        ->assertSee('Original comment')
        ->assertDontSee('Update Comment');
});

test('it displays guest comment form when enabled', function () {
    config(['comments.guest_commenting' => true]);

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('guest-name')
        ->assertSee('guest-email')
        ->assertSee('comments-form');
});

test('it does not display guest comment form when disabled', function () {
    config(['comments.guest_commenting' => false]);

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertDontSee('guest-name')
        ->assertDontSee('guest-email')
        ->assertSee('Please login to comment');
});

test('it displays comment count', function () {
    // Create multiple comments
    for ($i = 1; $i <= 3; $i++) {
        $this->post->comments()->create([
            'message' => "Comment {$i}",
            'user_id' => $this->user->id,
        ]);
    }

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('3 Comments')
        ->assertSee('comments-count');
});

test('it displays comment timestamps', function () {
    $comment = $this->post->comments()->create([
        'message' => 'Test comment',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.show', $this->post));

    $response->assertStatus(200)
        ->assertSee('comment-timestamp')
        ->assertSee($comment->created_at->diffForHumans());
});
