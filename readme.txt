=== WP Shield ===
Contributors: opportunityhub
Tags: security, firewall, waf, brute force, rate limiting
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, open-source Web Application Firewall (WAF) for WordPress. No external service required.

== Description ==

WP Shield blocks requests matching known-malicious patterns, rate-limits
abusive visitors, blocks a conservative list of bad bots, and locks out
brute-force login attempts — all running on your own server, with no
subscription or external dependency.

See Settings → WP Shield after activation.

== Installation ==

1. Upload the plugin to `wp-content/plugins/wp-shield`.
2. Activate through the Plugins screen.
3. Configure under Settings → WP Shield.

== Changelog ==

= 0.1.0 =
* Initial release: firewall rules, bad-bot blocking, rate limiting, login brute-force protection, activity log.
