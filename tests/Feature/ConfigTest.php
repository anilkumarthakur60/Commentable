<?php

use Anil\Comments\Models\Comment;
use Anil\Comments\Models\CommentReaction;
use Anil\Comments\Tests\Support\Models\PostModel;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Spatie\Honeypot\ProtectAgainstSpam;

describe('Config-driven behavior', function () {
    beforeEach(function () {
        $this->withoutMiddleware([ProtectAgainstSpam::class]);
        $this->user = UserModel::factory()->create();
        $this->post = PostModel::factory()->create();
    });

    describe('approval_required', function () {
        it('creates approved comments when approval is not required', function () {
            Config::set('comments.approval_required', false);

            $this->actingAs($this->user)
                ->postJson('/comments', [
                    'commentable_type' => PostModel::class,
                    'commentable_id'   => $this->post->id,
                    'message'          => 'Auto-approved',
                ])->assertJson(['data' => ['approved' => true]]);
        });

        it('creates unapproved comments when approval is required', function () {
            Config::set('comments.approval_required', true);

            $this->actingAs($this->user)
                ->postJson('/comments', [
                    'commentable_type' => PostModel::class,
                    'commentable_id'   => $this->post->id,
                    'message'          => 'Needs approval',
                ])->assertJson(['data' => ['approved' => false]]);
        });
    });

    describe('response_status', function () {
        it('uses configurable status code for created', function () {
            Config::set('comments.response_status.created', 200);

            $response = $this->actingAs($this->user)
                ->postJson('/comments', [
                    'commentable_type' => PostModel::class,
                    'commentable_id'   => $this->post->id,
                    'message'          => 'Test',
                ]);

            $response->assertStatus(200);
        });
    });

    describe('response_messages', function () {
        it('uses configurable response messages', function () {
            Config::set('comments.response_messages.deleted', 'Comment removed.');

            $comment = new Comment();
            $comment->comment = 'To delete';
            $comment->commenter()->associate($this->user);
            $comment->commentable()->associate($this->post);
            $comment->save();

            $response = $this->actingAs($this->user)
                ->deleteJson("/comments/{$comment->id}");

            $response->assertJson(['message' => 'Comment removed.']);
        });
    });

    describe('validation', function () {
        it('uses custom validation rules from config', function () {
            Config::set('comments.validation.message', ['required', 'string', 'min:20']);

            $response = $this->actingAs($this->user)
                ->postJson('/comments', [
                    'commentable_type' => PostModel::class,
                    'commentable_id'   => $this->post->id,
                    'message'          => 'Short',
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['message']);
        });
    });

    describe('table_names', function () {
        it('Comment model uses configured table name', function () {
            expect((new Comment())->getTable())->toBe('comments');

            Config::set('comments.table_names.comments', 'app_comments');
            expect((new Comment())->getTable())->toBe('app_comments');
        });

        it('CommentReaction model uses configured table name', function () {
            expect((new CommentReaction())->getTable())->toBe('comment_reactions');

            Config::set('comments.table_names.reactions', 'app_reactions');
            expect((new CommentReaction())->getTable())->toBe('app_reactions');
        });
    });

    describe('per_page', function () {
        it('has default per_page config value', function () {
            expect(Config::get('comments.per_page'))->toBe(10);
        });
    });

    describe('route_prefix', function () {
        it('has default route_prefix config value', function () {
            expect(Config::get('comments.route_prefix'))->toBe('comments');
        });
    });

    describe('markdown', function () {
        it('has markdown enabled by default', function () {
            expect(Config::get('comments.markdown.enabled'))->toBeTrue();
        });
    });

    describe('avatar', function () {
        it('has default avatar config', function () {
            expect(Config::get('comments.avatar.provider'))->toBe('gravatar')
                ->and(Config::get('comments.avatar.size'))->toBe(64)
                ->and(Config::get('comments.avatar.default'))->toBe('mp');
        });
    });

    describe('events', function () {
        it('has events enabled by default', function () {
            expect(Config::get('comments.events.enabled'))->toBeTrue();
        });
    });
});
