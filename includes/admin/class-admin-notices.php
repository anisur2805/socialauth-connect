<?php
namespace SocialAuth\Admin;

defined( 'ABSPATH' ) || exit;
class AdminNotices {

	public function register_hooks(): void {
		add_action( 'admin_notices', array( $this, 'render_notices' ) );
	}

	public function render_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if Google provider is configured.
		$options = get_option( 'socialauth_google_settings', array() );
		if ( empty( $options['client_id'] ) || empty( $options['client_secret'] ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo esc_html__( 'SocialAuth Connect: Google provider not configured. ', 'socialauth-connect' );
			echo '<a href="' . esc_url( admin_url( 'options-general.php?page=socialauth-connect&tab=google' ) ) . '">';
			echo esc_html__( 'Configure now', 'socialauth-connect' );
			echo '</a></p></div>';
		}
	}
}
