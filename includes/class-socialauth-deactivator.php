<?php
namespace SocialAuth;

class Deactivator {

	public static function deactivate(): void {
		// Clean up transients.
		delete_transient( 'socialauth_state_' );
	}
}
