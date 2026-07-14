<?php
namespace SocialAuth\Providers;

use SocialAuth\Abstracts\AbstractOAuth2Provider;
use SocialAuth\Helpers\HttpClient;

/**
 * GitHub OAuth 2.0 provider.
 *
 * @package SocialAuthConnect
 */
class GitHub extends AbstractOAuth2Provider {

	protected string $auth_url     = 'https://github.com/login/oauth/authorize';
	protected string $token_url    = 'https://github.com/login/oauth/access_token';
	protected string $userinfo_url = 'https://api.github.com/user';

	protected array $scopes = array(
		'read:user',
		'user:email',
	);

	public function get_id(): string {
		return 'github';
	}

	public function get_name(): string {
		return __( 'GitHub', 'socialauth-connect' );
	}

	/**
	 * Fetch user info from GitHub API.
	 */
	public function get_user_profile( string $access_token ): array {
		$user_response = HttpClient::get(
			$this->userinfo_url,
			array(),
			array(
				'Authorization' => 'Bearer ' . $access_token,
				'Accept'        => 'application/vnd.github+json',
			)
		);

		if ( is_wp_error( $user_response ) || empty( $user_response ) ) {
			return array();
		}

		// GitHub returns email as null in user endpoint if not public — fetch from emails endpoint.
		if ( empty( $user_response['email'] ) ) {
			$emails_response = HttpClient::get(
				'https://api.github.com/user/emails',
				array(),
				array(
					'Authorization' => 'Bearer ' . $access_token,
					'Accept'        => 'application/vnd.github+json',
				)
			);

			if ( ! is_wp_error( $emails_response ) && is_array( $emails_response ) ) {
				foreach ( $emails_response as $email ) {
					if ( ! empty( $email['primary'] ) && ! empty( $email['verified'] ) ) {
						$user_response['email'] = $email['email'];
						break;
					}
				}
			}
		}

		return $user_response;
	}

	/**
	 * Normalize GitHub profile to standard structure.
	 */
	public function normalize_user( array $raw ): array {
		$name_parts = explode( ' ', $raw['name'] ?? '', 2 );

		return array(
			'provider'    => $this->get_id(),
			'provider_id' => sanitize_text_field( (string) ( $raw['id'] ?? '' ) ),
			'email'       => sanitize_email( $raw['email'] ?? '' ),
			'name'        => sanitize_text_field( $raw['name'] ?? $raw['login'] ?? '' ),
			'first_name'  => sanitize_text_field( $name_parts[0] ?? '' ),
			'last_name'   => sanitize_text_field( $name_parts[1] ?? '' ),
			'avatar_url'  => esc_url_raw( $raw['avatar_url'] ?? '' ),
			'verified'    => ! empty( $raw['email'] ),
			'locale'      => '',
			'username'    => sanitize_text_field( $raw['login'] ?? '' ),
		);
	}
}
