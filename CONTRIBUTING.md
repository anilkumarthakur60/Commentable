# Contributing

Thank you for considering contributing to `anil/comments`! This document outlines the process and standards to follow.

## Code of Conduct

Please be respectful and constructive in all interactions. We follow the [Contributor Covenant](https://www.contributor-covenant.org/).

## How to Contribute

### Reporting Bugs

Before filing a bug report, check existing [issues](https://github.com/anil-comments/comments/issues) to avoid duplicates.

Include:
- PHP and Laravel version
- Package version
- Steps to reproduce
- Expected vs actual behaviour
- Any relevant stack traces

### Suggesting Features

Open an issue with the `enhancement` label. Describe:
- The problem you're trying to solve
- Your proposed solution
- Any alternatives you've considered

### Pull Requests

1. **Fork** the repository and create a branch from `2.x`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Install** dependencies:
   ```bash
   composer install
   ```

3. **Write tests** — all new features and bug fixes require tests.

4. **Run the full CI suite** before pushing:
   ```bash
   composer ci
   ```
   This runs code style, static analysis, and tests.

5. **Commit** with a clear message following [Conventional Commits](https://www.conventionalcommits.org/):
   ```
   feat: add support for anonymous reactions
   fix: prevent duplicate reactions on race condition
   docs: update configuration reference
   ```

6. **Open a Pull Request** against the `2.x` branch. Fill in the PR template.

## Development Setup

```bash
git clone https://github.com/your-fork/comments.git
cd comments
composer install
```

### Running Tests

```bash
# Run all tests
composer test

# With coverage report
composer test:coverage

# With type coverage
composer test:types
```

### Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) with the `laravel` preset:

```bash
# Fix style issues
composer lint

# Check without fixing
composer lint:check
```

### Static Analysis

PHPStan at level 9:

```bash
composer analyse
```

## Project Structure

```
src/
├── Concerns/        # Traits: Commentable, Commenter
├── Contracts/       # Interfaces: CommentControllerContract
├── Events/          # CommentCreated, CommentUpdated, CommentDeleted
├── Exceptions/      # Custom exception classes
├── Http/
│   ├── Controllers/ # CommentController, WebCommentController
│   └── Resources/   # CommentResource
├── Models/          # Comment, CommentReaction
├── Policies/        # CommentPolicy
├── CommentService.php
└── ServiceProvider.php
config/
database/migrations/
resources/
  lang/              # 13 language files
  views/comments/    # Blade views
routes/
  web.php
tests/
  Feature/           # HTTP and model integration tests
  Unit/              # Unit tests for services
  Support/           # Test models, factories
```

## Versioning

This project follows [Semantic Versioning](https://semver.org/). Please update `CHANGELOG.md` under the `[Unreleased]` section when making changes.
