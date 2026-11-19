<?php
/**
 * Database class for Aquarium Log.
 *
 * @package Aquarium_Log
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Include the base class.
require_once dirname( __DIR__, 1 ) . '/class-iworks-aqualog-base.php';

/**
 * Database class for Aquarium Log.
 *
 * Handles all database operations for the Aquarium Log plugin.
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
	private string $_db_version = 'aquarium_log_db_version';

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
		'aquarium_log_log',
		'aquarium_log_chemistry',
		'aquarium_log_maintenance',
		'aquarium_log_maintenance_status',
		'aquarium_log_dosing',
		'aquarium_log_reminder',
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
				_x( 'Biotope', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Brackish', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'High Tech', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Hobbyist', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Iwagumi', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Low Tech', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Marine', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Mizube', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Paludarium', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Pond', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Ragwork', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Ryuboku', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Shrimp', 'import terms to aquarium type', 'PLUGIN_NAME' ),
				_x( 'Wabi Kusa', 'import terms to aquarium type', 'PLUGIN_NAME' ),
			);
			foreach ( $aquarium_types as $aquarium_type ) {
				wp_insert_term( $aquarium_type, 'iw_aquarium_group' );
			}
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * chemistry table
		 */
		$version_to_update = 2;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aquarium_log_chemistry';
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
				KEY param_key (param_key),
				KEY measurement_date (measurement_date)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * maintenance table
		 */
		$version_to_update = 3;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aquarium_log_maintenance';
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
				repeat_interval int(11) DEFAULT NULL COMMENT 'Repeat interval in days',
				full_day tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Full day event (no time)',
				created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
				next_schedule_date datetime DEFAULT NULL COMMENT 'Next schedule date',
				PRIMARY KEY (id),
				KEY aquarium_id (aquarium_id),
				KEY type (type),
				KEY status (status)
			) $charset_collate;";
			dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * maintenance log table
		 */
		$version_to_update = 4;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aquarium_log_log';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				aquarium_id bigint(20) unsigned NOT NULL COMMENT 'Aquarium ID',
				log_type varchar(50) NOT NULL DEFAULT '' COMMENT 'Log type identifier',
				message text NOT NULL COMMENT 'Log message',
				user_id bigint(20) unsigned DEFAULT NULL COMMENT 'User who performed action',
				log_date datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Log timestamp',
				created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Log creation timestamp',
				details longtext DEFAULT NULL COMMENT 'Additional action details (JSON)',

				PRIMARY KEY (id),
				KEY aquarium_id (aquarium_id),
				KEY log_type (log_type),
				KEY user_id (user_id),
				KEY log_date (log_date)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * Create reminder table
		 */
		$version_to_update = 5;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aquarium_log_reminder';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL COMMENT 'User ID',
				aquarium_id bigint(20) unsigned NOT NULL COMMENT 'Aquarium ID',
				reminder_type varchar(50) NOT NULL DEFAULT '' COMMENT 'Reminder type identifier',
				reminder_date datetime DEFAULT NULL COMMENT 'Reminder date',
				reminder_interval int(11) DEFAULT NULL COMMENT 'Reminder interval in days',
				created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
				PRIMARY KEY (id),
				KEY user_id (user_id),
				KEY aquarium_id (aquarium_id),
				KEY reminder_type (reminder_type)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * Upgrade reminder table: add user_id and reminder_interval columns
		 * (older installs created the table without them; dbDelta does not
		 * reliably pick them up, so add them explicitly).
		 */
		$version_to_update = 6;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'aquarium_log_reminder';
			$columns    = $wpdb->get_col( "SHOW COLUMNS FROM {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$alter      = array();
			if ( ! in_array( 'user_id', $columns, true ) ) {
				$alter[] = "ADD COLUMN user_id bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT 'User ID' AFTER id";
			}
			if ( ! in_array( 'reminder_interval', $columns, true ) ) {
				$alter[] = "ADD COLUMN reminder_interval int(11) DEFAULT NULL COMMENT 'Reminder interval in days' AFTER reminder_date";
			}
			if ( ! empty( $alter ) ) {
				if ( ! in_array( 'user_id', $columns, true ) ) {
					$alter[] = 'ADD KEY user_id (user_id)';
				}
				$wpdb->query( sprintf( 'ALTER TABLE %s %s', $table_name, implode( ', ', $alter ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
			}
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * Merge reminders into tasks: add repeat_interval to the
		 * maintenance table and migrate existing reminder rows.
		 */
		$version_to_update = 7;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$maintenance_table = $wpdb->prefix . 'aquarium_log_maintenance';
			$columns           = $wpdb->get_col( "SHOW COLUMNS FROM {$maintenance_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			if ( ! in_array( 'repeat_interval', $columns, true ) ) {
				$wpdb->query( "ALTER TABLE {$maintenance_table} ADD COLUMN repeat_interval int(11) DEFAULT NULL COMMENT 'Repeat interval in days' AFTER status" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			}
			if ( ! in_array( 'full_day', $columns, true ) ) {
				$wpdb->query( "ALTER TABLE {$maintenance_table} ADD COLUMN full_day tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Full day event (no time)' AFTER repeat_interval" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			}
			/**
			 * One-time migration: copy existing reminders into the
			 * maintenance table as repeating tasks. The reminder table
			 * is kept (backward compatibility) but no longer written.
			 */
			$reminder_table = $wpdb->prefix . 'aquarium_log_reminder';
			$reminders      = $wpdb->get_results( "SELECT * FROM {$reminder_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			if ( $reminders ) {
				$migrated_ids = array();
				foreach ( $reminders as $reminder ) {
					$exists = intval(
						$wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(*) FROM {$maintenance_table} WHERE aquarium_id = %d AND type = %s AND start_date = %s AND status = %s AND repeat_interval = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
								$reminder->aquarium_id,
								$reminder->reminder_type,
								$reminder->reminder_date,
								$reminder->reminder_status,
								intval( $reminder->reminder_interval )
							)
						)
					);
					if ( $exists ) {
						$migrated_ids[] = intval( $reminder->id );
						continue;
					}
					$result = $wpdb->insert(
						$maintenance_table,
						array(
							'aquarium_id'     => $reminder->aquarium_id,
							'type'            => $reminder->reminder_type,
							'title'           => '',
							'description'     => '',
							'start_date'      => $reminder->reminder_date,
							'status'          => $reminder->reminder_status,
							'repeat_interval' => intval( $reminder->reminder_interval ),
							'full_day'        => 0,
						),
						array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
					);
					if ( $result ) {
						$migrated_ids[] = intval( $reminder->id );
					}
				}
				update_option( 'aquarium_log_reminders_migrated', $migrated_ids, false );
			}
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * Create the per-occurrence status table. A repeating task is a
		 * single row expanded into many calendar occurrences; statuses of
		 * individual occurrences (e.g. "completed this week") live here,
		 * so marking one occurrence does not affect the others.
		 */
		$version_to_update = 8;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'aquarium_log_maintenance_status';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				task_id bigint(20) unsigned NOT NULL COMMENT 'Maintenance task ID',
				occurrence_date date NOT NULL COMMENT 'Occurrence date (Y-m-d)',
				status varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'Occurrence status',
				user_id bigint(20) unsigned DEFAULT NULL COMMENT 'User who set status',
				created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
				PRIMARY KEY (id),
				UNIQUE KEY task_occurrence (task_id, occurrence_date),
				KEY status (status)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		/**
		 * Add repeat_until to the maintenance table: explicit end of a
		 * repeating series ("delete all future occurrences" sets it, past
		 * occurrences remain visible).
		 */
		$version_to_update = 9;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$maintenance_table = $wpdb->prefix . 'aquarium_log_maintenance';
			$columns           = $wpdb->get_col( "SHOW COLUMNS FROM {$maintenance_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			if ( ! in_array( 'repeat_until', $columns, true ) ) {
				$wpdb->query( "ALTER TABLE {$maintenance_table} ADD COLUMN repeat_until date DEFAULT NULL COMMENT 'Series end date (occurrences after it are deleted)' AFTER repeat_interval" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			}
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
		$version_to_update = 10;
		if ( $db_version < $version_to_update ) {
			global $wpdb;
			$maintenance_table = $wpdb->prefix . 'aquarium_log_maintenance';
			$columns           = $wpdb->get_col( "SHOW COLUMNS FROM {$maintenance_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			if ( ! in_array( 'series_id', $columns, true ) ) {
				$wpdb->query( "ALTER TABLE {$maintenance_table} ADD COLUMN series_id bigint(20) unsigned DEFAULT NULL COMMENT 'Repeating series root task ID (NULL = single task)' AFTER aquarium_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( "ALTER TABLE {$maintenance_table} ADD KEY series_id (series_id)" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
			}
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
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
			// Table identifiers cannot be prepared placeholders — the name
			// comes from the internal, hardcoded $this->table_names list.
			$sql   = "DROP TABLE IF EXISTS {$wpdb->$table_name}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query = $wpdb->prepare( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		// Re-enable foreign key checks
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		// Delete the database version option
		delete_option( $this->_db_version );
	}
}
