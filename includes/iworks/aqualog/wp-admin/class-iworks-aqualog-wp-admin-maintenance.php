<?php
/**
 * Aqualog Maintenance Class
 *
 * Handles all maintenance-related functionality for the Aqualog plugin.
 * This includes managing maintenance tasks, scheduling,
 * and maintenance data storage/retrieval.
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

require_once __DIR__ . '/class-iworks-aqualog-maintenance-list-table.php';

/**
 * Aqualog Maintenance Class
 *
 * Manages maintenance tasks for aquarium tracking and analysis.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_maintenance extends iworks_aqualog_base {

	/**
	 * Maintenance tasks for the current aquarium.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $tasks = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the maintenance class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * AJAX handler for adding maintenance tasks.
		 */
		add_action( 'wp_ajax_aquarium_log_maintenance_add_task', array( $this, 'ajax_add_maintenance_task' ) );
		/**
		 * AJAX handler for editing (saving) maintenance tasks.
		 */
		add_action( 'wp_ajax_aquarium_log_maintenance_edit_task', array( $this, 'ajax_edit_maintenance_task' ) );
		/**
		 * AJAX handler for adding maintenance reminders.
		 */
		add_action( 'wp_ajax_aquarium_log_maintenance_add_reminder', array( $this, 'ajax_add_reminder' ) );
		/**
		 * Aqualog plugin action hook for maintenance page rendering.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks/aqualog/wp-admin/maintenance_page', array( $this, 'render_page' ) );
		add_filter( 'iworks/aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	/**
	 * Filter WordPress localize script data for maintenance page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Localize script data.
	 * @return array Filtered localize script data.
	 */
	public function filter_wp_localize_script( $data ) {
		$this->set_current_aquarium_id();
		$data['maintenance']           = array(
			'tasks'       => $this->get_task_types(),
			'frequencies' => $this->get_frequencies(),
		);
		$data['nonces']['maintenance'] = array(
			'add'      => wp_create_nonce( $this->get_meta_name( 'maintenance_add' ) ),
			'save'     => wp_create_nonce( $this->get_meta_name( 'maintenance_save' ) ),
			'reminder' => wp_create_nonce( $this->get_meta_name( 'reminder_add' ) ),
		);
		return $data;
	}

	/**
	 * Render maintenance page.
	 *
	 * @since 1.0.0
	 */
	public function render_page() {
		$this->set_current_aquarium_id();
		/**
		 * list view: render all tasks in a WP_List_Table
		 */
		if ( 'list' === filter_input( INPUT_GET, 'view' ) ) {
			$this->render_list_table();
			return;
		}
		$this->load_template(
			'maintenance',
			'pages',
			true,
			apply_filters(
				'iworks/aqualog/wp-admin/maintenance/args',
				array(
					'aquarium_id' => $this->current_aquarium_id,
					'messages'    => apply_filters( 'iworks/aqualog/wp-admin/messages/files', array() ),
					'meta'        => get_post_meta( $this->current_aquarium_id ),
					'tasks'       => $this->get_tasks( $this->current_aquarium_id ),
					'completed'   => $this->get_tasks( $this->current_aquarium_id, 'completed' ),
					'scheduled'   => $this->get_scheduled_tasks( $this->current_aquarium_id ),
				)
			)
		);
	}

	/**
	 * Get available maintenance task types.
	 *
	 * Returns the list of predefined maintenance task types
	 * with their titles and descriptions.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Available task types.
	 */
	public function get_task_types() {
		$this->check_option_object();
		$config = $this->options->get_group( 'maintenance_tasks' );
		$tasks  = array();
		foreach ( $config as $key => $value ) {
			$tasks[ $key ] = wp_parse_args(
				$value,
				array(
					'title'       => $key,
					'description' => '',
					'frequency'   => '',
				)
			);
		}
		return apply_filters( 'iworks/aqualog/maintenance/task_types', $tasks );
	}

	/**
	 * Get maintenance tasks for an aquarium.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int    $aquarium_id Aquarium ID.
	 * @param string $status      Optional status filter ('pending', 'in_progress', 'completed').
	 * @param int    $limit       Number of tasks to retrieve.
	 * @return array List of tasks.
	 */
	public function get_tasks( $aquarium_id, $status = '', $limit = 10 ) {
		global $wpdb;
		if ( empty( $aquarium_id ) ) {
			return array();
		}
		$key = $aquarium_id . '|' . $status . '|' . $limit;
		if ( isset( $this->tasks[ $key ] ) ) {
			return $this->tasks[ $key ];
		}
		$where  = array( 'aquarium_id = %d' );
		$params = array( $aquarium_id );
		if ( ! empty( $status ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}
		$sql = sprintf(
			'SELECT * FROM %s WHERE %s ORDER BY COALESCE( start_date, created_at ) DESC LIMIT %%d',
			$wpdb->aquarium_log_maintenance,
			implode( ' AND ', $where )
		);
		$params[] = $limit;
		$results  = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		if ( empty( $results ) || ! is_array( $results ) ) {
			$results = array();
		}
		foreach ( $results as $index => $row ) {
			$results[ $index ]['date'] = ! empty( $row['start_date'] ) ? $row['start_date'] : $row['created_at'];
		}
		$this->tasks[ $key ] = $results;
		return $this->tasks[ $key ];
	}

	/**
	 * Get scheduled (pending) maintenance tasks for an aquarium.
	 *
	 * Tasks with a future next_schedule_date first, then pending ones.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $aquarium_id Aquarium ID.
	 * @param int $limit       Number of tasks to retrieve.
	 * @return array List of scheduled tasks.
	 */
	public function get_scheduled_tasks( $aquarium_id, $limit = 10 ) {
		global $wpdb;
		if ( empty( $aquarium_id ) ) {
			return array();
		}
		$sql = sprintf(
			"SELECT * FROM %s WHERE aquarium_id = %%d AND status IN ( 'pending', 'in_progress' ) ORDER BY COALESCE( next_schedule_date, start_date, created_at ) ASC LIMIT %%d",
			$wpdb->aquarium_log_maintenance
		);
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $aquarium_id, $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( empty( $results ) || ! is_array( $results ) ) {
			return array();
		}
		foreach ( $results as $index => $row ) {
			$results[ $index ]['date'] = ! empty( $row['start_date'] ) ? $row['start_date'] : $row['created_at'];
		}
		return $results;
	}

	/**
	 * Render the maintenance tasks list (WP_List_Table).
	 *
	 * Shown when the "Total Tasks" stat card is clicked
	 * (admin.php?page=aqualog-maintenance&view=list).
	 *
	 * @since 1.0.0
	 */
	public function render_list_table() {
		$table = new iworks_aqualog_maintenance_list_table();
		$table->process_actions();
		$table->prepare_items();
		?>
		<div class="wrap aqualog-maintenance aqualog-maintenance-list">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Maintenance', 'PLUGIN_NAME' ); ?></h1>
			<?php if ( isset( $_REQUEST['deleted'] ) && absint( wp_unslash( $_REQUEST['deleted'] ) ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Task(s) deleted.', 'PLUGIN_NAME' ); ?></p></div>
			<?php endif; ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( filter_input( INPUT_GET, 'page' ) ); ?>" />
				<input type="hidden" name="view" value="list" />
				<?php
				$table->views();
				$table->search_box( esc_html__( 'Search Tasks', 'PLUGIN_NAME' ), 'aqualog-maintenance' );
				$table->display();
				?>
			</form>
		</div>
		<?php
		// Load the maintenance form template (wp.template block, rendered by JS into a modal).
		$this->load_template(
			'maintenance-form',
			'elements',
			false,
			array( 'aquarium_id' => $this->current_aquarium_id ?? 0 )
		);
		?>
		<?php
	}

	/**
	 * AJAX handler for adding a maintenance task.
	 *
	 * Mirrors the chemistry "add parameter" flow: validates the task type
	 * against the configured task types, inserts a row into the
	 * maintenance table and logs the action.
	 *
	 * @since 1.0.0
	 */
	public function ajax_add_maintenance_task() {
		check_ajax_referer( $this->get_meta_name( 'maintenance_add' ) );
		if ( ! current_user_can( $this->get_this_capability() ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'PLUGIN_NAME' ) );
		}
		$this->check_option_object();
		$id          = intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) );
		$type        = sanitize_key( filter_input( INPUT_POST, 'type' ) );
		$title       = sanitize_text_field( filter_input( INPUT_POST, 'title' ) );
		$description = sanitize_textarea_field( filter_input( INPUT_POST, 'description' ) );
		$status      = sanitize_key( filter_input( INPUT_POST, 'status' ) );
		$date        = sanitize_text_field( filter_input( INPUT_POST, 'date' ) );
		/**
		 * validate aquarium
		 */
		if ( ! apply_filters( 'iworks/aqualog/post_type/aquarium/check/id', false, $id ) ) {
			wp_send_json_error( esc_html__( 'Invalid aquarium', 'PLUGIN_NAME' ) );
		}
		/**
		 * validate task type
		 */
		$config = $this->options->get_group( 'maintenance_tasks' );
		if ( ! array_key_exists( $type, $config ) ) {
			wp_send_json_error( esc_html__( 'Invalid task type', 'PLUGIN_NAME' ) );
		}
		/**
		 * sanitize
		 */
		if ( empty( $title ) ) {
			$title = isset( $config[ $type ]['title'] ) ? $config[ $type ]['title'] : $type;
		}
		$allowed_status = array( 'pending', 'in_progress', 'completed' );
		if ( ! in_array( $status, $allowed_status, true ) ) {
			$status = 'pending';
		}
		$start_date = $date ? gmdate( 'Y-m-d H:i:s', strtotime( $date ) ) : current_time( 'mysql' );
		/**
		 * Filter the data before inserting into the database.
		 *
		 * @since 1.0.0
		 * @param array $data The data to insert.
		 * @return array The filtered data.
		 */
		$data   = apply_filters(
			'iworks/aqualog/maintenance/sql/insert/data',
			array(
				'aquarium_id'  => $id,
				'type'         => $type,
				'title'        => $title,
				'description'  => $description,
				'status'       => $status,
				'start_date'   => $start_date,
			)
		);
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->aquarium_log_maintenance,
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		if ( $result ) {
			$this->log_maintenance_added( $id, $title, $type, $start_date );
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Task added successfully', 'PLUGIN_NAME' ),
				)
			);
		}
		wp_send_json_error( esc_html__( 'Failed to add task', 'PLUGIN_NAME' ) );
	}

	/**
	 * AJAX handler for editing (saving) a maintenance task.
	 *
	 * Mirrors the "add task" flow: validates the task type against the
	 * configured task types, updates the row in the maintenance table
	 * and logs the action.
	 *
	 * @since 1.0.0
	 */
	public function ajax_edit_maintenance_task() {
		check_ajax_referer( $this->get_meta_name( 'maintenance_save' ) );
		if ( ! current_user_can( $this->get_this_capability() ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'PLUGIN_NAME' ) );
		}
		$this->check_option_object();
		$task_id     = intval( filter_input( INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT ) );
		$id          = intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) );
		$type        = sanitize_key( filter_input( INPUT_POST, 'type' ) );
		$title       = sanitize_text_field( filter_input( INPUT_POST, 'title' ) );
		$description = sanitize_textarea_field( filter_input( INPUT_POST, 'description' ) );
		$status      = sanitize_key( filter_input( INPUT_POST, 'status' ) );
		$date        = sanitize_text_field( filter_input( INPUT_POST, 'date' ) );
		/**
		 * validate task
		 */
		global $wpdb;
		$table   = $wpdb->aquarium_log_maintenance;
		$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $task_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( empty( $row ) || ! is_array( $row ) ) {
			wp_send_json_error( esc_html__( 'Invalid task', 'PLUGIN_NAME' ) );
		}
		/**
		 * the aquarium of the task cannot be changed
		 */
		$id = intval( $row['aquarium_id'] );
		/**
		 * validate task type
		 */
		$config = $this->options->get_group( 'maintenance_tasks' );
		if ( ! array_key_exists( $type, $config ) ) {
			wp_send_json_error( esc_html__( 'Invalid task type', 'PLUGIN_NAME' ) );
		}
		/**
		 * sanitize
		 */
		if ( empty( $title ) ) {
			$title = isset( $config[ $type ]['title'] ) ? $config[ $type ]['title'] : $type;
		}
		$allowed_status = array( 'pending', 'in_progress', 'completed' );
		if ( ! in_array( $status, $allowed_status, true ) ) {
			$status = 'pending';
		}
		$start_date = $date ? gmdate( 'Y-m-d H:i:s', strtotime( $date ) ) : $row['start_date'];
		/**
		 * Filter the data before updating the database.
		 *
		 * @since 1.0.0
		 * @param array $data The data to update.
		 * @return array The filtered data.
		 */
		$data   = apply_filters(
			'iworks/aqualog/maintenance/sql/update/data',
			array(
				'type'       => $type,
				'title'      => $title,
				'description' => $description,
				'status'     => $status,
				'start_date' => $start_date,
			)
		);
		$result = $wpdb->update(
			$wpdb->aquarium_log_maintenance,
			$data,
			array( 'id' => $task_id ),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
		if ( false === $result ) {
			wp_send_json_error( esc_html__( 'Failed to save task', 'PLUGIN_NAME' ) );
		}
		$this->log_maintenance_updated( $id, $title, $type, $status, $start_date );
		wp_send_json_success(
			array(
				'message' => esc_html__( 'Task saved successfully', 'PLUGIN_NAME' ),
			)
		);
	}

	/**
	 * Get available reminder frequencies.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Frequencies (key => label).
	 */
	public function get_frequencies() {
		$this->check_option_object();
		return apply_filters( 'iworks/aqualog/reminder/frequencies', $this->options->get_group( 'frequencies' ) );
	}

	/**
	 * Convert a frequency key into a repeat interval in days.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $frequency Frequency key.
	 * @return int|null Interval in days, or null when the frequency does not repeat.
	 */
	public function get_frequency_interval( $frequency ) {
		$intervals = apply_filters(
			'iworks/aqualog/reminder/frequency/interval',
			array(
				'daily'           => 1,
				'twice_daily'     => 1,
				'every_other_day' => 2,
				'weekly'          => 7,
				'twice_monthly'   => 15,
				'monthly'         => 30,
				'quarterly'       => 91,
				'semi_annually'   => 182,
				'annually'        => 365,
				'on_demand'       => null,
				'other'           => null,
			)
		);
		return isset( $intervals[ $frequency ] ) ? $intervals[ $frequency ] : null;
	}

	/**
	 * Get reminders for an aquarium.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $aquarium_id Aquarium ID.
	 * @param int $limit       Number of reminders to retrieve.
	 * @return array List of upcoming reminders.
	 */
	public function get_reminders( $aquarium_id, $limit = 10 ) {
		global $wpdb;
		if ( empty( $aquarium_id ) ) {
			return array();
		}
		$sql     = sprintf(
			'SELECT * FROM %s WHERE aquarium_id = %%d AND reminder_date >= %%s ORDER BY reminder_date ASC LIMIT %%d',
			$wpdb->aquarium_log_reminder
		);
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $aquarium_id, current_time( 'mysql' ), $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( empty( $results ) || ! is_array( $results ) ) {
			return array();
		}
		return $results;
	}

	/**
	 * AJAX handler for adding a maintenance reminder.
	 *
	 * Mirrors the "add task" flow: validates the task type against the
	 * configured task types, inserts a row into the reminder table and
	 * logs the action.
	 *
	 * @since 1.0.0
	 */
	public function ajax_add_reminder() {
		check_ajax_referer( $this->get_meta_name( 'reminder_add' ) );
		if ( ! current_user_can( $this->get_this_capability() ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'PLUGIN_NAME' ) );
		}
		$this->check_option_object();
		$id        = intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) );
		$type      = sanitize_key( filter_input( INPUT_POST, 'type' ) );
		$frequency = sanitize_key( filter_input( INPUT_POST, 'frequency' ) );
		$date      = sanitize_text_field( filter_input( INPUT_POST, 'date' ) );
		/**
		 * validate aquarium
		 */
		if ( ! apply_filters( 'iworks/aqualog/post_type/aquarium/check/id', false, $id ) ) {
			wp_send_json_error( esc_html__( 'Invalid aquarium', 'PLUGIN_NAME' ) );
		}
		/**
		 * validate task type
		 */
		$config = $this->options->get_group( 'maintenance_tasks' );
		if ( ! array_key_exists( $type, $config ) ) {
			wp_send_json_error( esc_html__( 'Invalid task type', 'PLUGIN_NAME' ) );
		}
		/**
		 * validate frequency
		 */
		$frequencies = $this->options->get_group( 'frequencies' );
		if ( ! array_key_exists( $frequency, $frequencies ) ) {
			wp_send_json_error( esc_html__( 'Invalid frequency', 'PLUGIN_NAME' ) );
		}
		/**
		 * sanitize
		 */
		if ( empty( $date ) ) {
			wp_send_json_error( esc_html__( 'Invalid date', 'PLUGIN_NAME' ) );
		}
		$reminder_date     = gmdate( 'Y-m-d H:i:s', strtotime( $date ) );
		$reminder_interval = $this->get_frequency_interval( $frequency );
		/**
		 * Filter the data before inserting into the database.
		 *
		 * @since 1.0.0
		 * @param array $data The data to insert.
		 * @return array The filtered data.
		 */
		$data   = apply_filters(
			'iworks/aqualog/reminder/sql/insert/data',
			array(
				'user_id'           => get_current_user_id(),
				'aquarium_id'       => $id,
				'reminder_type'     => $type,
				'reminder_date'     => $reminder_date,
				'reminder_interval' => $reminder_interval,
			)
		);
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->aquarium_log_reminder,
			$data,
			array( '%d', '%d', '%s', '%s', is_null( $reminder_interval ) ? null : '%d' )
		);
		if ( $result ) {
			$this->log_reminder_added( $id, $type, $reminder_date, $reminder_interval );
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Reminder added successfully', 'PLUGIN_NAME' ),
				)
			);
		}
		wp_send_json_error( esc_html__( 'Failed to add reminder', 'PLUGIN_NAME' ) );
	}

	/**
	 * Log reminder addition.
	 *
	 * @since 1.0.0
	 * @param int      $aquarium_id Aquarium ID.
	 * @param string   $type        Task type key.
	 * @param string   $date        Reminder date.
	 * @param int|null $interval    Repeat interval in days.
	 * @return void
	 */
	private function log_reminder_added( $aquarium_id, $type, $date = null, $interval = null ) {
		$logger = new iworks_aqualog_logger();
		$logger->log_reminder_added( $aquarium_id, $type, $date, $interval );
	}

	/**
	 * Log maintenance task update.
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID.
	 * @param string $title       Task title.
	 * @param string $type        Task type key.
	 * @param string $status      Task status.
	 * @param string $date        Task date.
	 * @return void
	 */
	private function log_maintenance_updated( $aquarium_id, $title, $type, $status, $date = null ) {
		$logger = new iworks_aqualog_logger();
		$logger->log_maintenance_updated( $aquarium_id, $title, $type, $status, $date );
	}

	/**
	 * Log maintenance task addition.
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID.
	 * @param string $title       Task title.
	 * @param string $type        Task type key.
	 * @param string $date        Task date.
	 * @return void
	 */
	private function log_maintenance_added( $aquarium_id, $title, $type, $date = null ) {
		$logger = new iworks_aqualog_logger();
		$logger->log_maintenance_added( $aquarium_id, $title, $type, $date );
	}
}
