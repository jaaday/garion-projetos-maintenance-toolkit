=== Garion Projetos Maintenance Toolkit ===
Contributors: garionprojetos
Tags: maintenance, database, cache, performance, cleanup
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress site optimization and maintenance: database cleanup, cache, Heartbeat API control and diagnostics.

== Description ==

Maintenance Toolkit helps keep WordPress healthy:

* Revision cleanup
* Expired transient removal
* Database optimization
* Disabling unnecessary features
* Heartbeat API control
* Cache clearing
* Diagnostics panel
* PHP version check
* HTTPS validation
* Extension check (Imagick, Intl)

This plugin does not send data to external servers. All processing happens locally, on your own site.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from the "Plugins" screen.
3. Open the new "Maintenance Toolkit" menu in the admin sidebar to run maintenance routines, review diagnostics, toggle features and configure Heartbeat.

== Frequently Asked Questions ==

= Does this plugin send data to external services? =

No. All maintenance routines run locally on your server.

= Does it delete all my post revisions? =

No. It only deletes revisions beyond the 5 most recent per post, keeping your recent edit history intact.

= Can cleanup run automatically? =

Yes. Enable "Automatic weekly cleanup" under Settings to clean old revisions and expired transients on a weekly schedule via WP-Cron.

== Changelog ==

= 0.2.0 =
* Implemented all planned features: revision cleanup, expired transient removal, database table optimization, cache clearing, Heartbeat API control, feature toggles (emojis, embeds, XML-RPC, feed links, generator tag, self-pingbacks) and a diagnostics panel (PHP/WordPress version, HTTPS, disk space, extensions).
* Added admin screen with Diagnostics, Maintenance, Features and Settings tabs.
* Added optional automatic weekly cleanup via WP-Cron.

= 0.1.0 =
* Initial release.

== Upgrade Notice ==

= 0.2.0 =
Adds full functionality. Review the new "Maintenance Toolkit" admin menu after updating.
