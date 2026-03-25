<?php

use Anil\Comments\Events\CommentCreated;
use Anil\Comments\Events\CommentDeleted;
use Anil\Comments\Events\CommentUpdated;
use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

describe('Comment Events', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();
    });

    it('dispatches CommentCreated when a comment is created', function () {
        Event::fake([CommentCreated::class]);

        $comment = new Comment;
        $comment->comment = 'Test';
        $comment->commenter()->associate($this->user);
        $comment->commentable()->associate($this->post);
        $comment->save();

        Event::assertDispatched(CommentCreated::class, function ($event) use ($comment) {
            return $event->comment->id === $comment->id;
        });
    });

    it('dispatches CommentUpdated when a comment is updated', function () {
        $comment = new Comment;
        $comment->comment = 'Original';
        $comment->commenter()->associate($this->user);
        $comment->commentable()->associate($this->post);
        $comment->save();

        Event::fake([CommentUpdated::class]);

        $comment->update(['comment' => 'Updated']);

        Event::assertDispatched(CommentUpdated::class);
    });

    it('dispatches CommentDeleted when a comment is deleted', function () {
        $comment = new Comment;
        $comment->comment = 'To delete';
        $comment->commenter()->associate($this->user);
        $comment->commentable()->associate($this->post);
        $comment->save();

        Event::fake([CommentDeleted::class]);

        $comment->delete();

        Event::assertDispatched(CommentDeleted::class);
    });

    it('does not dispatch events when events are disabled in config', function () {
        Config::set('comments.events.enabled', false);

        Event::fake([CommentCreated::class, CommentUpdated::class, CommentDeleted::class]);

        $comment = new Comment;
        $comment->comment = 'Test';
        $comment->commenter()->associate($this->user);
        $comment->commentable()->associate($this->post);
        $comment->save();

        $comment->update(['comment' => 'Updated']);
        $comment->delete();

        Event::assertNotDispatched(CommentCreated::class);
        Event::assertNotDispatched(CommentUpdated::class);
        Event::assertNotDispatched(CommentDeleted::class);
    });
});
