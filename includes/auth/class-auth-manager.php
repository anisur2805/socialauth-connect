<?php
namespace SocialAuth\Auth;

use SocialAuth\User\UserManager;
use SocialAuth\Security\RateLimiter;
use SocialAuth\Database\DbManager;
use SocialAuth\Helpers\Logger;

class AuthManager {

    private array $providers = [];

    public function __construct( array $providers ) {
        $this->providers = $providers;
    }

    /**
     * Register all WordPress hooks for OAuth flow.
     */
    public function register_hooks(): void {
        add_action( 'init', [ $this, 'handle_callback' ] );
        add_action( 'init', [ $this, 'handle_redirect' ] );
    }

    /**
     * Handle the initial redirect to provider.
     */
    public function handle_redirect(): void {
        if ( ! isset( $_GET['socialauth_action'], $_GET['socialauth_provider'] ) ) {
            return;
        }

        if ( $_GET['socialauth_action'] !== 'login' ) {
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

        if ( $_GET['socialauth_action'] !== 'callback' ) {
            return;
        }

        $provider_id = sanitize_key( $_GET['socialauth_provider'] );
        $provider    = $this->get_provider( $provider_id );

        if ( ! $provider ) {
            wp_die( esc_html__( 'Invalid provider.', 'socialauth-connect' ), 400 );
        }

        // Validate CSRF state.
        $state = sanitize_text_field( $_GET['state'] ?? '' );
        if ( ! StateManager::validate( $state, $provider_id ) ) {
            Logger::warning( 'Invalid state in OAuth callback', [ 'provider' => $provider_id ] );
            wp_die( esc_html__( 'Invalid authentication state. Please try again.', 'socialauth-connect' ), 403 );
        }

        // Check for error from provider.
        if ( isset( $_GET['error'] ) ) {
            $error = sanitize_text_field( $_GET['error'] );
            Logger::warning( 'Provider returned error', [ 'provider' => $provider_id, 'error' => $error ] );
            wp_redirect( add_query_arg( 'socialauth_error', urlencode( $error ), wp_login_url() ) );
            exit;
        }

        $code   = sanitize_text_field( $_GET['code'] ?? '' );
        $tokens = $provider->exchange_code_for_token( $code );

        if ( empty( $tokens['access_token'] ) ) {
            wp_die( esc_html__( 'Failed to retrieve access token.', 'socialauth-connect' ), 500 );
        }

        $raw_profile = $provider->get_user_profile( $tokens['access_token'] );
        $social_user = $provider->normalize_user( $raw_profile );
        $wp_user     = UserManager::authenticate( $social_user );

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

        $redirect = apply_filters( 'socialauth_login_redirect', admin_url(), $wp_user );
        wp_redirect( $redirect );
        exit;
    }

    public function get_provider( string $id ): ?object {
        return $this->providers[ $id ] ?? null;
    }

    public function get_providers(): array {
        return $this->providers;
    }
}
