<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Models\CommentReaction;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;

describe('Comment Model', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $this->post = PostModel::factory()->create();
    });

    describe('relationships', function () {
        it('belongs to a commenter (morphTo)', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            $comment->refresh();

            expect($comment->commenter)->toBeInstanceOf(UserModel::class)
                ->and($comment->commenter->id)->toBe($this->user->id);
        });

        it('belongs to a commentable (morphTo)', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            $comment->refresh();

            expect($comment->commentable)->toBeInstanceOf(PostModel::class)
                ->and($comment->commentable->id)->toBe($this->post->id);
        });

        it('has many children (replies)', function () {
            $parent = new Comment;
            $parent->comment = 'Parent';
            $parent->commenter()->associate($this->user);
            $parent->commentable()->associate($this->post);
            $parent->save();

            $child = new Comment;
            $child->comment = 'Child';
            $child->commenter()->associate($this->user);
            $child->commentable()->associate($this->post);
            $child->parent()->associate($parent);
            $child->save();

            $parent->refresh();

            expect($parent->children)->toHaveCount(1)
                ->and($parent->children->first()->comment)->toBe('Child');
        });

        it('belongs to a parent comment', function () {
            $parent = new Comment;
            $parent->comment = 'Parent';
            $parent->commenter()->associate($this->user);
            $parent->commentable()->associate($this->post);
            $parent->save();

            $child = new Comment;
            $child->comment = 'Child';
            $child->commenter()->associate($this->user);
            $child->commentable()->associate($this->post);
            $child->parent()->associate($parent);
            $child->save();

            $child->refresh();

            expect($child->parent)->toBeInstanceOf(Comment::class)
                ->and($child->parent->id)->toBe($parent->id);
        });

        it('has many reactions', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            CommentReaction::create([
                'comment_id' => $comment->id,
                'reactor_id' => $this->user->id,
                'reactor_type' => UserModel::class,
                'type' => 'like',
            ]);

            $comment->refresh();

            expect($comment->reactions)->toHaveCount(1)
                ->and($comment->reactions->first()->type)->toBe('like');
        });
    });

    describe('getAvatarUrl', function () {
        it('returns gravatar URL for authenticated user', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            $url = $comment->getAvatarUrl();
            $hash = md5(strtolower(trim('john@example.com')));

            expect($url)->toContain("gravatar.com/avatar/{$hash}")
                ->and($url)->toContain('s=64')
                ->and($url)->toContain('d=mp');
        });

        it('returns gravatar URL for guest with email', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->guest_name = 'Guest';
            $comment->guest_email = 'guest@example.com';
            $comment->commentable()->associate($this->post);
            $comment->save();

            $url = $comment->getAvatarUrl();
            $hash = md5(strtolower(trim('guest@example.com')));

            expect($url)->toContain("gravatar.com/avatar/{$hash}");
        });

        it('respects avatar size from config', function () {
            Config::set('comments.avatar.size', 128);

            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAvatarUrl())->toContain('s=128');
        });

        it('respects avatar default from config', function () {
            Config::set('comments.avatar.default', 'identicon');

            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAvatarUrl())->toContain('d=identicon');
        });

        it('returns empty string when avatar provider is null', function () {
            Config::set('comments.avatar.provider', null);

            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAvatarUrl())->toBe('');
        });

        it('allows size override via parameter', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAvatarUrl(200))->toContain('s=200');
        });
    });

    describe('getAuthorName', function () {
        it('returns commenter name for authenticated comments', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAuthorName())->toBe('John Doe');
        });

        it('returns guest_name for guest comments', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->guest_name = 'Jane Guest';
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAuthorName())->toBe('Jane Guest');
        });

        it('returns Anonymous when no name is available', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->getAuthorName())->toBe('Anonymous');
        });
    });

    describe('isGuestComment', function () {
        it('returns true for guest comments', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->guest_name = 'Guest';
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->isGuestComment())->toBeTrue();
        });

        it('returns false for authenticated comments', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            expect($comment->isGuestComment())->toBeFalse();
        });
    });

    describe('table name', function () {
        it('uses configurable table name', function () {
            $comment = new Comment;
            expect($comment->getTable())->toBe('comments');

            Config::set('comments.table_names.comments', 'custom_comments');
            expect($comment->getTable())->toBe('custom_comments');
        });
    });

    describe('casts', function () {
        it('casts approved to boolean', function () {
            $comment = new Comment;
            $comment->comment = 'Test';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->approved = 1;
            $comment->save();

            $comment->refresh();

            expect($comment->approved)->toBeBool()->toBeTrue();
        });
    });
});
