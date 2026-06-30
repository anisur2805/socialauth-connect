WordPress Plugin: SocialAuth Connect
A modular, extensible social authentication plugin for WordPress following industry best practices.

📋 Plugin Overview
Field	Details
Plugin Name	SocialAuth Connect
Slug	socialauth-connect
Version	1.0.0
License	GPL-2.0+
Requires WP	6.0+
Requires PHP	8.1+
Author	Your Name
🎯 Goals & Objectives
Provide OAuth 2.0 / OpenID Connect authentication via Google (Phase 1)
Allow users to register, login, and link social accounts
Built with extensibility in mind — FB, X, Email Magic Link, GitHub, etc.
Follow WordPress coding standards and security best practices
Zero external framework dependency (pure WordPress APIs)
🗂️ Directory Structure
text

socialauth-connect/
├── socialauth-connect.php          # Main plugin bootstrap file
├── uninstall.php                   # Clean uninstall handler
├── readme.txt                      # WordPress.org readme
├── composer.json                   # Autoloading + dependencies
├── package.json                    # Frontend assets (optional)
│
├── assets/
│   ├── css/
│   │   ├── socialauth-admin.css
│   │   └── socialauth-public.css
│   ├── js/
│   │   ├── socialauth-admin.js
│   │   └── socialauth-public.js
│   └── images/
│       ├── google-logo.svg
│       └── btn-google.png
│
├── includes/
│   ├── class-socialauth-loader.php        # Hook loader
│   ├── class-socialauth-activator.php     # Activation handler
│   ├── class-socialauth-deactivator.php   # Deactivation handler
│   ├── class-socialauth-i18n.php          # Internationalisation
│   ├── class-socialauth-core.php          # Core plugin orchestrator
│   │
│   ├── abstracts/
│   │   ├── abstract-provider.php          # Base provider contract
│   │   └── abstract-oauth2-provider.php   # OAuth2 base logic
│   │
│   ├── interfaces/
│   │   └── interface-provider.php         # Provider interface
│   │
│   ├── providers/
│   │   ├── class-provider-google.php      # Google OAuth2 + OIDC
│   │   ├── class-provider-facebook.php    # Placeholder (Phase 2)
│   │   ├── class-provider-x.php           # Placeholder (Phase 2)
│   │   └── class-provider-email.php       # Magic Link (Phase 2)
│   │
│   ├── auth/
│   │   ├── class-auth-manager.php         # Auth flow orchestrator
│   │   ├── class-token-manager.php        # Token storage + refresh
│   │   ├── class-state-manager.php        # CSRF state management
│   │   └── class-nonce-manager.php        # WP nonce wrapper
│   │
│   ├── user/
│   │   ├── class-user-manager.php         # WP user create/link/find
│   │   └── class-user-meta.php            # Social account meta
│   │
│   ├── admin/
│   │   ├── class-admin-menu.php           # Admin pages
│   │   ├── class-admin-settings.php       # Settings API wrapper
│   │   └── class-admin-notices.php        # Admin notices
│   │
│   ├── frontend/
│   │   ├── class-login-buttons.php        # Button rendering
│   │   ├── class-shortcode.php            # [socialauth_login] shortcode
│   │   └── class-block.php               # Gutenberg block (optional)
│   │
│   ├── security/
│   │   ├── class-rate-limiter.php         # Brute-force protection
│   │   ├── class-sanitizer.php            # Input sanitisation
│   │   └── class-validator.php            # Data validation
│   │
│   └── helpers/
│       ├── class-logger.php               # Debug/error logging
│       ├── class-http-client.php          # Wrapper for wp_remote_*
│       └── functions.php                  # Global helper functions
│
├── admin/
│   ├── partials/
│   │   ├── settings-page.php              # Main settings view
│   │   ├── settings-google.php            # Google tab view
│   │   └── settings-general.php           # General options view
│   └── class-admin-page.php
│
├── public/
│   └── partials/
│       ├── button-google.php              # Google sign-in button
│       └── modal-login.php               # Optional modal overlay
│
├── database/
│   └── class-db-manager.php               # Custom tables manager
│
├── languages/
│   └── socialauth-connect.pot
│
└── tests/
    ├── bootstrap.php
    ├── unit/
    │   ├── test-auth-manager.php
    │   ├── test-state-manager.php
    │   ├── test-user-manager.php
    │   └── test-provider-google.php
    └── integration/
        └── test-google-flow.php
🔌 Core Plugin File
socialauth-connect.php
PHP

<?php
/**
 * Plugin Name:       SocialAuth Connect
 * Plugin URI:        https://yoursite.com/socialauth-connect
 * Description:       Modular social authentication with Google OAuth2/OIDC. Extensible to Facebook, X, Email, and more.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Your Name
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       socialauth-connect
 * Domain Path:       /languages
 *
 * @package SocialAuthConnect
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'SOCIALAUTH_VERSION', '1.0.0' );
define( 'SOCIALAUTH_PLUGIN_FILE', __FILE__ );
define( 'SOCIALAUTH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SOCIALAUTH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SOCIALAUTH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader
require_once SOCIALAUTH_PLUGIN_DIR . 'vendor/autoload.php';

// Activation / Deactivation hooks
register_activation_hook( __FILE__, [ 'SocialAuth\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SocialAuth\Deactivator', 'deactivate' ] );

// Bootstrap
require_once SOCIALAUTH_PLUGIN_DIR . 'includes/class-socialauth-core.php';

function socialauth_run(): void {
    $plugin = new \SocialAuth\Core();
    $plugin->run();
}

socialauth_run();
🔐 Provider Interface & Abstract Classes
includes/interfaces/interface-provider.php
PHP

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
includes/abstracts/abstract-oauth2-provider.php
PHP

<?php
namespace SocialAuth\Abstracts;

use SocialAuth\Interfaces\ProviderInterface;
use SocialAuth\Auth\StateManager;
use SocialAuth\Helpers\HttpClient;
use SocialAuth\Helpers\Logger;

abstract class AbstractOAuth2Provider implements ProviderInterface {

    protected string $client_id;
    protected string $client_secret;
    protected string $redirect_uri;
    protected array  $scopes    = [];
    protected string $auth_url  = '';
    protected string $token_url = '';

    public function __construct() {
        $options             = get_option( 'socialauth_' . $this->get_id() . '_settings', [] );
        $this->client_id     = sanitize_text_field( $options['client_id']     ?? '' );
        $this->client_secret = sanitize_text_field( $options['client_secret'] ?? '' );
        $this->redirect_uri  = $this->build_redirect_uri();
    }

    /**
     * Build the standardised OAuth2 callback URL.
     */
    protected function build_redirect_uri(): string {
        return add_query_arg(
            [
                'socialauth_provider' => $this->get_id(),
                'socialauth_action'   => 'callback',
            ],
            site_url( '/' )
        );
    }

    /**
     * Build authorization URL with CSRF state.
     */
    public function get_auth_url(): string {
        $state = StateManager::generate( $this->get_id() );

        return add_query_arg(
            [
                'client_id'     => $this->client_id,
                'redirect_uri'  => $this->redirect_uri,
                'response_type' => 'code',
                'scope'         => implode( ' ', $this->scopes ),
                'state'         => $state,
                'access_type'   => 'offline', // For refresh tokens where supported
            ],
            $this->auth_url
        );
    }

    /**
     * Exchange auth code for token via wp_remote_post.
     */
    public function exchange_code_for_token( string $code ): array {
        $response = HttpClient::post(
            $this->token_url,
            [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'code'          => $code,
                'redirect_uri'  => $this->redirect_uri,
                'grant_type'    => 'authorization_code',
            ]
        );

        if ( is_wp_error( $response ) ) {
            Logger::error( 'Token exchange failed', [ 'provider' => $this->get_id(), 'error' => $response->get_error_message() ] );
            return [];
        }

        return $response;
    }

    public function is_enabled(): bool {
        $options = get_option( 'socialauth_' . $this->get_id() . '_settings', [] );
        return ! empty( $options['enabled'] ) && ! empty( $this->client_id ) && ! empty( $this->client_secret );
    }
}
🔵 Google Provider Implementation
includes/providers/class-provider-google.php
PHP

<?php
namespace SocialAuth\Providers;

use SocialAuth\Abstracts\AbstractOAuth2Provider;
use SocialAuth\Helpers\HttpClient;

class Google extends AbstractOAuth2Provider {

    protected string $auth_url  = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected string $token_url = 'https://oauth2.googleapis.com/token';
    protected string $userinfo_url = 'https://openidconnect.googleapis.com/v1/userinfo';

    protected array $scopes = [
        'openid',
        'email',
        'profile',
    ];

    public function get_id(): string {
        return 'google';
    }

    public function get_name(): string {
        return __( 'Google', 'socialauth-connect' );
    }

    /**
     * Fetch user info from Google's OpenID Connect userinfo endpoint.
     */
    public function get_user_profile( string $access_token ): array {
        $response = HttpClient::get(
            $this->userinfo_url,
            [],
            [ 'Authorization' => 'Bearer ' . $access_token ]
        );

        if ( is_wp_error( $response ) || empty( $response ) ) {
            return [];
        }

        return $response;
    }

    /**
     * Normalize Google profile to standard structure.
     */
    public function normalize_user( array $raw ): array {
        return [
            'provider'    => $this->get_id(),
            'provider_id' => sanitize_text_field( $raw['sub']            ?? '' ),
            'email'       => sanitize_email( $raw['email']               ?? '' ),
            'name'        => sanitize_text_field( $raw['name']           ?? '' ),
            'first_name'  => sanitize_text_field( $raw['given_name']     ?? '' ),
            'last_name'   => sanitize_text_field( $raw['family_name']    ?? '' ),
            'avatar_url'  => esc_url_raw( $raw['picture']               ?? '' ),
            'verified'    => (bool) ( $raw['email_verified']            ?? false ),
            'locale'      => sanitize_text_field( $raw['locale']        ?? '' ),
        ];
    }

    /**
     * Override: Google supports ID token (JWT) — add PKCE / nonce support.
     */
    public function get_auth_url(): string {
        $url = parent::get_auth_url();

        // Add nonce for OIDC replay attack prevention
        $nonce = wp_create_nonce( 'socialauth_google_nonce' );

        return add_query_arg( [ 'nonce' => $nonce ], $url );
    }
}
🔒 Security: State Manager (CSRF Protection)
includes/auth/class-state-manager.php
PHP

<?php
namespace SocialAuth\Auth;

class StateManager {

    private const TRANSIENT_PREFIX = 'socialauth_state_';
    private const EXPIRY_SECONDS   = 600; // 10 minutes

    /**
     * Generate a cryptographically secure state token.
     */
    public static function generate( string $provider ): string {
        $state = bin2hex( random_bytes( 32 ) );

        set_transient(
            self::TRANSIENT_PREFIX . $state,
            [
                'provider'   => $provider,
                'created_at' => time(),
                'user_ip'    => self::get_client_ip(),
            ],
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
            return false; // Expired or not found
        }

        // One-time use: delete immediately
        delete_transient( $key );

        if ( $data['provider'] !== $provider ) {
            return false;
        }

        // Optional: IP binding check (can disable for mobile users)
        if ( apply_filters( 'socialauth_state_check_ip', true ) ) {
            if ( $data['user_ip'] !== self::get_client_ip() ) {
                return false;
            }
        }

        return true;
    }

    private static function get_client_ip(): string {
        // Returns sanitised IP for state binding
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
    }
}
👤 User Manager
includes/user/class-user-manager.php
PHP

<?php
namespace SocialAuth\User;

use SocialAuth\Helpers\Logger;

class UserManager {

    /**
     * Main entry point: find, create or link WP user from social profile.
     */
    public static function authenticate( array $social_user ): \WP_User|false {
        // 1. Try to find by social provider + ID
        $wp_user = self::find_by_provider( $social_user['provider'], $social_user['provider_id'] );

        if ( $wp_user ) {
            return $wp_user;
        }

        // 2. Try to find by matching email
        if ( ! empty( $social_user['email'] ) ) {
            $wp_user = get_user_by( 'email', $social_user['email'] );
            if ( $wp_user ) {
                // Link the social account to existing user
                self::link_provider( $wp_user->ID, $social_user );
                return $wp_user;
            }
        }

        // 3. Create new user if registration is allowed
        if ( self::can_register() ) {
            return self::create_user( $social_user );
        }

        return false;
    }

    /**
     * Find WP user by social provider + social user ID.
     */
    public static function find_by_provider( string $provider, string $provider_id ): \WP_User|false {
        $users = get_users([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'   => '_socialauth_provider',
                    'value' => $provider,
                ],
                [
                    'key'   => '_socialauth_provider_id_' . $provider,
                    'value' => $provider_id,
                ],
            ],
            'number' => 1,
        ]);

        return ! empty( $users ) ? $users[0] : false;
    }

    /**
     * Create a new WordPress user from social profile data.
     */
    public static function create_user( array $social_user ): \WP_User|false {
        $username = self::generate_unique_username( $social_user );
        $email    = sanitize_email( $social_user['email'] ?? '' );

        if ( empty( $email ) ) {
            Logger::warning( 'Cannot create user: no email from provider', $social_user );
            return false;
        }

        $user_id = wp_insert_user([
            'user_login'   => $username,
            'user_email'   => $email,
            'display_name' => sanitize_text_field( $social_user['name'] ?? $username ),
            'first_name'   => sanitize_text_field( $social_user['first_name'] ?? '' ),
            'last_name'    => sanitize_text_field( $social_user['last_name']  ?? '' ),
            'user_pass'    => wp_generate_password( 32, true, true ),
            'role'         => get_option( 'default_role', 'subscriber' ),
        ]);

        if ( is_wp_error( $user_id ) ) {
            Logger::error( 'User creation failed', [ 'error' => $user_id->get_error_message() ] );
            return false;
        }

        self::link_provider( $user_id, $social_user );

        do_action( 'socialauth_user_created', $user_id, $social_user );

        return get_user_by( 'id', $user_id );
    }

    /**
     * Store social account link as user meta.
     */
    public static function link_provider( int $user_id, array $social_user ): void {
        $provider = $social_user['provider'];

        update_user_meta( $user_id, '_socialauth_provider', $provider );
        update_user_meta( $user_id, '_socialauth_provider_id_' . $provider, $social_user['provider_id'] );
        update_user_meta( $user_id, '_socialauth_profile_' . $provider, $social_user );
        update_user_meta( $user_id, '_socialauth_linked_at_' . $provider, current_time( 'mysql' ) );

        do_action( 'socialauth_provider_linked', $user_id, $provider, $social_user );
    }

    private static function generate_unique_username( array $social_user ): string {
        $base = sanitize_user(
            strtolower( $social_user['first_name'] ?? '' ) . '_' . strtolower( $social_user['last_name'] ?? '' ),
            true
        );
        $base = empty( $base ) ? 'user' : $base;

        $username = $base;
        $counter  = 1;

        while ( username_exists( $username ) ) {
            $username = $base . '_' . $counter++;
        }

        return $username;
    }

    private static function can_register(): bool {
        $plugin_allows = (bool) get_option( 'socialauth_allow_registration', true );
        $wp_allows     = (bool) get_option( 'users_can_register', false );

        return apply_filters( 'socialauth_can_register', $plugin_allows || $wp_allows );
    }
}
⚙️ Auth Flow Manager
includes/auth/class-auth-manager.php
PHP

<?php
namespace SocialAuth\Auth;

use SocialAuth\User\UserManager;
use SocialAuth\Security\RateLimiter;
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

        // Rate limiting check
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

        // Validate CSRF state
        $state = sanitize_text_field( $_GET['state'] ?? '' );
        if ( ! StateManager::validate( $state, $provider_id ) ) {
            Logger::warning( 'Invalid state in OAuth callback', [ 'provider' => $provider_id ] );
            wp_die( esc_html__( 'Invalid authentication state. Please try again.', 'socialauth-connect' ), 403 );
        }

        // Check for error from provider
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

        $raw_profile    = $provider->get_user_profile( $tokens['access_token'] );
        $social_user    = $provider->normalize_user( $raw_profile );
        $wp_user        = UserManager::authenticate( $social_user );

        if ( ! $wp_user ) {
            wp_redirect( add_query_arg( 'socialauth_error', 'registration_disabled', wp_login_url() ) );
            exit;
        }

        // Log the user in
        wp_set_auth_cookie( $wp_user->ID, true );
        do_action( 'wp_login', $wp_user->user_login, $wp_user );
        do_action( 'socialauth_login_success', $wp_user->ID, $provider_id );

        $redirect = apply_filters( 'socialauth_login_redirect', admin_url(), $wp_user );
        wp_redirect( $redirect );
        exit;
    }

    private function get_provider( string $id ): ?object {
        return $this->providers[ $id ] ?? null;
    }
}
⚙️ Admin Settings Page
includes/admin/class-admin-settings.php
PHP

<?php
namespace SocialAuth\Admin;

class AdminSettings {

    public function register_hooks(): void {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_menu(): void {
        add_options_page(
            __( 'SocialAuth Connect', 'socialauth-connect' ),
            __( 'SocialAuth', 'socialauth-connect' ),
            'manage_options',
            'socialauth-connect',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings(): void {
        // General settings
        register_setting( 'socialauth_general', 'socialauth_allow_registration', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ]);

        register_setting( 'socialauth_general', 'socialauth_login_redirect', [
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => admin_url(),
        ]);

        // Google provider settings
        register_setting( 'socialauth_google_settings', 'socialauth_google_settings', [
            'type'              => 'object',
            'sanitize_callback' => [ $this, 'sanitize_google_settings' ],
            'default'           => [],
        ]);

        // Sections
        add_settings_section(
            'socialauth_google_section',
            __( 'Google OAuth2 Settings', 'socialauth-connect' ),
            [ $this, 'render_google_section_description' ],
            'socialauth_google_settings'
        );

        // Fields
        add_settings_field(
            'google_enabled',
            __( 'Enable Google Login', 'socialauth-connect' ),
            [ $this, 'render_checkbox_field' ],
            'socialauth_google_settings',
            'socialauth_google_section',
            [ 'option_name' => 'socialauth_google_settings', 'field' => 'enabled', 'label' => __( 'Enable', 'socialauth-connect' ) ]
        );

        add_settings_field(
            'google_client_id',
            __( 'Client ID', 'socialauth-connect' ),
            [ $this, 'render_text_field' ],
            'socialauth_google_settings',
            'socialauth_google_section',
            [ 'option_name' => 'socialauth_google_settings', 'field' => 'client_id' ]
        );

        add_settings_field(
            'google_client_secret',
            __( 'Client Secret', 'socialauth-connect' ),
            [ $this, 'render_password_field' ],
            'socialauth_google_settings',
            'socialauth_google_section',
            [ 'option_name' => 'socialauth_google_settings', 'field' => 'client_secret' ]
        );
    }

    public function sanitize_google_settings( array $input ): array {
        return [
            'enabled'       => rest_sanitize_boolean( $input['enabled']       ?? false ),
            'client_id'     => sanitize_text_field( $input['client_id']       ?? '' ),
            'client_secret' => sanitize_text_field( $input['client_secret']   ?? '' ),
        ];
    }

    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        include SOCIALAUTH_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    // Field renderers ...
    public function render_text_field( array $args ): void {
        $options = get_option( $args['option_name'], [] );
        $value   = esc_attr( $options[ $args['field'] ] ?? '' );
        $field   = esc_attr( $args['field'] );
        echo "<input type='text' name='{$args['option_name']}[{$field}]' value='{$value}' class='regular-text' />";
    }

    public function render_password_field( array $args ): void {
        $options = get_option( $args['option_name'], [] );
        $value   = esc_attr( $options[ $args['field'] ] ?? '' );
        $field   = esc_attr( $args['field'] );
        echo "<input type='password' name='{$args['option_name']}[{$field}]' value='{$value}' class='regular-text' autocomplete='off' />";
    }

    public function render_checkbox_field( array $args ): void {
        $options = get_option( $args['option_name'], [] );
        $checked = checked( $options[ $args['field'] ] ?? false, true, false );
        $field   = esc_attr( $args['field'] );
        $label   = esc_html( $args['label'] ?? '' );
        echo "<label><input type='checkbox' name='{$args['option_name']}[{$field}]' value='1' {$checked} /> {$label}</label>";
    }

    public function render_google_section_description(): void {
        echo '<p>' . sprintf(
            esc_html__( 'Create credentials at %s. Set the Authorized redirect URI to: %s', 'socialauth-connect' ),
            '<a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>',
            '<code>' . esc_url( add_query_arg( [ 'socialauth_provider' => 'google', 'socialauth_action' => 'callback' ], site_url('/') ) ) . '</code>'
        ) . '</p>';
    }
}
🔢 Database Schema
database/class-db-manager.php
PHP

<?php
namespace SocialAuth\Database;

class DbManager {

    private const TABLE_VERSION_OPTION = 'socialauth_db_version';
    private const CURRENT_VERSION      = '1.0';

    /**
     * Run on activation — create custom tables.
     */
    public static function install(): void {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $prefix  = $wpdb->prefix;

        // Social accounts linking table
        $sql_accounts = "CREATE TABLE IF NOT EXISTS {$prefix}socialauth_accounts (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id       BIGINT(20) UNSIGNED NOT NULL,
            provider      VARCHAR(50)         NOT NULL,
            provider_id   VARCHAR(255)        NOT NULL,
            email         VARCHAR(255)        DEFAULT NULL,
            profile_data  LONGTEXT            DEFAULT NULL,
            access_token  TEXT                DEFAULT NULL,
            refresh_token TEXT                DEFAULT NULL,
            token_expires DATETIME            DEFAULT NULL,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY   provider_user (provider, provider_id),
            KEY          user_id (user_id),
            KEY          email (email)
        ) {$charset};";

        // Audit / activity log table
        $sql_log = "CREATE TABLE IF NOT EXISTS {$prefix}socialauth_log (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id    BIGINT(20) UNSIGNED DEFAULT NULL,
            provider   VARCHAR(50)         NOT NULL,
            action     VARCHAR(100)        NOT NULL,
            ip_address VARCHAR(45)         DEFAULT NULL,
            user_agent TEXT                DEFAULT NULL,
            created_at DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_accounts );
        dbDelta( $sql_log );

        update_option( self::TABLE_VERSION_OPTION, self::CURRENT_VERSION );
    }

    /**
     * Clean up on uninstall.
     */
    public static function uninstall(): void {
        global $wpdb;

        if ( get_option( 'socialauth_remove_data_on_uninstall', false ) ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}socialauth_accounts" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}socialauth_log" );
            delete_option( self::TABLE_VERSION_OPTION );
        }
    }
}
🛡️ Rate Limiter
includes/security/class-rate-limiter.php
PHP

<?php
namespace SocialAuth\Security;

class RateLimiter {

    private const PREFIX       = 'socialauth_rl_';
    private const MAX_ATTEMPTS = 10;
    private const WINDOW       = 300; // 5 minutes

    public static function check( string $action ): bool {
        $key      = self::PREFIX . $action . '_' . self::get_client_ip();
        $attempts = (int) get_transient( $key );

        if ( $attempts >= self::MAX_ATTEMPTS ) {
            return false;
        }

        if ( $attempts === 0 ) {
            set_transient( $key, 1, self::WINDOW );
        } else {
            set_transient( $key, $attempts + 1, self::WINDOW );
        }

        return true;
    }

    private static function get_client_ip(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return md5( filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0' );
    }
}
🖼️ Frontend: Login Button Rendering
includes/frontend/class-login-buttons.php
PHP

<?php
namespace SocialAuth\Frontend;

class LoginButtons {

    private array $providers;

    public function __construct( array $providers ) {
        $this->providers = $providers;
    }

    public function register_hooks(): void {
        add_action( 'login_form', [ $this, 'render_login_buttons' ] );
        add_action( 'woocommerce_login_form_end', [ $this, 'render_login_buttons' ] );
    }

    public function render_login_buttons(): void {
        $enabled_providers = array_filter(
            $this->providers,
            fn( $p ) => $p->is_enabled()
        );

        if ( empty( $enabled_providers ) ) {
            return;
        }

        echo '<div class="socialauth-buttons">';
        echo '<p class="socialauth-divider"><span>' . esc_html__( 'or continue with', 'socialauth-connect' ) . '</span></p>';

        foreach ( $enabled_providers as $provider ) {
            $this->render_button( $provider );
        }

        echo '</div>';
    }

    private function render_button( object $provider ): void {
        $url = add_query_arg([
            'socialauth_action'   => 'login',
            'socialauth_provider' => $provider->get_id(),
        ], site_url('/'));

        $icon_url = SOCIALAUTH_PLUGIN_URL . 'assets/images/' . $provider->get_id() . '-logo.svg';
        $label    = sprintf( __( 'Continue with %s', 'socialauth-connect' ), $provider->get_name() );

        printf(
            '<a href="%s" class="socialauth-btn socialauth-btn-%s" rel="nofollow noopener">
                <img src="%s" alt="" class="socialauth-icon" aria-hidden="true" />
                <span>%s</span>
            </a>',
            esc_url( $url ),
            esc_attr( $provider->get_id() ),
            esc_url( $icon_url ),
            esc_html( $label )
        );
    }
}
🔗 Shortcode
Usage in posts/pages
text

[socialauth_login providers="google" redirect="/dashboard" show_label="true"]
🗓️ Development Phases & Roadmap
Phase 1 — MVP (Google) ✅
 Plugin scaffold + autoloader
 Google OAuth2 + OpenID Connect
 CSRF state protection
 User creation + linking
 Admin settings UI
 Login page button injection
 Shortcode support
 Rate limiting
 Audit logging
Phase 2 — Extended Providers
 Facebook — OAuth2 via Graph API
 X (Twitter) — OAuth 2.0 PKCE
 GitHub — OAuth2
 Email Magic Link — Passwordless
Phase 3 — Advanced Features
 WooCommerce integration
 Account Linking UI (user profile page)
 Unlink social account feature
 REST API endpoints for headless/mobile
 Two-Factor as second step
 Gutenberg block for login form
 Multisite support
🧪 Testing Strategy
Bash

# Install test dependencies
composer require --dev phpunit/phpunit wp-phpunit/wp-phpunit brain/monkey

# Run unit tests
./vendor/bin/phpunit --testsuite unit

# Run integration tests (requires local WP test env)
./vendor/bin/phpunit --testsuite integration
Key Test Cases
Scenario	Type
State token generation and expiry	Unit
State token one-time use enforcement	Unit
Duplicate user prevention by email	Unit
User creation from Google profile	Unit
Rate limiter blocks after threshold	Unit
Full Google OAuth callback flow	Integration
Malformed callback handling	Integration
🔐 Security Checklist
 CSRF — Cryptographic state tokens (one-time, time-limited, IP-bound)
 XSS — All output escaped with esc_html, esc_url, esc_attr
 SQL Injection — $wpdb->prepare() for all custom queries
 Capability Checks — current_user_can() on all admin actions
 Nonce Verification — All forms include WordPress nonces
 Input Sanitisation — All input sanitised before processing or storage
 Secrets Storage — Client secret stored in wp_options (never hardcoded)
 Rate Limiting — Transient-based limit on auth attempts per IP
 Redirect Validation — wp_safe_redirect() + whitelist for post-login URLs
 Token Storage — Access tokens stored encrypted (Phase 2: Sodium)
 HTTPS — Enforce HTTPS for callback URLs in production
 Error Disclosure — Generic errors to users, details only in logs
📦 composer.json
JSON

{
    "name": "yourname/socialauth-connect",
    "description": "Modular social authentication plugin for WordPress",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "require": {
        "php": ">=8.1"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "brain/monkey": "^2.6",
        "wp-coding-standards/wpcs": "^3.0",
        "squizlabs/php_codesniffer": "^3.7"
    },
    "autoload": {
        "psr-4": {
            "SocialAuth\\": "includes/"
        },
        "files": [
            "includes/helpers/functions.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "SocialAuth\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "phpcs": "phpcs --standard=WordPress includes/",
        "phpcbf": "phpcbf --standard=WordPress includes/",
        "test": "phpunit"
    }
}
🌐 Environment Variables (Optional wp-config.php)
PHP

// Instead of storing secrets only in DB, optionally support constants
// Add to wp-config.php for higher security environments
define( 'SOCIALAUTH_GOOGLE_CLIENT_ID',     'your-client-id.apps.googleusercontent.com' );
define( 'SOCIALAUTH_GOOGLE_CLIENT_SECRET', 'your-client-secret' );
📝 Key WordPress Filters & Actions
Filters (extensibility hooks)
PHP

// Modify login redirect URL
add_filter( 'socialauth_login_redirect', fn( $url, $user ) => '/dashboard', 10, 2 );

// Allow or deny registration
add_filter( 'socialauth_can_register', '__return_true' );

// Modify normalized user data before processing
add_filter( 'socialauth_normalize_user', fn( $data, $provider ) => $data, 10, 2 );

// Disable IP binding in state check (useful for mobile)
add_filter( 'socialauth_state_check_ip', '__return_false' );

// Register a new provider dynamically
add_filter( 'socialauth_providers', function( $providers ) {
    $providers['github'] = new MyGithubProvider();
    return $providers;
});
Actions (event hooks)
PHP

// Fires after a new social user is created in WP
add_action( 'socialauth_user_created', fn( $user_id, $social_data ) => ..., 10, 2 );

// Fires after a provider is linked to an existing user
add_action( 'socialauth_provider_linked', fn( $user_id, $provider, $data ) => ..., 10, 3 );

// Fires after successful social login
add_action( 'socialauth_login_success', fn( $user_id, $provider ) => ..., 10, 2 );
🚀 Getting Started (CLI Quick Setup)
Bash

# 1. Clone into your plugins directory
cd wp-content/plugins
mkdir socialauth-connect && cd socialauth-connect

# 2. Install dependencies
composer install

# 3. Setup coding standards
./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs

# 4. Lint code
composer run phpcs

# 5. Run tests
composer run test

# 6. Activate via WP CLI
wp plugin activate socialauth-connect

# 7. Set Google credentials via WP CLI
wp option update socialauth_google_settings \
  '{"enabled":true,"client_id":"YOUR_ID","client_secret":"YOUR_SECRET"}' \
  --format=json
Next Steps: Share this spec with your CLI development tool or AI coding assistant to scaffold the files. Start with socialauth-connect.php, composer.json, the interface, the Google provider, and the auth manager — then build outward from there.
