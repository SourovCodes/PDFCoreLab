# Contributing

## Development Workflow

1. Fork the repository.
2. Create a focused branch for one change.
3. Run the relevant tests before opening a pull request.
4. Keep pull requests small enough to review quickly.

## Setup

Use the setup steps documented in [README.md](README.md) and make sure Ghostscript is installed locally before testing compression changes.

## Pull Request Expectations

- Describe the problem and the approach.
- Include screenshots or request and response examples when the change affects the UI or API behavior.
- Add or update tests when behavior changes.
- Avoid bundling unrelated refactors with feature work.

## Style

- Follow the existing Laravel and Pest conventions already used in the repository.
- Run `vendor/bin/pint --dirty --format agent` after PHP changes.
- Run `php artisan test --compact` before submitting.

## Reporting Issues

Open an issue with a minimal reproduction, expected behavior, actual behavior, and environment details.