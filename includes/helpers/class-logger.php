<?php
namespace SocialAuth\Helpers;

class Logger {

	public static function info( string $message, array $context = array() ): void {
		self::log( 'info', $message, $context );
	}

	public static function warning( string $message, array $context = array() ): void {
		self::log( 'warning', $message, $context );
	}

	public static function error( string $message, array $context = array() ): void {
		self::log( 'error', $message, $context );
	}

	public static function debug( string $message, array $context = array() ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		self::log( 'debug', $message, $context );
	}

	private static function log( string $level, string $message, array $context ): void {
		$context_str = ! empty( $context ) ? ' | ' . wp_json_encode( $context ) : '';
		$log_line    = sprintf( '[SocialAuth][%s] %s%s', strtoupper( $level ), $message, $context_str );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( $log_line );
		}
	}
}
