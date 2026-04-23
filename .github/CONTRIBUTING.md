# Contributing

Thank you for your interest in contributing to this Laravel project.  This document explains how to set up your environment, the coding rules, and the workflow to follow for issues, branches, commits, and pull requests.

## Code of Conduct

This project is governed by the project’s Code of Conduct, which applies to all spaces including issues, pull requests, discussions, and any community channels.  By participating, you agree to treat all contributors with respect, assume good faith, and follow the reporting and enforcement processes described in the Code of Conduct.

If you witness or experience unacceptable behavior, follow the reporting instructions in the Code of Conduct so the maintainers can investigate and take appropriate action.

## Getting Started

- Fork the repository to your own GitHub account.
- Clone your fork locally and add the original repository as `upstream` to keep in sync.
- Create a new branch from the main development branch (`main` or `develop`) for each change you plan to make.

Before starting a larger feature, consider opening an issue or discussion to align with maintainers on scope and approach.

## Development Setup

### Prerequisites

- PHP 8.4+
- Composer 2.x
- PostgreSQL or MySQL (depending on the project configuration)
- Redis
- Node.js and npm (for front-end assets)
- Docker and Docker Compose (optional, if you prefer containerized development)  
  
### Local Setup

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (if applicable)
php artisan db:seed

# Start development server
php artisan serve

# Compile assets (in a separate terminal)
npm run dev
```

### Docker Setup (Optional)

```bash
# Start all services
docker-compose up -d

# Install dependencies inside the app container
docker-compose exec app composer install

# Run migrations
docker-compose exec app php artisan migrate
```

### Environment configuration

- `.env` files must store **only secrets** (API keys, passwords, salts, tokens). Runtime settings such as `APP_URL`, `APP_DEBUG`, locales, log channels, and similar toggles must live in versioned configuration (for example, our Docker Compose files).
- Docker Compose files **must not** use `env_file`. Declare each variable explicitly under `environment` so contributors immediately see which secrets they must provide in their personal `.env`.
- Non-secret defaults should be hard-coded in Compose (for example, `APP_ENV=production`), while secrets must reference `${VAR_NAME}` placeholders and be documented in `.env.example`.

## No-Comments Policy

This project maintains a strict no-comments policy for PHP source code, inspired by the same rule in the Node project.

### What This Means

- No commented-out code; remove it instead of keeping it “just in case”.
- No dead code; delete unused classes, methods, imports, and variables.
- No temporary debug code such as `dd()`, `dump()`, `var_dump()`, or logging added only for debugging.
- No inline comments explaining what code does and no PHPDoc for trivial methods where type hints are enough.
- No placeholder comments like “TODO”, “FIXME”, or “HACK” in source files.

### Why

- Code should be self-explanatory through good naming and structure rather than comments.
- Comments tend to become outdated and misleading over time, creating maintenance debt.
- Version control already preserves history, so commented-out code does not need to be kept in files.

### Allowed Exceptions

- Configuration files such as `.env.example` or `config/*.php` where short comments help clarify usage.
- PHPDoc for complex interfaces, public APIs, or where needed for static analysis.
- Database seeders where brief explanations clarify generated test data.
- Markdown documentation in `docs/` or similar directories.

### If Code Needs Explanation

- Refactor to improve naming of classes, methods, and variables.
- Extract smaller, focused methods or classes to clarify intent.
- Use appropriate patterns (e.g., actions, services) to separate concerns.
- Document complex decisions in dedicated markdown files (for example, `docs/architecture/` or `docs/security-decisions.md`).

Pull requests that introduce comments outside the allowed exceptions may be rejected or requested to remove those comments.

## Code Style Guidelines

### Standards and Tools

- Follow PSR-1, PSR-12, and PSR-4 for PHP code.
- Use Laravel Pint (or the configured formatter) to enforce coding standards.
- Do not change the project’s coding style or tooling configuration without prior discussion.

Run style checks before committing:

```bash
./vendor/bin/pint --test
./vendor/bin/pint
```

### Structure and Size

- Prefer small, focused classes and methods for better readability and maintainability.
- When files become large or complex, extract logic into services, actions, traits, or additional classes instead of exceeding agreed limits.

### General Principles

- Readability first: Code is read more often than it is written.
- Follow SOLID, DRY, KISS, and YAGNI to keep the codebase clean and maintainable.
- Use clear, descriptive names; avoid abbreviations that are not obvious.

### Naming and Types

- Use `PascalCase` for classes and enums, `camelCase` for methods and variables, and `UPPER_SNAKE_CASE` for constants.
- Use strict typing and declare parameter, return, and property types whenever possible.
- Keep public interfaces stable and documented when they are part of the project’s public API.

## Laravel Best Practices

- Keep controllers thin: validate input, delegate to actions/services, and return responses.
- Move domain logic to service classes, actions, or domain models rather than controllers.
- Use Form Request classes for validation and policies/gates for authorization.
- Use API Resources for consistent JSON responses in APIs.
- Avoid N+1 queries by using eager loading and lean, focused queries.

Tests should cover critical business flows, edge cases, and any behavior added or changed by your contribution.  Use feature and unit tests as appropriate, and keep them fast and deterministic.

## Action-First API Workflow

This project follows a strict action-first architecture where API routes bind directly to Action classes, completely bypassing controllers. Controllers are being phased out in favor of this streamlined approach.

### Route-to-Action Binding

API routes (e.g., users v1 in `routes/api.php`) must bind directly to Action classes using class references:

```php
Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', \App\Actions\v1\User\Index::class);
        Route::get('/{user}', \App\Actions\v1\User\Get::class);
    });
});
```

This eliminates the controller layer entirely, with Actions serving as the HTTP entry point.

### Action Structure

Every Action follows the standard shape using the `lorisleiva/actions` package:

- **`handle()` method** - Contains the core business logic, receives the Form Request and any additional dependencies (like authenticated User models)
- **`asController()` method** - Serves as the HTTP entry point, delegates to `handle()`, and returns API Resources

```php
class Index
{
    use AsAction;

    public function handle(IndexRequest $request, User $user): LengthAwarePaginator
    {
        // Business logic implementation
        return User::query()->paginate($request->per_page);
    }

    public function asController(IndexRequest $request): ResourceCollection
    {
        $users = $this->handle($request, auth()->user());
        return new UserCollection($users);
    }
}
```

### Required Components

Each Action MUST include:

1. **Versioned Form Request** - Located in `app/Http/Requests/{version}/{Model}/`, handles validation and authorization
2. **Pest Feature Test** - Located in `tests/Feature/Http/Actions/{version}/{Model}/`, covers all HTTP scenarios
3. **API Resource** - Transforms data for consistent JSON responses

Services are optional and should only be created when business logic becomes complex enough to warrant additional orchestration layers.

### Scaffolding Requirements

The exclusive and only supported way to create new Actions/FormRequests/tests is the custom command:

```bash
php artisan make:action-request {model} {name} {version=v1}
```

This command (`app/Console/Commands/CreateNewActionWithValidation.php`) automatically generates:

- Action class with proper structure and stub
- Versioned Form Request with base inheritance
- Pest feature test with initial setup

**Manual creation of these files is strictly prohibited.**

### Implementation Patterns

When implementing new endpoints, mirror existing patterns:

- **Actions**: `app/Actions/v1/User/*` - See `Index.php` and `Get.php` for reference
- **Form Requests**: `app/Http/Requests/v1/User/*` - Extend versioned base classes for consistency
- **Tests**: `tests/Feature/Http/Actions/v1/User/*` - Follow the comprehensive testing approach with datasets for edge cases

This architecture ensures consistency, testability, and clear separation of concerns across all API endpoints.

### New Endpoint Workflow

Every new endpoint must follow these mandatory steps in order:

1. Run `php artisan make:action-request {model} {name} {version=v1}`.
2. Register the new Action in routes/api.php so the route binds directly to the Action (no controllers).
3. Edit the generated Form Request to add authorization (via policies) and validation rules.
4. Create or update the relevant API Resource(s) if the payload needs adjustments.
5. Implement the Action logic (invoking Services if orchestration is needed) so handle()/asController() stay aligned with the architecture.
6. Expand the generated Pest feature test to cover all HTTP scenarios and ensure at least 90% coverage for the Action, Form Request, and Resource, targeting 100%.

<ins>**THIS WORKFLOW IS MANDATORY FOR EVERY NEW ENDPOINT AND MUST BE FOLLOWED WITHOUT DEVIATION.**</ins>

## Git Workflow

### Branching

- `main` (and optionally `staging`/`develop`) is always kept in a releasable state.
- Use topic branches: `feature/description`, `fix/description`, `refactor/description`, or `hotfix/description`.
- Avoid committing directly to `main` or protected branches.

### Commit Messages

This project uses the Conventional Commits specification.

Format:

```text
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

Common types include:

- `feat:` for new features.
- `fix:` for bug fixes.
- `docs:` for documentation-only changes.
- `style:` for formatting or style changes without logic changes.
- `refactor:`, `perf:`, `test:`, `build:`, `ci:`, and `chore:` for their respective purposes.

Mention related issues (for example, `Closes #123` or `Fixes #456`) in the footer so automation and maintainers can track progress.

## Pull Request Process

### Before Submitting

- Ensure your branch is up to date with the target branch and resolve conflicts locally.
- Run style checks, static analysis, and the test suite (for example, `./vendor/bin/pint --test`, `./vendor/bin/phpstan analyse`, `php artisan test`).
- Remove debugging statements and any commented or dead code.
- Update documentation and example configuration files when behavior or requirements change.

### PR Description

Use a clear description that explains:

- What changed and why.
- Any breaking changes or migration steps required.
- How reviewers can test the changes (commands, environment, sample requests).

Include checklists such as tests run, documentation updated, and adherence to coding standards to make review easier.

### Review

- Be respectful and constructive when reviewing others’ work; focus on code and behavior, not the person.
- As a contributor, respond to all review comments, apply requested changes, and ask for clarification when something is unclear.
- Maintainers may request additional tests, refactors, or documentation updates before merging.

## Testing

Every action's test suite must maintain at least 90% coverage, with 100% as the target. Contributors should expand the generated Pest tests until that bar is met.

- Write unit tests for pure logic and service classes, and feature tests for HTTP endpoints or user flows.
- Aim for good coverage on critical paths rather than chasing a specific numeric coverage target.

Common commands:

```bash
# Run all tests
php artisan test

# Run with coverage (if configured)
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel
```

When fixing a bug, add a test that fails before your change and passes after, to prevent regressions.

## Questions and Support

If you are unsure about something:

- Check the existing documentation (for example, `README.md` and `docs/` directory).
- Review previous issues and pull requests for similar topics or patterns.
- Open a new issue or discussion with a clear description of your question or proposal.

Thank you for taking the time to contribute and for helping keep the project healthy, consistent, and welcoming.
