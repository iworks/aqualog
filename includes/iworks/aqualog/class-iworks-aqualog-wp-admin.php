<?php
/**
 * AquaLog Admin Class
 *
 * Handles all WordPress admin-specific functionality for the AquaLog plugin.
 * This includes settings management, asset registration, menu creation,
 * and admin interface rendering.
 *
 * @package    iWorks\AquaLog
 * @subpackage Admin
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @since      1.0.0
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
 * - Admin assets registration and localization
 * - Plugin meta links and support information
 * - Admin interface rendering and menu management
 * - Dashboard message handling
 * - Template file management
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin extends iworks_aqualog_base {

	/**
	 * The capability required to access plugin admin features.
	 *
	 * Users must have this capability to access admin pages and settings.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private string $capability = 'manage_options';

	/**
	 * User meta key for dashboard message dismissal.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private string $user_meta_dashboard_message_dismissed_name = 'dashboard_message_dismissed';

	/**
	 * Initialize admin class.
	 *
	 * Sets up required capability and registers WordPress hooks for admin functionality.
	 * Also loads admin classes dynamically from the wp-admin directory.
	 *
	 * @since 1.0.0
	 * @see   iworks_aqualog_base::__construct()
	 * @uses  load_admin_classes()
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Register WordPress hooks for admin functionality.
		 *
		 * @since 1.0.0
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts_register_assets' ), 0 );
		add_action( 'wp_loaded', array( $this, 'action_wp_loaded_init_options' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'wp_ajax_iworks_aqualog_dismiss_message', array( $this, 'ajax_dismiss_dashboard_message' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'init', array( $this, 'action_init' ) );
		/**
		 * Register iWorks Options hooks.
		 *
		 * @since 1.0.0
		 */
		add_filter( 'aqualog/etc/config/metaboxes', array( $this, 'filter_iworks_options_add_meta_boxes' ) );
		/**
		 * Load admin classes from wp-admin directory.
		 *
		 * Dynamically loads all admin class files that match the expected pattern.
		 *
		 * @since 1.0.0
		 */
		$admin_classes_dir = $this->includes_directory . '/wp-admin/';
		/**
		 * Iterate through all PHP files in wp-admin directory.
		 *
		 * @since 1.0.0
		 */
		foreach ( glob( $admin_classes_dir . 'class*.php' ) as $filename_with_path ) {
			/**
			 * Extract base filename from full path.
			 *
			 * @since 1.0.0
			 */
			$filename = basename( $filename_with_path );
			/**
			 * Validate filename format using regex pattern.
			 *
			 * Only processes files that match the pattern 'class-iworks-aqualog-wp-admin-{name}.php'.
			 *
			 * @since 1.0.0
			 */
			if ( ! preg_match( '/^class-iworks-aqualog-wp-admin-([a-z]+).php$/', $filename, $matches ) ) {
				continue;
			}
			/**
			 * Extract admin class name from filename.
			 *
			 * @since 1.0.0
			 */
			$admin_name = $matches[1];
			/**
			 * Include the admin class file.
			 *
			 * @since 1.0.0
			 */
			include_once $admin_classes_dir . $filename;

			/**
			 * Generate fully qualified class name.
			 *
			 * @since 1.0.0
			 */
			$class_name = sprintf( 'iworks_aqualog_wp_admin_%s', $admin_name );

			/**
			 * Initialize admin class instance.
			 *
			 * @since 1.0.0
			 */
			do_action( 'aqualog/register_objects', $admin_name, 'wp-admin', new $class_name() );
		}
		/**
		 * Register additional plugin hooks.
		 *
		 * @since 1.0.0
		 */
		add_action( 'aqualog/wp-admin/current-aquarium-bar', array( $this, 'current_aquarium_bar' ) );
		add_filter( 'aqualog/wp-admin/messages/files', array( $this, 'read_messages_files' ) );
	}

	public function read_messages_files( $messages ) {
		$files = glob( $this->plugin_file_dir . '/assets/templates/messages/*.php' );
		foreach ( $files as $file ) {
			if ( 'index.php' === basename( $file ) ) {
				continue;
			}
			$file_slug              = basename( $file, '.php' );
			$messages[ $file_slug ] = $this->get_template_file( $file_slug, 'messages' );
		}
		return $messages;
	}

	public function action_init() {
		$this->check_option_object();
		/**
		 * settings
		 */
		$this->user_meta_dashboard_message_dismissed_name = $this->options->get_option_name( 'dashboard_message_dismissed' );
		$this->set_current_aquarium_id();
	}

	/**
	 * Add meta boxes to iWorks Options page.
	 *
	 * Adds assistance and plugin appreciation meta boxes to the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $metaboxes Existing meta boxes array.
	 * @return array Modified meta boxes array with new boxes added.
	 */
	public function filter_iworks_options_add_meta_boxes( $metaboxes ) {
		$metaboxes['assistance'] = array(
			'title'    => /* translators: Meta box title for assistance section */ __( 'Have a question or need help?', 'aqualog' ),
			'callback' => array( $this, 'need_assistance' ),
			'context'  => 'side',
			'priority' => 'core',
		);
		$metaboxes['love']       = array(
			'title'    => /* translators: Meta box title for plugin appreciation section */ __( 'Enjoying this plugin?', 'aqualog' ),
			'callback' => array( $this, 'loved_this_plugin' ),
			'context'  => 'side',
			'priority' => 'core',
		);
		return $metaboxes;
	}
	/**
	 * Display plugin appreciation links.
	 *
	 * Outputs HTML for "Love this plugin" section, including links to rate
	 * the plugin and share it with others. Uses filter to allow custom content.
	 *
	 * @since 1.0.0
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
	 * Outputs HTML for "Need Assistance" section, including support links.
	 * Uses filter to allow custom content.
	 *
	 * @since 1.0.0
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
	 * This method is called when WordPress is fully loaded. It performs:
	 * 1. Ensures the options object is properly initialized
	 * 2. Initializes plugin options through the options object
	 *
	 * @since 1.0.0
	 * @action wp_loaded
	 * @see    iworks_aqualog_base::check_option_object()
	 * @see    iworks_options::options_init()
	 * @return   void
	 */
	public function action_wp_loaded_init_options() {
		$this->check_option_object();
		$this->options->options_init();
	}

	/**
	 * Register admin assets.
	 *
	 * Registers required JavaScript and CSS files for the admin interface.
	 * Includes Select2, jQuery UI Slider, and main admin script.
	 * Localizes script data for AJAX and internationalization.
	 *
	 * @since 1.1.0
	 * @action admin_enqueue_scripts
	 * @return   void
	 */
	public function action_admin_enqueue_scripts_register_assets() {
		//
		$file = 'includes/iworks/options/assets/scripts/select2.min.js';
		wp_register_script(
			'select2',
			plugins_url( $file, $this->plugin_file_path ),
			array( 'jquery' ),
			md5( file_get_contents( plugin_dir_path( $this->plugin_file_path ) . $file ) ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		// Register admin script
		$name = $this->dir . '-admin';
		$file = 'assets/scripts/aqualog-admin' . $this->dev . '.js';
		wp_register_script(
			$name,
			plugins_url( $file, $this->plugin_file_path ),
			array( 'jquery', 'select2', 'wp-util', 'jquery-ui-slider' ),
			md5( file_get_contents( plugin_dir_path( $this->plugin_file_path ) . $file ) ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		$file = 'assets/styles/aqualog-admin' . $this->dev . '.css';
		wp_register_style(
			$name,
			plugins_url( $file, $this->plugin_file_path ),
			array(),
			md5( file_get_contents( plugin_dir_path( $this->plugin_file_path ) . $file ) )
		);
		/**
		 * Add data to localize script.
		 *
		 * Prepares data for JavaScript localization including AJAX URLs,
		 * nonces, and internationalized messages.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters(
			'aqualog/wp-admin/wp_localize_script',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonces'      => array(
					'dismiss_message' => wp_create_nonce( 'iworks_aqualog_dismiss_message' ),
				),
				'i18n'        => array(
					'messages' => array(
						'loading'       => /* translators: Loading message */ __( 'Loading…', 'aqualog' ),
						'saving'        => /* translators: Saving message */ __( 'Saving…', 'aqualog' ),
						'saveError'     => /* translators: Error message when saving fails */ __( 'An error occurred while saving. Please try again.', 'aqualog' ),
						'invalidValues' => /* translators: Validation error message */ __( 'Please correct the highlighted fields and try again.', 'aqualog' ),
					),
				),
				'chemistry'   => array(),
				'maintenance' => array(),
			)
		);
		wp_localize_script( $name, 'aqualog', $data );
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
	 * @since 1.0.0
	 * @filter plugin_row_meta
	 *
	 * @param string[] $links An array of the plugin's metadata, including version, author,
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
					/* translators: Settings menu item */
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
				/* translators: Donate menu item */
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
				/* translators: GitHub menu item */
						esc_html__( 'GitHub', 'aqualog' )
			);
		}
		return $links;
	}

	/**
	 * Display admin dashboard message.
	 *
	 * Shows a dismissible admin notice with welcome content. The message
	 * will only be displayed if the user hasn't dismissed it previously.
	 * Includes AJAX handler for dismissal functionality.
	 *
	 * @since 1.0.0
	 * @action admin_notices
	 * @return  void
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
		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, $this->user_meta_dashboard_message_dismissed_name, true );

		if ( $dismissed ) {
			return;
		}
		$this->load_template( 'messages/dashboard-welcome', 'templates' );
	}

	/**
	 * Handle AJAX dismissal of dashboard message.
	 *
	 * Processes the AJAX request to dismiss the admin dashboard message
	 * and stores the user's preference in user meta.
	 * Includes nonce verification and capability checks.
	 *
	 * @since 1.0.0
	 * @action wp_ajax_iworks_aqualog_dismiss_message
	 * @return  void
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
		update_user_meta( $user_id, $this->user_meta_dashboard_message_dismissed_name, true );

		wp_die( 'Message dismissed' );
	}

	/**
	 * Get base64 encoded SVG icon.
	 *
	 * Reads the SVG icon file and returns it as a base64 encoded data URI.
	 * Falls back to dashicon if SVG file doesn't exist or can't be read.
	 *
	 * @since 1.0.0
	 * @return string Base64 encoded SVG data URI or dashicon class name.
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
	 * Includes Dashboard, Chemistry, Maintenance, Notes, Aquariums, and Help pages.
	 * Uses filter to control which submenu items are loaded.
	 *
	 * @since 1.0.0
	 * @action admin_menu
	 * @return  void
	 */
	public function register_admin_menu() {
		// Main menu item
		$slug = add_menu_page(
			/* translators: Main menu page title */
			__( 'AquaLog Dashboard', 'aqualog' ),
			/* translators: Main menu item title */
			__( 'AquaLog', 'aqualog' ),
			$this->capability,
			$this->wp_admin_slug,
			array( $this, 'render_dashboard_page' ),
			$this->get_base64_svg_icon(),
			25
		);
		add_action( 'load-' . $slug, array( $this, 'admin_enqueue_assets' ) );

		$submenus = array(
			array(
				/* translators: Dashboard submenu title */
				'title'             => __( 'Dashboard', 'aqualog' ),
				'slug'              => $this->wp_admin_slug,
				'callback'          => array( $this, 'render_dashboard_page' ),
				'module_load_check' => 'skip',
			),
			array(
				/* translators: Chemistry submenu title */
				'title'             => __( 'Dosings', 'aqualog' ),
				'slug'              => 'aqualog-dosings',
				'callback'          => array( $this, 'render_dosings_page' ),
				'module_load_check' => 'check',
			),
			array(
				/* translators: Chemistry submenu title */
				'title'             => __( 'Chemistry', 'aqualog' ),
				'slug'              => 'aqualog-chemistry',
				'callback'          => array( $this, 'render_chemistry_page' ),
				'module_load_check' => 'check',
			),
			array(
				/* translators: Maintenance submenu title */
				'title'             => __( 'Maintenance', 'aqualog' ),
				'slug'              => 'aqualog-maintenance',
				'callback'          => array( $this, 'render_maintenance_page' ),
				'module_load_check' => 'check',
			),
			array(
				/* translators: Notes submenu title */
				'title'             => __( 'Notes', 'aqualog' ),
				'slug'              => 'edit.php?post_type=iw_note',
				'callback'          => null,
				'module_load_check' => 'check',
			),
			array(
				/* translators: Equipment submenu title */
				'title'             => __( 'Equipment', 'aqualog' ),
				'slug'              => 'edit.php?post_type=iw_equipment',
				'callback'          => null,
				'module_load_check' => 'check',
			),
			array(
				/* translators: Plants submenu title */
				'title'             => __( 'Plants', 'aqualog' ),
				'slug'              => 'edit.php?post_type=iw_plant',
				'callback'          => null,
				'module_load_check' => 'check',
			),
			array(
				/* translators: Aquariums submenu title */
				'title'             => __( 'Aquariums', 'aqualog' ),
				'slug'              => 'edit.php?post_type=iw_aquarium',
				'callback'          => null,
				'module_load_check' => 'skip',
			),
			array(
				/* translators: Help submenu title */
				'title'             => __( 'Help', 'aqualog' ),
				'slug'              => 'aqualog-help',
				'callback'          => array( $this, 'render_help_page' ),
				'module_load_check' => 'skip',
			),
		);
		foreach ( $submenus as $submenu ) {
			if ( 'check' === $submenu['module_load_check'] ) {
				$module = str_replace( 'aqualog-', '', $submenu['slug'] );
				if ( ! $this->is_module_enabled( $module ) ) {
					continue;
				}
				add_filter( 'aqualog/load/module/' . $module, '__return_true' );
			}
			$slug = add_submenu_page(
				$this->wp_admin_slug,
				$submenu['title'],
				$submenu['title'],
				$this->capability,
				$submenu['slug'],
				$submenu['callback']
			);
			add_action( 'load-' . $slug, array( $this, 'admin_enqueue_assets' ) );
		}
	}

	/**
	 * Render dashboard page.
	 *
	 * Displays the main AquaLog dashboard with statistics and overview.
	 * Loads dashboard template if available.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public function render_dashboard_page() {
		do_action( 'aqualog/wp-admin/page/dashboard' );
	}

	/**
	 * Render water quality stats.
	 *
	 * Displays water quality statistics on the dashboard.
	 * Calculates average temperature and pH values from all aquariums.
	 * Shows color-coded indicators for pH levels.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	private function render_water_quality_stats() {
		// Get average values from all aquariums
		$aquariums = get_posts(
			array(
				'post_type'      => 'iw_aquarium',
				'posts_per_page' => -1,
			)
		);

		if ( empty( $aquariums ) ) {
			/* translators: Message when no aquarium data is available */
			echo '<p>' . esc_html__( 'No aquarium data available.', 'aqualog' ) . '</p>';
			return;
		}

		$total_temp = 0;
		$total_ph   = 0;
		$count      = 0;

		foreach ( $aquariums as $aquarium ) {
			$temp = get_post_meta( $aquarium->ID, '_iw_temperature', true );
			$ph   = get_post_meta( $aquarium->ID, '_iw_ph_level', true );

			if ( $temp && $ph ) {
				$total_temp += floatval( $temp );
				$total_ph   += floatval( $ph );
				++$count;
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
			/* translators: Message when no water quality data is available */
			echo '<p>' . esc_html__( 'No water quality data available.', 'aqualog' ) . '</p>';
		}
	}

	/**
	 * Render help page.
	 *
	 * Displays the AquaLog help and support page with documentation links.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public function render_help_page() {
		?>
		<div class="wrap">
			<?php
			/* translators: Help & Support page title */
			$this->html_title( __( 'Help & Support', 'aqualog' ) );
			?>
			<div class="aqualog-card">
				<h3><?php esc_html_e( 'Getting Started', 'aqualog' ); ?></h3>
				<p><?php esc_html_e( 'Welcome to AquaLog! Here are some resources to help you get started:', 'aqualog' ); ?></p>
				<ul>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/plugins/aqualog/' ); ?>" target="_blank"><?php esc_html_e( 'Plugin Documentation', 'aqualog' ); ?></a></li>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/aqualog/' ); ?>" target="_blank"><?php esc_html_e( 'Support Forum', 'aqualog' ); ?></a></li>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/aqualog/reviews/' ); ?>" target="_blank"><?php esc_html_e( 'Leave a Review', 'aqualog' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Render chemistry page.
	 *
	 * Triggers the chemistry page action to allow other components
	 * to render the chemistry interface.
	 *
	 * @since 1.0.0
	 * @action aqualog/wp-admin/chemistry_page
	 * @return  void
	 */
	public function render_chemistry_page() {
		do_action( 'aqualog/wp-admin/chemistry_page' );
	}

	/**
	 * Render maintenance page.
	 *
	 * Triggers the maintenance page action to allow other components
	 * to render the maintenance interface.
	 *
	 * @since 1.0.0
	 * @action aqualog/wp-admin/maintenance_page
	 * @return  void
	 */
	public function render_maintenance_page() {
		do_action( 'aqualog/wp-admin/maintenance_page' );
	}

	/**
	 * Display current aquarium bar.
	 *
	 * Outputs the current aquarium selection bar in the admin interface.
	 *
	 * @since 1.0.0
	 * @action aqualog/wp-admin/current-aquarium-bar
	 * @return  void
	 */
	public function current_aquarium_bar() {
		echo $this->get_current_aquarium(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get current aquarium information.
	 *
	 * Retrieves and formats the current aquarium information for display.
	 * Sets the current aquarium ID and returns formatted HTML content.
	 *
	 * @since 1.0.0
	 * @return string Formatted HTML content for current aquarium bar.
	 */
	private function get_current_aquarium() {
		$this->set_current_aquarium_id();
		$count   = intval( wp_count_posts( 'iw_aquarium' )->publish );
		$content = '';
		$id      = 0;
		$title   = /* translators: Default text when no aquarium is selected */ esc_html__( 'no aquarium selected.', 'aqualog' );
		if ( empty( $this->current_aquarium_id ) ) {
		} else {
			$aquarium = get_post( $this->current_aquarium_id );
			if ( $aquarium ) {
				$title = sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_edit_post_link( $this->current_aquarium_id ) ),
					esc_html( $aquarium->post_title )
				);
			}
		}
		$content .= '<div class="aqualog-current-aquarium">';
		$content .= '<span class="aqualog-current-aquarium-label">';
		$content .= /* translators: Label for current aquarium display */ esc_html__( 'Current Aquarium:', 'aqualog' );
		$content .= ' ';
		$content .= $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$content .= '</span>';
		$content .= ' ';
		if ( $this->current_aquarium_id && 1 < $count ) {
			$content .= '<span class="aqualog-current-aquarium-change">';
			$content .= '<a href="' . esc_url( add_query_arg( 'change', 'aquarium' ) ) . '">';
			$content .= /* translators: Link text to change aquarium */ esc_html__( 'Change Aquarium', 'aqualog' );
			$content .= '</a>';
			$content .= '</span>';
		}
		$content .= '</div>';
		return $content;
	}
}
