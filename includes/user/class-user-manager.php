<?php
namespace SocialAuth\User;

use SocialAuth\Helpers\Logger;

class UserManager {

    /**
     * Main entry point: find, create or link WP user from social profile.
     */
    public static function authenticate( array $social_user ): \WP_User|false {
        // 1. Try to find by social provider + ID.
        $wp_user = self::find_by_provider( $social_user['provider'], $social_user['provider_id'] );

        if ( $wp_user ) {
            return $wp_user;
        }

        // 2. Try to find by matching email.
        if ( ! empty( $social_user['email'] ) ) {
            $wp_user = get_user_by( 'email', $social_user['email'] );
            if ( $wp_user ) {
                // Link the social account to existing user.
                self::link_provider( $wp_user->ID, $social_user );
                return $wp_user;
            }
        }

        // 3. Create new user if registration is allowed.
        if ( self::can_register() ) {
            return self::create_user( $social_user );
        }

        return false;
    }

    /**
     * Find WP user by social provider + social user ID.
     */
    public static function find_by_provider( string $provider, string $provider_id ): \WP_User|false {
        $users = get_users( [
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
        ] );

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

        $user_id = wp_insert_user( [
            'user_login'   => $username,
            'user_email'   => $email,
            'display_name' => sanitize_text_field( $social_user['name'] ?? $username ),
            'first_name'   => sanitize_text_field( $social_user['first_name'] ?? '' ),
            'last_name'    => sanitize_text_field( $social_user['last_name']  ?? '' ),
            'user_pass'    => wp_generate_password( 32, true, true ),
            'role'         => get_option( 'default_role', 'subscriber' ),
        ] );

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
