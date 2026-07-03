<?php
namespace SocialAuth\Admin;

class AdminSettings {

	public function register_hooks(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
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
		/* translators: %1$s = link to Google Cloud Console, %2$s = redirect URI code */
		echo '<p>' . sprintf(
			esc_html__( 'Create credentials at %1$s. Set the Authorized redirect URI to: %2$s', 'socialauth-connect' ),
			'<a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>',
			'<code>' . esc_url(
				add_query_arg(
					array(
						'socialauth_provider' => 'google',
						'socialauth_action'   => 'callback',
					),
					site_url( '/' )
				)
			) . '</code>'
		) . '</p>';
	}

	public function render_facebook_section_description(): void {
		/* translators: %1$s = link to Facebook Developers, %2$s = redirect URI code */
		echo '<p>' . sprintf(
			esc_html__( 'Create a Facebook App at %1$s. Set the Authorized redirect URI (Valid OAuth Redirect URIs) to: %2$s', 'socialauth-connect' ),
			'<a href="https://developers.facebook.com/apps/" target="_blank">Facebook Developers</a>',
			'<code>' . esc_url(
				add_query_arg(
					array(
						'socialauth_provider' => 'facebook',
						'socialauth_action'   => 'callback',
					),
					site_url( '/' )
				)
			) . '</code>'
		) . '</p>';
	}
}
