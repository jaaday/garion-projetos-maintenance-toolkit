<?php
/**
 * Expired transient removal.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Transients {

	public function count_expired() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off maintenance scan, must reflect current state.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);
	}

	public function clean() {
		$count = $this->count_expired();

		if ( function_exists( 'delete_expired_transients' ) ) {
			delete_expired_transients( true );
		}

		return $count;
	}
}
