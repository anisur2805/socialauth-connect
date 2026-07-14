<?php
namespace SocialAuth\Providers;

use SocialAuth\Abstracts\AbstractOAuth2Provider;
use SocialAuth\Auth\StateManager;
use SocialAuth\Helpers\HttpClient;
use SocialAuth\Helpers\Logger;

/**
 * X (Twitter) OAuth 2.0 PKCE provider.
 *
 * @package SocialAuthConnect
 */
class XProvider extends AbstractOAuth2Provider {

	protected string $auth_url     = 'https://twitter.com/i/oauth2/authorize';
	protected string $token_url    = 'https://api.twitter.com/2/oauth2/token';
	protected string $userinfo_url = 'https://api.twitter.com/2/users/me';

	protected array $scopes = array(
		'users.read',
		'tweet.read',
	);

	/**
	 * Override to add PKCE code_challenge to the authorization URL.
	 */
	public function get_auth_url(): string {
		$state         = StateManager::generate( $this->get_id() );
		$code_verifier = $this->generate_code_verifier();
		$code_challenge = $this->generate_code_challenge( $code_verifier );

		// Store code_verifier keyed by state for later use in token exchange.
		set_transient(
			'socialauth_pkce_' . $state,
			$code_verifier,
			600 // 10 minutes.
		);

		$params = array(
			'client_id'             => $this->client_id,
			'redirect_uri'          => $this->redirect_uri,
			'response_type'         => 'code',
			'scope'                 => implode( ' ', $this->scopes ),
			'state'                 => $state,
			'code_challenge'        => $code_challenge,
			'code_challenge_method' => 'S256',
		);

		return $this->auth_url . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Override to add code_verifier and use Basic Auth header for token exchange.
	 * X/Twitter requires HTTP Basic Auth (base64 of client_id:client_secret).
	 */
	public function exchange_code_for_token( string $code ): array {
		$state = sanitize_text_field( $_GET['state'] ?? '' );
		$code_verifier = get_transient( 'socialauth_pkce_' . $state );

		if ( false === $code_verifier ) {
			Logger::error( 'PKCE code_verifier not found', array( 'provider' => 'x' ) );
			return array();
		}

		delete_transient( 'socialauth_pkce_' . $state );

		$credentials = base64_encode( $this->client_id . ':' . $this->client_secret );

		$response = wp_remote_post(
			$this->token_url,
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . $credentials,
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => http_build_query( array(
					'code'          => $code,
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => $this->redirect_uri,
					'code_verifier' => $code_verifier,
				) ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			Logger::error(
				'Token exchange failed',
				array(
					'provider' => 'x',
					'error'    => $response->get_error_message(),
				)
			);
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['access_token'] ) ) {
			$error_msg = $body['errors'][0]['message'] ?? 'Unknown error';
			Logger::error(
				'Token exchange error',
				array(
					'provider' => 'x',
					'error'    => $error_msg,
				)
			);
			return array();
		}

		return $body;
	}

	public function get_id(): string {
		return 'x';
	}

	public function get_name(): string {
		return __( 'X (Twitter)', 'socialauth-connect' );
	}

	/**
	 * Fetch user info from X/Twitter API v2.
	 */
	public function get_user_profile( string $access_token ): array {
		$response = HttpClient::get(
			$this->userinfo_url,
			array(
				'user.fields' => 'id,name,username,profile_image_url,email',
			),
			array( 'Authorization' => 'Bearer ' . $access_token )
		);

		if ( is_wp_error( $response ) || empty( $response ) ) {
			return array();
		}

		return $response;
	}

	/**
	 * Normalize X/Twitter profile to standard structure.
	 */
	public function normalize_user( array $raw ): array {
		$data = $raw['data'] ?? $raw;

		// X/Twitter returns profile_image_url with _normal suffix — get larger version.
		$avatar_url = $data['profile_image_url'] ?? '';
		if ( ! empty( $avatar_url ) ) {
			$avatar_url = str_replace( '_normal', '_400x400', $avatar_url );
		}

		return array(
			'provider'    => $this->get_id(),
			'provider_id' => sanitize_text_field( $data['id'] ?? '' ),
			'email'       => sanitize_email( $data['email'] ?? '' ),
			'name'        => sanitize_text_field( $data['name'] ?? '' ),
			'first_name'  => '',
			'last_name'   => '',
			'avatar_url'  => esc_url_raw( $avatar_url ),
			'verified'    => ! empty( $data['email'] ),
			'locale'      => '',
			'username'    => sanitize_text_field( $data['username'] ?? '' ),
		);
	}

	/**
	 * Generate a cryptographically random code verifier for PKCE.
	 */
	private function generate_code_verifier(): string {
		return bin2hex( random_bytes( 32 ) );
	}

	/**
	 * Generate code challenge from code verifier using SHA-256.
	 */
	private function generate_code_challenge( string $code_verifier ): string {
		$hash = hash( 'sha256', $code_verifier, true );
		return rtrim( strtr( base64_encode( $hash ), '+/', '-_' ), '=' );
	}
}
