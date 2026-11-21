<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal rolling log, stored as a single option so there is no custom
 * table to create or clean up. Capped to the configured limit (default 200).
 */
class WP_Shield_Logger {

	public static function record( $reason, $context = array() ) {
		$settings = wp_shield_get_settings();
		$limit    = isset( $settings['log_limit'] ) ? (int) $settings['log_limit'] : 200;

		$log   = get_option( 'wp_shield_log', array() );
		$entry = array_merge(
			array(
				'time'   => time(),
				'ip'     => WP_Shield_IP::get_client_ip(),
				'uri'    => isset( $_SERVER['REQUEST_URI'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 0, 300 ) : '',
				'reason' => $reason,
			),
			$context
		);

		array_unshift( $log, $entry );
		if ( count( $log ) > $limit ) {
			$log = array_slice( $log, 0, $limit );
		}

		update_option( 'wp_shield_log', $log, false );
	}

	public static function get_log() {
		return get_option( 'wp_shield_log', array() );
	}

	public static function clear() {
		update_option( 'wp_shield_log', array(), false );
	}
}
