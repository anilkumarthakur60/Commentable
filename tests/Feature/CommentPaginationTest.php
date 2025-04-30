<?php

use Anil\Comments\Tests\TestSetup\Models\PostModel;
use Anil\Comments\Tests\TestSetup\Models\UserModel;

beforeEach(function () {
    $this->user = UserModel::factory()->create();
    $this->post = PostModel::factory()->create();
});

test('it can paginate comments', function () {
    config(['comments.pagination' => true]);

    // Create 3 parent comments
    for ($i = 1; $i <= 3; $i++) {
        $this->post->comments()->create([
            'message' => "Parent comment {$i}",
            'user_id' => $this->user->id,
        ]);
    }

    // Test first page with 2 items per page
    $response = $this->actingAs($this->user)
        ->getJson('/comments?model='.PostModel::class.'&id='.$this->post->id.'&perPage=2');

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonFragment(['message' => 'Parent comment 1'])
        ->assertJsonFragment(['message' => 'Parent comment 2'])
        ->assertJsonMissing(['message' => 'Parent comment 3']);

    // Test second page
    $response = $this->actingAs($this->user)
        ->getJson('/comments?model='.PostModel::class.'&id='.$this->post->id.'&perPage=2&page=2');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['message' => 'Parent comment 3']);
});

test('it respects maximum indentation level', function () {
    // Create a comment chain with 4 levels
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

    // Test with max indentation level of 2
    $response = $this->actingAs($this->user)
        ->getJson('/comments?model='.PostModel::class.'&id='.$this->post->id.'&maxIndentationLevel=2');

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Level 1'])
        ->assertJsonFragment(['message' => 'Level 2'])
        ->assertJsonFragment(['message' => 'Level 3'])
        ->assertJsonMissing(['message' => 'Level 4']);
});

test('it includes replies in pagination count', function () {
    config(['comments.pagination' => true]);

    // Create 2 parent comments
    $parent1 = $this->post->comments()->create([
        'message' => 'Parent 1',
        'user_id' => $this->user->id,
    ]);

    $parent2 = $this->post->comments()->create([
        'message' => 'Parent 2',
        'user_id' => $this->user->id,
    ]);

    // Add 3 replies to parent 1
    for ($i = 1; $i <= 3; $i++) {
        $this->post->comments()->create([
            'message' => "Reply {$i} to Parent 1",
            'user_id' => $this->user->id,
            'parent_id' => $parent1->id,
        ]);
    }

    // Test pagination with 1 item per page
    $response = $this->actingAs($this->user)
        ->getJson('/comments?model='.PostModel::class.'&id='.$this->post->id.'&perPage=1');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['message' => 'Parent 1'])
        ->assertJsonMissing(['message' => 'Parent 2']);

    // Verify that replies are included in the response
    $data = $response->json();
    expect($data[0]['replies'])->toHaveCount(3);
});
