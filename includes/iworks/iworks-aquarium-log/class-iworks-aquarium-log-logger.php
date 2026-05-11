<?php
/**
 * iWorks Aquarium Log Logger Class
 *
 * Handles logging of various actions and events within the iWorks Aquarium Log plugin.
 * Provides a centralized logging system for tracking user activities,
 * system events, and important changes.
 *
 * @since      1.0.0
 * @package    iWorks Aquarium Log
 * @subpackage iWorks Aquarium Log/Includes
 * @author     iWorks Aquarium Log Team
 */

defined( 'ABSPATH' ) || exit;

class iworks_aquarium_log_logger extends iworks_aquarium_log_base {

	/**
	 * Log types
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $log_types = array(
		'aquarium'    => 'Aquarium',
		'chemistry'   => 'Chemistry',
		'maintenance' => 'Maintenance',
		'system'      => 'System',
		'user'        => 'User',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks() {
		// Register the main logging action
		add_action( 'iworks-aquarium-log/log_action', array( $this, 'log_action' ), 10, 5 );
	}

	/**
	 * Log an action to the database
	 *
	 * @since 1.0.0
	 * @param string      $type        Log type identifier
	 * @param int         $aquarium_id Aquarium ID
	 * @param string      $message     Log message
	 * @param array       $details     Additional details (JSON encoded)
	 * @param int|null    $user_id     User ID (null for current user)
	 * @return bool|WP_Error           True on success, WP_Error on failure
	 */
	public function log_action( $type, $aquarium_id, $message, $details = array(), $user_id = null ) {
		global $wpdb;

		// Validate log type
		if ( ! isset( $this->log_types[ $type ] ) ) {
			return new WP_Error(
				'invalid_log_type',
				/* translators: %s: invalid log type */
				sprintf( esc_html__( 'Invalid log type: %s', 'iworks-aquarium-log' ), $type )
			);
		}

		// Validate aquarium ID
		if ( ! is_numeric( $aquarium_id ) || $aquarium_id <= 0 ) {
			return new WP_Error(
				'invalid_aquarium_id',
				esc_html__( 'Invalid aquarium ID', 'iworks-aquarium-log' )
			);
		}

		// Get current user if not specified
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		// Prepare data
		$table_name = $wpdb->prefix . 'aquarium_log_log';
		$data       = array(
			'aquarium_id' => absint( $aquarium_id ),
			'type'        => sanitize_key( $type ),
			'message'     => wp_kses_post( $message ),
			'details'     => ! empty( $details ) ? wp_json_encode( $details ) : null,
			'user_id'     => absint( $user_id ),
			'log_date'    => current_time( 'mysql' ),
		);

		// Insert log entry
		$result = $wpdb->insert( $table_name, $data );

		if ( false === $result ) {
			return new WP_Error(
				'log_insert_failed',
				esc_html__( 'Failed to insert log entry', 'iworks-aquarium-log' )
			);
		}

		/**
		 * Action fired after a log entry is successfully created
		 *
		 * @since 1.0.0
		 * @param int   $log_id      The ID of the created log entry
		 * @param array $data        The log entry data
		 */
		do_action( 'iworks-aquarium-log/log_entry_created', $wpdb->insert_id, $data );

		return true;
	}

	/**
	 * Log aquarium creation
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID
	 * @param string $title       Aquarium title
	 * @return bool|WP_Error
	 */
	public function log_aquarium_created( $aquarium_id, $title ) {
		/* translators: %s: aquarium title */
		$message = sprintf( esc_html__( 'Aquarium "%s" created', 'iworks-aquarium-log' ), $title );
		$details = array(
			'action' => 'create',
			'title'  => $title,
		);

		return $this->log_action( 'aquarium', $aquarium_id, $message, $details );
	}

	/**
	 * Log aquarium update
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID
	 * @param string $title       Aquarium title
	 * @param array  $changes     Changed fields
	 * @return bool|WP_Error
	 */
	public function log_aquarium_updated( $aquarium_id, $title, $changes = array() ) {
		/* translators: %s: aquarium title */
		$message = sprintf( esc_html__( 'Aquarium "%s" updated', 'iworks-aquarium-log' ), $title );
		$details = array(
			'action'  => 'update',
			'title'   => $title,
			'changes' => $changes,
		);

		return $this->log_action( 'aquarium', $aquarium_id, $message, $details );
	}

	/**
	 * Log aquarium deletion
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID
	 * @param string $title       Aquarium title
	 * @return bool|WP_Error
	 */
	public function log_aquarium_deleted( $aquarium_id, $title ) {
		/* translators: %s: aquarium title */
		$message = sprintf( esc_html__( 'Aquarium "%s" deleted', 'iworks-aquarium-log' ), $title );
		$details = array(
			'action' => 'delete',
			'title'  => $title,
		);

		return $this->log_action( 'aquarium', $aquarium_id, $message, $details );
	}

	/**
	 * Log chemistry measurement addition
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID
	 * @param string $param_key   Parameter key
	 * @param float  $param_value Parameter value
	 * @param string $date        Measurement date
	 * @return bool|WP_Error
	 */
	public function log_chemistry_measurement_added( $aquarium_id, $param_key, $param_value, $date ) {
		$param_name = $this->get_parameter_name( $param_key );
		$message = sprintf(
			/* translators: 1: parameter name, 2: parameter value, 3: measurement date */
			esc_html__( 'Chemistry measurement added: %1$s = %2$s on %3$s', 'iworks-aquarium-log' ),
			$param_name,
			number_format_i18n( $param_value, 2 ),
			$date
		);
		$details = array(
			'action'           => 'add_measurement',
			'param_key'        => $param_key,
			'param_name'       => $param_name,
			'param_value'      => $param_value,
			'measurement_date' => $date,
		);

		return $this->log_action( 'chemistry', $aquarium_id, $message, $details );
	}

	/**
	 * Log chemistry measurement update
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID
	 * @param string $param_key   Parameter key
	 * @param float  $old_value   Old parameter value
	 * @param float  $new_value   New parameter value
	 * @param string $date        Measurement date
	 * @return bool|WP_Error
	 */
	public function log_chemistry_measurement_updated( $aquarium_id, $param_key, $old_value, $new_value, $date ) {
		$param_name = $this->get_parameter_name( $param_key );
		$message = sprintf(
			/* translators: 1: parameter name, 2: old value, 3: new value, 4: measurement date */
			esc_html__( 'Chemistry measurement updated: %1$s changed from %2$s to %3$s on %4$s', 'iworks-aquarium-log' ),
			$param_name,
			number_format_i18n( $old_value, 2 ),
			number_format_i18n( $new_value, 2 ),
			$date
		);
		$details = array(
			'action'           => 'update_measurement',
			'param_key'        => $param_key,
			'param_name'       => $param_name,
			'old_value'        => $old_value,
			'new_value'        => $new_value,
			'measurement_date' => $date,
		);

		return $this->log_action( 'chemistry', $aquarium_id, $message, $details );
	}

	/**
	 * Get parameter name from key
	 *
	 * @since 1.0.0
	 * @param string $param_key Parameter key
	 * @return string Parameter name
	 */
	private function get_parameter_name( $param_key ) {
		$this->check_option_object();
		$config = $this->options->get_group( 'chemistry' );
		return isset( $config[ $param_key ]['name'] ) ? $config[ $param_key ]['name'] : $param_key;
	}

	/**
	 * Get log entries for an aquarium
	*
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID
	 * @param string $type        Log type filter (optional)
	 * @param int    $limit       Number of entries to retrieve
	 * @param int    $offset      Offset for pagination
	 * @return array|WP_Error
	 */
	public function get_log_entries( $aquarium_id, $type = '', $limit = 50, $offset = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aquarium_log_log';
		$where      = array( 'aquarium_id = %d' );
		$params     = array( $aquarium_id );
		if ( ! empty( $type ) && isset( $this->log_types[ $type ] ) ) {
			$where[]  = 'type = %s';
			$params[] = $type;
		}
		$where_clause = implode( ' AND ', $where );
		$limit_clause = $wpdb->prepare( 'LIMIT %d OFFSET %d', $limit, $offset );
		$sql          = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY log_date DESC {$limit_clause}";
		$results      = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		if ( null === $results ) {
			return new WP_Error(
				'query_failed',
				__( 'Failed to retrieve log entries', 'iworks-aquarium-log' )
			);
		}

		return $results;
	}

	/**
	 * Get available log types
	 *
	 * @since 1.0.0
	 * @return array Available log types
	 */
	public function get_log_types() {
		return $this->log_types;
	}
}
