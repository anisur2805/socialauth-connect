<?php
defined( 'ABSPATH' ) || exit;
namespace SocialAuth\Auth;

use SocialAuth\User\UserManager;
use SocialAuth\Security\RateLimiter;
use SocialAuth\Database\DbManager;
use SocialAuth\Helpers\Logger;

class AuthManager {

	private array $providers = array();

	public function __construct( array $providers ) {
		$this->providers = $providers;
	}

	/**
	 * Register all WordPress hooks for OAuth flow.
	 */
	public function register_hooks(): void {
		// 'init' fires on every request (front-end and wp-login.php),
		// unlike 'template_redirect' which never runs on wp-login.php.
		add_action( 'init', array( $this, 'handle_callback' ) );
		add_action( 'init', array( $this, 'handle_redirect' ) );
	}

	/**
	 * Handle the initial redirect to provider.
	 */
	public function handle_redirect(): void {
		if ( ! isset( $_GET['socialauth_action'], $_GET['socialauth_provider'] ) ) {
			return;
		}

		if ( 'login' !== $_GET['socialauth_action'] ) {
			return;
		}

		$provider_id = sanitize_key( $_GET['socialauth_provider'] );
		$provider    = $this->get_provider( $provider_id );

		if ( ! $provider || ! $provider->is_enabled() ) {
			wp_die( esc_html__( 'Authentication provider not available.', 'socialauth-connect' ), 403 );
		}

		// Rate limiting check.
		if ( ! RateLimiter::check( 'redirect_' . $provider_id ) ) {
			wp_die( esc_html__( 'Too many authentication attempts. Please wait.', 'socialauth-connect' ), 429 );
		}

		wp_redirect( $provider->get_auth_url() );
		exit;
	}

	/**
	 * Handle OAuth callback from provider.
	 */
	public function handle_callback(): void {
		if ( ! isset( $_GET['socialauth_action'], $_GET['socialauth_provider'] ) ) {
			return;
		}

		if ( 'callback' !== $_GET['socialauth_action'] ) {
			return;
		}

		$provider_id = sanitize_key( $_GET['socialauth_provider'] );
		$provider    = $this->get_provider( $provider_id );

		if ( ! $provider ) {
			wp_die( esc_html__( 'Invalid provider.', 'socialauth-connect' ), 400 );
		}

		// Rate limiting on callback.
		if ( ! RateLimiter::check( 'callback_' . $provider_id ) ) {
			wp_die( esc_html__( 'Too many authentication attempts. Please wait.', 'socialauth-connect' ), 429 );
		}

		// Check for error from provider BEFORE consuming state.
		if ( isset( $_GET['error'] ) ) {
			$error = sanitize_text_field( $_GET['error'] );
			Logger::warning(
				'Provider returned error',
				array(
					'provider' => $provider_id,
					'error'    => $error,
				)
			);

			// Validate state even on error to prevent state manipulation.
			$state = sanitize_text_field( $_GET['state'] ?? '' );
			StateManager::validate( $state, $provider_id );

			wp_redirect( add_query_arg( 'socialauth_error', urlencode( $error ), wp_login_url() ) );
			exit;
		}

		// Validate CSRF state.
		$state = sanitize_text_field( $_GET['state'] ?? '' );
		if ( ! StateManager::validate( $state, $provider_id ) ) {
			Logger::warning( 'Invalid state in OAuth callback', array( 'provider' => $provider_id ) );
			wp_die( esc_html__( 'Invalid authentication state. Please try again.', 'socialauth-connect' ), 403 );
		}

		$code = sanitize_text_field( $_GET['code'] ?? '' );
		if ( empty( $code ) ) {
			wp_die( esc_html__( 'Missing authorization code.', 'socialauth-connect' ), 400 );
		}

		$tokens = $provider->exchange_code_for_token( $code );

		if ( empty( $tokens['access_token'] ) ) {
			Logger::error( 'Token exchange failed', array( 'provider' => $provider_id ) );
			wp_die( esc_html__( 'Failed to retrieve access token.', 'socialauth-connect' ), 500 );
		}

		$raw_profile = $provider->get_user_profile( $tokens['access_token'] );

		if ( empty( $raw_profile ) ) {
			Logger::error( 'Profile fetch failed', array( 'provider' => $provider_id ) );
			wp_redirect( add_query_arg( 'socialauth_error', 'profile_fetch_failed', wp_login_url() ) );
			exit;
		}

		$social_user = $provider->normalize_user( $raw_profile );

		/** This filter is documented in plan.md */
		$social_user = apply_filters( 'socialauth_normalize_user', $social_user, $provider_id );

		// Check email verification before account linking (C-5).
		if ( ! empty( $social_user['email'] ) && empty( $social_user['verified'] ) ) {
			Logger::warning(
				'Unverified email rejected',
				array(
					'provider' => $provider_id,
					'email'    => $social_user['email'],
				)
			);
			wp_redirect( add_query_arg( 'socialauth_error', 'email_not_verified', wp_login_url() ) );
			exit;
		}

		$wp_user = UserManager::authenticate( $social_user );

		if ( ! $wp_user ) {
			wp_redirect( add_query_arg( 'socialauth_error', 'registration_disabled', wp_login_url() ) );
			exit;
		}

		// Store tokens.
		TokenManager::store( $wp_user->ID, $provider_id, $tokens );

		// Log the user in.
		wp_set_auth_cookie( $wp_user->ID, true );
		do_action( 'wp_login', $wp_user->user_login, $wp_user );
		do_action( 'socialauth_login_success', $wp_user->ID, $provider_id );

		// Audit log.
		DbManager::log( $wp_user->ID, $provider_id, 'login' );

		// Redirect with validation and shortcode redirect support.
		$redirect = admin_url();

		// Support shortcode redirect attribute.
		if ( ! empty( $_GET['socialauth_redirect'] ) ) {
			$redirect = wp_validate_redirect( sanitize_url( wp_unslash( $_GET['socialauth_redirect'] ) ), admin_url() );
		}

		$redirect = apply_filters( 'socialauth_login_redirect', $redirect, $wp_user );
		wp_redirect( wp_validate_redirect( $redirect, admin_url() ) );
		exit;
	}

	public function get_provider( string $id ): ?object {
		return $this->providers[ $id ] ?? null;
	}

	public function get_providers(): array {
		return $this->providers;
	}
}
