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
	protected array $scopes     = array();
	protected string $auth_url  = '';
	protected string $token_url = '';

	public function __construct() {
		$options             = get_option( 'socialauth_' . $this->get_id() . '_settings', array() );
		$this->client_id     = sanitize_text_field( $options['client_id'] ?? '' );
		$this->client_secret = sanitize_text_field( $options['client_secret'] ?? '' );
		$this->redirect_uri  = $this->build_redirect_uri();
	}

	/**
	 * Build the standardised OAuth2 callback URL.
	 */
	protected function build_redirect_uri(): string {
		return add_query_arg(
			array(
				'socialauth_provider' => $this->get_id(),
				'socialauth_action'   => 'callback',
			),
			site_url( '/' )
		);
	}

	/**
	 * Build authorization URL with CSRF state.
	 */
	public function get_auth_url(): string {
		$state = StateManager::generate( $this->get_id() );

		// http_build_query (RFC 3986) instead of add_query_arg: add_query_arg
		// does not urlencode values, so the '&' inside redirect_uri would
		// truncate it and cause a redirect_uri_mismatch at the provider.
		$params = array(
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->redirect_uri,
			'response_type' => 'code',
			'scope'         => implode( ' ', $this->scopes ),
			'state'         => $state,
			'access_type'   => 'offline', // For refresh tokens where supported
		);

		return $this->auth_url . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Exchange auth code for token via wp_remote_post.
	 */
	public function exchange_code_for_token( string $code ): array {
		$response = HttpClient::post(
			$this->token_url,
			array(
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
				'code'          => $code,
				'redirect_uri'  => $this->redirect_uri,
				'grant_type'    => 'authorization_code',
			)
		);

		if ( is_wp_error( $response ) ) {
			Logger::error(
				'Token exchange failed',
				array(
					'provider' => $this->get_id(),
					'error'    => $response->get_error_message(),
				)
			);
			return array();
		}

		return $response;
	}

	public function is_enabled(): bool {
		$options = get_option( 'socialauth_' . $this->get_id() . '_settings', array() );
		return ! empty( $options['enabled'] ) && ! empty( $this->client_id ) && ! empty( $this->client_secret );
	}
}
