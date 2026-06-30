<?php
namespace SocialAuth\Admin;

class AdminMenu {

    public function register_hooks(): void {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
    }

    public function add_menu(): void {
        add_options_page(
            __( 'SocialAuth Connect', 'socialauth-connect' ),
            __( 'SocialAuth', 'socialauth-connect' ),
            'manage_options',
            'socialauth-connect',
            [ $this, 'render_page' ]
        );
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = sanitize_text_field( $_GET['tab'] ?? 'general' );

        include SOCIALAUTH_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }
}
