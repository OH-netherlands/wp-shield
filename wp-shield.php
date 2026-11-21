<?php
/**
 * Plugin Name: WP Shield
 * Plugin URI:  https://github.com/OH-netherlands/wp-shield
 * Description: A lightweight, open-source Web Application Firewall (WAF) for WordPress. Request-level firewall rules, rate limiting, bad-bot blocking and brute-force login protection — no external service required.
 * Version:     0.1.0
 * Author:      Opportunity Hub B.V.
 * Author URI:  https://www.opportunityhub.io
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-shield
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'WP_SHIELD_VERSION', '0.1.0' );
define( 'WP_SHIELD_FILE', __FILE__ );
define( 'WP_SHIELD_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_SHIELD_URL', plugin_dir_url( __FILE__ ) );

require_once WP_SHIELD_DIR . 'includes/class-wp-shield-logger.php';
require_once WP_SHIELD_DIR . 'includes/class-wp-shield-ip.php';
require_once WP_SHIELD_DIR . 'includes/class-wp-shield-firewall.php';
require_once WP_SHIELD_DIR . 'includes/class-wp-shield-rate-limiter.php';
require_once WP_SHIELD_DIR . 'includes/class-wp-shield-login-guard.php';
require_once WP_SHIELD_DIR . 'includes/class-wp-shield-admin.php';

/**
 * Default settings. Stored as a single option so activation/uninstall stay simple.
 */
function wp_shield_default_settings() {
	return array(
		'firewall_enabled'      => true,
		'rate_limit_enabled'    => true,
		'login_guard_enabled'   => true,
		'rate_limit_requests'   => 120,   // requests
		'rate_limit_window'     => 60,    // seconds
		'login_max_attempts'    => 5,
		'login_window'          => 15 * MINUTE_IN_SECONDS,
		'login_lockout'         => 15 * MINUTE_IN_SECONDS,
		'ip_blocklist'          => array(),
		'ip_allowlist'          => array(),
		'block_bad_bots'        => true,
		'log_limit'             => 200,
	);
}

function wp_shield_get_settings() {
	$defaults = wp_shield_default_settings();
	$saved    = get_option( 'wp_shield_settings', array() );
	return wp_parse_args( $saved, $defaults );
}

register_activation_hook( __FILE__, function () {
	if ( false === get_option( 'wp_shield_settings', false ) ) {
		add_option( 'wp_shield_settings', wp_shield_default_settings() );
	}
	if ( false === get_option( 'wp_shield_log', false ) ) {
		add_option( 'wp_shield_log', array(), '', false );
	}
} );

/**
 * Boot the modules as early as possible. WP Shield runs as a normal plugin,
 * so it loads after WordPress core has already parsed the request — see the
 * README for how to promote it to a must-use plugin for earlier protection.
 */
add_action( 'plugins_loaded', function () {
	$settings = wp_shield_get_settings();

	if ( ! empty( $settings['firewall_enabled'] ) ) {
		WP_Shield_Firewall::instance( $settings )->hook();
	}
	if ( ! empty( $settings['rate_limit_enabled'] ) ) {
		WP_Shield_Rate_Limiter::instance( $settings )->hook();
	}
	if ( ! empty( $settings['login_guard_enabled'] ) ) {
		WP_Shield_Login_Guard::instance( $settings )->hook();
	}

	WP_Shield_Admin::instance()->hook();
}, 1 );
