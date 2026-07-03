<?php
namespace SocialAuth\Auth;

class NonceManager {

	/**
	 * Create a nonce for a given action.
	 */
	public static function create( string $action ): string {
		return wp_create_nonce( 'socialauth_' . $action );
	}

	/**
	 * Verify a nonce for a given action.
	 */
	public static function verify( string $nonce, string $action ): bool {
		return 1 === wp_verify_nonce( $nonce, 'socialauth_' . $action );
	}
}
