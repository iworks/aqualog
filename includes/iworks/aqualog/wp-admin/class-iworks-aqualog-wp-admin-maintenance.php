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
			'tasks' => $this->get_task_types(),
		);
		$data['nonces']['maintenance'] = array(
			'add'  => wp_create_nonce( $this->get_meta_name( 'maintenance_add' ) ),
			'save' => wp_create_nonce( $this->get_meta_name( 'maintenance_save' ) ),
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
		$results  = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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
			<?php if ( isset( $_REQUEST['deleted'] ) && $_REQUEST['deleted'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
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
		$start_date = $date ? date( 'Y-m-d H:i:s', strtotime( $date ) ) : current_time( 'mysql' );
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
