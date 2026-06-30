<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SocialAuthConnect
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . 'vendor/autoload.php';

SocialAuth\Database\DbManager::uninstall();
