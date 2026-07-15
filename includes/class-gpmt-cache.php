<?php
/**
 * Cache clearing: flushes the WordPress object cache and, when present, popular caching plugins.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Cache {

	public function clear() {
		$cleared = array( 'object_cache' );

		wp_cache_flush();

		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
			$cleared[] = 'w3-total-cache';
		}

		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
			$cleared[] = 'wp-super-cache';
		}

		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
			$cleared[] = 'wp-rocket';
		}

		/**
		 * Fires after Maintenance Toolkit clears the built-in caches, so other
		 * plugins can hook in and clear their own cache layers too.
		 */
		do_action( 'gpmt_clear_cache' );

		return $cleared;
	}
}
