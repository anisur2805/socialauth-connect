<?php
namespace SocialAuth\Providers;

use SocialAuth\Abstracts\AbstractOAuth2Provider;
use SocialAuth\Auth\StateManager;
use SocialAuth\Helpers\HttpClient;

class Facebook extends AbstractOAuth2Provider {

	protected string $auth_url     = 'https://www.facebook.com/v18.0/dialog/oauth';
	protected string $token_url    = 'https://graph.facebook.com/v18.0/oauth/access_token';
	protected string $userinfo_url = 'https://graph.facebook.com/v18.0/me';

	protected array $scopes = array(
		'public_profile',
	);

	/**
	 * Override to use comma-separated scopes (Facebook requirement)
	 * and omit unsupported parameters like access_type.
	 */
	public function get_auth_url(): string {
		$state = StateManager::generate( $this->get_id() );

		$params = array(
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->redirect_uri,
			'response_type' => 'code',
			'state'         => $state,
		);

		if ( ! empty( $this->scopes ) ) {
			$params['scope'] = implode( ',', $this->scopes );
		}

		return $this->auth_url . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	public function get_id(): string {
		return 'facebook';
	}

	public function get_name(): string {
		return __( 'Facebook', 'socialauth-connect' );
	}

	/**
	 * Fetch user info from Facebook Graph API.
	 */
	public function get_user_profile( string $access_token ): array {
		$response = HttpClient::get(
			$this->userinfo_url,
			array(
				'fields' => 'id,name,first_name,last_name,picture.width(200)',
			),
			array( 'Authorization' => 'Bearer ' . $access_token )
		);

		if ( is_wp_error( $response ) || empty( $response ) ) {
			return array();
		}

		return $response;
	}

	/**
	 * Normalize Facebook profile to standard structure.
	 */
	public function normalize_user( array $raw ): array {
		$avatar_url = '';
		if ( ! empty( $raw['picture']['data']['url'] ) ) {
			$avatar_url = esc_url_raw( $raw['picture']['data']['url'] );
		}

		return array(
			'provider'    => $this->get_id(),
			'provider_id' => sanitize_text_field( $raw['id'] ?? '' ),
			'email'       => sanitize_email( $raw['email'] ?? '' ),
			'name'        => sanitize_text_field( $raw['name'] ?? '' ),
			'first_name'  => sanitize_text_field( $raw['first_name'] ?? '' ),
			'last_name'   => sanitize_text_field( $raw['last_name'] ?? '' ),
			'avatar_url'  => $avatar_url,
			'verified'    => ! empty( $raw['email'] ),
			'locale'      => '',
		);
	}
}
