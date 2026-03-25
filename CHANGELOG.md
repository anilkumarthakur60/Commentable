# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Restructured source into domain-organized subdirectories (`Models/`, `Concerns/`, `Http/`, `Policies/`, `Contracts/`, `Exceptions/`)
- Moved routes to `routes/web.php` following Laravel package conventions
- Moved migrations to `database/migrations/` following Laravel conventions
- Renamed `CommentControllerInterface` to `CommentControllerContract`
- Renamed traits directory to `Concerns/` following Laravel core conventions
- Improved `ServiceProvider` with extracted, single-responsibility protected methods
- Added `Exceptions/` namespace with `CommentNotFoundException`, `GuestCommentingDisabledException`, and `MaxDepthExceededException`
- Updated `composer.json` scripts with consistent naming (`test`, `lint`, `analyse`, `ci`)

## [2.0.0] - 2024-06-01

### Added
- Reactions system with configurable reaction types (like/dislike and custom)
- `CommentReaction` model and `comment_reactions` migration
- `react()` endpoint with toggle behaviour (same type removes, different type switches)
- Rate limiting support for all comment actions
- Configurable HTTP response status codes and messages
- `CommentResource` JSON API resource
- Events: `CommentCreated`, `CommentUpdated`, `CommentDeleted`
- 13-language translation support (ar, ca, de, en, es, fr, in, it, ja, nl, np, pt, ru)
- Guest commenting with Honeypot spam protection
- `max_depth` configuration for threaded replies
- PHPStan level 9 static analysis

### Changed
- Minimum PHP version raised to 8.2
- Minimum Laravel version raised to 11.0

## [1.0.0] - 2018-06-30

### Added
- Initial release
- Polymorphic `Commentable` trait for any Eloquent model
- `Commenter` trait for User models
- Threaded comment replies
- Comment approval workflow
- Soft delete support
- Gravatar avatar support with fallback initials
- Markdown rendering with Parsedown (safe mode)
- `CommentPolicy` with owner and admin authorization
- Configurable middleware and gate permissions
