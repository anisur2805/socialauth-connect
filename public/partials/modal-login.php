<?php
/**
 * Optional modal overlay for social login.
 *
 * @package SocialAuthConnect
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="socialauth-modal" class="socialauth-modal" style="display:none;">
    <div class="socialauth-modal-overlay"></div>
    <div class="socialauth-modal-content">
        <button type="button" class="socialauth-modal-close" aria-label="<?php esc_attr_e( 'Close', 'socialauth-connect' ); ?>">&times;</button>
        <h2><?php esc_html_e( 'Sign in', 'socialauth-connect' ); ?></h2>
        <div class="socialauth-modal-body">
            <?php
            // Render buttons inside modal via shortcode or LoginButtons class.
            do_action( 'socialauth_modal_buttons' );
            ?>
        </div>
    </div>
</div>
