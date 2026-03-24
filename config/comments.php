<?php

use Anil\Comments\Comment;
use Anil\Comments\CommentPolicy;
use Anil\Comments\CommentReaction;
use Anil\Comments\WebCommentController;

return [

    /*
    |--------------------------------------------------------------------------
    | Comment Model
    |--------------------------------------------------------------------------
    | Extend Comment and point here to customise the comment model.
    */
    'model' => Comment::class,

    /*
    |--------------------------------------------------------------------------
    | Reaction Model
    |--------------------------------------------------------------------------
    | Extend CommentReaction and point here to customise the reaction model.
    */
    'reaction_model' => CommentReaction::class,

    /*
    |--------------------------------------------------------------------------
    | Permissions / Policy
    |--------------------------------------------------------------------------
    | Map gate abilities to policy methods. Swap in your own policy class to
    | override any authorisation logic without touching the package source.
    */
    'permissions' => [
        'create-comment' => [CommentPolicy::class, 'create'],
        'delete-comment' => [CommentPolicy::class, 'delete'],
        'edit-comment' => [CommentPolicy::class, 'update'],
        'reply-to-comment' => [CommentPolicy::class, 'reply'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller
    |--------------------------------------------------------------------------
    | Replace with your own controller that extends CommentController or
    | implements CommentControllerInterface for full customisation.
    */
    'controller' => WebCommentController::class,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | Set to false to disable the package routes entirely and define your own.
    */
    'routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Comment Approval
    |--------------------------------------------------------------------------
    | When true, every new comment requires manual approval before it appears.
    | Use @comments(['model' => $post, 'approved' => true]) to show only
    | approved comments in the view.
    */
    'approval_required' => false,

    /*
    |--------------------------------------------------------------------------
    | Guest Commenting
    |--------------------------------------------------------------------------
    | Allow unauthenticated visitors to post comments using a name + email.
    */
    'guest_commenting' => false,

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    | When true, comments are soft-deleted (recoverable).
    | When false, they are permanently removed.
    */
    'soft_deletes' => false,

    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    | Set to false if you need full control over which database connection
    | runs the package migrations.
    */
    'load_migrations' => true,

    /*
    |--------------------------------------------------------------------------
    | Reactions (Likes / Dislikes)
    |--------------------------------------------------------------------------
    | enabled — toggle the entire reactions feature on/off.
    |           When false, reaction buttons are hidden and the react route
    |           is not registered.
    |
    | types    — list of allowed reaction types.
    |           Default: ['like', 'dislike'].
    |           Note: the comment_reactions table stores type as a string,
    |           so you can add any custom types here. Each type renders its
    |           own button in the view. Add an entry to the icon map in
    |           resources/views/comments/_comment.blade.php for a custom icon.
    */
    'reactions' => [
        'enabled' => true,
        'types' => ['like', 'dislike'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    | Override any of these arrays with your own Laravel validation rules to
    | customise comment field constraints without touching the package source.
    */
    'validation' => [
        'message' => ['required', 'string'],
        'guest_name' => ['required', 'string', 'max:255'],
        'guest_email' => ['required', 'string', 'email', 'max:255'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Max Comment Depth
    |--------------------------------------------------------------------------
    | Maximum nesting level for threaded replies (0 = no replies allowed).
    | Can also be overridden per-view: @comments(['model' => $post, 'maxIndentationLevel' => 5])
    */
    'max_depth' => 3,

    /*
    |--------------------------------------------------------------------------
    | Default Sort Order
    |--------------------------------------------------------------------------
    | 'latest'  — newest comments first  (default)
    | 'oldest'  — oldest comments first
    */
    'sort' => 'latest',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Throttle comment submission, replies, edits, and reactions per IP.
    */
    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Applied to all comment routes.
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | HTTP Response Codes & Messages
    |--------------------------------------------------------------------------
    */
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
