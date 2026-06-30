<?php
namespace SocialAuth\Frontend;

class Shortcode {

    private array $providers;

    public function __construct( array $providers ) {
        $this->providers = $providers;
    }

    public function register_hooks(): void {
        add_shortcode( 'socialauth_login', [ $this, 'render' ] );
    }

    /**
     * [socialauth_login providers="google" redirect="/dashboard" show_label="true"]
     */
    public function render( array $atts ): string {
        $atts = shortcode_atts( [
            'providers'   => 'google',
            'redirect'    => '',
            'show_label'  => 'true',
        ], $atts, 'socialauth_login' );

        $provider_ids = array_map( 'trim', explode( ',', $atts['providers'] ) );
        $output       = '<div class="socialauth-shortcode-buttons">';

        foreach ( $provider_ids as $id ) {
            $provider = $this->providers[ $id ] ?? null;

            if ( ! $provider || ! $provider->is_enabled() ) {
                continue;
            }

            $url = add_query_arg( [
                'socialauth_action'   => 'login',
                'socialauth_provider' => $id,
            ], site_url( '/' ) );

            if ( ! empty( $atts['redirect'] ) ) {
                $url = add_query_arg( 'socialauth_redirect', rawurlencode( $atts['redirect'] ), $url );
            }

            $icon_url = SOCIALAUTH_PLUGIN_URL . 'assets/images/' . $id . '-logo.svg';
            $label    = sprintf( __( 'Continue with %s', 'socialauth-connect' ), $provider->get_name() );

            $output .= sprintf(
                '<a href="%s" class="socialauth-btn socialauth-btn-%s" rel="nofollow noopener">
                    <img src="%s" alt="" class="socialauth-icon" aria-hidden="true" />
                    %s<span>%s</span>
                </a>',
                esc_url( $url ),
                esc_attr( $id ),
                esc_url( $icon_url ),
                'true' === $atts['show_label'] ? '' : ' style="font-size:0;"',
                esc_html( $label )
            );
        }

        $output .= '</div>';

        return $output;
    }
}
