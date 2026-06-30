<?php
/**
 * Google sign-in button partial.
 *
 * @package SocialAuthConnect
 */

defined( 'ABSPATH' ) || exit;

$url = add_query_arg( [
    'socialauth_action'   => 'login',
    'socialauth_provider' => 'google',
], site_url( '/' ) );

$icon_url = SOCIALAUTH_PLUGIN_URL . 'assets/images/google-logo.svg';
$label    = __( 'Continue with Google', 'socialauth-connect' );
?>

<a href="<?php echo esc_url( $url ); ?>"
   class="socialauth-btn socialauth-btn-google"
   rel="nofollow noopener">
    <img src="<?php echo esc_url( $icon_url ); ?>"
         alt=""
         class="socialauth-icon"
         aria-hidden="true" />
    <span><?php echo esc_html( $label ); ?></span>
</a>
