---
status: completed
created_at: 2026-04-23
files_edited:
  - .gitignore
  - .markdownlint.json
  - .markdownlintignore
  - composer.json
  - composer.lock
  - .github/workflows/main.yml
  - README.md
  - CHANGELOG.md
  - LICENSE.md
rationale:
  - Add Laravel 13 install support without dropping Laravel 12.
  - Raise minimum compatible upstream package versions where Laravel 13 support begins.
  - Add CI coverage for both supported Laravel majors.
supporting_docs:
  - https://laravel.com/docs/13.x/releases
  - https://laravel.com/docs/13.x/upgrade
  - https://packagist.org/packages/orchestra/testbench
  - https://packagist.org/packages/lorisleiva/laravel-actions
  - https://packagist.org/packages/laravel/boost
---

# Laravel 13 support

## Summary of changes

- widened package support to `laravel/framework ^12.0 || ^13.0`
- raised upstream minimums to Laravel 13-compatible releases:
  - `lorisleiva/laravel-actions ^2.10.1`
  - `laravel/boost ^2.2.3`
  - `orchestra/testbench ^10.0 || ^11.0`
  - `jasonmccreary/laravel-test-assertions ^2.9.0`
- refreshed `composer.lock` on a Laravel 13 / Testbench 11 stack
- rewrote CI matrix to test Laravel 12 and Laravel 13 on PHP 8.4 with `prefer-lowest` and `prefer-stable`
- hardened CI by pinning GitHub Actions to SHAs, setting `contents: read`, disabling Composer scripts/plugins during matrix resolution, and adding `composer audit`
- updated package documentation and changelog to state Laravel 12 + 13 support
- fixed Markdown lint heading issue in `LICENSE.md`

## Technical reasoning

Laravel 13 itself introduces minimal package-facing breakage for this library's code surface. Real compatibility work is dependency and validation driven.

Research showed:

- Laravel 13 requires PHP 8.3+, while this package already requires PHP 8.4, so no PHP constraint change was needed.
- `orchestra/testbench` major versions track Laravel majors, so Laravel 12 requires Testbench 10 and Laravel 13 requires Testbench 11.
- Laravel 13 support in key upstream dependencies starts at newer releases than the repository previously allowed, so only widening the framework constraint would not be enough.

Because package source code already relies on stable framework APIs, no PHP implementation changes were required.

## Impact assessment

### Positive impact

- package can now be installed in Laravel 13 applications
- Laravel 12 compatibility remains declared and CI-covered
- CI signal now matches real package support instead of an obsolete Laravel 8 matrix

### Side effects

- local lockfile now resolves to Laravel 13 / Testbench 11 as current dev baseline
- `composer validate --strict` still warns about root `version` in `composer.json`; warning left unchanged because it predates this work

## Validation steps

- `composer validate --strict`
- `composer test`
- `composer update --dry-run "laravel/framework:^12.0" "orchestra/testbench:^10.0" --with-all-dependencies`
- `composer update --dry-run "laravel/framework:^13.0" "orchestra/testbench:^11.0" --with-all-dependencies`
- `ruby -e "require 'yaml'; data = YAML.load_file('.github/workflows/main.yml'); p data['jobs']['test']['strategy']['matrix']"`
- `composer audit --locked --format=json`
- `npx markdownlint-cli "**/*.md" --config .markdownlint.json --ignore-path .markdownlintignore --dot --fix`
