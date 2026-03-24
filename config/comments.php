<?php

use Anil\Comments\Comment;
use Anil\Comments\CommentPolicy;
use Anil\Comments\Enums\UiTheme;
use Anil\Comments\WebCommentController;

return [

    /**
     * To extend the base Comment model one just needs to create a new
     * CustomComment model extending the Comment model shipped with the
     * package and change this configuration option to their extended model.
     */
    'model' => Comment::class,

    /**
     * You can customize the behaviour of these permissions by
     * creating your own policy and pointing to it here.
     */
    'permissions' => [
        'create-comment'   => [CommentPolicy::class, 'create'],
        'delete-comment'   => [CommentPolicy::class, 'delete'],
        'edit-comment'     => [CommentPolicy::class, 'update'],
        'reply-to-comment' => [CommentPolicy::class, 'reply'],
    ],

    /**
     * The Comment Controller.
     * Change this to your own implementation of the CommentController.
     * You can use the \Anil\Comments\CommentControllerInterface
     * or extend the \Anil\Comments\CommentController.
     */
    'controller' => WebCommentController::class,

    /**
     * Disable/enable the package routes.
     * If you want to completely take over the way this package handles
     * routes and controller logic, set this to false and provide your
     * own routes and controller for comments.
     */
    'routes' => true,

    /**
     * By default, comments posted are marked as approved. If you want
     * to change this, set this option to true. Then, all comments
     * will need to be approved by setting the `approved` column to
     * `true` for each comment.
     *
     * To see only approved comments use this code in your view:
     *
     * @comments(['model' => $book, 'approved' => true])
     */
    'approval_required' => false,

    /**
     * Set this option to `true` to enable guest commenting.
     *
     * Visitors will be asked to provide their name and email
     * address in order to post a comment.
     */
    'guest_commenting' => false,

    /**
     * Set this option to `true` to enable soft deleting of comments.
     *
     * Comments will be soft deleted using Laravel "softDeletes" trait.
     */
    'soft_deletes' => false,

    /**
     * Enable/disable the package provider to load migrations.
     * This option might be useful if you use multiple database connections.
     */
    'load_migrations' => true,

    /**
     * UI theme for comment views.
     *
     * Available options:
     *   - 'bootstrap5'  Bootstrap 5 (default) — requires Bootstrap 5 JS/CSS
     *   - 'bootstrap4'  Bootstrap 4 — requires Bootstrap 4 JS/CSS
     *   - 'tailwind'    Tailwind CSS — requires Tailwind CSS; modals use native <dialog>
     *
     * For Bootstrap themes, the package automatically calls Paginator::useBootstrap().
     */
    'ui_theme' => UiTheme::Bootstrap5->value,

    /**
     * Rate limiting for comment submission.
     *
     * Set `enabled` to true to throttle comment creation, replies, and updates.
     * `max_attempts` — max requests per `decay_minutes` window per IP.
     */
    'rate_limiting' => [
        'enabled'       => true,
        'max_attempts'  => 10,
        'decay_minutes' => 1,
    ],

    /**
     * Middleware applied to all comment routes.
     */
    'middleware' => [
        'web',
    ],

    'response_status' => [
        'created' => 201,
        'updated' => 200,
        'deleted' => 200,
    ],

    'response_messages' => [
        'created' => 'Comment created successfully.',
        'updated' => 'Comment updated successfully.',
        'deleted' => 'Comment deleted successfully.',
    ],

];
