<?php
namespace SocialAuth\Admin;

defined( 'ABSPATH' ) || exit;
use SocialAuth\User\UserMeta;

class DashboardWidget {

	public function register_hooks(): void {
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	public function register_widget(): void {
		if ( ! current_user_can( 'read' ) ) {
			return;
		}

		if ( ! get_option( 'socialauth_dashboard_widget', true ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'socialauth_account',
			__( 'Social Login Account', 'socialauth-connect' ),
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$user_id    = get_current_user_id();
		$linked     = UserMeta::get_linked_providers( $user_id );
		$logout_url = wp_logout_url( admin_url() );

		if ( empty( $linked ) ) {
			echo '<p>' . esc_html__( 'No social accounts connected.', 'socialauth-connect' ) . '</p>';
			return;
		}

		echo '<ul class="socialauth-dashboard-linked">';
		foreach ( $linked as $provider_id ) {
			$profile     = UserMeta::get_profile( $user_id, $provider_id );
			$name        = esc_html( $profile['name'] ?? $provider_id );
			$email       = esc_html( $profile['email'] ?? '' );
			$avatar_url  = $profile['avatar_url'] ?? '';
			$provider_icon = SOCIALAUTH_PLUGIN_URL . 'assets/images/' . $provider_id . '-logo.svg';

			if ( ! empty( $avatar_url ) ) {
				$avatar = '<img src="' . esc_url( $avatar_url ) . '" alt="" class="socialauth-dashboard-avatar" />';
			} else {
				$avatar = '<img src="' . esc_url( $provider_icon ) . '" alt="" class="socialauth-dashboard-avatar socialauth-dashboard-avatar--fallback" />';
			}

			printf(
				'<li>
					%s
					<div class="socialauth-dashboard-info">
						<span class="socialauth-dashboard-name">%s</span>
						%s
					</div>
				</li>',
				$avatar,
				$name,
				$email ? '<span class="socialauth-dashboard-email">' . $email . '</span>' : ''
			);
		}
		echo '</ul>';

		printf(
			'<p class="socialauth-dashboard-logout"><a href="%s" class="button">%s</a></p>',
			esc_url( $logout_url ),
			esc_html__( 'Log Out', 'socialauth-connect' )
		);
	}
}
