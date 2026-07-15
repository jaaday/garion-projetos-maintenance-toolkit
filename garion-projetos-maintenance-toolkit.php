<?php
/**
 * Plugin Name: Garion Projetos Maintenance Toolkit
 * Description: WordPress site optimization and maintenance: database cleanup, cache, Heartbeat API control and diagnostics.
 * Version: 0.2.0
 * Author: Garion Projetos
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: garion-projetos-maintenance-toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GPMT_VERSION', '0.2.0' );
define( 'GPMT_PATH', plugin_dir_path( __FILE__ ) );
define( 'GPMT_URL', plugin_dir_url( __FILE__ ) );

require_once GPMT_PATH . 'includes/class-gpmt-revisions.php';
require_once GPMT_PATH . 'includes/class-gpmt-transients.php';
require_once GPMT_PATH . 'includes/class-gpmt-database.php';
require_once GPMT_PATH . 'includes/class-gpmt-feature-toggles.php';
require_once GPMT_PATH . 'includes/class-gpmt-heartbeat.php';
require_once GPMT_PATH . 'includes/class-gpmt-cache.php';
require_once GPMT_PATH . 'includes/class-gpmt-diagnostics.php';
require_once GPMT_PATH . 'includes/class-gpmt-auto-cleanup.php';
require_once GPMT_PATH . 'admin/class-gpmt-admin-page.php';

register_deactivation_hook( __FILE__, array( 'GP_MT_Auto_Cleanup', 'deactivate' ) );

add_action( 'plugins_loaded', 'gpmt_init' );

function gpmt_init() {
	new GP_MT_Feature_Toggles();
	new GP_MT_Heartbeat();
	new GP_MT_Auto_Cleanup();

	if ( is_admin() ) {
		new GP_MT_Admin_Page();
	}
}
