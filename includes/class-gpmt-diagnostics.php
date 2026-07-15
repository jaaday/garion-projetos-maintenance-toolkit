<?php
/**
 * Diagnostics panel: PHP/WordPress version, HTTPS, extensions, disk space and more.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Diagnostics {

	const MIN_PHP_VERSION = '8.0';

	public function get_report() {
		global $wpdb;

		$disk_free  = @disk_free_space( ABSPATH ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- disk_free_space() can be disabled on some hosts; a warning would be noise, we just show "unknown" below.
		$active_theme = wp_get_theme();

		return array(
			array(
				'label'  => __( 'WordPress version', 'garion-projetos-maintenance-toolkit' ),
				'value'  => get_bloginfo( 'version' ),
				'status' => 'ok',
			),
			array(
				'label'  => __( 'PHP version', 'garion-projetos-maintenance-toolkit' ),
				'value'  => PHP_VERSION,
				'status' => version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' ) ? 'ok' : 'warning',
			),
			array(
				'label'  => __( 'HTTPS', 'garion-projetos-maintenance-toolkit' ),
				'value'  => is_ssl() ? __( 'Active', 'garion-projetos-maintenance-toolkit' ) : __( 'Not active', 'garion-projetos-maintenance-toolkit' ),
				'status' => is_ssl() ? 'ok' : 'warning',
			),
			array(
				'label'  => __( 'Site URL scheme', 'garion-projetos-maintenance-toolkit' ),
				'value'  => wp_parse_url( home_url(), PHP_URL_SCHEME ),
				'status' => 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ? 'ok' : 'warning',
			),
			array(
				'label'  => __( 'Database server version', 'garion-projetos-maintenance-toolkit' ),
				'value'  => $wpdb->db_version(),
				'status' => 'ok',
			),
			array(
				'label'  => __( 'PHP memory limit', 'garion-projetos-maintenance-toolkit' ),
				'value'  => ini_get( 'memory_limit' ),
				'status' => 'ok',
			),
			array(
				'label'  => __( 'PHP max execution time', 'garion-projetos-maintenance-toolkit' ),
				'value'  => ini_get( 'max_execution_time' ) . 's',
				'status' => 'ok',
			),
			array(
				'label'  => __( 'Free disk space', 'garion-projetos-maintenance-toolkit' ),
				'value'  => false !== $disk_free ? size_format( $disk_free ) : __( 'Unknown (disabled by host)', 'garion-projetos-maintenance-toolkit' ),
				'status' => false !== $disk_free && $disk_free < 500 * MB_IN_BYTES ? 'warning' : 'ok',
			),
			array(
				'label'  => __( 'Persistent object cache', 'garion-projetos-maintenance-toolkit' ),
				'value'  => wp_using_ext_object_cache() ? __( 'Active', 'garion-projetos-maintenance-toolkit' ) : __( 'Not active (using default DB cache)', 'garion-projetos-maintenance-toolkit' ),
				'status' => 'ok',
			),
			array(
				'label'  => __( 'Active theme', 'garion-projetos-maintenance-toolkit' ),
				'value'  => $active_theme->get( 'Name' ) . ' ' . $active_theme->get( 'Version' ),
				'status' => 'ok',
			),
			array(
				'label'  => __( 'Active plugins', 'garion-projetos-maintenance-toolkit' ),
				'value'  => count( (array) get_option( 'active_plugins', array() ) ),
				'status' => 'ok',
			),
			array(
				'label'  => 'Imagick',
				'value'  => extension_loaded( 'imagick' ) ? __( 'Installed', 'garion-projetos-maintenance-toolkit' ) : __( 'Not installed (used for image processing)', 'garion-projetos-maintenance-toolkit' ),
				'status' => extension_loaded( 'imagick' ) ? 'ok' : 'warning',
			),
			array(
				'label'  => 'Intl',
				'value'  => extension_loaded( 'intl' ) ? __( 'Installed', 'garion-projetos-maintenance-toolkit' ) : __( 'Not installed (used for locale-aware formatting)', 'garion-projetos-maintenance-toolkit' ),
				'status' => extension_loaded( 'intl' ) ? 'ok' : 'warning',
			),
			array(
				'label'  => 'mbstring',
				'value'  => extension_loaded( 'mbstring' ) ? __( 'Installed', 'garion-projetos-maintenance-toolkit' ) : __( 'Not installed (used for multibyte string handling)', 'garion-projetos-maintenance-toolkit' ),
				'status' => extension_loaded( 'mbstring' ) ? 'ok' : 'warning',
			),
			array(
				'label'  => 'cURL',
				'value'  => extension_loaded( 'curl' ) ? __( 'Installed', 'garion-projetos-maintenance-toolkit' ) : __( 'Not installed (used for outgoing HTTP requests)', 'garion-projetos-maintenance-toolkit' ),
				'status' => extension_loaded( 'curl' ) ? 'ok' : 'warning',
			),
		);
	}
}
