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

if ( class_exists( 'iworks_aquarium_log' ) ) {
	return;
}

require_once __DIR__ . '/class-iworks-aquarium-log-base.php';

/**
 * Main plugin class.
 *
 * This class initializes the plugin and loads all necessary components.
 *
 * @since 1.0.0
 */
class iworks_aquarium_log extends iworks_aquarium_log_base {

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
		add_action( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		/**
		 * Enable aquarium post type
		 */
		add_filter( 'iworks-aquarium-log/load/posttype/aquarium', '__return_true' );
		/**
		 * post types
		 */
		$filename = $this->includes_directory . '/class-iworks-aquarium-log-posttypes.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			$this->objects['posttypes'] = new iworks_aquarium_log_posttypes();
		}
		/**
		 * load github class
		 */
		$filename = $this->includes_directory . '/class-iworks-aquarium-log-github.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			$this->objects['github'] = new iworks_aquarium_log_github();
		}
		/**
		 * admin
		 */
		if ( is_admin() ) {
			$filename = $this->includes_directory . '/class-iworks-aquarium-log-wp-admin.php';
			if ( is_file( $filename ) ) {
				include_once $filename;
				$this->objects['wp-admin'] = new iworks_aquarium_log_wp_admin();
			}
		}
		/**
		 * load db class
		 */
		$filename = $this->includes_directory . '/class-iworks-aquarium-log-db.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			$this->objects['db'] = new iworks_aquarium_log_db();
		}
		/**
		 * load logger class
		 */
		$filename = $this->includes_directory . '/class-iworks-aquarium-log-logger.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			$this->objects['logger'] = new iworks_aquarium_log_logger();
		}
		/**
		 * register objects filter
		 */
		add_action( 'iworks-aquarium-log/register_objects', array( $this, 'register_objects' ), 10, 3 );
		/**
		 * is active?
		 */
		add_filter( 'iworks-aquarium-log/is_active', '__return_true' );
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
		if ( ! isset( $this->objects[ $group ] ) ) {
			$this->objects[ $group ] = array();
		}
		return $this->objects[ $group ][ $name ] = $object;
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
			$file = 'assets/styles/iworks-aquarium-log-frontend' . $this->dev . '.css';
			wp_enqueue_style( 'iworks_aquarium_log', plugins_url( $file, $this->base ), array(), $this->get_version( $file ) );
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
		$this->objects['db']->db_install();
		$this->check_option_object();
		$this->options->activate();
		do_action( 'iworks/iworks-aquarium-log/register_activation_hook' );
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
		do_action( 'iworks/iworks-aquarium-log/register_deactivation_hook' );
	}

	/**
	 * Add aquarium_id query var.
	 *
	 * Adds 'aquarium_id' to the list of public query variables
	 * so WordPress recognizes it in URLs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'aquarium_id';
		return $vars;
	}
}
