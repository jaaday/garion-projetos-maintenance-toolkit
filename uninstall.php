<?php
/**
 * Uninstall routine: removes options created by the plugin and clears its scheduled cron event.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'gpmt_features' );
delete_option( 'gpmt_heartbeat_mode' );
delete_option( 'gpmt_auto_cleanup' );

wp_clear_scheduled_hook( 'gpmt_weekly_cleanup' );
