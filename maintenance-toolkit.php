<?php
/**
 * Plugin Name: Maintenance Toolkit
 * Description: WordPress site optimization and maintenance: database cleanup, cache, Heartbeat API control and diagnostics.
 * Version: 0.1.0
 * Author: Garion Projetos
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: maintenance-toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPMT_VERSION', '0.1.0' );
define( 'WPMT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPMT_URL', plugin_dir_url( __FILE__ ) );
