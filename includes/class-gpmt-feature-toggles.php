<?php
/**
 * Disables unnecessary default WordPress features, based on saved settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Feature_Toggles {

	const OPTION_KEY = 'gpmt_features';

	public static function defaults() {
		return array(
			'disable_emojis'         => false,
			'disable_embeds'         => false,
			'disable_xmlrpc'         => false,
			'disable_rss_feed_links' => false,
			'remove_generator_tag'   => false,
			'disable_self_pingbacks' => false,
		);
	}

	public static function get_settings() {
		return wp_parse_args( get_option( self::OPTION_KEY, array() ), self::defaults() );
	}

	public function __construct() {
		$settings = self::get_settings();

		if ( $settings['disable_emojis'] ) {
			$this->disable_emojis();
		}

		if ( $settings['disable_embeds'] ) {
			$this->disable_embeds();
		}

		if ( $settings['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( $settings['disable_rss_feed_links'] ) {
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}

		if ( $settings['remove_generator_tag'] ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}

		if ( $settings['disable_self_pingbacks'] ) {
			add_action( 'pre_ping', array( $this, 'remove_self_pings' ) );
		}
	}

	private function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'remove_emoji_tinymce_plugin' ) );
		add_filter( 'wp_resource_hints', array( $this, 'remove_emoji_dns_prefetch' ), 10, 2 );
	}

	public function remove_emoji_tinymce_plugin( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	}

	public function remove_emoji_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' !== $relation_type ) {
			return $urls;
		}

		return array_filter(
			$urls,
			static function ( $url ) {
				return false === strpos( $url, 's.w.org' );
			}
		);
	}

	private function disable_embeds() {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'embed_oembed_discover', '__return_false' );
		add_action(
			'wp_enqueue_scripts',
			static function () {
				wp_deregister_script( 'wp-embed' );
			},
			20
		);
	}

	public function remove_self_pings( &$links ) {
		$home = home_url();

		foreach ( $links as $key => $link ) {
			if ( 0 === strpos( $link, $home ) ) {
				unset( $links[ $key ] );
			}
		}
	}
}
