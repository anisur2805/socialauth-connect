<?php
namespace SocialAuth;

class I18n {

	public static function load(): void {
		load_plugin_textdomain(
			'socialauth-connect',
			false,
			dirname( SOCIALAUTH_PLUGIN_BASENAME ) . '/languages'
		);
	}
}
