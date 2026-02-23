---
title: Digitalygo Action Request (Core)
package: digitalygo/laravel-action-request
audience: ai
version: core
tags:
  - laravel
  - actions
  - pest
  - form-request
---

## Overview

- Provides an Artisan command `make:action-request` to scaffold three files at
  once: **Action**, **Form Request**, and **Pest test** for action-first APIs
  (built on `lorisleiva/laravel-actions`).
- Supports presets and flags for response type, lint, and test execution; stubs
  are publishable.

## Command

```bash
php artisan make:action-request {model} {name} {version=v1} \
  [--preset=simple-endpoint] [--resource=none|resource|collection] \
  [--with-lint] [--with-test]
```

- `model`: domain/model folder (e.g., Post)
- `name`: action name (e.g., Store)
- `version`: API/version namespace (default `v1`)
- `--preset=simple-endpoint`: minimal stubs (Action returns validated array;
  Request minimal; Test minimal)
- `--resource`: `none` (array), `resource` (JsonResource), `collection`
  (ResourceCollection)
- `--with-lint`: run Pint on generated files
- `--with-test`: run Pest on generated test

## Defaults (config/action-request.php)

- Namespace base: `App\`
- Paths: `app/Actions`, `app/Http/Requests`, `tests/Feature/Http/Actions`
- `default_version`: `v1`
- `response_type`: `none`
- `preset_default`: `null`
- Flags default: `with_lint=false`, `with_test=false`

## Generated structure (standard stubs)

- **Action**: uses `AsAction`; `asController(FormRequest $request): JsonResponse`;
  includes OpenAPI comment placeholders (operationId/path/method); return type
  driven by `--resource`.
- **handle signature**: `handle(array $data)` (array of validated payload) so
  Actions work in controllers, jobs, listeners, and commands where no HTTP
  FormRequest instance exists.
- **Request**: extends `FormRequest`; `authorize()` true; `rules()` to be filled
  (placeholder). Messages optional.
- **Test (Pest)**: datasets for valid/invalid; asserts success and validation
  failure patterns.

## Simple-endpoint preset

- Action returns `$request->validated()` array; minimal logic.
- Request has a sample `name` rule.
- Test covers 200 + 422 with minimal dataset.

## Publishing

```bash
php artisan vendor:publish --tag=action-request-config
php artisan vendor:publish --tag=action-request-stubs
```

- Custom stubs are read from `stubs/action-request/*.stub` if present.

## Best practices

- Always complete `rules()` in Form Request; avoid leaving TODOs.
- If using `--resource`/`--collection`, create/update API Resources and adjust
  return shape.
- Keep versioned folder layout (`v1`, `v2`, ...) for breaking changes.
- Update OpenAPI comments with actual schema/status codes; align operationId/path
  with routing.
- Run `--with-lint`/`--with-test` for quick feedback; prefer Pint+Pest before
  committing.

## Integration notes

- Depends on `lorisleiva/laravel-actions` and `laravel/boost` (for skills /
  guidelines distribution).
- Command is registered by
  `Digitalygo\ActionRequest\ActionRequestServiceProvider` and auto-discovered.
