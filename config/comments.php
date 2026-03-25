<?php

use Anil\Comments\Http\Controllers\WebCommentController;
use Anil\Comments\Models\Comment;
use Anil\Comments\Models\CommentReaction;
use Anil\Comments\Policies\CommentPolicy;

return [

    /*
    |--------------------------------------------------------------------------
    | Comment Model
    |--------------------------------------------------------------------------
    |
    | Extend Comment and point here to customise the comment model.
    |
    */
    'model' => Comment::class,

    /*
    |--------------------------------------------------------------------------
    | Reaction Model
    |--------------------------------------------------------------------------
    |
    | Extend CommentReaction and point here to customise the reaction model.
    |
    */
    'reaction_model' => CommentReaction::class,

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customise the database table names used by the package.
    | Useful when you have naming conventions or conflicts.
    |
    */
    'table_names' => [
        'comments' => 'comments',
        'reactions' => 'comment_reactions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions / Policy
    |--------------------------------------------------------------------------
    |
    | Map gate abilities to policy methods. Swap in your own policy class to
    | override any authorisation logic without touching the package source.
    |
    */
    'permissions' => [
        'create-comment' => [CommentPolicy::class, 'create'],
        'delete-comment' => [CommentPolicy::class, 'delete'],
        'edit-comment' => [CommentPolicy::class, 'update'],
        'reply-to-comment' => [CommentPolicy::class, 'reply'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Attribute
    |--------------------------------------------------------------------------
    |
    | The attribute on the User model used to determine admin status.
    | Set to null and override CommentPolicy if you use a different
    | admin detection method (e.g. roles/permissions package).
    |
    */
    'admin_attribute' => 'is_admin',

    /*
    |--------------------------------------------------------------------------
    | Controller
    |--------------------------------------------------------------------------
    |
    | Replace with your own controller that extends CommentController or
    | implements CommentControllerContract for full customisation.
    |
    */
    'controller' => WebCommentController::class,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Set to false to disable the package routes entirely and define your own.
    |
    */
    'routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for all comment routes (e.g. /comments, /api/comments).
    |
    */
    'route_prefix' => 'comments',

    /*
    |--------------------------------------------------------------------------
    | Comment Approval
    |--------------------------------------------------------------------------
    |
    | When true, every new comment requires manual approval before it appears.
    | Use @comments(['model' => $post, 'approved' => true]) to show only
    | approved comments in the view.
    |
    */
    'approval_required' => false,

    /*
    |--------------------------------------------------------------------------
    | Guest Commenting
    |--------------------------------------------------------------------------
    |
    | Allow unauthenticated visitors to post comments using a name + email.
    |
    */
    'guest_commenting' => false,

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | When true, comments are soft-deleted (recoverable).
    | When false, they are permanently removed from the database.
    |
    */
    'soft_deletes' => false,

    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    |
    | Set to false if you need full control over which database connection
    | runs the package migrations.
    |
    */
    'load_migrations' => true,

    /*
    |--------------------------------------------------------------------------
    | Reactions (Likes / Dislikes)
    |--------------------------------------------------------------------------
    |
    | enabled — toggle the entire reactions feature on/off.
    |           When false, reaction buttons are hidden and the react route
    |           is not registered.
    |
    | types    — list of allowed reaction types.
    |           Default: ['like', 'dislike'].
    |           The comment_reactions table stores type as a string, so you
    |           can add any custom types here. Each type renders its own
    |           button in the view. Add an icon entry in
    |           resources/views/comments/_comment.blade.php for custom icons.
    |
    */
    'reactions' => [
        'enabled' => true,
        'types' => ['like', 'dislike'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Override any of these arrays with your own Laravel validation rules to
    | customise comment field constraints without touching the package source.
    |
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
    |
    | Maximum nesting level for threaded replies (0 = no replies allowed).
    | Can also be overridden per-view:
    |   @comments(['model' => $post, 'maxIndentationLevel' => 5])
    |
    */
    'max_depth' => 3,

    /*
    |--------------------------------------------------------------------------
    | Allow Self Reply
    |--------------------------------------------------------------------------
    |
    | When false, users cannot reply to their own comments.
    | Set to true to allow users to reply to their own comments.
    |
    */
    'allow_self_reply' => false,

    /*
    |--------------------------------------------------------------------------
    | Default Sort Order
    |--------------------------------------------------------------------------
    |
    | 'latest'  — newest comments first (default)
    | 'oldest'  — oldest comments first
    |
    */
    'sort' => 'latest',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of top-level comments per page. Set to null to disable
    | pagination entirely. Can be overridden per-view:
    |   @comments(['model' => $post, 'perPage' => 20])
    |
    */
    'per_page' => 10,

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Throttle comment submission, replies, edits, and reactions per IP.
    |
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
    |
    | Applied to all comment routes.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Markdown
    |--------------------------------------------------------------------------
    |
    | When enabled, comment text is parsed as Markdown before rendering.
    |
    */
    'markdown' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar
    |--------------------------------------------------------------------------
    |
    | Configure the avatar displayed alongside each comment.
    |
    | provider  — 'gravatar' (default) or null to disable.
    | size      — avatar dimensions in pixels.
    | default   — Gravatar fallback style (mp, identicon, monsterid, wavatar,
    |             retro, robohash, blank). See gravatar.com docs.
    |
    */
    'avatar' => [
        'provider' => 'gravatar',
        'size' => 64,
        'default' => 'mp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Toggle the dispatching of comment events (CommentCreated, CommentUpdated,
    | CommentDeleted). Disable if you don't use event listeners and want to
    | reduce overhead.
    |
    */
    'events' => [
        'enabled' => true,
    ],

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
