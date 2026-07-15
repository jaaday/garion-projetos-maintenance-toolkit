<?php
/**
 * Admin screen: tabbed UI for Diagnostics, Maintenance, Features and Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_MT_Admin_Page {

	const MENU_SLUG = 'gpmt-toolkit';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'show_notice' ) );
		add_action( 'admin_post_gpmt_clean_revisions', array( $this, 'handle_clean_revisions' ) );
		add_action( 'admin_post_gpmt_clean_transients', array( $this, 'handle_clean_transients' ) );
		add_action( 'admin_post_gpmt_optimize_database', array( $this, 'handle_optimize_database' ) );
		add_action( 'admin_post_gpmt_clear_cache', array( $this, 'handle_clear_cache' ) );
		add_action( 'admin_post_gpmt_save_features', array( $this, 'handle_save_features' ) );
		add_action( 'admin_post_gpmt_save_settings', array( $this, 'handle_save_settings' ) );
	}

	public function add_menu() {
		add_menu_page(
			__( 'Garion Projetos Maintenance Toolkit', 'garion-projetos-maintenance-toolkit' ),
			__( 'Maintenance Toolkit', 'garion-projetos-maintenance-toolkit' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' ),
			'dashicons-admin-tools',
			81
		);
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style( 'gpmt-admin', GPMT_URL . 'assets/css/admin.css', array(), GPMT_VERSION );
	}

	private function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'diagnostics'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector.

		return in_array( $tab, array( 'diagnostics', 'maintenance', 'features', 'settings' ), true ) ? $tab : 'diagnostics';
	}

	private function redirect_to( $tab, $notice = '' ) {
		$args = array(
			'page' => self::MENU_SLUG,
			'tab'  => $tab,
		);

		if ( $notice ) {
			$args['gpmt_notice'] = rawurlencode( $notice );
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	public function show_notice() {
		if ( empty( $_GET['gpmt_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice trigger.
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sanitize_text_field( wp_unslash( $_GET['gpmt_notice'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab = $this->current_tab();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Maintenance Toolkit', 'garion-projetos-maintenance-toolkit' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $this->tabs() as $slug => $label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::MENU_SLUG, 'tab' => $slug ), admin_url( 'admin.php' ) ) ); ?>"
						class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<div class="gpmt-tab-content">
				<?php
				switch ( $tab ) {
					case 'maintenance':
						$this->render_maintenance();
						break;
					case 'features':
						$this->render_features();
						break;
					case 'settings':
						$this->render_settings();
						break;
					default:
						$this->render_diagnostics();
				}
				?>
			</div>
		</div>
		<?php
	}

	private function tabs() {
		return array(
			'diagnostics' => __( 'Diagnostics', 'garion-projetos-maintenance-toolkit' ),
			'maintenance' => __( 'Maintenance', 'garion-projetos-maintenance-toolkit' ),
			'features'    => __( 'Features', 'garion-projetos-maintenance-toolkit' ),
			'settings'    => __( 'Settings', 'garion-projetos-maintenance-toolkit' ),
		);
	}

	private function render_diagnostics() {
		$report = ( new GP_MT_Diagnostics() )->get_report();
		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Check', 'garion-projetos-maintenance-toolkit' ); ?></th>
					<th><?php esc_html_e( 'Value', 'garion-projetos-maintenance-toolkit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $report as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['label'] ); ?></td>
						<td>
							<span class="gpmt-status gpmt-status-<?php echo esc_attr( $row['status'] ); ?>">
								<?php echo esc_html( $row['value'] ); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	private function render_maintenance() {
		$revisions  = new GP_MT_Revisions();
		$transients = new GP_MT_Transients();
		$database   = new GP_MT_Database();
		?>
		<div class="gpmt-cards">
			<div class="gpmt-card">
				<h2><?php esc_html_e( 'Post revisions', 'garion-projetos-technical-seo-toolkit' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %d: number of excess revisions found. */
						esc_html__( '%d revision(s) beyond the last 5 per post.', 'garion-projetos-maintenance-toolkit' ),
						(int) $revisions->count_excess()
					);
					?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'gpmt_clean_revisions' ); ?>
					<input type="hidden" name="action" value="gpmt_clean_revisions" />
					<?php submit_button( __( 'Clean old revisions', 'garion-projetos-maintenance-toolkit' ), 'secondary' ); ?>
				</form>
			</div>

			<div class="gpmt-card">
				<h2><?php esc_html_e( 'Expired transients', 'garion-projetos-maintenance-toolkit' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %d: number of expired transients found. */
						esc_html__( '%d expired transient(s) found.', 'garion-projetos-maintenance-toolkit' ),
						(int) $transients->count_expired()
					);
					?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'gpmt_clean_transients' ); ?>
					<input type="hidden" name="action" value="gpmt_clean_transients" />
					<?php submit_button( __( 'Remove expired transients', 'garion-projetos-maintenance-toolkit' ), 'secondary' ); ?>
				</form>
			</div>

			<div class="gpmt-card">
				<h2><?php esc_html_e( 'Database optimization', 'garion-projetos-maintenance-toolkit' ); ?></h2>
				<p><?php echo esc_html( sprintf( /* translators: %d: number of tables. */ __( '%d table(s) with this site\'s prefix.', 'garion-projetos-maintenance-toolkit' ), count( $database->get_tables_status() ) ) ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'gpmt_optimize_database' ); ?>
					<input type="hidden" name="action" value="gpmt_optimize_database" />
					<?php submit_button( __( 'Optimize all tables', 'garion-projetos-maintenance-toolkit' ), 'secondary' ); ?>
				</form>
			</div>

			<div class="gpmt-card">
				<h2><?php esc_html_e( 'Cache', 'garion-projetos-maintenance-toolkit' ); ?></h2>
				<p><?php esc_html_e( 'Flushes the object cache and any supported caching plugin (W3 Total Cache, WP Super Cache, WP Rocket).', 'garion-projetos-maintenance-toolkit' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'gpmt_clear_cache' ); ?>
					<input type="hidden" name="action" value="gpmt_clear_cache" />
					<?php submit_button( __( 'Clear cache', 'garion-projetos-maintenance-toolkit' ), 'secondary' ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	private function render_features() {
		$settings = GP_MT_Feature_Toggles::get_settings();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'gpmt_save_features' ); ?>
			<input type="hidden" name="action" value="gpmt_save_features" />
			<table class="form-table">
				<?php foreach ( $this->feature_labels() as $key => $label ) : ?>
					<tr>
						<th><?php echo esc_html( $label ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="features[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?> />
								<?php esc_html_e( 'Enabled', 'garion-projetos-maintenance-toolkit' ); ?>
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	private function feature_labels() {
		return array(
			'disable_emojis'         => __( 'Disable emoji scripts and styles', 'garion-projetos-maintenance-toolkit' ),
			'disable_embeds'         => __( 'Disable WordPress embeds script', 'garion-projetos-maintenance-toolkit' ),
			'disable_xmlrpc'         => __( 'Disable XML-RPC', 'garion-projetos-maintenance-toolkit' ),
			'disable_rss_feed_links' => __( 'Remove RSS feed links from <head>', 'garion-projetos-maintenance-toolkit' ),
			'remove_generator_tag'   => __( 'Remove WordPress generator meta tag', 'garion-projetos-maintenance-toolkit' ),
			'disable_self_pingbacks' => __( 'Disable self-pingbacks', 'garion-projetos-maintenance-toolkit' ),
		);
	}

	private function render_settings() {
		$heartbeat_mode = GP_MT_Heartbeat::get_mode();
		$auto_cleanup   = GP_MT_Auto_Cleanup::is_enabled();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'gpmt_save_settings' ); ?>
			<input type="hidden" name="action" value="gpmt_save_settings" />
			<table class="form-table">
				<tr>
					<th><label for="heartbeat_mode"><?php esc_html_e( 'Heartbeat API', 'garion-projetos-maintenance-toolkit' ); ?></label></th>
					<td>
						<select id="heartbeat_mode" name="heartbeat_mode">
							<option value="default" <?php selected( $heartbeat_mode, 'default' ); ?>><?php esc_html_e( 'Default (15-60s, WordPress default)', 'garion-projetos-maintenance-toolkit' ); ?></option>
							<option value="slow" <?php selected( $heartbeat_mode, 'slow' ); ?>><?php esc_html_e( 'Slow down to 60s everywhere', 'garion-projetos-maintenance-toolkit' ); ?></option>
							<option value="disable_except_editor" <?php selected( $heartbeat_mode, 'disable_except_editor' ); ?>><?php esc_html_e( 'Disable everywhere in admin except the post editor', 'garion-projetos-maintenance-toolkit' ); ?></option>
							<option value="disable_frontend" <?php selected( $heartbeat_mode, 'disable_frontend' ); ?>><?php esc_html_e( 'Disable on the front-end only', 'garion-projetos-maintenance-toolkit' ); ?></option>
							<option value="disable_all" <?php selected( $heartbeat_mode, 'disable_all' ); ?>><?php esc_html_e( 'Disable everywhere', 'garion-projetos-maintenance-toolkit' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Automatic weekly cleanup', 'garion-projetos-maintenance-toolkit' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="auto_cleanup" value="1" <?php checked( $auto_cleanup ); ?> />
							<?php esc_html_e( 'Automatically clean old revisions and expired transients once a week', 'garion-projetos-maintenance-toolkit' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	public function handle_clean_revisions() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gpmt_clean_revisions' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'garion-projetos-maintenance-toolkit' ) );
		}

		$count = ( new GP_MT_Revisions() )->clean();

		/* translators: %d: number of revisions deleted. */
		$this->redirect_to( 'maintenance', sprintf( _n( '%d revision deleted.', '%d revisions deleted.', $count, 'garion-projetos-maintenance-toolkit' ), $count ) );
	}

	public function handle_clean_transients() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gpmt_clean_transients' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'garion-projetos-maintenance-toolkit' ) );
		}

		$count = ( new GP_MT_Transients() )->clean();

		/* translators: %d: number of transients deleted. */
		$this->redirect_to( 'maintenance', sprintf( _n( '%d expired transient removed.', '%d expired transients removed.', $count, 'garion-projetos-maintenance-toolkit' ), $count ) );
	}

	public function handle_optimize_database() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gpmt_optimize_database' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'garion-projetos-maintenance-toolkit' ) );
		}

		$result = ( new GP_MT_Database() )->optimize_all();

		$this->redirect_to(
			'maintenance',
			sprintf(
				/* translators: 1: number of tables, 2: reclaimed space. */
				__( '%1$d table(s) optimized, %2$s reclaimed.', 'garion-projetos-maintenance-toolkit' ),
				$result['tables'],
				size_format( $result['reclaimed_bytes'] )
			)
		);
	}

	public function handle_clear_cache() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gpmt_clear_cache' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'garion-projetos-maintenance-toolkit' ) );
		}

		$cleared = ( new GP_MT_Cache() )->clear();

		$this->redirect_to( 'maintenance', sprintf( __( 'Cache cleared: %s.', 'garion-projetos-maintenance-toolkit' ), implode( ', ', $cleared ) ) );
	}

	public function handle_save_features() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gpmt_save_features' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'garion-projetos-maintenance-toolkit' ) );
		}

		$input    = isset( $_POST['features'] ) ? (array) $_POST['features'] : array();
		$settings = array();

		foreach ( array_keys( GP_MT_Feature_Toggles::defaults() ) as $key ) {
			$settings[ $key ] = ! empty( $input[ $key ] );
		}

		update_option( GP_MT_Feature_Toggles::OPTION_KEY, $settings );

		$this->redirect_to( 'features', __( 'Feature settings saved.', 'garion-projetos-maintenance-toolkit' ) );
	}

	public function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gpmt_save_settings' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'garion-projetos-maintenance-toolkit' ) );
		}

		$mode = isset( $_POST['heartbeat_mode'] ) ? sanitize_key( wp_unslash( $_POST['heartbeat_mode'] ) ) : 'default';
		update_option( GP_MT_Heartbeat::OPTION_KEY, $mode );

		GP_MT_Auto_Cleanup::sync_schedule( ! empty( $_POST['auto_cleanup'] ) );

		$this->redirect_to( 'settings', __( 'Settings saved.', 'garion-projetos-maintenance-toolkit' ) );
	}
}
