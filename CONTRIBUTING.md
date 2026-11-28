# Contributing to WP Shield

Thanks for helping. Small, focused pull requests are easiest to review.

## Reporting bugs and false positives

Open an issue and include:

- WordPress and PHP versions,
- the request that was blocked (path and parameters, with private data removed),
- the matching entry from **Settings → WP Shield → Activity log**.

False positives are valuable: they help us keep the rules conservative.

For security problems, do not open an issue. Follow [SECURITY.md](SECURITY.md).

## Pull requests

1. Fork the repository and create a branch from `main`.
2. Follow the [WordPress PHP coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
3. Keep changes small. Explain what changed and why in the pull request.
4. Note in the description how you tested the change (WordPress and PHP versions).
5. Update `CHANGELOG.md` under **Unreleased**.

## Licence

By contributing you agree that your contribution is licensed under GPL v2 or later,
the same as the project.
