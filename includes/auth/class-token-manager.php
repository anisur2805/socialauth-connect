<?php
namespace SocialAuth\Auth;

class TokenManager {

	/**
	 * Store tokens for a user + provider.
	 */
	public static function store( int $user_id, string $provider, array $tokens ): void {
		update_user_meta( $user_id, '_socialauth_access_token_' . $provider, $tokens['access_token'] ?? '' );

		if ( ! empty( $tokens['refresh_token'] ) ) {
			update_user_meta( $user_id, '_socialauth_refresh_token_' . $provider, $tokens['refresh_token'] );
		}

		if ( ! empty( $tokens['expires_in'] ) ) {
			$expires_at = gmdate( 'Y-m-d H:i:s', time() + (int) $tokens['expires_in'] );
			update_user_meta( $user_id, '_socialauth_token_expires_' . $provider, $expires_at );
		}
	}

	/**
	 * Get access token for a user + provider.
	 */
	public static function get( int $user_id, string $provider ): string {
		return get_user_meta( $user_id, '_socialauth_access_token_' . $provider, true ) ?? '';
	}

	/**
	 * Get refresh token for a user + provider.
	 */
	public static function get_refresh_token( int $user_id, string $provider ): string {
		return get_user_meta( $user_id, '_socialauth_refresh_token_' . $provider, true ) ?? '';
	}

	/**
	 * Check if token is expired.
	 */
	public static function is_expired( int $user_id, string $provider ): bool {
		$expires = get_user_meta( $user_id, '_socialauth_token_expires_' . $provider, true );

		if ( empty( $expires ) ) {
			return false; // No expiry recorded = treat as valid.
		}

		return strtotime( $expires ) <= time();
	}

	/**
	 * Delete all tokens for a user + provider.
	 */
	public static function delete( int $user_id, string $provider ): void {
		delete_user_meta( $user_id, '_socialauth_access_token_' . $provider );
		delete_user_meta( $user_id, '_socialauth_refresh_token_' . $provider );
		delete_user_meta( $user_id, '_socialauth_token_expires_' . $provider );
	}
}
