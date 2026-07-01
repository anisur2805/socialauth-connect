<?php
namespace SocialAuth\Providers;

use SocialAuth\Abstracts\AbstractOAuth2Provider;

/**
 * Facebook OAuth2 provider (Phase 2 placeholder).
 *
 * @package SocialAuthConnect
 */
class Facebook extends AbstractOAuth2Provider {

    protected string $auth_url  = 'https://www.facebook.com/v18.0/dialog/oauth';
    protected string $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
    protected string $userinfo_url = 'https://graph.facebook.com/v18.0/me';

    protected array $scopes = [
        'email',
        'public_profile',
    ];

    public function get_id(): string {
        return 'facebook';
    }

    public function get_name(): string {
        return __( 'Facebook', 'socialauth-connect' );
    }

    public function get_user_profile( string $access_token ): array {
        // Phase 2: implement Graph API call.
        return [];
    }

    public function normalize_user( array $raw ): array {
        return [
            'provider'    => $this->get_id(),
            'provider_id' => sanitize_text_field( $raw['id']         ?? '' ),
            'email'       => sanitize_email( $raw['email']          ?? '' ),
            'name'        => sanitize_text_field( $raw['name']      ?? '' ),
            'first_name'  => sanitize_text_field( $raw['first_name'] ?? '' ),
            'last_name'   => sanitize_text_field( $raw['last_name'] ?? '' ),
            'avatar_url'  => '',
            'verified'    => false,
            'locale'      => '',
        ];
    }
}
