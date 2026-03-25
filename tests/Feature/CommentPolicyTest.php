<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

describe('Comment Policy', function () {
    beforeEach(function () {
        $this->user = UserModel::factory()->create();
        $this->otherUser = UserModel::factory()->create();
        $this->admin = UserModel::factory()->create(['is_admin' => true]);
        $this->post = PostModel::factory()->create();

        $this->comment = new Comment;
        $this->comment->comment = 'Test comment';
        $this->comment->commenter()->associate($this->user);
        $this->comment->commentable()->associate($this->post);
        $this->comment->approved = true;
        $this->comment->save();
    });

    describe('create-comment', function () {
        it('allows any authenticated user to create a comment', function () {
            $this->actingAs($this->user);
            expect(Gate::allows('create-comment', Comment::class))->toBeTrue();
        });

        it('allows admin to create a comment', function () {
            $this->actingAs($this->admin);
            expect(Gate::allows('create-comment', Comment::class))->toBeTrue();
        });
    });

    describe('edit-comment', function () {
        it('allows the author to edit their own comment', function () {
            $this->actingAs($this->user);
            expect(Gate::allows('edit-comment', $this->comment))->toBeTrue();
        });

        it('prevents other users from editing', function () {
            $this->actingAs($this->otherUser);
            expect(Gate::allows('edit-comment', $this->comment))->toBeFalse();
        });

        it('prevents admin from editing others comments', function () {
            $this->actingAs($this->admin);
            expect(Gate::allows('edit-comment', $this->comment))->toBeFalse();
        });
    });

    describe('delete-comment', function () {
        it('allows the author to delete their own comment', function () {
            $this->actingAs($this->user);
            expect(Gate::allows('delete-comment', $this->comment))->toBeTrue();
        });

        it('allows admin to delete any comment', function () {
            $this->actingAs($this->admin);
            expect(Gate::allows('delete-comment', $this->comment))->toBeTrue();
        });

        it('prevents other non-admin users from deleting', function () {
            $this->actingAs($this->otherUser);
            expect(Gate::allows('delete-comment', $this->comment))->toBeFalse();
        });

        it('uses configurable admin_attribute', function () {
            Config::set('comments.admin_attribute', 'is_admin');
            $this->actingAs($this->admin);
            expect(Gate::allows('delete-comment', $this->comment))->toBeTrue();
        });

        it('disables admin check when admin_attribute is null', function () {
            Config::set('comments.admin_attribute', null);
            $this->actingAs($this->admin);
            // Admin can't delete others' comments when admin detection is disabled
            expect(Gate::allows('delete-comment', $this->comment))->toBeFalse();
        });
    });

    describe('reply-to-comment', function () {
        it('allows other users to reply', function () {
            $this->actingAs($this->otherUser);
            expect(Gate::allows('reply-to-comment', $this->comment))->toBeTrue();
        });

        it('prevents self-reply by default', function () {
            Config::set('comments.allow_self_reply', false);
            $this->actingAs($this->user);
            expect(Gate::allows('reply-to-comment', $this->comment))->toBeFalse();
        });

        it('allows self-reply when configured', function () {
            Config::set('comments.allow_self_reply', true);
            $this->actingAs($this->user);
            expect(Gate::allows('reply-to-comment', $this->comment))->toBeTrue();
        });
    });
});
