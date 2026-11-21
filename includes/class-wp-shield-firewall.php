<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Request-level firewall: IP allow/block lists, a conservative set of
 * known-malicious request patterns, and (optional) bad-bot user-agent
 * blocking.
 *
 * This inspects the request on 'init', which is early for a normal plugin
 * but still after WordPress core has bootstrapped. For stronger protection
 * (inspecting the request before WP core loads at all), copy wp-shield.php
 * into wp-content/mu-plugins/ — see README.md.
 */
class WP_Shield_Firewall {

	protected static $instance;
	protected $settings;

	/**
	 * Conservative, low-false-positive patterns aimed at the request URI
	 * and query string: path traversal, obvious SQLi tokens, attempts to
	 * read sensitive files, and common PHP-injection payload shapes.
	 * This is a starting point, not a substitute for a managed WAF on a
	 * high-value target — see README.md.
	 */
	const URI_PATTERNS = array(
		'/\.\.\//',                         // path traversal
		'/\b(union)\s+(all\s+)?select\b/i', // SQLi
		'/\bunion\s+select\b/i',
		'/base64_decode\s*\(/i',
		'/\betc\/passwd\b/i',
		'/wp-config\.php/i',
		'/<\s*script\b/i',
		'/\bphpinfo\s*\(/i',
		'/\bSELECT\b.*\bFROM\b.*\bWHERE\b/i',
	);

	const BAD_BOT_PATTERNS = array(
		'/MJ12bot/i',
		'/AhrefsBot/i',
		'/SemrushBot/i',
		'/DotBot/i',
		'/PetalBot/i',
		'/python-requests/i',
		'/masscan/i',
		'/sqlmap/i',
		'/nikto/i',
	);

	public static function instance( $settings = array() ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $settings );
		}
		return self::$instance;
	}

	protected function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function hook() {
		add_action( 'init', array( $this, 'check_request' ), 1 );
	}

	public function check_request() {
		$ip = WP_Shield_IP::get_client_ip();

		$allowlist = (array) ( $this->settings['ip_allowlist'] ?? array() );
		if ( WP_Shield_IP::ip_in_list( $ip, $allowlist ) ) {
			return; // Trusted IP: skip all firewall checks.
		}

		$blocklist = (array) ( $this->settings['ip_blocklist'] ?? array() );
		if ( WP_Shield_IP::ip_in_list( $ip, $blocklist ) ) {
			$this->block( 'ip_blocklist' );
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		foreach ( self::URI_PATTERNS as $pattern ) {
			if ( preg_match( $pattern, $uri ) ) {
				$this->block( 'uri_pattern', array( 'pattern' => $pattern ) );
			}
		}

		if ( ! empty( $this->settings['block_bad_bots'] ) ) {
			$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
			foreach ( self::BAD_BOT_PATTERNS as $pattern ) {
				if ( $ua && preg_match( $pattern, $ua ) ) {
					$this->block( 'bad_bot', array( 'pattern' => $pattern ) );
				}
			}
		}
	}

	protected function block( $reason, $context = array() ) {
		WP_Shield_Logger::record( $reason, $context );

		/**
		 * Fires right before WP Shield blocks a request. A theme or
		 * another plugin can hook this for its own alerting.
		 */
		do_action( 'wp_shield_blocked', $reason, $context );

		status_header( 403 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo "Forbidden\n";
		exit;
	}
}
