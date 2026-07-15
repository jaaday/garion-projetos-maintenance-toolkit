<?php
/**
 * Post revision cleanup: keeps only the N most recent revisions per post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Revisions {

	const DEFAULT_KEEP = 5;

	/**
	 * IDs of revisions beyond the $keep most recent for their post, across the whole site.
	 */
	private function get_excess_ids( $keep ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off maintenance scan across all revisions, must reflect current state.
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->posts} p
				WHERE p.post_type = 'revision'
				AND (
					SELECT COUNT(*) FROM {$wpdb->posts} p2
					WHERE p2.post_parent = p.post_parent
					AND p2.post_type = 'revision'
					AND p2.ID >= p.ID
				) > %d",
				$keep
			)
		);
	}

	public function count_excess( $keep = self::DEFAULT_KEEP ) {
		return count( $this->get_excess_ids( $keep ) );
	}

	public function clean( $keep = self::DEFAULT_KEEP ) {
		$ids     = $this->get_excess_ids( $keep );
		$deleted = 0;

		foreach ( $ids as $id ) {
			if ( wp_delete_post_revision( (int) $id ) ) {
				++$deleted;
			}
		}

		return $deleted;
	}
}
