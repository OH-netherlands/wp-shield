<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Shield_Admin {

	protected static $instance;

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function hook() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_wp_shield_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_wp_shield_clear_log', array( $this, 'clear_log' ) );
	}

	public function add_menu() {
		add_options_page(
			__( 'WP Shield', 'wp-shield' ),
			__( 'WP Shield', 'wp-shield' ),
			'manage_options',
			'wp-shield',
			array( $this, 'render_page' )
		);
	}

	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wp-shield' ) );
		}
		check_admin_referer( 'wp_shield_save_settings' );

		$settings = wp_shield_get_settings();

		$settings['firewall_enabled']    = ! empty( $_POST['firewall_enabled'] );
		$settings['rate_limit_enabled']  = ! empty( $_POST['rate_limit_enabled'] );
		$settings['login_guard_enabled'] = ! empty( $_POST['login_guard_enabled'] );
		$settings['block_bad_bots']      = ! empty( $_POST['block_bad_bots'] );

		$settings['rate_limit_requests'] = max( 10, (int) ( $_POST['rate_limit_requests'] ?? 120 ) );
		$settings['rate_limit_window']   = max( 5, (int) ( $_POST['rate_limit_window'] ?? 60 ) );
		$settings['login_max_attempts']  = max( 3, (int) ( $_POST['login_max_attempts'] ?? 5 ) );
		$settings['login_window']        = max( 60, (int) ( $_POST['login_window'] ?? 900 ) );
		$settings['login_lockout']       = max( 60, (int) ( $_POST['login_lockout'] ?? 900 ) );

		$settings['ip_blocklist'] = $this->textarea_to_list( $_POST['ip_blocklist'] ?? '' );
		$settings['ip_allowlist'] = $this->textarea_to_list( $_POST['ip_allowlist'] ?? '' );

		update_option( 'wp_shield_settings', $settings );

		wp_safe_redirect( add_query_arg( array( 'page' => 'wp-shield', 'updated' => '1' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	public function clear_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wp-shield' ) );
		}
		check_admin_referer( 'wp_shield_clear_log' );
		WP_Shield_Logger::clear();
		wp_safe_redirect( add_query_arg( array( 'page' => 'wp-shield', 'cleared' => '1' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	protected function textarea_to_list( $raw ) {
		$raw   = sanitize_textarea_field( wp_unslash( $raw ) );
		$lines = preg_split( '/[\r\n]+/', $raw );
		$lines = array_filter( array_map( 'trim', $lines ) );
		return array_values( $lines );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s   = wp_shield_get_settings();
		$log = WP_Shield_Logger::get_log();
		include WP_SHIELD_DIR . 'includes/views/settings-page.php';
	}
}
