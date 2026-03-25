<?php

namespace Anil\Comments\Tests\Feature\Models;

use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;

describe('Testing Commentable trait on PostModel', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create();
        $this->post = PostModel::factory()->create([
            'name' => 'Test Post',
        ]);
    });

    test('it can create a comment on post', function () {
        $comment = $this->post->comments()->create([
            'comment' => 'Test comment',
            'commenter_id' => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);
        $comment->commenter()->associate($this->user);

        expect($comment->comment)->toBe('Test comment')
            ->and($comment->commenter_id)->toBe($this->user->id)
            ->and($comment->commenter_type)->toBe(UserModel::class)
            ->and($comment->commentable_id)->toBe($this->post->id)
            ->and($comment->commentable_type)->toBe(PostModel::class);
    });

    test('it can get all comments for a post', function () {
        $this->post->comments()->createMany([
            [
                'comment' => 'First comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
            ],
            [
                'comment' => 'Second comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
            ],
        ]);

        $comments = $this->post->comments;

        expect($comments)->toHaveCount(2)
            ->and($comments[0]->comment)->toBe('First comment')
            ->and($comments[1]->comment)->toBe('Second comment');
    });

    test('it can get approved comments only', function () {
        $this->post->comments()->createMany([
            [
                'comment' => 'Approved comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved' => true,
            ],
            [
                'comment' => 'Unapproved comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved' => false,
            ],
        ]);

        $approvedComments = $this->post->approvedComments;

        expect($approvedComments)->toHaveCount(1)
            ->and($approvedComments[0]->comment)->toBe('Approved comment');
    });

    test('it can get comments count', function () {
        $this->post->comments()->createMany([
            [
                'comment' => 'First comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
            ],
            [
                'comment' => 'Second comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
            ],
        ]);

        expect($this->post->comments()->count())->toBe(2);
    });

    test('it can get approved comments count', function () {
        $this->post->comments()->createMany([
            [
                'comment' => 'Approved comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved' => true,
            ],
            [
                'comment' => 'Unapproved comment',
                'commenter_id' => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved' => false,
            ],
        ]);

        expect($this->post->approvedCommentsCount())->toBe(1);
    });

    test('it can check if post has comments', function () {
        expect($this->post->hasComments())->toBe(0);

        $this->post->comments()->create([
            'comment' => 'Test comment',
            'commenter_id' => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);

        expect($this->post->hasComments())->toBe(1);
    });

    test('it can check if post has approved comments', function () {
        expect($this->post->hasApprovedComments())->toBeFalse();

        $this->post->comments()->create([
            'comment' => 'Test comment',
            'commenter_id' => $this->user->id,
            'commenter_type' => UserModel::class,
            'approved' => true,
        ]);

        expect($this->post->hasApprovedComments())->toBeTrue();
    });
});
