<?php

use Anil\Comments\Tests\TestSetup\Models\TagModel;

describe('Testing Commentable on TagModel', function () {
    it('can create a tag', function () {
        $tag = TagModel::create([
            'name' => 'Test Tag',
        ]);

        expect($tag)->toBeInstanceOf(TagModel::class);
        expect($tag->name)->toBe('Test Tag');
    });
});
