# Digitalygo Action Request

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digitalygo/laravel-action-request.svg?style=flat-square)](https://packagist.org/packages/digitalygo/laravel-action-request)
[![Total Downloads](https://img.shields.io/packagist/dt/digitalygo/laravel-action-request.svg?style=flat-square)](https://packagist.org/packages/digitalygo/laravel-action-request)

A Laravel package to generate Actions, Form Requests, and Pest tests with a
single command. Built on top of
[lorisleiva/laravel-actions](https://github.com/lorisleiva/laravel-actions).

## Installation

Install the package via Composer:

```bash
composer require digitalygo/laravel-action-request
```

The package will auto-register its service provider.

## Usage

Generate an Action, Form Request, and Pest test:

```bash
php artisan make:action-request Post Store v1
```

This creates:

- `app/Actions/v1/Post/StorePostAction.php`
- `app/Http/Requests/v1/Post/StorePostRequest.php`
- `tests/Feature/Http/Actions/v1/Post/StorePostActionTest.php`

### Simple Endpoint Preset

For minimal API endpoints, use the `--preset` flag:

```bash
php artisan make:action-request User Create v1 --preset=simple-endpoint
```

This generates streamlined stubs with minimal boilerplate.

### Resource Types

Control the return type with the `--resource` flag:

```bash
# Return array (default)
php artisan make:action-request Post Index v1 --resource=none

# Return JsonResource
php artisan make:action-request Post Show v1 --resource=resource

# Return ResourceCollection
php artisan make:action-request Post List v1 --resource=collection
```

### Why `handle` accepts arrays

- The generated Action `handle` method takes an **array of validated data**, not
  the Form Request instance. This keeps Actions compatible across controller,
  job, listener, command, and object execution paths where HTTP FormRequest
  instances are not available. The controller entrypoint still passes
  `$request->validated()` to `handle`.

### Lint and Test

Automatically run Pint and Pest on generated files:

```bash
php artisan make:action-request Post Store v1 --with-lint --with-test
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=action-request-config
```

Key options in `config/action-request.php`:

- `namespace` - Base namespace (default: `App\`)
- `actions_path` - Path for actions (default: `app/Actions`)
- `requests_path` - Path for requests (default: `app/Http/Requests`)
- `tests_path` - Path for tests (default: `tests/Feature/Http/Actions`)
- `default_version` - Default API version (default: `v1`)
- `response_type` - Default response type (default: `none`)
- `pest` - Generate Pest tests by default (default: `true`)
- `preset_default` - Default preset (default: `null`)
- `flags.with_lint` - Default for `--with-lint`
- `flags.with_test` - Default for `--with-test`

## Publishing Stubs

Customize the generated code by publishing stubs:

```bash
php artisan vendor:publish --tag=action-request-stubs
```

Stubs are copied to `stubs/action-request/`:

- `action.stub` - Standard action template
- `action-simple.stub` - Simple endpoint action template
- `request.stub` - Standard request template
- `request-simple.stub` - Simple endpoint request template
- `test.stub` - Standard Pest test template
- `test-simple.stub` - Simple endpoint test template

## OpenAPI Guidance

Generated actions include OpenAPI placeholder comments:

```php
/**
 * OpenAPI Operation
 * operationId: store-post
 * path: /api/v1/posts
 * method: POST
 */
```

Use these as guidance for documenting your API endpoints.

## Laravel Boost Skill

This package includes a Laravel Boost skill definition in `boost/skills.php`.
It registers package commands and paths for enhanced IDE support.
The skill is auto-discovered by Laravel Boost per the 12.x documentation.

## Dependencies

- PHP ^8.4
- Laravel ^12.0
- lorisleiva/laravel-actions ^2.9
- laravel/boost ^2.1 (for skills support)

## Testing

Run the package tests:

```bash
composer test
```

Run code style checks:

```bash
composer pint
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

Report security issues to [dev@digitalygo.it](mailto:dev@digitalygo.it).

## Credits

- Diggo Team
- [lorisleiva/laravel-actions](https://github.com/lorisleiva/laravel-actions)

## License

MIT License. See [LICENSE](LICENSE.md) for details.
