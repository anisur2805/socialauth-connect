<?php
namespace SocialAuth\Security;

class RateLimiter {

    private const PREFIX       = 'socialauth_rl_';
    private const MAX_ATTEMPTS = 10;
    private const WINDOW       = 300; // 5 minutes.

    /**
     * Check if action is allowed under rate limit.
     */
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
