<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;

describe('Commenter Trait', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();
    });

    it('returns all comments by user', function () {
        $comment = new Comment;
        $comment->comment = 'User comment';
        $comment->commenter()->associate($this->user);
        $comment->commentable()->associate($this->post);
        $comment->save();

        expect($this->user->comments)->toHaveCount(1)
            ->and($this->user->comments->first()->comment)->toBe('User comment');
    });

    it('returns approved comments by user', function () {
        $approved = new Comment;
        $approved->comment = 'Approved';
        $approved->approved = true;
        $approved->commenter()->associate($this->user);
        $approved->commentable()->associate($this->post);
        $approved->save();

        $unapproved = new Comment;
        $unapproved->comment = 'Unapproved';
        $unapproved->approved = false;
        $unapproved->commenter()->associate($this->user);
        $unapproved->commentable()->associate($this->post);
        $unapproved->save();

        expect($this->user->approvedComments()->get())->toHaveCount(1)
            ->and($this->user->approvedComments()->first()->comment)->toBe('Approved');
    });

    it('returns unapproved comments by user', function () {
        $approved = new Comment;
        $approved->comment = 'Approved';
        $approved->approved = true;
        $approved->commenter()->associate($this->user);
        $approved->commentable()->associate($this->post);
        $approved->save();

        $unapproved = new Comment;
        $unapproved->comment = 'Unapproved';
        $unapproved->approved = false;
        $unapproved->commenter()->associate($this->user);
        $unapproved->commentable()->associate($this->post);
        $unapproved->save();

        expect($this->user->approvedComments(false)->get())->toHaveCount(1)
            ->and($this->user->approvedComments(false)->first()->comment)->toBe('Unapproved');
    });

    it('provides scope to find users with approved comments', function () {
        $userWithApproved = UserModel::factory()->create();
        $userWithoutComments = UserModel::factory()->create();

        $comment = new Comment;
        $comment->comment = 'Approved';
        $comment->approved = true;
        $comment->commenter()->associate($userWithApproved);
        $comment->commentable()->associate($this->post);
        $comment->save();

        $result = UserModel::query()->approvedComments()->pluck('id')->toArray();

        expect($result)->toContain($userWithApproved->id)
            ->and($result)->not->toContain($userWithoutComments->id);
    });
});
