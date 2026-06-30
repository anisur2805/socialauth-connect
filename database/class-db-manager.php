<?php
namespace SocialAuth\Database;

class DbManager {

    private const TABLE_VERSION_OPTION = 'socialauth_db_version';
    private const CURRENT_VERSION      = '1.0';

    /**
     * Run on activation — create custom tables.
     */
    public static function install(): void {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $prefix  = $wpdb->prefix;

        $sql_accounts = "CREATE TABLE IF NOT EXISTS {$prefix}socialauth_accounts (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id       BIGINT(20) UNSIGNED NOT NULL,
            provider      VARCHAR(50)         NOT NULL,
            provider_id   VARCHAR(255)        NOT NULL,
            email         VARCHAR(255)        DEFAULT NULL,
            profile_data  LONGTEXT            DEFAULT NULL,
            access_token  TEXT                DEFAULT NULL,
            refresh_token TEXT                DEFAULT NULL,
            token_expires DATETIME            DEFAULT NULL,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY   provider_user (provider, provider_id),
            KEY          user_id (user_id),
            KEY          email (email)
        ) {$charset};";

        $sql_log = "CREATE TABLE IF NOT EXISTS {$prefix}socialauth_log (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id    BIGINT(20) UNSIGNED DEFAULT NULL,
            provider   VARCHAR(50)         NOT NULL,
            action     VARCHAR(100)        NOT NULL,
            ip_address VARCHAR(45)         DEFAULT NULL,
            user_agent TEXT                DEFAULT NULL,
            created_at DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_accounts );
        dbDelta( $sql_log );

        update_option( self::TABLE_VERSION_OPTION, self::CURRENT_VERSION );
    }

    /**
     * Log an audit event to the socialauth_log table.
     */
    public static function log( int $user_id, string $provider, string $action ): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'socialauth_log',
            [
                'user_id'    => $user_id,
                'provider'   => $provider,
                'action'     => $action,
                'ip_address' => filter_var( $_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP ) ?: null,
                'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 500 ) : null,
            ],
            [ '%d', '%s', '%s', '%s', '%s' ]
        );
    }

    /**
     * Clean up on uninstall.
     */
    public static function uninstall(): void {
        global $wpdb;

        if ( get_option( 'socialauth_remove_data_on_uninstall', false ) ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}socialauth_accounts" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}socialauth_log" );
            delete_option( self::TABLE_VERSION_OPTION );
        }
    }
}
