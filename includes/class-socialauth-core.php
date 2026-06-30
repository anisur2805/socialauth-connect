<?php
namespace SocialAuth;

use SocialAuth\Auth\AuthManager;
use SocialAuth\Admin\AdminMenu;
use SocialAuth\Admin\AdminSettings;
use SocialAuth\Admin\AdminNotices;
use SocialAuth\Frontend\LoginButtons;
use SocialAuth\Frontend\Shortcode;
use SocialAuth\Frontend\Block;
use SocialAuth\Providers\Google;

class Core {

    private static ?self $instance = null;

    private array $providers = [];

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run(): void {
        $this->register_providers();
        $this->init_hooks();
    }

    private function register_providers(): void {
        $this->providers['google'] = new Google();

        /**
         * Filter: register additional providers.
         */
        $this->providers = apply_filters( 'socialauth_providers', $this->providers );
    }

    private function init_hooks(): void {
        // Auth flow.
        $auth = new AuthManager( $this->providers );
        $auth->register_hooks();

        // Admin.
        if ( is_admin() ) {
            $admin_menu     = new AdminMenu();
            $admin_settings = new AdminSettings();
            $admin_notices  = new AdminNotices();

            $admin_menu->register_hooks();
            $admin_settings->register_hooks();
            $admin_notices->register_hooks();
        }

        // Frontend.
        $buttons  = new LoginButtons( $this->providers );
        $shortcode = new Shortcode( $this->providers );
        $block    = new Block();

        $buttons->register_hooks();
        $shortcode->register_hooks();
        $block->register_hooks();
    }

    public function get_provider( string $id ): ?object {
        return $this->providers[ $id ] ?? null;
    }

    public function get_providers(): array {
        return $this->providers;
    }
}
