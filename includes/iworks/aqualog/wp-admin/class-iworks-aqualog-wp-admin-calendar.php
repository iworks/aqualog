<?php
/**
 * Aqualog Calendar Class
 *
 * Renders the Calendar submenu page with month and week views.
 * Events come from the maintenance tasks table (with recurrence
 * expansion for repeating tasks) and are rendered as a
 * server-generated grid - no external calendar library.
 *
 * @package    iWorks
 * @subpackage Aqualog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __DIR__, 2 ) . '/class-iworks-aqualog-base.php';

/**
 * Aqualog Calendar Class
 *
 * Builds and renders the month/week calendar grid for the
 * current aquarium.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_calendar extends iworks_aqualog_base {

	/**
	 * Class constructor.
	 *
	 * Initializes the calendar class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'iworks/aqualog/wp-admin/calendar_page', array( $this, 'render_page' ) );
		add_filter( 'iworks/aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	/**
	 * Filter WordPress localize script data for calendar page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Localize script data.
	 * @return array Filtered localize script data.
	 */
	public function filter_wp_localize_script( $data ) {
		$data['calendar'] = array(
			'everyDays' => /* translators: %d: number of days */ esc_html__( 'Every %d days', 'PLUGIN_NAME' ),
		);
		return $data;
	}

	/**
	 * Render calendar page.
	 *
	 * @since 1.0.0
	 */
	public function render_page() {
		$this->set_current_aquarium_id();
		/**
		 * The "Change Aquarium" link forces the selection screen.
		 */
		$change = 'aquarium' === filter_input( INPUT_GET, 'change' );
		if ( empty( $this->current_aquarium_id ) || $change ) {
			$this->load_template( 'select-aquarium', 'messages' );
			return;
		}
		$view = 'week' === filter_input( INPUT_GET, 'view' ) ? 'week' : 'month';
		$date = sanitize_text_field( filter_input( INPUT_GET, 'date' ) );
		if ( ! $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$date = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
		}
		$range = $this->get_range( $view, $date );
		$this->load_template(
			'calendar',
			'pages',
			true,
			apply_filters(
				'iworks/aqualog/wp-admin/calendar/args',
				array(
					'aquarium_id' => $this->current_aquarium_id,
					'view'        => $view,
					'date'        => $date,
					'range'       => $range,
					'events'      => $this->get_events( $range['from'], $range['to'] ),
					'weekdays'    => $this->get_weekdays( intval( get_option( 'start_of_week', 1 ) ) ),
				)
			)
		);
	}

	/**
	 * Compute the visible date range for a view.
	 *
	 * Month view: grid always starts at the first cell of the weekday
	 * grid (aligned to the WP "start_of_week" option) and spans 6 weeks.
	 * Week view: 7 days starting at the week's first day.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $view View name ('month' or 'week').
	 * @param string $date Anchor date in Y-m-d format.
	 * @return array {
	 *     @type string $from  First visible day (Y-m-d).
	 *     @type string $to    Last visible day (Y-m-d).
	 *     @type array  $cells Day cells: arrays with 'date' (Y-m-d) and 'in_month' flags.
	 *     @type string $prev  Date argument for the previous period.
	 *     @type string $next  Date argument for the next period.
	 *     @type string $title Localized period title.
	 * }
	 */
	public function get_range( $view, $date ) {
		$anchor        = strtotime( $date );
		$start_of_week = intval( get_option( 'start_of_week', 1 ) );
		$cells         = array();
		if ( 'week' === $view ) {
			$first = $this->get_week_start( $anchor, $start_of_week );
			$days  = 7;
			$prev  = gmdate( 'Y-m-d', strtotime( '-7 days', $first ) );
			$next  = gmdate( 'Y-m-d', strtotime( '+7 days', $first ) );
			$title = sprintf(
				/* translators: 1: start date, 2: end date */
				__( '%1$s – %2$s', 'PLUGIN_NAME' ),
				date_i18n( get_option( 'date_format' ), $first ),
				date_i18n( get_option( 'date_format' ), strtotime( '+6 days', $first ) )
			);
		} else {
			$first = strtotime( gmdate( 'Y-m-01', $anchor ) );
			$first = $this->get_week_start( $first, $start_of_week );
			$days  = 42;
			$prev  = gmdate( 'Y-m-01', strtotime( '-1 month', $anchor ) );
			$next  = gmdate( 'Y-m-01', strtotime( '+1 month', $anchor ) );
			$title = date_i18n( 'F Y', $anchor );
		}
		$month_of_anchor = gmdate( 'Y-m', $anchor );
		for ( $i = 0; $i < $days; $i++ ) {
			$day    = strtotime( sprintf( '+%d days', $i ), $first );
			$cells[] = array(
				'date'     => gmdate( 'Y-m-d', $day ),
				'in_month' => ( 'week' === $view ) || ( gmdate( 'Y-m', $day ) === $month_of_anchor ),
			);
		}
		return array(
			'from'  => $cells[0]['date'],
			'to'    => $cells[ count( $cells ) - 1 ]['date'],
			'cells' => $cells,
			'prev'  => $prev,
			'next'  => $next,
			'title' => $title,
		);
	}

	/**
	 * Shift a timestamp back to the first day of its week.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param int $timestamp     Timestamp.
	 * @param int $start_of_week Weekday the week starts with (0 = Sunday).
	 * @return int Timestamp of the week's first day (midnight).
	 */
	private function get_week_start( $timestamp, $start_of_week ) {
		$weekday = intval( gmdate( 'w', $timestamp ) );
		$diff    = ( $weekday - $start_of_week + 7 ) % 7;
		return strtotime( sprintf( '-%d days', $diff ), $timestamp );
	}

	/**
	 * Get localized weekday names starting at the week start.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $start_of_week Weekday the week starts with (0 = Sunday).
	 * @return array List of 7 weekday names.
	 */
	public function get_weekdays( $start_of_week ) {
		$base = strtotime( 'next sunday' );
		$days = array();
		for ( $i = 0; $i < 7; $i++ ) {
			$offset = ( $start_of_week + $i ) % 7;
			$days[] = date_i18n( 'l', strtotime( sprintf( '+%d days', $offset ), $base ) );
		}
		return $days;
	}

	/**
	 * Get calendar events for the current aquarium in a date range.
	 *
	 * Collects maintenance tasks (start_date, expanded by repeat_interval
	 * days when the task repeats) plus non-migrated legacy reminders and
	 * sorts them by date.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $from First day (Y-m-d 00:00:00).
	 * @param string $to   Last day (Y-m-d 23:59:59).
	 * @return array Grouped events: array of 'Y-m-d' => list of events.
	 */
	public function get_events( $from, $to ) {
		global $wpdb;
		$aquarium_id = $this->current_aquarium_id;
		if ( empty( $aquarium_id ) ) {
			return array();
		}
		$this->check_option_object();
		$config    = $this->options->get_group( 'maintenance_tasks' );
		$events    = array();
		$from_time = $from . ' 00:00:00';
		$to_time   = $to . ' 23:59:59';
		/**
		 * maintenance tasks, with recurrence expansion
		 * (repeating tasks replace the former separate reminders)
		 */
		$sql     = sprintf(
			'SELECT * FROM %s WHERE aquarium_id = %%d AND start_date <= %%s',
			$wpdb->aquarium_log_maintenance
		);
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $aquarium_id, $to_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$task_rows = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				if ( empty( $row['start_date'] ) ) {
					continue;
				}
				$task_rows[] = $row;
			}
		}
		$occurrence_statuses = array();
		$maintenance         = null;
		$maintenance_file    = dirname( __FILE__ ) . '/class-iworks-aqualog-wp-admin-maintenance.php';
		if ( is_file( $maintenance_file ) ) {
			include_once $maintenance_file;
			$maintenance         = new iworks_aqualog_wp_admin_maintenance();
			$occurrence_statuses = $maintenance->get_occurrence_statuses( wp_list_pluck( $task_rows, 'id' ) );
		}
		foreach ( $task_rows as $row ) {
				$type  = isset( $row['type'] ) ? $row['type'] : '';
				$title = ! empty( $row['title'] ) ? $row['title'] : ( isset( $config[ $type ]['title'] ) ? $config[ $type ]['title'] : $type );
				/**
				 * Occurrences are stored as separate rows (repeating tasks
				 * are materialized up to the configured horizon), so each
				 * row is one calendar event.
				 */
				$interval = isset( $row['repeat_interval'] ) ? intval( $row['repeat_interval'] ) : 0;
				$current  = strtotime( $row['start_date'] );
				$datetime = gmdate( 'Y-m-d H:i:s', $current );
				if ( $datetime >= $from_time && $datetime <= $to_time ) {
					$date_key   = substr( $datetime, 0, 10 );
					$occurrence = $occurrence_statuses[ intval( $row['id'] ) ][ $date_key ] ?? null;
					$events[]   = array(
						'id'    => intval( $row['id'] ),
						'date'  => $date_key,
						'time'  => substr( $datetime, 11, 5 ),
						'kind'  => 'task',
						'type'  => $type,
						'status' => null !== $occurrence ? $occurrence : ( ! empty( $row['status'] ) ? $row['status'] : 'pending' ),
						'title' => $title,
						'description' => isset( $row['description'] ) ? $row['description'] : '',
						'interval' => $interval,
						'series_id' => intval( $row['series_id'] ?? 0 ),
						'full_day' => empty( $row['full_day'] ) ? 0 : 1,
						'aquarium_id' => $aquarium_id,
					);
				}
		}
		/**
		 * Backward compatibility: reminders created before the merge that
		 * were NOT migrated are also expanded (the table is kept read-only
		 * for old data).
		 */
		$migrated = get_option( 'aquarium_log_reminders_migrated', array() );
		$migrated = is_array( $migrated ) ? array_map( 'intval', $migrated ) : array();
		$sql     = sprintf(
			'SELECT * FROM %s WHERE aquarium_id = %%d AND reminder_date <= %%s',
			$wpdb->aquarium_log_reminder
		);
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $aquarium_id, $to_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				if ( in_array( intval( $row['id'] ), $migrated, true ) ) {
					continue;
				}
				if ( empty( $row['reminder_date'] ) ) {
					continue;
				}
				$interval = isset( $row['reminder_interval'] ) ? intval( $row['reminder_interval'] ) : 0;
				$current  = strtotime( $row['reminder_date'] );
				$end      = strtotime( $to_time );
				$guard    = 0;
				while ( $current <= $end && $guard < 366 ) {
					$guard++;
					$datetime = gmdate( 'Y-m-d H:i:s', $current );
					if ( $datetime >= $from_time ) {
						$type  = isset( $row['reminder_type'] ) ? $row['reminder_type'] : '';
						$events[] = array(
							'id'    => intval( $row['id'] ),
							'date'  => substr( $datetime, 0, 10 ),
							'time'  => substr( $datetime, 11, 5 ),
							'kind'  => 'reminder',
							'type'  => $type,
							'status' => ! empty( $row['reminder_status'] ) ? $row['reminder_status'] : 'pending',
							'title' => isset( $config[ $type ]['title'] ) ? $config[ $type ]['title'] : $type,
							'description' => '',
							'interval' => $interval,
							'full_day' => 0,
							'aquarium_id' => $aquarium_id,
						);
					}
					if ( $interval < 1 ) {
						break;
					}
					$current = strtotime( sprintf( '+%d days', $interval ), $current );
				}
			}
		}
		/**
		 * Filter the events before rendering.
		 *
		 * @since 1.0.0
		 * @param array  $events      Flat list of events (not grouped).
		 * @param string $from        First visible day (Y-m-d).
		 * @param string $to          Last visible day (Y-m-d).
		 * @param int    $aquarium_id Current aquarium ID.
		 */
		$events = apply_filters( 'iworks/aqualog/calendar/events', $events, $from, $to, $aquarium_id );
		/**
		 * group by day
		 */
		$grouped = array();
		foreach ( $events as $event ) {
			$grouped[ $event['date'] ][] = $event;
		}
		foreach ( $grouped as $day => $day_events ) {
			usort( $day_events, function ( $a, $b ) {
				return strcmp( $a['time'], $b['time'] );
			} );
			$grouped[ $day ] = $day_events;
		}
		return $grouped;
	}
}
