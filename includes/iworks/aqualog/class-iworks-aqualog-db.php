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
		'aqualog_maintenance',
		'aqualog_dosing',
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
			$aquarium_types = array(
				_x( 'Biotope', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Brackish', 'import terms to aquarium type', 'aqualog' ),
				_x( 'High Tech', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Hobbyist', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Iwagumi', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Low Tech', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Marine', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Mizube', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Paludarium', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Pond', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Ragwork', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Ryuboku', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Shrimp', 'import terms to aquarium type', 'aqualog' ),
				_x( 'Wabi Kusa', 'import terms to aquarium type', 'aqualog' ),
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
			$table_name      = $wpdb->prefix . 'aqualog_chemistry';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE  IF NOT EXISTS $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				aquarium_id bigint(20) unsigned NOT NULL,
				param_key varchar(100) NOT NULL DEFAULT '',
				param_value float NOT NULL,
				measurement_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
				PRIMARY KEY  (id),
				KEY aquarium_id (aquarium_id),
				KEY param (param_key),
				KE measurement_date (measurement_date)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
		/**
		 * maintenance table
		 */
		$version_to_update = 3;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aqualog_maintenance';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				aquarium_id bigint(20) unsigned NOT NULL COMMENT 'Aquarium ID',
				type varchar(50) NOT NULL DEFAULT '' COMMENT 'Task type identifier',
				title varchar(255) NOT NULL COMMENT 'Task title',
				description text NOT NULL COMMENT 'Task description',
				start_date datetime DEFAULT NULL COMMENT 'Task start date',
				end_date datetime DEFAULT NULL COMMENT 'Task end date',
				status varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'Task status',
				created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
				next_schedule_date datetime DEFAULT NULL COMMENT 'Next schedule date',
				PRIMARY KEY (id),
				KEY idx_aquarium_id (aquarium_id),
				KEY idx_type (type),
				KEY idx_status (status)
			) $charset_collate;";
			dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
		}
		/**
		 * maintenance log table
		 */
		$version_to_update = 5;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aqualog_log';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				aquarium_id bigint(20) unsigned NOT NULL COMMENT 'Aquarium ID',
				type varchar(50) NOT NULL DEFAULT '' COMMENT 'Log type identifier',
				message text NOT NULL COMMENT 'Log message',
				user_id bigint(20) unsigned DEFAULT NULL COMMENT 'User who performed action',
				log_date datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Log timestamp',
				details longtext DEFAULT NULL COMMENT 'Additional action details (JSON)',
				PRIMARY KEY (id),
				KEY idx_aquarium_id (aquarium_id),
				KEY idx_type (type),
				KEY idx_user_id (user_id),
				KEY idx_log_date (log_date)
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
			$sql   = 'DROP TABLE IF EXISTS %s';
			$query = $wpdb->prepare( $sql, $wpdb->$table_name );
			$wpdb->query( $query );
		}

		// Re-enable foreign key checks
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		// Delete the database version option
		delete_option( $this->_db_version );
	}
}
