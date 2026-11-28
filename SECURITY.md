# Security Policy

WP Shield is a security plugin, so we take reports about it seriously.

## Supported versions

Only the latest release receives fixes. The project is early (v0.1) and is not a
replacement for a professionally audited WAF.

## Reporting a vulnerability

Please do **not** open a public issue for a security problem.

Email **office@opportunityhub.io** with the subject line `WP Shield security`
and include:

- what you found and where (file, function or rule),
- steps to reproduce, and the WordPress and PHP versions you used,
- the impact as you understand it.

We will acknowledge your report, keep you informed while we investigate, and
credit you in the changelog once a fix is released, unless you prefer to stay
anonymous. Please give us reasonable time to fix the issue before you publish
details.

## Scope

In scope: bypasses of the firewall rules, rate limiting or login lockout; IP
spoofing issues; flaws in the admin screen (such as missing capability or nonce
checks); data exposure from the activity log.

Out of scope: vulnerabilities in WordPress core or other plugins, and reports
that need an already-compromised administrator account.
