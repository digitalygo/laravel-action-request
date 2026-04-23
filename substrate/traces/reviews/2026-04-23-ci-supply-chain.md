---
status: draft
created_at: 2026-04-23
reviewer: security-specialist
target: Laravel 13 support update
scope: composer.json, composer.lock, .github/workflows/main.yml, README.md, CHANGELOG.md, LICENSE.md, .markdownlint.json, .markdownlintignore, substrate/traces/**
supporting_docs:
  - substrate/traces/operations/2026-04-23-laravel-13-support.md
---

# Summary

1 medium finding. `composer audit` returned no package advisories.

# Scope and methodology

Reviewed diff and current file contents for dependency, CI, and docs impact. Ran `git diff --check`, `composer audit --format=json`, and `composer validate --strict`. Docker toolbox scan was attempted but GHCR image pull failed in this environment.

# Findings by severity

## Medium

### `.github/workflows/main.yml:L35-L51`

- Evidence: workflow uses mutable third-party actions (`actions/checkout@v4`, `shivammathur/setup-php@v2`) and runs `composer require`/`composer update` with Composer scripts/plugins enabled on `pull_request`.
- Impact: a compromised upstream action or a PR that changes Composer metadata can execute code in GitHub Actions before tests. Runner workspace and repo-scoped token become exposed.
- False-positive notes: no current package advisory was found; issue is CI supply-chain exposure, not an active CVE.
- Remediation: pin actions to commit SHAs and run dependency refresh with `--no-scripts --no-plugins` (or move lockfile refresh into a trusted maintenance job).

# Remediation timeline

1. Pin external actions to SHAs.
2. Harden Composer install/update path.
3. Add `composer audit` to CI gate.

# Validation notes

Re-run workflow after pinning and hardening. Confirm test matrix still passes and dependency install no longer executes package scripts/plugins.
