<?php
/**
 * Database table optimization.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Database {

	public function get_tables_status() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off diagnostics read, must reflect current state.
		return $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name LIKE %s', $wpdb->esc_like( $wpdb->prefix ) . '%' ) );
	}

	public function optimize_all() {
		global $wpdb;

		$tables         = $this->get_tables_status();
		$optimized      = 0;
		$reclaimed_bytes = 0;

		foreach ( $tables as $table ) {
			$reclaimed_bytes += (int) $table->Data_free;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange -- table names come from SHOW TABLE STATUS on this site's own prefix, not user input; OPTIMIZE TABLE has no placeholder syntax.
			$wpdb->query( 'OPTIMIZE TABLE `' . esc_sql( $table->Name ) . '`' );
			++$optimized;
		}

		return array(
			'tables'          => $optimized,
			'reclaimed_bytes' => $reclaimed_bytes,
		);
	}
}
