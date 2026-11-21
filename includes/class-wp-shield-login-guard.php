<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brute-force login protection: locks out an IP after N failed logins
 * within a rolling window, for a configurable lockout period. Counters
 * are per-IP transients, so no database table is needed.
 */
class WP_Shield_Login_Guard {

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
		add_filter( 'authenticate', array( $this, 'maybe_block_login' ), 20, 1 );
		add_action( 'wp_login_failed', array( $this, 'record_failed_attempt' ) );
		add_action( 'wp_login', array( $this, 'clear_on_success' ) );
	}

	protected function fail_key( $ip ) {
		return 'wp_shield_lf_' . md5( $ip );
	}

	protected function lock_key( $ip ) {
		return 'wp_shield_lk_' . md5( $ip );
	}

	public function maybe_block_login( $user ) {
		$ip = WP_Shield_IP::get_client_ip();

		if ( get_transient( $this->lock_key( $ip ) ) ) {
			WP_Shield_Logger::record( 'login_locked_out' );
			return new WP_Error(
				'wp_shield_locked_out',
				__( 'Too many failed login attempts. Please try again later.', 'wp-shield' )
			);
		}

		return $user;
	}

	public function record_failed_attempt( $username ) {
		$ip       = WP_Shield_IP::get_client_ip();
		$window   = max( 60, (int) ( $this->settings['login_window'] ?? 15 * MINUTE_IN_SECONDS ) );
		$max      = max( 3, (int) ( $this->settings['login_max_attempts'] ?? 5 ) );
		$lockout  = max( 60, (int) ( $this->settings['login_lockout'] ?? 15 * MINUTE_IN_SECONDS ) );

		$count = (int) get_transient( $this->fail_key( $ip ) );
		$count++;
		set_transient( $this->fail_key( $ip ), $count, $window );

		if ( $count >= $max ) {
			set_transient( $this->lock_key( $ip ), 1, $lockout );
			WP_Shield_Logger::record( 'login_lockout_triggered', array( 'attempts' => $count ) );
		} else {
			WP_Shield_Logger::record( 'login_failed', array( 'attempts' => $count ) );
		}
	}

	public function clear_on_success( $user_login ) {
		$ip = WP_Shield_IP::get_client_ip();
		delete_transient( $this->fail_key( $ip ) );
		delete_transient( $this->lock_key( $ip ) );
	}
}
