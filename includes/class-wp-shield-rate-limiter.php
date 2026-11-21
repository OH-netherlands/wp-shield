<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple per-IP request rate limiting using a transient counter. Good
 * enough to blunt a basic flood from a single IP; it is not a replacement
 * for rate limiting at the load balancer / CDN level for anything facing
 * serious volumetric abuse.
 */
class WP_Shield_Rate_Limiter {

	protected static $instance;
	protected $settings;

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
		add_action( 'init', array( $this, 'check_rate' ), 2 );
	}

	public function check_rate() {
		// Never rate-limit logged-in users with manage_options; avoids
		// locking the site owner out of wp-admin during a false positive.
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$ip      = WP_Shield_IP::get_client_ip();
		$window  = max( 5, (int) ( $this->settings['rate_limit_window'] ?? 60 ) );
		$limit   = max( 10, (int) ( $this->settings['rate_limit_requests'] ?? 120 ) );
		$key     = 'wp_shield_rl_' . md5( $ip );

		$count = get_transient( $key );
		if ( false === $count ) {
			set_transient( $key, 1, $window );
			return;
		}

		$count = (int) $count + 1;
		set_transient( $key, $count, $window );

		if ( $count > $limit ) {
			WP_Shield_Logger::record( 'rate_limit', array( 'count' => $count, 'window' => $window ) );
			do_action( 'wp_shield_rate_limited', $ip, $count );

			status_header( 429 );
			nocache_headers();
			header( 'Retry-After: ' . $window );
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo "Too Many Requests\n";
			exit;
		}
	}
}
