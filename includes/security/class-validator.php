<?php
namespace SocialAuth\Security;

class Validator {

	/**
	 * Validate that a string is non-empty.
	 */
	public static function required( string $value, string $field_name ): \WP_Error|true {
		if ( empty( trim( $value ) ) ) {
			return new \WP_Error( 'required', sprintf( '%s is required.', $field_name ) );
		}
		return true;
	}

	/**
	 * Validate an email address.
	 */
	public static function email( string $value ): \WP_Error|true {
		if ( ! is_email( $value ) ) {
			return new \WP_Error( 'invalid_email', 'Invalid email address.' );
		}
		return true;
	}

	/**
	 * Validate that a value is a valid URL.
	 */
	public static function url( string $value ): \WP_Error|true {
		if ( empty( esc_url_raw( $value ) ) ) {
			return new \WP_Error( 'invalid_url', 'Invalid URL.' );
		}
		return true;
	}

	/**
	 * Validate a provider ID (alphanumeric + underscores only).
	 */
	public static function provider_id( string $value ): \WP_Error|true {
		if ( ! preg_match( '/^[a-z0-9_]+$/', $value ) ) {
			return new \WP_Error( 'invalid_provider_id', 'Invalid provider ID.' );
		}
		return true;
	}

	/**
	 * Validate OAuth state token format (hex string, 64 chars).
	 */
	public static function state_token( string $value ): \WP_Error|true {
		if ( ! preg_match( '/^[a-f0-9]{64}$/', $value ) ) {
			return new \WP_Error( 'invalid_state', 'Invalid state token format.' );
		}
		return true;
	}
}
