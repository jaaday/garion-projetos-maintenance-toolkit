<?php
/**
 * Optional automatic weekly cleanup (revisions + expired transients) via WP-Cron.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Auto_Cleanup {

	const CRON_HOOK = 'gpmt_weekly_cleanup';
	const OPTION_KEY = 'gpmt_auto_cleanup';

	public function __construct() {
		add_action( self::CRON_HOOK, array( $this, 'run' ) );
	}

	public static function is_enabled() {
		return (bool) get_option( self::OPTION_KEY, false );
	}

	public static function sync_schedule( $enabled ) {
		update_option( self::OPTION_KEY, (bool) $enabled );

		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		if ( $enabled && ! $scheduled ) {
			wp_schedule_event( time(), 'weekly', self::CRON_HOOK );
		} elseif ( ! $enabled && $scheduled ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	public function run() {
		( new GP_MT_Revisions() )->clean();
		( new GP_MT_Transients() )->clean();
	}
}
