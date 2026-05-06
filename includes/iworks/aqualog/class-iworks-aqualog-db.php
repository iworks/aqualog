<?php
/**
 * Database class for OPI Polls Server.
 *
 * @package OPI_Polls_Server
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Include the base class.
require_once dirname( __DIR__, 1 ) . '/class-iworks-aqualog-base.php';

/**
 * Database class for OPI Polls Server.
 *
 * Handles all database operations for the OPI Polls Server plugin.
 */
class iworks_aqualog_db extends iworks_aqualog_base {

	/**
	 * Option name for storing the database version.
	 *
	 * This is used to track the current version of the database schema
	 * and to determine if database updates are needed.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $_db_version = 'aqualog_db_version';

	/**
	 * Array of database table names without prefix.
	 *
	 * Contains the names of all custom database tables used by the plugin.
	 * These names will be automatically prefixed with the WordPress table prefix.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	private array $table_names = array(
		'aqualog_log',
		'aqualog_chemistry',
	);
	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->register_tables();
		/**
		 * WordPress hooks.
		 */
		add_action( 'shutdown', array( $this, 'db_install' ) );
		/**
		 * Aqualog hooks.
		 */
		add_action( 'iworks/aqualog/register_uninstall_hook', array( $this, 'drop_tables' ) );
		add_action( 'iworks/aqualog/register_activation_hook', array( $this, 'db_install' ) );
	}

	/**
	 * Register plugin tables with $wpdb.
	 *
	 * This allows the tables to be referenced as $wpdb->{table_name} throughout the plugin.
	 * Tables are registered with the WordPress database prefix.
	 *
	 * @since 1.0.0
	 */
	private function register_tables() {
		global $wpdb;

		// Register each table.
		foreach ( $this->table_names as $key => $table_name ) {
			$wpdb->$table_name = $wpdb->prefix . $table_name;
		}
	}

	/**
	 * Create the database table if it doesn't exist.
	 *
	 * @since 1.0.0
	 */
	public function db_install() {
		/**
		 * Get current DB version.
		 */
		$db_version = intval( get_option( $this->_db_version ) );
		if ( 1 > $db_version ) {
			add_option( $this->_db_version, 0, '', 'no' );
		}
		/**
		 * init, import few taxonomies
		 */
		$version_to_update = 1;
		if ( $db_version < $version_to_update ) {
			$aquarium_types	 = array(
				_x('Biotope', 'import terms to aquarium type', 'aqualog' ),
				_x('Brackish', 'import terms to aquarium type', 'aqualog' ),
				_x('High Tech', 'import terms to aquarium type', 'aqualog' ),
				_x('Hobbyist', 'import terms to aquarium type', 'aqualog' ),
				_x('Iwagumi', 'import terms to aquarium type', 'aqualog' ),
				_x('Low Tech', 'import terms to aquarium type', 'aqualog' ),
				_x('Marine', 'import terms to aquarium type', 'aqualog' ),
				_x('Mizube', 'import terms to aquarium type', 'aqualog' ),
				_x('Paludarium', 'import terms to aquarium type', 'aqualog' ),
				_x('Pond', 'import terms to aquarium type', 'aqualog' ),
				_x('Ragwork', 'import terms to aquarium type', 'aqualog' ),
				_x('Ryuboku', 'import terms to aquarium type', 'aqualog' ),
				_x('Shrimp', 'import terms to aquarium type', 'aqualog' ),
				_x('Wabi Kusa', 'import terms to aquarium type', 'aqualog' ),
			);
			foreach ( $aquarium_types as $aquarium_type ) {
				wp_insert_term( $aquarium_type, 'iw_aquarium_group' );
			}
			update_option( $this->_db_version, $version_to_update );
		}
		/**
		 * chemistry table
		 */
		$version_to_update = 2;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'aqualog_chemistry';
			
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				aquarium_id bigint(20) unsigned NOT NULL,
				param_key varchar(100) NOT NULL DEFAULT '',
				param_value float NOT NULL,
				measurement_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY  (id),
				KEY aquarium_id (aquarium_id),
				KEY param (param_key),
				KEY measurement_date (measurement_date)
			) $charset_collate;";
			
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			
			update_option( $this->_db_version, $version_to_update );
		}
	}

	/**
	 * Drop all plugin database tables
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function drop_tables() {
		global $wpdb;

		// Disable foreign key checks to avoid issues with table dependencies
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		// Drop each table
		foreach ( $this->table_names as $table_name ) {
			$wpdb->query( "DROP TABLE IF EXISTS `$wpdb->$table_name`" );
		}

		// Re-enable foreign key checks
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		// Delete the database version option
		delete_option( $this->_db_version );
	}
}
