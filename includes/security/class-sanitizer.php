<?php
namespace SocialAuth\Security;

class Sanitizer {

    /**
     * Sanitize a string field.
     */
    public static function text( string $input ): string {
        return sanitize_text_field( $input );
    }

    /**
     * Sanitize an email field.
     */
    public static function email( string $input ): string {
        return sanitize_email( $input );
    }

    /**
     * Sanitize a URL field.
     */
    public static function url( string $input ): string {
        return esc_url_raw( $input );
    }

    /**
     * Sanitize an array of mixed data recursively.
     */
    public static function sanitize_array( array $input ): array {
        $clean = [];
        foreach ( $input as $key => $value ) {
            $key = sanitize_key( $key );
            if ( is_array( $value ) ) {
                $clean[ $key ] = self::sanitize_array( $value );
            } elseif ( is_string( $value ) ) {
                $clean[ $key ] = sanitize_text_field( $value );
            } else {
                $clean[ $key ] = $value;
            }
        }
        return $clean;
    }
}
