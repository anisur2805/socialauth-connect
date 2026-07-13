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
        <a href="?page=socialauth-connect&tab=facebook"
           class="nav-tab <?php echo 'facebook' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Facebook', 'socialauth-connect' ); ?>
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
        <?php elseif ( 'facebook' === $active_tab ) : ?>
            <?php
            // Build values for wizard.
            $fb_parsed      = wp_parse_url( site_url( '/' ) );
            $fb_app_domain  = $fb_parsed['host'] ?? '';
            $fb_is_localhost = ( 'localhost' === $fb_app_domain || '127.0.0.1' === $fb_app_domain );
            $fb_redirect    = esc_url(
                add_query_arg(
                    array(
                        'socialauth_provider' => 'facebook',
                        'socialauth_action'   => 'callback',
                    ),
                    site_url( '/' )
                )
            );
            ?>
            <div class="socialauth-wizard">
                <h3><?php esc_html_e( 'Facebook App Setup Guide', 'socialauth-connect' ); ?></h3>
                <p class="socialauth-wizard-desc">
                    <?php esc_html_e( 'Follow these steps to configure your Facebook App for login:', 'socialauth-connect' ); ?>
                </p>
                <ol class="socialauth-wizard-steps">
                    <li>
                        <?php esc_html_e( 'Go to', 'socialauth-connect' ); ?>
                        <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener"><?php esc_html_e( 'Facebook Developers', 'socialauth-connect' ); ?></a>
                        <?php esc_html_e( 'and create a new App (type: "Business").', 'socialauth-connect' ); ?>
                    </li>
                    <?php if ( ! $fb_is_localhost ) : ?>
                    <li>
                        <?php esc_html_e( 'In Settings → Basic, add this value to "App Domains":', 'socialauth-connect' ); ?>
                        <div class="socialauth-copy-field">
                            <code id="socialauth-fb-domain"><?php echo esc_html( $fb_app_domain ); ?></code>
                            <button type="button" class="socialauth-copy-btn" data-copy-target="socialauth-fb-domain"><?php esc_html_e( 'Copy', 'socialauth-connect' ); ?></button>
                        </div>
                    </li>
                    <?php else : ?>
                    <li>
                        <?php esc_html_e( 'No "App Domains" entry needed for localhost — Facebook automatically allows it.', 'socialauth-connect' ); ?>
                    </li>
                    <?php endif; ?>
                    <li>
                        <?php esc_html_e( 'In Settings → Basic, copy the App ID and App Secret into the fields below.', 'socialauth-connect' ); ?>
                    </li>
                    <li>
                        <?php esc_html_e( 'Add "Facebook Login" product: left sidebar → "Add Products" → find "Facebook Login" → click "Set Up".', 'socialauth-connect' ); ?>
                    </li>
                    <li>
                        <?php esc_html_e( 'Then go to Facebook Login → Settings (left sidebar) and add this URL to "Valid OAuth Redirect URIs":', 'socialauth-connect' ); ?>
                        <div class="socialauth-copy-field">
                            <code id="socialauth-fb-redirect"><?php echo esc_html( $fb_redirect ); ?></code>
                            <button type="button" class="socialauth-copy-btn" data-copy-target="socialauth-fb-redirect"><?php esc_html_e( 'Copy', 'socialauth-connect' ); ?></button>
                        </div>
                    </li>
                    <li>
                        <?php esc_html_e( 'Click "Test Connection" to verify your credentials work, then Save Changes.', 'socialauth-connect' ); ?>
                    </li>
                </ol>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'socialauth_facebook_settings' );
                do_settings_sections( 'socialauth-facebook' );
                ?>
                <div class="socialauth-test-section">
                    <button type="button" id="socialauth-test-fb-connection" class="button button-secondary">
                        <?php esc_html_e( 'Test Connection', 'socialauth-connect' ); ?>
                    </button>
                    <div id="socialauth-test-result" class="socialauth-test-result"></div>
                </div>
                <?php submit_button(); ?>
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
                    <tr>
                        <th scope="row">
                            <label for="socialauth_dashboard_widget">
                                <?php esc_html_e( 'Dashboard Widget', 'socialauth-connect' ); ?>
                            </label>
                        </th>
                        <td>
                            <?php $widget_checked = get_option( 'socialauth_dashboard_widget', true ); ?>
                            <input type="checkbox"
                                   id="socialauth_dashboard_widget"
                                   name="socialauth_dashboard_widget"
                                   value="1"
                                   <?php checked( $widget_checked ); ?> />
                            <p class="description">
                                <?php esc_html_e( 'Show social account info and logout button on the dashboard.', 'socialauth-connect' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
    </div>
</div>
