<?php
/**
 * Global helper functions for SocialAuth Connect.
 *
 * @package SocialAuthConnect
 */

if ( ! function_exists( 'socialauth_get_provider' ) ) {
    /**
     * Get a registered provider by ID.
     */
    function socialauth_get_provider( string $id ): ?object {
        $core = \SocialAuth\Core::instance();
        return $core->get_provider( $id );
    }
}

if ( ! function_exists( 'socialauth_is_ssl' ) ) {
    /**
     * Check if connection is HTTPS.
     */
    function socialauth_is_ssl(): bool {
        return is_ssl();
    }
}

if ( ! function_exists( 'socialauth_get_callback_url' ) ) {
    /**
     * Get callback URL for a given provider.
     */
    function socialauth_get_callback_url( string $provider_id ): string {
        return add_query_arg(
            [
                'socialauth_provider' => $provider_id,
                'socialauth_action'   => 'callback',
            ],
            site_url( '/' )
        );
    }
}
