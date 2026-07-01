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

}
