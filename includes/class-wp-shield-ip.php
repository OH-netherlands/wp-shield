<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IP helpers. Honors common reverse-proxy headers (Cloudflare, generic
 * X-Forwarded-For) only when WP_SHIELD_TRUST_PROXY is defined truthy in
 * wp-config.php, since trusting these headers blindly lets a visitor spoof
 * their IP on a server that isn't actually behind that proxy.
 */
class WP_Shield_IP {

	public static function get_client_ip() {
		$trust_proxy = defined( 'WP_SHIELD_TRUST_PROXY' ) && WP_SHIELD_TRUST_PROXY;

		if ( $trust_proxy ) {
			if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				return self::sanitize_ip( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			}
			if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$parts = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
				return self::sanitize_ip( trim( $parts[0] ) );
			}
		}

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return self::sanitize_ip( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return '0.0.0.0';
	}

	public static function sanitize_ip( $ip ) {
		$ip = filter_var( trim( $ip ), FILTER_VALIDATE_IP );
		return $ip ? $ip : '0.0.0.0';
	}

	public static function ip_in_list( $ip, array $list ) {
		foreach ( $list as $entry ) {
			$entry = trim( $entry );
			if ( '' === $entry ) {
				continue;
			}
			if ( strpos( $entry, '/' ) !== false ) {
				if ( self::ip_in_cidr( $ip, $entry ) ) {
					return true;
				}
			} elseif ( $entry === $ip ) {
				return true;
			}
		}
		return false;
	}

	protected static function ip_in_cidr( $ip, $cidr ) {
		if ( strpos( $ip, ':' ) !== false || strpos( $cidr, ':' ) !== false ) {
			return false; // IPv6 CIDR not supported in v0.1.
		}
		list( $subnet, $mask ) = array_pad( explode( '/', $cidr ), 2, 32 );
		$mask                  = (int) $mask;
		if ( $mask < 0 || $mask > 32 ) {
			return false;
		}
		$ip_long     = ip2long( $ip );
		$subnet_long = ip2long( $subnet );
		if ( false === $ip_long || false === $subnet_long ) {
			return false;
		}
		$mask_long = -1 << ( 32 - $mask );
		return ( $ip_long & $mask_long ) === ( $subnet_long & $mask_long );
	}
}
