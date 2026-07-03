<?php
namespace SocialAuth\User;

class UserMeta {

	private const PREFIX = '_socialauth_';

	/**
	 * Get all linked providers for a user.
	 */
	public static function get_linked_providers( int $user_id ): array {
		global $wpdb;

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
				$user_id,
				self::PREFIX . 'provider_id_%'
			)
		);

		return array_filter( $results );
	}

	/**
	 * Check if user has linked a specific provider.
	 */
	public static function has_provider( int $user_id, string $provider ): bool {
		$provider_id = get_user_meta( $user_id, self::PREFIX . 'provider_id_' . $provider, true );
		return ! empty( $provider_id );
	}

	/**
	 * Get user's profile data from a provider.
	 */
	public static function get_profile( int $user_id, string $provider ): array {
		return (array) get_user_meta( $user_id, self::PREFIX . 'profile_' . $provider, true );
	}

	/**
	 * Unlink a provider from a user.
	 */
	public static function unlink_provider( int $user_id, string $provider ): void {
		delete_user_meta( $user_id, self::PREFIX . 'provider_id_' . $provider );
		delete_user_meta( $user_id, self::PREFIX . 'profile_' . $provider );
		delete_user_meta( $user_id, self::PREFIX . 'linked_at_' . $provider );
	}
}
