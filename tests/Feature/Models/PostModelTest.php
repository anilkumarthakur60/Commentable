<?php

namespace Anil\Comments\Tests\Feature\Models;

use Anil\Comments\Tests\TestSetup\Models\PostModel;

it('Testing Commentable on PostModel', function () {
    $post = PostModel::create([
        'name' => 'Test Post',
    ]);

    expect($post)->toBeInstanceOf(PostModel::class);
    expect($post->name)->toBe('Test Post');
});
