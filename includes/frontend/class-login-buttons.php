<?php
namespace SocialAuth\Frontend;

class LoginButtons {

    private array $providers;

    public function __construct( array $providers ) {
        $this->providers = $providers;
    }

    public function register_hooks(): void {
        add_action( 'login_form', [ $this, 'render_login_buttons' ] );
        add_action( 'woocommerce_login_form_end', [ $this, 'render_login_buttons' ] );
    }

    public function render_login_buttons(): void {
        $enabled_providers = array_filter(
            $this->providers,
            fn( $p ) => $p->is_enabled()
        );

        if ( empty( $enabled_providers ) ) {
            return;
        }

        echo '<div class="socialauth-buttons">';
        echo '<p class="socialauth-divider"><span>' . esc_html__( 'or continue with', 'socialauth-connect' ) . '</span></p>';

        foreach ( $enabled_providers as $provider ) {
            $this->render_button( $provider );
        }

        echo '</div>';
    }

    private function render_button( object $provider ): void {
        $url = add_query_arg( [
            'socialauth_action'   => 'login',
            'socialauth_provider' => $provider->get_id(),
        ], site_url( '/' ) );

        $icon_url = SOCIALAUTH_PLUGIN_URL . 'assets/images/' . $provider->get_id() . '-logo.svg';
        $label    = sprintf( __( 'Continue with %s', 'socialauth-connect' ), $provider->get_name() );

        printf(
            '<a href="%s" class="socialauth-btn socialauth-btn-%s" rel="nofollow noopener">
                <img src="%s" alt="" class="socialauth-icon" aria-hidden="true" />
                <span>%s</span>
            </a>',
            esc_url( $url ),
            esc_attr( $provider->get_id() ),
            esc_url( $icon_url ),
            esc_html( $label )
        );
    }
}
