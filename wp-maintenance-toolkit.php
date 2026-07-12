<?php
/**
 * Plugin Name: WP Maintenance Toolkit
 * Description: Otimização e manutenção de sites WordPress — limpeza de banco, cache, Heartbeat API e diagnóstico.
 * Version: 0.1.0
 * Author: Garion Projetos
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-maintenance-toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPMT_VERSION', '0.1.0' );
define( 'WPMT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPMT_URL', plugin_dir_url( __FILE__ ) );
