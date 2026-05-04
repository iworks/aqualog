<?php
/**
 * Main plugin class file.
 *
 * @package WordPress_Plugin_Stub
 * @author Marcin Pietrzak <marcin@iworks.pl>
 * @copyright 2026-PLUGIN_TILL_YEAR Marcin Pietrzak
 * @license GPL-3.0-or-later
 * @link https://iworks.pl/
 *
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_aqualog' ) ) {
	return;
}

require_once __DIR__ . '/class-iworks-aqualog-base.php';

/**
 * Main plugin class.
 *
 * This class initializes the plugin and loads all necessary components.
 *
 * @since 1.0.0
 */
class iworks_aqualog extends iworks_aqualog_base {

	/**
	 * Plugin objects container.
	 *
	 * @since 1.0.0
	 * @var array $objects Array to store plugin objects.
	 */
	private array $objects = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the plugin by setting up hooks and loading required files.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->version = 'PLUGIN_VERSION';
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'action_init_settings' ) );
		/**
		 * Enable aquarium post type
		 */
		add_filter( 'aqualog/load/posttype/aquarium', '__return_true' );
		/**
		 * post types
		 */
		$filename = $this->includes_directory . '/class-iworks-aqualog-posttypes.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_aqualog_posttypes();
		}
		/**
		 * load github class
		 */
		$filename = $this->includes_directory . '/class-iworks-aqualog-github.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_aqualog_github();
		}
		/**
		 * admin
		 */
		if ( is_admin() ) {
			$filename = $this->includes_directory . '/class-iworks-aqualog-wp-admin.php';
			if ( is_file( $filename ) ) {
				include_once $filename;
				new iworks_aqualog_wp_admin();
			}
		}
		/**
		 * load db class
		 */
		$filename = $this->includes_directory . '/class-iworks-aqualog-db.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_aqualog_db();
		}
		/**
		 * register objects filter
		 */
		add_action( 'aqualog/register_objects', array( $this, 'register_objects' ),10, 3 );
		/**
		 * is active?
		 */
		add_filter( 'aqualog/is_active', '__return_true' );
	}

	/**
	 * Register plugin objects.
	 *
	 * @since 1.0.0
	 * @param array $objects The objects array.
	 * @param string $type The type of objects to register.
	 * @param string $name The name of the objects to register.
	 * @return array The modified objects array.
	 */
	public function register_objects( $name, $group, $object ) {
		if ( ! isset( $objects[ $group ] ) ) {
			$objects[ $group ] = array();
		}
		return $objects[ $group ][ $name ] = $object;
	}

	/**
	 * Initialize plugin settings and assets.
	*
	 * Handles the initialization of plugin settings and enqueues frontend assets.
	*
	 * @since 1.0.0
	 * @return void
	 */
	public function action_init_settings() {
		/**
		 * options
		 */
		if ( is_admin() ) {
		} else {
			$file = 'assets/styles/aqualog-frontend' . $this->dev . '.css';
			wp_enqueue_style( 'aqualog', plugins_url( $file, $this->base ), array(), $this->get_version( $file ) );
		}
	}

	/**
	 * Plugin activation hook.
	 *
	 * Handles database installation and option initialization
	 * when the plugin is activated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_activation_hook() {
		$this->db_install();
		$this->check_option_object();
		$this->options->activate();
		do_action( 'iworks/aqualog/register_activation_hook' );
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * Handles cleanup tasks when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_deactivation_hook() {
		$this->check_option_object();
		$this->options->deactivate();
		do_action( 'iworks/aqualog/register_deactivation_hook' );
	}

	/**
	 * Database installation method.
	 *
	 * Handles the creation of required database tables.
	 * Currently empty as it's a stub implementation.
	 *
	 * @since 1.0.0
	 * @return void
	 * @todo Implement database table creation if needed.
	 */
	private function db_install() {
	}
}
