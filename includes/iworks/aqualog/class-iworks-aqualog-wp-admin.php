<?php
/**
 * AquaLog - Admin Class
 *
 * Handles all WordPress admin-specific functionality for the plugin.
 *
 * @package WordPress_Plugin_Stub
 * @author  Marcin Pietrzak <marcin@iworks.pl>
 * @license GPL-2.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_aqualog_wp_admin' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/class-aqualog-base.php';

/**
 * Admin functionality for AquaLog.
 *
 * This class handles all admin-specific functionality including:
 * - Plugin settings and options management
 * - Admin assets registration
 * - Plugin meta links
 * - Admin interface rendering
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin extends iworks_aqualog_base {

	/**
	 * The capability required to access plugin admin features.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $capability = 'manage_options';

	/**
	 * Initialize the admin class.
	 *
	 * Sets up the required capability and registers admin hooks.
	 *
	 * @since 1.0.0
	 * @see iworks_aqualog_base::__construct()
	 */
	public function __construct() {
		parent::__construct();
		$this->capability = apply_filters( 'iworks_aqualog_capability', $this->capability );
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts_register_assets' ), 0 );
		add_action( 'wp_loaded', array( $this, 'action_wp_loaded_init_options' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'display_admin_dashboard_message' ) );
		add_action( 'wp_ajax_iworks_aqualog_dismiss_message', array( $this, 'ajax_dismiss_dashboard_message' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		/**
		 * iWorks Options Hooks
		 */
		add_filter( 'aqualog/etc/config/metaboxes', array( $this, 'filter_iworks_options_add_meta_boxes' ) );
	}

	/**
	 * Add meta boxes to the iWorks Options page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $metaboxes The options array.
	 *
	 * @return array The modified options array.
	 */
	public function filter_iworks_options_add_meta_boxes( $metaboxes ) {
		$metaboxes['assistance'] = array(
			'title'    => __( 'Have a question or need help?', 'aqualog' ),
			'callback' => array( $this, 'need_assistance' ),
			'context'  => 'side',
			'priority' => 'core',
		);
		$metaboxes['love']       = array(
			'title'    => __( 'Enjoying this plugin?', 'aqualog' ),
			'callback' => array( $this, 'loved_this_plugin' ),
			'context'  => 'side',
			'priority' => 'core',
		);
		return $metaboxes;
	}
	/**
	 * Display plugin appreciation links.
	 *
	 * Outputs HTML for the "Love this plugin" section, including links to rate the plugin
	 * and share it with others.
	 *
	 * @since 1.0.0
	 * @param object $iworks_orphan The main plugin instance.
	 * @return void
	 */
	public function loved_this_plugin() {
		$content = apply_filters( 'iworks_rate_love', '', 'aqualog' );
		if ( ! empty( $content ) ) {
			echo wp_kses_post( $content );
			return;
		}
		?>
<p><?php esc_html_e( 'Help others discover it—share the link with your friends and community!', 'aqualog' ); ?></p>
<ul>
	<li><a target="_blank" href="<?php echo esc_url( _x( 'https://wordpress.org/support/plugin/aqualog/reviews/#new-post', 'link to add new review page on WordPress.org', 'aqualog' ) ); ?>"><?php esc_html_e( 'Give it a five stars on WordPress.org', 'aqualog' ); ?></a></li>
	<li><a target="_blank" href="<?php echo esc_url( _x( 'https://wordpress.org/plugins/aqualog/', 'plugin home page on WordPress.org', 'aqualog' ) ); ?>"><?php esc_html_e( 'Link to it so others can easily find it', 'aqualog' ); ?></a></li>
</ul>
		<?php
	}
	/**
	 * Display assistance information.
	 *
	 * Outputs HTML for the "Need Assistance" section, including support links.
	 *
	 * @since 1.0.0
	 * @param object $iworks_orphans The main plugin instance.
	 * @return void
	 */
	public function need_assistance() {
		$content = apply_filters( 'iworks_rate_assistance', '', 'aqualog' );
		if ( ! empty( $content ) ) {
			echo wp_kses_post( $content );
			return;
		}

		?>
<p><?php esc_html_e( 'We’re here for you! Send us a message and we’ll get back to you as soon as possible.', 'aqualog' ); ?></p>
<ul>
	<li><a target="_blank" href="<?php echo esc_url( _x( 'https://wordpress.org/support/plugin/aqualog/', 'link to support forum on WordPress.org', 'aqualog' ) ); ?>"><?php esc_html_e( 'WordPress Help Forum', 'aqualog' ); ?></a></li>
</ul>
		<?php
	}
	/**
	 * Initialize plugin options during WordPress loaded hook.
	 *
	 * This method is called when WordPress is fully loaded. It performs the following actions:
	 * 1. Ensures the options object is properly initialized
	 * 2. Initializes the plugin options through the options object
	 *
	 * @hook wp_loaded
	 * @since 1.0.0
	 * @see iworks_aqualog_base::check_option_object()
	 * @see iworks_options::options_init()
	 *
	 * @return void
	 */
	public function action_wp_loaded_init_options() {
		$this->check_option_object();
		$this->options->options_init();
	}

	/**
	 * Register admin assets.
	 *
	 * Registers the required JavaScript files for the admin interface.
	 *
	 * @hook admin_enqueue_scripts
	 * @since 1.1.0
	 */
	public function action_admin_enqueue_scripts_register_assets() {
		$name = $this->dir . '-admin';
		$file = 'assets/scripts/' . $this->dir . '-admin' . $this->dev . '.js';
		wp_register_script(
			$name,
			plugins_url( $file, $this->plugin_file_path ),
			array(),
			md5( file_get_contents( plugin_dir_path( $this->plugin_file_path ) . $file ) ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		$file = 'assets/styles/' . $this->dir . '-admin' . $this->dev . '.css';
		wp_enqueue_style(
			$name,
			plugins_url( $file, $this->plugin_file_path ),
			array(),
			md5( file_get_contents( plugin_dir_path( $this->plugin_file_path ) . $file ) )
		);
	}

	/**
	 * Filters the array of row meta for the plugin in the Plugins list table.
	 *
	 * This method adds custom links to the plugin's row in the WordPress admin Plugins page.
	 * It adds:
	 * 1. A 'Settings' link (for non-multisite installations with proper capabilities)
	 * 2. A 'Donate' link (only in the free version)
	 * 3. A 'GitHub' link
	 *
	 * @hook plugin_row_meta
	 * @since 1.0.0
	 *
	 * @param string[] $links An array of the plugin's metadata, including the version, author,
	 *                       author URI, and plugin name.
	 * @param string  $file  Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Array of plugin row links with our custom links added.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/aqualog.php' == $file ) {
			if ( ! is_multisite() && current_user_can( $this->capability ) ) {
				$links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						add_query_arg(
							array(
								'page' => $this->dir . '/admin/index.php',
							),
							admin_url( 'admin.php' )
						)
					),
					esc_html__( 'Settings', 'aqualog' )
				);
			}
			/* start:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'aqualog',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://ko-fi.com/iworks'
					)
				),
				esc_html__( 'Donate', 'aqualog' )
			);
			/* end:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'aqualog',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://github.com/iworks.pl/aqualog'
					)
				),
				esc_html__( 'GitHub', 'aqualog' )
			);
		}
		return $links;
	}

	/**
	 * Display admin dashboard message.
	 *
	 * Shows a dismissible admin notice with dummy content. The message
	 * will only be displayed if the user hasn't dismissed it previously.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function display_admin_dashboard_message() {
		// Only show on admin dashboard page
		global $pagenow;
		if ( 'index.php' !== $pagenow ) {
			return;
		}

		// Only show to users with proper capabilities
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		// Check if user has dismissed this message
		$user_id = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'iworks_aqualog_dashboard_message_dismissed', true );

		if ( $dismissed ) {
			return;
		}

		// Enqueue the JavaScript for dismissal functionality
		wp_enqueue_script( 'jquery' );
		?>
		<div id="iworks-aqualog-dashboard-message" class="notice notice-info is-dismissible">
			<h3><?php esc_html_e( 'Welcome to AquaLog!', 'aqualog' ); ?></h3>
			<p>
				<?php 
				esc_html_e( 'Thank you for installing AquaLog! This powerful plugin helps you manage and track your water-related activities with ease. Here are some quick tips to get you started:', 'aqualog' ); 
				?>
			</p>
			<ul>
				<li><?php esc_html_e( 'Navigate to the AquaLog settings page to configure your preferences', 'aqualog' ); ?></li>
				<li><?php esc_html_e( 'Add your first water entry to start tracking your daily consumption', 'aqualog' ); ?></li>
				<li><?php esc_html_e( 'Check out the analytics dashboard to view your water usage patterns', 'aqualog' ); ?></li>
				<li><?php esc_html_e( 'Set up reminders to help you stay hydrated throughout the day', 'aqualog' ); ?></li>
			</ul>
			<p>
				<strong><?php esc_html_e( 'Pro Tip:', 'aqualog' ); ?></strong> 
				<?php esc_html_e( 'Regular water tracking can improve your health and wellbeing. Make it a daily habit!', 'aqualog' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->dir . '/admin/index.php' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Get Started', 'aqualog' ); ?>
				</a>
				<a href="<?php echo esc_url( _x( 'https://wordpress.org/plugins/aqualog/', 'plugin homepage', 'aqualog' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'Learn More', 'aqualog' ); ?>
				</a>
			</p>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#iworks-aqualog-dashboard-message').on('click', '.notice-dismiss', function(e) {
				e.preventDefault();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'iworks_aqualog_dismiss_message',
						nonce: '<?php echo wp_create_nonce( 'iworks_aqualog_dismiss_message' ); ?>'
					},
					success: function(response) {
						$('#iworks-aqualog-dashboard-message').fadeOut();
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Handle AJAX dismissal of dashboard message.
	 *
	 * Processes the AJAX request to dismiss the admin dashboard message
	 * and stores the user's preference in user meta.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function ajax_dismiss_dashboard_message() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'iworks_aqualog_dismiss_message' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check user capabilities
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'Insufficient permissions' );
		}

		// Store dismissal preference
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'iworks_aqualog_dashboard_message_dismissed', true );

		wp_die( 'Message dismissed' );
	}

	/**
	 * Enqueue dashboard styles.
	 *
	 * Loads the CSS styles for the dashboard page.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	public function enqueue_assets() {
		$name = $this->dir . '-admin';
		wp_enqueue_style( $name );
		wp_enqueue_script( $name );
	}

	/**
	 * Get base64 encoded SVG icon.
	 *
	 * Reads the SVG icon file and returns it as a base64 encoded data URI.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return string Base64 encoded SVG data URI.
	 */
	private function get_base64_svg_icon() {
		$icon_file = $this->plugin_file_dir . '/assets/images/icon.svg';
		if ( ! file_exists( $icon_file ) ) {
			// Fallback to dashicon if SVG file doesn't exist
			return 'dashicons-button';
		}

		$svg_content = file_get_contents( $icon_file );
		if ( false === $svg_content ) {
			// Fallback to dashicon if file cannot be read
			return 'dashicons-button';
		}

		$base64 = base64_encode( $svg_content );
		return 'data:image/svg+xml;base64,' . $base64;
	}

	/**
	 * Register admin menu.
	 *
	 * Creates the main AquaLog admin menu and submenu items.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function register_admin_menu() {
		// Main menu item
		$slug = add_menu_page(
			__( 'AquaLog Dashboard', 'aqualog' ),
			__( 'AquaLog', 'aqualog' ),
			$this->capability,
			$this->wp_admin_slug,
			array( $this, 'render_dashboard_page' ),
			$this->get_base64_svg_icon(),
			25
		);
		add_action( 'load-' . $slug, array( $this, 'enqueue_assets' ) );

		// Dashboard submenu (same as main menu)
		$slug = add_submenu_page(
			$this->wp_admin_slug,
			__( 'Dashboard', 'aqualog' ),
			__( 'Dashboard', 'aqualog' ),
			$this->capability,
			$this->wp_admin_slug,
			array( $this, 'render_dashboard_page' )
		);
		add_action( 'load-' . $slug, array( $this, 'enqueue_assets' ) );

		// Aquariums submenu
		$slug = add_submenu_page(
			$this->wp_admin_slug,
			__( 'Aquariums', 'aqualog' ),
			__( 'Aquariums', 'aqualog' ),
			$this->capability,
			'edit.php?post_type=iw_aquarium'
		);
		add_action( 'load-' . $slug, array( $this, 'enqueue_assets' ) );

		// Help submenu
		$slug = add_submenu_page(
			$this->wp_admin_slug,
			__( 'Help & Support', 'aqualog' ),
			__( 'Help', 'aqualog' ),
			$this->capability,
			'aqualog-help',
			array( $this, 'render_help_page' )
		);
		add_action( 'load-' . $slug, array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render dashboard page.
	 *
	 * Displays the main AquaLog dashboard with statistics and overview.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function render_dashboard_page() {
		?>
		<div class="wrap">
			<?php $this->html_title( __( 'AquaLog Dashboard', 'aqualog' ) ); ?>
			
			<div class="aqualog-dashboard-grid">
				<!-- Statistics Cards -->
				<div class="aqualog-stats-grid">
					<?php $this->render_statistic_card( 'aquariums', __( 'Total Aquariums', 'aqualog' ), 'dashicons-water' ); ?>
					<?php $this->render_statistic_card( 'water-entries', __( 'Water Entries', 'aqualog' ), 'dashicons-chart-line' ); ?>
					<?php $this->render_statistic_card( 'ph-readings', __( 'pH Readings', 'aqualog' ), 'dashicons-clipboard' ); ?>
					<?php $this->render_statistic_card( 'maintenance', __( 'Maintenance Tasks', 'aqualog' ), 'dashicons-hammer' ); ?>
				</div>

				<!-- Recent Activity -->
				<div class="aqualog-activity-section">
					<div class="aqualog-card">
						<h2><?php esc_html_e( 'Recent Activity', 'aqualog' ); ?></h2>
						<div class="aqualog-activity-list">
							<?php $this->render_recent_activity(); ?>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="aqualog-quick-actions-section">
					<div class="aqualog-card">
						<h2><?php esc_html_e( 'Quick Actions', 'aqualog' ); ?></h2>
						<div class="aqualog-actions-grid">
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
								<span class="dashicons dashicons-plus-alt"></span>
								<span><?php esc_html_e( 'Add Aquarium', 'aqualog' ); ?></span>
							</a>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
								<span class="dashicons dashicons-list-view"></span>
								<span><?php esc_html_e( 'View All', 'aqualog' ); ?></span>
							</a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=aqualog-settings' ) ); ?>" class="aqualog-action-card">
								<span class="dashicons dashicons-admin-settings"></span>
								<span><?php esc_html_e( 'Settings', 'aqualog' ); ?></span>
							</a>
							<a href="<?php echo esc_url( 'https://wordpress.org/plugins/aqualog/' ); ?>" target="_blank" class="aqualog-action-card">
								<span class="dashicons dashicons-external"></span>
								<span><?php esc_html_e( 'Documentation', 'aqualog' ); ?></span>
							</a>
						</div>
					</div>
				</div>

				<!-- Water Quality Overview -->
				<div class="aqualog-water-quality-section">
					<div class="aqualog-card">
						<h2><?php esc_html_e( 'Water Quality Overview', 'aqualog' ); ?></h2>
						<div class="aqualog-water-stats">
							<?php $this->render_water_quality_stats(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render statistic card.
	 *
	 * Displays a single statistics card on the dashboard.
	*
	 * @since 1.0.0
	 * @access private
	 * @param string $type    The statistic type.
	 * @param string $label   The statistic label.
	 * @param string $icon    The dashicon class.
	 * @return void
	 */
	private function render_statistic_card( $type, $label, $icon ) {
		$count = $this->get_statistic_count( $type );
		?>
		<div class="aqualog-stat-card">
			<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
			<div class="aqualog-stat-number"><?php echo esc_html( $count ); ?></div>
			<div class="aqualog-stat-label"><?php echo esc_html( $label ); ?></div>
		</div>
		<?php
	}

	/**
	 * Get statistic count.
	 *
	 * Retrieves the count for a specific statistic type.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $type The statistic type.
	 * @return int The count value.
	 */
	private function get_statistic_count( $type ) {
		switch ( $type ) {
			case 'aquariums':
				return wp_count_posts( 'iw_aquarium' )->publish;
			case 'water-entries':
				// This would be implemented when water entries are created
				return 0;
			case 'ph-readings':
				// This would be implemented when pH readings are tracked
				return 0;
			case 'maintenance':
				// This would be implemented when maintenance tasks are added
				return 0;
			default:
				return 0;
		}
	}

	/**
	 * Render recent activity.
	 *
	 * Displays recent activity items on the dashboard.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function render_recent_activity() {
		// Get recent aquarium posts
		$recent_posts = get_posts( array(
			'post_type'      => 'iw_aquarium',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( empty( $recent_posts ) ) {
			echo '<p>' . esc_html__( 'No recent activity found.', 'aqualog' ) . '</p>';
			return;
		}

		foreach ( $recent_posts as $post ) {
			?>
			<div class="aqualog-activity-item">
				<strong><?php echo esc_html( get_the_title( $post->ID ) ); ?></strong>
				<br>
				<span class="aqualog-activity-meta">
					<?php 
					printf(
						esc_html__( 'Created on %s', 'aqualog' ),
						get_the_date( get_option( 'date_format' ), $post->ID )
					);
					?>
				</span>
			</div>
			<?php
		}
	}

	/**
	 * Render water quality stats.
	 *
	 * Displays water quality statistics on the dashboard.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function render_water_quality_stats() {
		// Get average values from all aquariums
		$aquariums = get_posts( array(
			'post_type'      => 'iw_aquarium',
			'posts_per_page' => -1,
		) );

		if ( empty( $aquariums ) ) {
			echo '<p>' . esc_html__( 'No aquarium data available.', 'aqualog' ) . '</p>';
			return;
		}

		$total_temp = 0;
		$total_ph = 0;
		$count = 0;

		foreach ( $aquariums as $aquarium ) {
			$temp = get_post_meta( $aquarium->ID, '_iw_temperature', true );
			$ph   = get_post_meta( $aquarium->ID, '_iw_ph_level', true );

			if ( $temp && $ph ) {
				$total_temp += floatval( $temp );
				$total_ph   += floatval( $ph );
				$count++;
			}
		}

		if ( $count > 0 ) {
			$avg_temp = $total_temp / $count;
			$avg_ph   = $total_ph / $count;

			?>
			<div class="aqualog-water-stat">
				<div class="aqualog-water-stat-value aqualog-info"><?php echo esc_html( number_format( $avg_temp, 1 ) ); ?>°C</div>
				<div class="aqualog-water-stat-label"><?php esc_html_e( 'Avg Temperature', 'aqualog' ); ?></div>
			</div>
			<div class="aqualog-water-stat">
				<div class="aqualog-water-stat-value <?php echo ( $avg_ph >= 6.5 && $avg_ph <= 7.5 ) ? 'aqualog-good' : 'aqualog-warning'; ?>">
					<?php echo esc_html( number_format( $avg_ph, 1 ) ); ?>
				</div>
				<div class="aqualog-water-stat-label"><?php esc_html_e( 'Avg pH Level', 'aqualog' ); ?></div>
			</div>
			<?php
		} else {
			echo '<p>' . esc_html__( 'No water quality data available.', 'aqualog' ) . '</p>';
		}
	}

	/**
	 * Render settings page.
	 *
	 * Displays the AquaLog settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<?php $this->html_title( __( 'AquaLog Settings', 'aqualog' ) ); ?>
			<div class="aqualog-card">
				<p><?php esc_html_e( 'Settings page content will be implemented here.', 'aqualog' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render help page.
	 *
	 * Displays the AquaLog help and support page.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function render_help_page() {
		?>
		<div class="wrap">
			<?php $this->html_title( __( 'Help & Support', 'aqualog' ) ); ?>
			<div class="aqualog-card">
				<h3><?php esc_html_e( 'Getting Started', 'aqualog' ); ?></h3>
				<p><?php esc_html_e( 'Welcome to AquaLog! Here are some resources to help you get started:', 'aqualog' ); ?></p>
				<ul>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/plugins/aqualog/' ); ?>" target="_blank"><?php esc_html_e( 'Plugin Documentation', 'aqualog' ); ?></a></li>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/aqualog/' ); ?>" target="_blank"><?php esc_html_e( 'Support Forum', 'aqualog' ); ?></li>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/aqualog/reviews/' ); ?>" target="_blank"><?php esc_html_e( 'Leave a Review', 'aqualog' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
