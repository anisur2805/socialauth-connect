<?php
/**
 * SocialAuth Connect — Settings Page.
 *
 * @package SocialAuthConnect
 */

defined( 'ABSPATH' ) || exit;

$active_tab = $active_tab ?? 'general';
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=socialauth-connect&tab=general"
           class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'General', 'socialauth-connect' ); ?>
        </a>
        <a href="?page=socialauth-connect&tab=google"
           class="nav-tab <?php echo 'google' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Google', 'socialauth-connect' ); ?>
        </a>
    </nav>

    <div class="tab-content" style="margin-top: 20px;">
        <?php if ( 'google' === $active_tab ) : ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'socialauth_google_settings' );
                do_settings_sections( 'socialauth-google' );
                submit_button();
                ?>
            </form>
        <?php else : ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'socialauth_general' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="socialauth_allow_registration">
                                <?php esc_html_e( 'Allow Registration', 'socialauth-connect' ); ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            $checked = get_option( 'socialauth_allow_registration', true );
                            ?>
                            <input type="checkbox"
                                   id="socialauth_allow_registration"
                                   name="socialauth_allow_registration"
                                   value="1"
                                   <?php checked( $checked ); ?> />
                            <p class="description">
                                <?php esc_html_e( 'Allow new users to register via social login.', 'socialauth-connect' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="socialauth_login_redirect">
                                <?php esc_html_e( 'Login Redirect URL', 'socialauth-connect' ); ?>
                            </label>
                        </th>
                        <td>
                            <?php $redirect = get_option( 'socialauth_login_redirect', admin_url() ); ?>
                            <input type="url"
                                   id="socialauth_login_redirect"
                                   name="socialauth_login_redirect"
                                   value="<?php echo esc_attr( $redirect ); ?>"
                                   class="regular-text" />
                            <p class="description">
                                <?php esc_html_e( 'Where to redirect after successful login.', 'socialauth-connect' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
    </div>
</div>
