# WP Shield

A lightweight, open-source Web Application Firewall (WAF) plugin for WordPress.
No external service, no subscription — it runs entirely on your own server.

Built and maintained by [Opportunity Hub B.V.](https://www.opportunityhub.io).

## What it does

- **Firewall** — blocks requests matching a conservative set of known-malicious
  patterns (path traversal, SQLi-style payloads, attempts to read `wp-config.php`,
  `<script>` injection, etc.), plus a manual IP allowlist/blocklist (supports CIDR).
- **Bad-bot blocking** — optionally blocks a short list of known scraper/bot
  user agents.
- **Rate limiting** — caps requests per visitor IP within a rolling time window.
  Logged-in administrators are never rate limited.
- **Login brute-force protection** — locks out an IP after too many failed
  logins within a window, for a configurable lockout period.
- **Activity log** — the last 200 (configurable) blocked/flagged requests,
  viewable under *Settings → WP Shield*.

All settings are under **Settings → WP Shield** in wp-admin.

## Requirements

- WordPress 5.8 or later (tested up to 6.7, as listed in `readme.txt`)
- PHP 7.4 or later

There is no automated test suite yet. Please report problems you find.

## Installation

1. Download or clone this repository into `wp-content/plugins/wp-shield`.
2. Activate **WP Shield** from the Plugins screen.
3. Visit **Settings → WP Shield** to review the defaults and add your own
   IP allowlist (e.g. your office/VPN IP) before enabling anything stricter.

### For stronger protection: run it as a must-use plugin

As a normal plugin, WP Shield's checks run on WordPress's `init` hook —
after WordPress core has already parsed the request. For most sites that's
plenty. If you want the firewall to run *before* WordPress core loads at
all, copy `wp-shield.php` and the `includes/` folder into
`wp-content/mu-plugins/` instead of `wp-content/plugins/`. Must-use plugins
load earlier and can't be deactivated from the Plugins screen, so only do
this once you're confident in your configuration (keep your own IP on the
allowlist first).

## Behind a reverse proxy / CDN (Cloudflare, etc.)

By default WP Shield reads the visitor's IP from `REMOTE_ADDR`, which is
correct unless your server sits behind a proxy or CDN. If it does, define
this in `wp-config.php` so WP Shield reads the real visitor IP from the
`CF-Connecting-IP` / `X-Forwarded-For` header instead of your proxy's IP:

```php
define( 'WP_SHIELD_TRUST_PROXY', true );
```

**Only set this if your server is genuinely only reachable through that
proxy.** Trusting these headers on a server that's also reachable directly
lets a visitor spoof their own IP.

## Project status

This is an early (v0.1), actively-developed starting point — a useful
baseline for small and mid-size WordPress sites, not a drop-in replacement
for a dedicated, professionally audited WAF on a high-value or
high-traffic target. Pull requests and issue reports are welcome.

## Security, contributing and changes

- Report vulnerabilities privately: see [SECURITY.md](SECURITY.md).
- Want to help? See [CONTRIBUTING.md](CONTRIBUTING.md).
- What changed: see [CHANGELOG.md](CHANGELOG.md).

## License

GPL v2 or later — see [LICENSE](LICENSE).
