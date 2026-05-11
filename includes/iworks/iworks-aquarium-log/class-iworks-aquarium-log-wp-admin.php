<?php
/**
 * iWorks Aquarium Log Admin Class
 *
 * Handles all WordPress admin-specific functionality for the iWorks Aquarium Log plugin.
 * This includes settings management, asset registration, menu creation,
 * and admin interface rendering.
 *
 * @package    iWorks\iWorks Aquarium Log
 * @subpackage Admin
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_aquarium_log_wp_admin' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/class-aquarium-log-base.php';

/**
 * Admin functionality for iWorks Aquarium Log.
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
class iworks_aquarium_log_wp_admin extends iworks_aquarium_log_base {

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
	 * @see   iworks_aquarium_log_base::__construct()
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
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'init', array( $this, 'action_init' ) );
		/**
		 * Register iWorks Options hooks.
		 *
		 * @since 1.0.0
		 */
		add_filter( 'iworks-aquarium-log/etc/config/metaboxes', array( $this, 'filter_iworks_options_add_meta_boxes' ) );
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
			 * Only processes files that match the pattern 'class-iworks-aquarium-log-wp-admin-{name}.php'.
			 *
			 * @since 1.0.0
			 */
			if ( ! preg_match( '/^class-iworks-aquarium-log-wp-admin-([a-z]+).php$/', $filename, $matches ) ) {
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
			$class_name = sprintf( 'iworks_aquarium_log_wp_admin_%s', $admin_name );

			/**
			 * Initialize admin class instance.
			 *
			 * @since 1.0.0
			 */
			do_action( 'iworks-aquarium-log/register_objects', $admin_name, 'wp-admin', new $class_name() );
		}
		/**
		 * Register additional plugin hooks.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks-aquarium-log/wp-admin/current-aquarium-bar', array( $this, 'current_aquarium_bar' ) );
		add_filter( 'iworks-aquarium-log/wp-admin/messages/files', array( $this, 'read_messages_files' ) );
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
			'title'    => /* translators: Meta box title for assistance section */ esc_html__( 'Have a question or need help?', 'iworks-aquarium-log' ),
			'callback' => array( $this, 'need_assistance' ),
			'context'  => 'side',
			'priority' => 'core',
		);
		$metaboxes['love']       = array(
			'title'    => /* translators: Meta box title for plugin appreciation section */ esc_html__( 'Enjoying this plugin?', 'iworks-aquarium-log' ),
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
		$content = apply_filters( 'iworks_rate_love', '', 'iworks-aquarium-log' );
		if ( ! empty( $content ) ) {
			echo wp_kses_post( $content );
			return;
		}
		?>
<p><?php esc_html_e( 'Help others discover it—share the link with your friends and community!', 'iworks-aquarium-log' ); ?></p>
<ul>
	<li><a target="_blank" href="<?php echo esc_url( _x( 'https://wordpress.org/support/plugin/iworks-aquarium-log/reviews/#new-post', 'link to add new review page on WordPress.org', 'iworks-aquarium-log' ) ); ?>"><?php esc_html_e( 'Give it a five stars on WordPress.org', 'iworks-aquarium-log' ); ?></a></li>
	<li><a target="_blank" href="<?php echo esc_url( _x( 'https://wordpress.org/plugins/iworks-aquarium-log/', 'plugin home page on WordPress.org', 'iworks-aquarium-log' ) ); ?>"><?php esc_html_e( 'Link to it so others can easily find it', 'iworks-aquarium-log' ); ?></a></li>
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
		$content = apply_filters( 'iworks_rate_assistance', '', 'iworks-aquarium-log' );
		if ( ! empty( $content ) ) {
			echo wp_kses_post( $content );
			return;
		}

		?>
<p><?php esc_html_e( 'We’re here for you! Send us a message and we’ll get back to you as soon as possible.', 'iworks-aquarium-log' ); ?></p>
<ul>
	<li><a target="_blank" href="<?php echo esc_url( _x( 'https://wordpress.org/support/plugin/iworks-aquarium-log/', 'link to support forum on WordPress.org', 'iworks-aquarium-log' ) ); ?>"><?php esc_html_e( 'WordPress Help Forum', 'iworks-aquarium-log' ); ?></a></li>
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
	 * @see    iworks_aquarium_log_base::check_option_object()
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
		$file = 'assets/scripts/iworks-aquarium-log-admin' . $this->dev . '.js';
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
		$file = 'assets/styles/iworks-aquarium-log-admin' . $this->dev . '.css';
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
			'iworks-aquarium-log/wp-admin/wp_localize_script',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonces'      => array(),
				'i18n'        => array(
					'messages' => array(
						'loading'       => /* translators: Loading message */ esc_html__( 'Loading…', 'iworks-aquarium-log' ),
						'saving'        => /* translators: Saving message */ esc_html__( 'Saving…', 'iworks-aquarium-log' ),
						'saveError'     => /* translators: Error message when saving fails */ esc_html__( 'An error occurred while saving. Please try again.', 'iworks-aquarium-log' ),
						'invalidValues' => /* translators: Validation error message */ esc_html__( 'Please correct the highlighted fields and try again.', 'iworks-aquarium-log' ),
					),
				),
				'chemistry'   => array(),
				'maintenance' => array(),
			)
		);
		wp_localize_script( $name, 'iworks_aquarium_log', $data );
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
		if ( $this->dir . '/iworks-aquarium-log.php' == $file ) {
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
						esc_html__( 'Settings', 'iworks-aquarium-log' )
				);
			}
			/* start:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'iworks-aquarium-log',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://ko-fi.com/iworks'
					)
				),
				/* translators: Donate menu item */
						esc_html__( 'Donate', 'iworks-aquarium-log' )
			);
			/* end:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'iworks-aquarium-log',
							'utm_medium' => 'plugin-row-github-link',
						),
						'https://github.com/iworks.pl/iworks-aquarium-log'
					)
				),
				/* translators: GitHub menu item */
						esc_html__( 'GitHub', 'iworks-aquarium-log' )
			);
		}
		return $links;
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
	 * Creates the main iWorks Aquarium Log admin menu and submenu items.
	 * Includes Dashboard, Chemistry, Maintenance, Notes, Aquariums, and Help pages.
	 * Uses filter to control which submenu items are loaded.
	 *
	 * @since 1.0.0
	 * @action admin_menu
	 * @return  void
	 */
	public function register_admin_menu() {
		$this->check_option_object();
		// Main menu item
		$slug = add_menu_page(
			/* translators: Main menu page title */
			__( 'iWorks Aquarium Log Dashboard', 'iworks-aquarium-log' ),
			/* translators: Main menu item title */
			__( 'iWorks Aquarium Log', 'iworks-aquarium-log' ),
			$this->capability,
			$this->wp_admin_slug,
			array( $this, 'render_dashboard_page' ),
			$this->get_base64_svg_icon(),
			$this->options->get_option( 'menu_position' )
		);
		add_action( 'load-' . $slug, array( $this, 'admin_enqueue_assets' ) );

		$submenus = array(
			array(
				/* translators: Dashboard submenu title */
				'title'             => esc_html__( 'Dashboard', 'iworks-aquarium-log' ),
				'slug'              => $this->wp_admin_slug,
				'callback'          => array( $this, 'render_dashboard_page' ),
				'module_load_check' => 'skip',
			),
			array(
				/* translators: Chemistry submenu title */
				'title'             => esc_html__( 'Dosings', 'iworks-aquarium-log' ),
				'slug'              => 'aquarium-log-dosings',
				'callback'          => array( $this, 'render_dosings_page' ),
				'module_load_check' => 'check',
			),
			array(
				/* translators: Chemistry submenu title */
				'title'             => esc_html__( 'Chemistry', 'iworks-aquarium-log' ),
				'slug'              => 'aquarium-log-chemistry',
				'callback'          => array( $this, 'render_chemistry_page' ),
				'module_load_check' => 'check',
			),
			array(
				/* translators: Maintenance submenu title */
				'title'             => esc_html__( 'Maintenance', 'iworks-aquarium-log' ),
				'slug'              => 'aquarium-log-maintenance',
				'callback'          => array( $this, 'render_maintenance_page' ),
				'module_load_check' => 'check',
			),
			array(
				/* translators: Notes submenu title */
				'title'             => esc_html__( 'Notes', 'iworks-aquarium-log' ),
				'slug'              => 'edit.php?post_type=iw_note',
				'callback'          => null,
				'module_load_check' => 'check',
			),
			array(
				/* translators: Equipment submenu title */
				'title'             => esc_html__( 'Equipment', 'iworks-aquarium-log' ),
				'slug'              => 'edit.php?post_type=iw_equipment',
				'callback'          => null,
				'module_load_check' => 'check',
			),
			array(
				/* translators: Plants submenu title */
				'title'             => esc_html__( 'Plants', 'iworks-aquarium-log' ),
				'slug'              => 'edit.php?post_type=iw_plant',
				'callback'          => null,
				'module_load_check' => 'check',
			),
			array(
				/* translators: Aquariums submenu title */
				'title'             => esc_html__( 'Aquariums', 'iworks-aquarium-log' ),
				'slug'              => 'edit.php?post_type=iw_aquarium',
				'callback'          => null,
				'module_load_check' => 'skip',
			),
			array(
				/* translators: Help submenu title */
				'title'             => esc_html__( 'Help', 'iworks-aquarium-log' ),
				'slug'              => 'aquarium-log-help',
				'callback'          => array( $this, 'render_help_page' ),
				'module_load_check' => 'skip',
			),
		);
		foreach ( $submenus as $submenu ) {
			if ( 'check' === $submenu['module_load_check'] ) {
				$module = str_replace( 'aquarium-log-', '', $submenu['slug'] );
				if ( ! $this->is_module_enabled( $module ) ) {
					continue;
				}
				add_filter( 'iworks-aquarium-log/load/module/' . $module, '__return_true' );
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
	 * Displays the main iWorks Aquarium Log dashboard with statistics and overview.
	 * Loads dashboard template if available.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public function render_dashboard_page() {
		do_action( 'iworks-aquarium-log/wp-admin/page/dashboard' );
	}

	/**
	 * Render help page.
	 *
	 * Displays the iWorks Aquarium Log help and support page with documentation links.
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public function render_help_page() {
		?>
		<div class="wrap">
			<?php
			/* translators: Help & Support page title */
			$this->html_title( esc_html__( 'Help & Support', 'iworks-aquarium-log' ) );
			?>
			<div class="aquarium-log-card">
				<h3><?php esc_html_e( 'Getting Started', 'iworks-aquarium-log' ); ?></h3>
				<p><?php esc_html_e( 'Welcome to iWorks Aquarium Log! Here are some resources to help you get started:', 'iworks-aquarium-log' ); ?></p>
				<ul>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/plugins/iworks-aquarium-log/' ); ?>" target="_blank"><?php esc_html_e( 'Plugin Documentation', 'iworks-aquarium-log' ); ?></a></li>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/iworks-aquarium-log/' ); ?>" target="_blank"><?php esc_html_e( 'Support Forum', 'iworks-aquarium-log' ); ?></a></li>
					<li><a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/iworks-aquarium-log/reviews/' ); ?>" target="_blank"><?php esc_html_e( 'Leave a Review', 'iworks-aquarium-log' ); ?></a></li>
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
	 * @action iworks-aquarium-log/wp-admin/chemistry_page
	 * @return  void
	 */
	public function render_chemistry_page() {
		do_action( 'iworks-aquarium-log/wp-admin/chemistry_page' );
	}

	/**
	 * Render maintenance page.
	 *
	 * Triggers the maintenance page action to allow other components
	 * to render the maintenance interface.
	 *
	 * @since 1.0.0
	 * @action iworks-aquarium-log/wp-admin/maintenance_page
	 * @return  void
	 */
	public function render_maintenance_page() {
		do_action( 'iworks-aquarium-log/wp-admin/maintenance_page' );
	}

	/**
	 * Display current aquarium bar.
	 *
	 * Outputs the current aquarium selection bar in the admin interface.
	 *
	 * @since 1.0.0
	 * @action iworks-aquarium-log/wp-admin/current-aquarium-bar
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
		$title   = /* translators: Default text when no aquarium is selected */ esc_html__( 'no aquarium selected.', 'iworks-aquarium-log' );
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
		$content .= '<div class="aquarium-log-current-aquarium">';
		$content .= '<span class="aquarium-log-current-aquarium-label">';
		$content .= /* translators: Label for current aquarium display */ esc_html__( 'Current Aquarium:', 'iworks-aquarium-log' );
		$content .= ' ';
		$content .= $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$content .= '</span>';
		$content .= ' ';
		if ( ! $this->current_aquarium_id && 1 < $count ) {
			$content .= '<span class="aquarium-log-current-aquarium-change">';
			$content .= '<a href="' . esc_url( add_query_arg( 'change', 'aquarium' ) ) . '">';
			$content .= /* translators: Link text to change aquarium */ esc_html__( 'Change Aquarium', 'iworks-aquarium-log' );
			$content .= '</a>';
			$content .= '</span>';
		}
		$content .= '</div>';
		return $content;
	}
}
