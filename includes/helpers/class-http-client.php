<?php
namespace SocialAuth\Helpers;

class HttpClient {

    private const TIMEOUT = 30;

    /**
     * Send GET request.
     */
    public static function get( string $url, array $args = [], array $headers = [] ): array|\WP_Error {
        $defaults = [
            'timeout' => self::TIMEOUT,
            'headers' => $headers,
        ];

        $response = wp_remote_get( $url, array_merge( $defaults, $args ) );

        return self::parse_response( $response );
    }

    /**
     * Send POST request with form data.
     */
    public static function post( string $url, array $data = [], array $args = [] ): array|\WP_Error {
        $defaults = [
            'method'  => 'POST',
            'timeout' => self::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => http_build_query( $data ),
        ];

        // Override body encoding if already encoded.
        if ( isset( $args['body'] ) ) {
            $defaults['body'] = $args['body'];
            unset( $args['body'] );
        }

        $response = wp_remote_post( $url, array_merge( $defaults, $args ) );

        return self::parse_response( $response );
    }

    /**
     * Parse wp_remote response into decoded array or WP_Error.
     */
    private static function parse_response( mixed $response ): array|\WP_Error {
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        $data = json_decode( $body, true );

        if ( ! is_array( $data ) ) {
            return new \WP_Error(
                'socialauth_invalid_response',
                sprintf( 'Invalid JSON response (HTTP %d)', $code )
            );
        }

        if ( $code < 200 || $code >= 300 ) {
            $error_msg = $data['error_description'] ?? $data['error'] ?? sprintf( 'HTTP %d error', $code );
            return new \WP_Error(
                'socialauth_http_error',
                $error_msg,
                [ 'status' => $code, 'body' => $data ]
            );
        }

        return $data;
    }
}
