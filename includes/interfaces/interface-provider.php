<?php
namespace SocialAuth\Interfaces;

interface ProviderInterface {

	/**
	 * Return unique provider ID slug.
	 */
	public function get_id(): string;

	/**
	 * Return display name of provider.
	 */
	public function get_name(): string;

	/**
	 * Build and return the authorization URL.
	 */
	public function get_auth_url(): string;

	/**
	 * Exchange authorization code for access token.
	 */
	public function exchange_code_for_token( string $code ): array;

	/**
	 * Fetch the authenticated user's profile data.
	 */
	public function get_user_profile( string $access_token ): array;

	/**
	 * Return normalized user data array.
	 * Keys: id, email, name, first_name, last_name, avatar_url
	 */
	public function normalize_user( array $raw_profile ): array;

	/**
	 * Check if provider is configured and enabled.
	 */
	public function is_enabled(): bool;
}
