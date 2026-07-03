<?php
defined( 'ABSPATH' ) || exit;
namespace SocialAuth\Frontend;

/**
 * Gutenberg block registration (Phase 3).
 * Placeholder for now.
 */
class Block {

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	public function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Block registration will go here when block.json is created.
		// For now, the shortcode handles rendering.
	}
}
