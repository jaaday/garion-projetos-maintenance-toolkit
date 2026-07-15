<?php
/**
 * Heartbeat API control: slow it down or disable it in specific contexts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Heartbeat {

	const OPTION_KEY = 'gpmt_heartbeat_mode';

	public static function get_mode() {
		$mode = get_option( self::OPTION_KEY, 'default' );

		return in_array( $mode, array( 'default', 'slow', 'disable_frontend', 'disable_except_editor', 'disable_all' ), true )
			? $mode
			: 'default';
	}

	public function __construct() {
		$mode = self::get_mode();

		if ( 'default' === $mode ) {
			return;
		}

		if ( 'slow' === $mode ) {
			add_filter( 'heartbeat_settings', array( $this, 'slow_down' ) );
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_disable' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_disable' ), 100 );
	}

	public function slow_down( $settings ) {
		$settings['interval'] = 60;

		return $settings;
	}

	public function maybe_disable() {
		$mode = self::get_mode();

		if ( 'disable_all' === $mode ) {
			wp_deregister_script( 'heartbeat' );
			return;
		}

		if ( 'disable_frontend' === $mode && ! is_admin() ) {
			wp_deregister_script( 'heartbeat' );
			return;
		}

		if ( 'disable_except_editor' === $mode && is_admin() ) {
			global $pagenow;

			if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
				wp_deregister_script( 'heartbeat' );
			}
		}
	}
}
