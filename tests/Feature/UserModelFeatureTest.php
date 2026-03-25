<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;

describe('Testing Commentable trait on UserModel', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create();
        $this->otherUser = UserModel::factory()->create();
        $this->post = PostModel::factory()->create([
            'name' => 'Test Post',
        ]);
    });

    it('user can add comments to a post', function () {
        $comment = $this->post->comments()->create([
            'comment' => 'Test comment',
        ]);
        $comment->commenter()->associate($this->user);
        $comment->save();

        expect($comment)->toBeInstanceOf(Comment::class)
            ->and($this->post->comments)->toHaveCount(1)
            ->and($this->post->comments->first()->comment)->toBe('Test comment');
    });

    it('user can add nested comments', function () {
        $parentComment = $this->post->comments()->create([
            'comment' => 'Parent comment',
        ]);
        $parentComment->commenter()->associate($this->user);
        $parentComment->save();

        $reply = $this->post->comments()->create([
            'comment' => 'Reply to parent',
        ]);
        $reply->commenter()->associate($this->otherUser);
        $reply->save();
        $reply->parent()->associate($parentComment);
        $reply->save();

        expect($parentComment->children)->toHaveCount(1)
            ->and($reply->parent->id)->toBe($parentComment->id);
    });

    it('can get latest comments', function () {
        $oldComment = $this->post->comments()->create([
            'comment' => 'Old comment',
        ]);
        $oldComment->created_at = now()->subDays(2);
        $oldComment->commenter()->associate($this->user);
        $oldComment->save();

        $newComment = $this->post->comments()->create([
            'comment' => 'New comment',
        ]);
        $newComment->created_at = now();
        $newComment->commenter()->associate($this->user);
        $newComment->save();

        $latestComments = $this->post->latestComments(1);

        expect($latestComments)->toHaveCount(1)
            ->and($latestComments->first()->comment)->toBe('New comment');
    });

    it('can get most commented posts', function () {
        $post2 = PostModel::factory()->create(['name' => 'Test Post 2']);
        $post3 = PostModel::factory()->create(['name' => 'Test Post 3']);

        $this->post->comments()->createMany([
            [
                'comment'        => 'Comment 1',
                'commenter_id'   => $this->user->id,
                'commenter_type' => UserModel::class,
            ],
            [
                'comment'        => 'Comment 2',
                'commenter_id'   => $this->user->id,
                'commenter_type' => UserModel::class,
            ],
        ]);
        $post2->comments()->create([
            'comment'        => 'Comment 3',
            'commenter_id'   => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);

        $mostCommented = PostModel::mostCommented(2);

        expect($mostCommented)->toHaveCount(2)
            ->and($mostCommented->first()->id)->toBe($this->post->id)
            ->and($mostCommented->first()->comments_count)->toBe(2);
    });

    it('can get comments with replies', function () {
        $parentComment = $this->post->comments()->create([
            'comment'        => 'Parent comment',
            'commenter_id'   => $this->user->id,
            'commenter_type' => UserModel::class,
            'approved'       => true,
        ]);

        $reply = new Comment();
        $reply->comment = 'Reply';
        $reply->commenter()->associate($this->otherUser);
        $reply->commentable()->associate($this->post);
        $reply->parent()->associate($parentComment);
        $reply->approved = false;
        $reply->save();

        $commentsWithReplies = $this->post->commentsWithReplies();

        expect($commentsWithReplies)->toHaveCount(1)
            ->and($commentsWithReplies->first()->children)->toHaveCount(1)
            ->and($commentsWithReplies->first()->children->first()->comment)->toBe('Reply')
            ->and($commentsWithReplies->first()->children->first()->approved)->toBe(false);
    });

    it('can get total comments count', function () {
        $this->post->comments()->createMany([
            ['comment' => 'Comment 1', 'commenter_id' => $this->user->id, 'commenter_type' => UserModel::class],
            ['comment' => 'Comment 2', 'commenter_id' => $this->user->id, 'commenter_type' => UserModel::class],
            ['comment' => 'Comment 3', 'commenter_id' => $this->user->id, 'commenter_type' => UserModel::class],
        ]);

        expect($this->post->totalComments())->toBe(3);
    });

    it('can get comments by specific user', function () {
        $comments = $this->post->comments()->createMany([
            ['comment' => 'User 1 comment'],
            ['comment' => 'User 2 comment'],
        ]);
        $comments[0]->commenter()->associate($this->user);
        $comments[0]->save();
        $comments[1]->commenter()->associate($this->otherUser);
        $comments[1]->save();

        $userComments = $this->post->commentsByUser($this->user->id, UserModel::class)->get();

        expect($userComments)->toHaveCount(1)
            ->and($userComments->first()->comment)->toBe('User 1 comment');
    });

    it('can get comments in date range', function () {
        $oldComment = $this->post->comments()->create([
            'comment'        => 'Old comment',
            'commenter_id'   => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);
        $oldComment->created_at = '2024-01-01';
        $oldComment->save();

        $newComment = $this->post->comments()->create([
            'comment'        => 'New comment',
            'commenter_id'   => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);
        $newComment->created_at = '2024-03-01';
        $newComment->save();

        $commentsInRange = $this->post->commentsInDateRange('2024-02-01', '2024-03-31')->get();

        expect($commentsInRange)->toHaveCount(1)
            ->and($commentsInRange->first()->comment)->toBe('New comment');
    });

    it('can get comments with specific attributes', function () {
        $this->post->comments()->createMany([
            [
                'comment'        => 'Approved comment',
                'commenter_id'   => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved'       => true,
            ],
            [
                'comment'        => 'Unapproved comment',
                'commenter_id'   => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved'       => false,
            ],
        ]);

        $approvedComments = $this->post->commentsWithAttributes(['approved' => true])->get();

        expect($approvedComments)->toHaveCount(1)
            ->and($approvedComments->first()->comment)->toBe('Approved comment');
    });

    it('can get comments with relations', function () {
        $this->post->comments()->create([
            'comment'        => 'Test comment',
            'commenter_id'   => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);

        $commentsWithRelations = $this->post->commentsWithRelations(['commenter', 'commentable'])->get();

        expect($commentsWithRelations)->toHaveCount(1)
            ->and($commentsWithRelations->first()->relationLoaded('commenter'))->toBeTrue()
            ->and($commentsWithRelations->first()->relationLoaded('commentable'))->toBeTrue();
    });

    it('comments are deleted when post is deleted', function () {
        $this->post->comments()->create([
            'comment'        => 'Test comment',
            'commenter_id'   => $this->user->id,
            'commenter_type' => UserModel::class,
        ]);

        $this->post->delete();

        expect($this->post->comments()->count())->toBe(0);
    });

    it('can get approved comments only', function () {
        $this->post->comments()->createMany([
            [
                'comment'        => 'Approved comment',
                'commenter_id'   => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved'       => true,
            ],
            [
                'comment'        => 'Unapproved comment',
                'commenter_id'   => $this->user->id,
                'commenter_type' => UserModel::class,
                'approved'       => false,
            ],
        ]);

        $approvedComments = $this->post->approvedComments()->get();

        expect($approvedComments)->toHaveCount(1)
            ->and($approvedComments->first()->comment)->toBe('Approved comment');
    });
});
