<?php
namespace SocialAuth\Admin;

defined( 'ABSPATH' ) || exit;

class AdminSettings {

	public function register_hooks(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_socialauth_test_facebook', array( $this, 'ajax_test_facebook' ) );
	}

	public function register_settings(): void {
		// General settings.
		register_setting(
			'socialauth_general',
			'socialauth_allow_registration',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			)
		);

		register_setting(
			'socialauth_general',
			'socialauth_login_redirect',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => admin_url(),
			)
		);

		register_setting(
			'socialauth_general',
			'socialauth_dashboard_widget',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			)
		);

		// Google provider settings.
		register_setting(
			'socialauth_google_settings',
			'socialauth_google_settings',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( $this, 'sanitize_google_settings' ),
				'default'           => array(),
			)
		);

		// Google section.
		add_settings_section(
			'socialauth_google_section',
			__( 'Google OAuth2 Settings', 'socialauth-connect' ),
			array( $this, 'render_google_section_description' ),
			'socialauth-google'
		);

		// Google fields.
		add_settings_field(
			'google_enabled',
			__( 'Enable Google Login', 'socialauth-connect' ),
			array( $this, 'render_checkbox_field' ),
			'socialauth-google',
			'socialauth_google_section',
			array(
				'option_name' => 'socialauth_google_settings',
				'field'       => 'enabled',
				'label'       => __( 'Enable', 'socialauth-connect' ),
			)
		);

		add_settings_field(
			'google_client_id',
			__( 'Client ID', 'socialauth-connect' ),
			array( $this, 'render_text_field' ),
			'socialauth-google',
			'socialauth_google_section',
			array(
				'option_name' => 'socialauth_google_settings',
				'field'       => 'client_id',
			)
		);

		add_settings_field(
			'google_client_secret',
			__( 'Client Secret', 'socialauth-connect' ),
			array( $this, 'render_password_field' ),
			'socialauth-google',
			'socialauth_google_section',
			array(
				'option_name' => 'socialauth_google_settings',
				'field'       => 'client_secret',
			)
		);

		// Facebook provider settings.
		register_setting(
			'socialauth_facebook_settings',
			'socialauth_facebook_settings',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( $this, 'sanitize_facebook_settings' ),
				'default'           => array(),
			)
		);

		// Facebook section.
		add_settings_section(
			'socialauth_facebook_section',
			__( 'Facebook OAuth2 Settings', 'socialauth-connect' ),
			array( $this, 'render_facebook_section_description' ),
			'socialauth-facebook'
		);

		// Facebook fields.
		add_settings_field(
			'facebook_enabled',
			__( 'Enable Facebook Login', 'socialauth-connect' ),
			array( $this, 'render_checkbox_field' ),
			'socialauth-facebook',
			'socialauth_facebook_section',
			array(
				'option_name' => 'socialauth_facebook_settings',
				'field'       => 'enabled',
				'label'       => __( 'Enable', 'socialauth-connect' ),
			)
		);

		add_settings_field(
			'facebook_client_id',
			__( 'App ID', 'socialauth-connect' ),
			array( $this, 'render_text_field' ),
			'socialauth-facebook',
			'socialauth_facebook_section',
			array(
				'option_name' => 'socialauth_facebook_settings',
				'field'       => 'client_id',
			)
		);

		add_settings_field(
			'facebook_client_secret',
			__( 'App Secret', 'socialauth-connect' ),
			array( $this, 'render_password_field' ),
			'socialauth-facebook',
			'socialauth_facebook_section',
			array(
				'option_name' => 'socialauth_facebook_settings',
				'field'       => 'client_secret',
			)
		);

		// X (Twitter) provider settings.
		register_setting(
			'socialauth_x_settings',
			'socialauth_x_settings',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( $this, 'sanitize_x_settings' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			'socialauth_x_section',
			__( 'X (Twitter) OAuth2 Settings', 'socialauth-connect' ),
			array( $this, 'render_x_section_description' ),
			'socialauth-x'
		);

		add_settings_field(
			'x_enabled',
			__( 'Enable X Login', 'socialauth-connect' ),
			array( $this, 'render_checkbox_field' ),
			'socialauth-x',
			'socialauth_x_section',
			array(
				'option_name' => 'socialauth_x_settings',
				'field'       => 'enabled',
				'label'       => __( 'Enable', 'socialauth-connect' ),
			)
		);

		add_settings_field(
			'x_client_id',
			__( 'Client ID', 'socialauth-connect' ),
			array( $this, 'render_text_field' ),
			'socialauth-x',
			'socialauth_x_section',
			array(
				'option_name' => 'socialauth_x_settings',
				'field'       => 'client_id',
			)
		);

		add_settings_field(
			'x_client_secret',
			__( 'Client Secret', 'socialauth-connect' ),
			array( $this, 'render_password_field' ),
			'socialauth-x',
			'socialauth_x_section',
			array(
				'option_name' => 'socialauth_x_settings',
				'field'       => 'client_secret',
			)
		);

		// GitHub provider settings.
		register_setting(
			'socialauth_github_settings',
			'socialauth_github_settings',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( $this, 'sanitize_github_settings' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			'socialauth_github_section',
			__( 'GitHub OAuth2 Settings', 'socialauth-connect' ),
			array( $this, 'render_github_section_description' ),
			'socialauth-github'
		);

		add_settings_field(
			'github_enabled',
			__( 'Enable GitHub Login', 'socialauth-connect' ),
			array( $this, 'render_checkbox_field' ),
			'socialauth-github',
			'socialauth_github_section',
			array(
				'option_name' => 'socialauth_github_settings',
				'field'       => 'enabled',
				'label'       => __( 'Enable', 'socialauth-connect' ),
			)
		);

		add_settings_field(
			'github_client_id',
			__( 'Client ID', 'socialauth-connect' ),
			array( $this, 'render_text_field' ),
			'socialauth-github',
			'socialauth_github_section',
			array(
				'option_name' => 'socialauth_github_settings',
				'field'       => 'client_id',
			)
		);

		add_settings_field(
			'github_client_secret',
			__( 'Client Secret', 'socialauth-connect' ),
			array( $this, 'render_password_field' ),
			'socialauth-github',
			'socialauth_github_section',
			array(
				'option_name' => 'socialauth_github_settings',
				'field'       => 'client_secret',
			)
		);
	}

	public function sanitize_google_settings( array $input ): array {
		return array(
			'enabled'       => rest_sanitize_boolean( $input['enabled'] ?? false ),
			'client_id'     => sanitize_text_field( $input['client_id'] ?? '' ),
			'client_secret' => sanitize_text_field( $input['client_secret'] ?? '' ),
		);
	}

	public function sanitize_facebook_settings( array $input ): array {
		return array(
			'enabled'       => rest_sanitize_boolean( $input['enabled'] ?? false ),
			'client_id'     => sanitize_text_field( $input['client_id'] ?? '' ),
			'client_secret' => sanitize_text_field( $input['client_secret'] ?? '' ),
		);
	}

	public function sanitize_x_settings( array $input ): array {
		return array(
			'enabled'       => rest_sanitize_boolean( $input['enabled'] ?? false ),
			'client_id'     => sanitize_text_field( $input['client_id'] ?? '' ),
			'client_secret' => sanitize_text_field( $input['client_secret'] ?? '' ),
		);
	}

	public function sanitize_github_settings( array $input ): array {
		return array(
			'enabled'       => rest_sanitize_boolean( $input['enabled'] ?? false ),
			'client_id'     => sanitize_text_field( $input['client_id'] ?? '' ),
			'client_secret' => sanitize_text_field( $input['client_secret'] ?? '' ),
		);
	}

	public function render_text_field( array $args ): void {
		$options = get_option( $args['option_name'], array() );
		$value   = esc_attr( $options[ $args['field'] ] ?? '' );
		$field   = esc_attr( $args['field'] );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- form input is safe (variables escaped)
		echo "<input type='text' name='{$args['option_name']}[{$field}]' value='{$value}' class='regular-text' />";
	}

	public function render_password_field( array $args ): void {
		$options = get_option( $args['option_name'], array() );
		$value   = esc_attr( $options[ $args['field'] ] ?? '' );
		$field   = esc_attr( $args['field'] );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- form input is safe (variables escaped)
		echo "<input type='password' name='{$args['option_name']}[{$field}]' value='{$value}' class='regular-text' autocomplete='off' />";
	}

	public function render_checkbox_field( array $args ): void {
		$options = get_option( $args['option_name'], array() );
		$checked = checked( $options[ $args['field'] ] ?? false, true, false );
		$field   = esc_attr( $args['field'] );
		$label   = esc_html( $args['label'] ?? '' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- form input is safe (variables escaped)
		echo "<label><input type='checkbox' name='{$args['option_name']}[{$field}]' value='1' {$checked} /> {$label}</label>";
	}

	public function render_google_section_description(): void {
		$redirect_uri = esc_url(
			add_query_arg(
				array(
					'socialauth_provider' => 'google',
					'socialauth_action'   => 'callback',
				),
				site_url( '/' )
			)
		);

		echo '<p>' . sprintf(
			/* translators: %s = link to Google Cloud Console */
			esc_html__( 'Create OAuth 2.0 credentials at %s and configure below:', 'socialauth-connect' ),
			'<a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>'
		) . '</p>';

		echo '<ol style="margin-left:20px;">';
		echo '<li>' . esc_html__( 'Create an OAuth 2.0 Client ID (Web Application type).', 'socialauth-connect' ) . '</li>';
		echo '<li>' . esc_html__( 'Add this URL to "Authorized redirect URIs":', 'socialauth-connect' ) . ' ';
		echo '<code>' . $redirect_uri . '</code></li>';
		echo '<li>' . esc_html__( 'Copy the Client ID and Client Secret into the fields below.', 'socialauth-connect' ) . '</li>';
		echo '</ol>';
	}

	public function render_facebook_section_description(): void {
		$redirect_uri = esc_url(
			add_query_arg(
				array(
					'socialauth_provider' => 'facebook',
					'socialauth_action'   => 'callback',
				),
				site_url( '/' )
			)
		);

		$parsed     = wp_parse_url( site_url( '/' ) );
		$app_domain = $parsed['host'] ?? '';
		$is_localhost = ( 'localhost' === $app_domain || '127.0.0.1' === $app_domain );

		echo '<p>' . sprintf(
			/* translators: %s = link to Facebook Developers */
			esc_html__( 'Create a Facebook App at %s and configure the settings below:', 'socialauth-connect' ),
			'<a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener">Facebook Developers</a>'
		) . '</p>';

		echo '<h4>' . esc_html__( 'Required Facebook App Configuration:', 'socialauth-connect' ) . '</h4>';
		echo '<ol style="margin-left:20px;">';

		if ( $is_localhost ) {
			echo '<li>' . esc_html__( 'No "App Domains" entry needed — Facebook automatically allows localhost.', 'socialauth-connect' ) . '</li>';
		} else {
			echo '<li>' . sprintf(
				/* translators: %s = App Domains value */
				esc_html__( 'In your Facebook App → Settings → Basic, add "%s" to the "App Domains" field:', 'socialauth-connect' ),
				'<code>' . esc_html( $app_domain ) . '</code>'
			) . '</li>';
		}

		echo '<li>' . esc_html__( 'Copy the App ID and App Secret from Settings → Basic into the fields below.', 'socialauth-connect' ) . '</li>';

		echo '<li>' . esc_html__( 'Add "Facebook Login" product: left sidebar → "Add Products" → find "Facebook Login" → click "Set Up".', 'socialauth-connect' ) . '</li>';

		echo '<li>' . esc_html__( 'Then go to Facebook Login → Settings (left sidebar) and add this URL to "Valid OAuth Redirect URIs":', 'socialauth-connect' ) . ' ';
		echo '<code>' . $redirect_uri . '</code></li>';

		echo '</ol>';

		echo '<p class="description">';
		echo esc_html__( 'Important: The "Valid OAuth Redirect URI" must match exactly (including protocol and path).', 'socialauth-connect' );
		echo '</p>';
	}

	public function render_x_section_description(): void {
		$redirect_uri = esc_url(
			add_query_arg(
				array(
					'socialauth_provider' => 'x',
					'socialauth_action'   => 'callback',
				),
				site_url( '/' )
			)
		);

		echo '<p>' . sprintf(
			/* translators: %s = link to X Developer Portal */
			esc_html__( 'Create an X/Twitter app at %s and configure OAuth 2.0:', 'socialauth-connect' ),
			'<a href="https://developer.x.com/en/portal/dashboard" target="_blank" rel="noopener">X Developer Portal</a>'
		) . '</p>';

		echo '<h4>' . esc_html__( 'Required X/Twitter App Configuration:', 'socialauth-connect' ) . '</h4>';
		echo '<ol style="margin-left:20px;">';
		echo '<li>' . esc_html__( 'Create a new app in the X Developer Portal.', 'socialauth-connect' ) . '</li>';
		echo '<li>' . esc_html__( 'Go to app settings → User authentication settings → Enable OAuth 2.0.', 'socialauth-connect' ) . '</li>';
		echo '<li>' . sprintf(
			/* translators: %s = redirect URI */
			esc_html__( 'Add this URL to "Callback URI / Redirect URL": %s', 'socialauth-connect' ),
			'<code>' . $redirect_uri . '</code>'
		) . '</li>';
		echo '<li>' . esc_html__( 'Copy the Client ID (under "Client ID" or "Consumer Key") and Client Secret into the fields below.', 'socialauth-connect' ) . '</li>';
		echo '</ol>';

		echo '<p class="description">';
		echo esc_html__( 'Important: X/Twitter uses OAuth 2.0 with PKCE. The redirect URI must match exactly.', 'socialauth-connect' );
		echo '</p>';
	}

	public function render_github_section_description(): void {
		$redirect_uri = esc_url(
			add_query_arg(
				array(
					'socialauth_provider' => 'github',
					'socialauth_action'   => 'callback',
				),
				site_url( '/' )
			)
		);

		echo '<p>' . sprintf(
			/* translators: %s = link to GitHub Developer Settings */
			esc_html__( 'Create a GitHub OAuth app at %s and configure below:', 'socialauth-connect' ),
			'<a href="https://github.com/settings/developers" target="_blank" rel="noopener">GitHub Developer Settings</a>'
		) . '</p>';

		echo '<h4>' . esc_html__( 'Required GitHub App Configuration:', 'socialauth-connect' ) . '</h4>';
		echo '<ol style="margin-left:20px;">';
		echo '<li>' . esc_html__( 'Go to Settings → Developer settings → OAuth Apps → New OAuth App.', 'socialauth-connect' ) . '</li>';
		echo '<li>' . esc_html__( 'Enter an Application name (e.g., your site name).', 'socialauth-connect' ) . '</li>';
		echo '<li>' . sprintf(
			/* translators: %s = redirect URI */
			esc_html__( 'Add this URL to "Authorization callback URL": %s', 'socialauth-connect' ),
			'<code>' . $redirect_uri . '</code>'
		) . '</li>';
		echo '<li>' . esc_html__( 'Copy the Client ID and Client Secret into the fields below.', 'socialauth-connect' ) . '</li>';
		echo '</ol>';

		echo '<p class="description">';
		echo esc_html__( 'Important: The callback URL must match exactly (including protocol and path).', 'socialauth-connect' );
		echo '</p>';
	}

	/**
	 * AJAX handler — test Facebook App credentials.
	 */
	public function ajax_test_facebook(): void {
		check_ajax_referer( 'socialauth_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}

		$client_id     = sanitize_text_field( wp_unslash( $_POST['client_id'] ?? '' ) );
		$client_secret = sanitize_text_field( wp_unslash( $_POST['client_secret'] ?? '' ) );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json_error( array( 'message' => 'App ID and App Secret are required.' ) );
		}

		// Step 1: Exchange a dummy code to verify credentials are valid.
		// A real test would fail, but the error message tells us if creds are OK.
		$token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
		$response  = wp_remote_post(
			$token_url,
			array(
				'body' => array(
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'grant_type'    => 'client_credentials',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => 'Network error: ' . $response->get_error_message() ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! empty( $body['access_token'] ) ) {
			// App credentials valid. Try fetching app info using the token we just obtained.
			$app_url   = 'https://graph.facebook.com/v18.0/app?access_token=' . rawurlencode( $body['access_token'] );
			$app_resp  = wp_remote_get( $app_url, array( 'timeout' => 10 ) );
			$app_name  = '';

			if ( ! is_wp_error( $app_resp ) ) {
				$app_data = json_decode( wp_remote_retrieve_body( $app_resp ), true );
				$app_name = $app_data['name'] ?? '';
			}

			$redirect_uri = esc_url(
				add_query_arg(
					array(
						'socialauth_provider' => 'facebook',
						'socialauth_action'   => 'callback',
					),
					site_url( '/' )
				)
			);

			wp_send_json_success( array(
				'valid'        => true,
				'app_name'     => $app_name,
				'redirect_uri' => $redirect_uri,
			) );
		} else {
			$error_msg = $body['error']['message'] ?? 'Unknown error';
			wp_send_json_error( array( 'message' => 'Invalid credentials: ' . esc_html( $error_msg ) ) );
		}
	}
}
