<?php
namespace SocialAuth\Providers;

use SocialAuth\Abstracts\AbstractOAuth2Provider;

/**
 * X (Twitter) OAuth 2.0 PKCE provider (Phase 2 placeholder).
 *
 * @package SocialAuthConnect
 */
class XProvider extends AbstractOAuth2Provider {

    protected string $auth_url  = 'https://twitter.com/i/oauth2/authorize';
    protected string $token_url = 'https://api.twitter.com/2/oauth2/token';
    protected string $userinfo_url = 'https://api.twitter.com/2/users/me';

    protected array $scopes = [
        'tweet.read',
        'users.read',
    ];

    public function get_id(): string {
        return 'x';
    }

    public function get_name(): string {
        return __( 'X (Twitter)', 'socialauth-connect' );
    }

    public function get_user_profile( string $access_token ): array {
        // Phase 2: implement Twitter API v2 call.
        return [];
    }

    public function normalize_user( array $raw ): array {
        $data = $raw['data'] ?? $raw;
        return [
            'provider'    => $this->get_id(),
            'provider_id' => sanitize_text_field( $data['id']              ?? '' ),
            'email'       => '',
            'name'        => sanitize_text_field( $data['name']            ?? '' ),
            'first_name'  => sanitize_text_field( $data['name']            ?? '' ),
            'last_name'   => '',
            'avatar_url'  => esc_url_raw( $data['profile_image_url']       ?? '' ),
            'verified'    => false,
            'locale'      => '',
        ];
    }
}
