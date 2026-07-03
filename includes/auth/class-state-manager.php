<?php
namespace SocialAuth\Auth;

class StateManager {

	private const TRANSIENT_PREFIX = 'socialauth_state_';
	private const EXPIRY_SECONDS   = 600;

	public static function generate( string $provider ): string {
		$state = bin2hex( random_bytes( 32 ) );

		set_transient(
			'socialauth_state_' . $state,
			array(
				'provider'   => $provider,
				'created_at' => time(),
				'user_ip'    => self::get_client_ip(),
			),
			self::EXPIRY_SECONDS
		);

		return $state;
	}

	/**
	 * Validate and consume a state token (one-time use).
	 */
	public static function validate( string $state, string $provider ): bool {
		$key  = self::TRANSIENT_PREFIX . $state;
		$data = get_transient( $key );

		if ( false === $data ) {
			return false; // Expired or not found.
		}

		// One-time use: delete immediately.
		delete_transient( $key );

		if ( $data['provider'] !== $provider ) {
			return false;
		}

		// Optional: IP binding check (can disable for mobile users).
		if ( apply_filters( 'socialauth_state_check_ip', true ) ) {
			if ( $data['user_ip'] !== self::get_client_ip() ) {
				return false;
			}
		}

		return true;
	}

	private static function get_client_ip(): string {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}
}
