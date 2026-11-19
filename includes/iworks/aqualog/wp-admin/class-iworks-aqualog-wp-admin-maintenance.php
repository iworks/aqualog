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
		 * AJAX handler for deleting a maintenance task (optionally with
		 * all its future occurrences).
		 */
		add_action( 'wp_ajax_aquarium_log_maintenance_delete_task', array( $this, 'ajax_delete_maintenance_task' ) );
		/**
		 * AJAX handler for changing a calendar event status.
		 */
		add_action( 'wp_ajax_aquarium_log_calendar_event_status', array( $this, 'ajax_calendar_event_status' ) );
		/**
		 * Aqualog plugin action hook for maintenance page rendering.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks/aqualog/wp-admin/maintenance_page', array( $this, 'render_page' ) );
		add_filter( 'iworks/aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
		add_filter( 'iworks/aqualog/dashboard/upcoming_tasks', array( $this, 'filter_upcoming_tasks' ), 10, 3 );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
		add_filter( 'set_screen_option_aqualog_upcoming_days', array( $this, 'filter_set_screen_option' ), 10, 3 );
		add_filter( 'set_screen_option_aqualog_dashboard_scope', array( $this, 'filter_set_screen_option' ), 10, 3 );
		add_filter( 'screen_settings', array( $this, 'filter_screen_settings' ), 10, 2 );
		add_action( 'wp_loaded', array( $this, 'save_dashboard_scope' ) );
	}

	/**
	 * Add the aquarium scope selector to the dashboard Screen Options.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param string $screen_settings Screen settings HTML.
	 * @param WP_Screen $screen        Current screen.
	 * @return string Modified screen settings HTML.
	 */
	public function filter_screen_settings( $screen_settings, $screen ) {
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return $screen_settings;
		}
		if ( ! apply_filters( 'iworks/aqualog/load/module/maintenance', false ) ) {
			return $screen_settings;
		}
		$this->check_option_object();
		if ( ! intval( $this->options->get_option( 'notification_enabled' ) ) ) {
			return $screen_settings;
		}
		$scope = $this->is_all_aquariums_scope() ? 'all' : 'current';
		ob_start();
		?>
		<fieldset class="aqualog-screen-scope">
			<legend><?php esc_html_e( 'Aqualog tasks scope', 'PLUGIN_NAME' ); ?></legend>
			<label>
				<input type="radio" name="aqualog_dashboard_scope" value="current" <?php checked( 'current', $scope ); ?>>
				<?php esc_html_e( 'Current aquarium', 'PLUGIN_NAME' ); ?>
			</label>
			<label>
				<input type="radio" name="aqualog_dashboard_scope" value="all" <?php checked( 'all', $scope ); ?>>
				<?php esc_html_e( 'All aquariums', 'PLUGIN_NAME' ); ?>
			</label>
		</fieldset>
		<?php
		return $screen_settings . ob_get_clean();
	}

	/**
	 * Register WordPress dashboard widgets.
	 *
	 * Adds "Today's Tasks" and "Upcoming Tasks" widgets to the WP admin
	 * dashboard when notifications are enabled and the maintenance module
	 * is active.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return void
	 */
	public function register_dashboard_widgets() {
		if ( ! apply_filters( 'iworks/aqualog/load/module/maintenance', false ) ) {
			return;
		}
		$this->check_option_object();
		if ( ! intval( $this->options->get_option( 'notification_enabled' ) ) ) {
			return;
		}
		$this->set_current_aquarium_id();
		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Upcoming tasks days', 'PLUGIN_NAME' ),
				'default' => max( 1, intval( $this->options->get_option( 'notification_upcoming_days' ) ) ?: 3 ),
				'option'  => 'aqualog_upcoming_days',
			)
		);
		$days = $this->get_user_upcoming_days();
		$all  = $this->is_all_aquariums_scope();
		$this->register_dashboard_widget(
			'aqualog_dashboard_today_tasks',
			esc_html__( 'Aqualog: Today\'s Tasks', 'PLUGIN_NAME' ),
			$all
		);
		$this->register_dashboard_widget(
			'aqualog_dashboard_upcoming_tasks',
			sprintf(
				/* translators: %d: number of days */
				esc_html__( 'Aqualog: Upcoming Tasks (next %d days)', 'PLUGIN_NAME' ),
				$days
			),
			$all
		);
	}

	/**
	 * Register a dashboard widget when its scope has data to show.
	 *
	 * @since 1.1.0
	 * @access private
	 * @param string $id           Widget ID.
	 * @param string $title        Widget title.
	 * @param bool   $all_aquariums Whether the "all aquariums" scope is active.
	 * @return void
	 */
	private function register_dashboard_widget( $id, $title, $all_aquariums ) {
		if ( ! $all_aquariums && empty( $this->current_aquarium_id ) ) {
			return;
		}
		$callback = 'aqualog_dashboard_today_tasks' === $id
			? array( $this, 'render_dashboard_widget_today' )
			: array( $this, 'render_dashboard_widget_upcoming' );
		wp_add_dashboard_widget( $id, $title, $callback );
	}

	/**
	 * Is the per-user scope set to "all aquariums"?
	 *
	 * @since 1.1.0
	 * @access private
	 * @return bool True when all aquariums are shown.
	 */
	private function is_all_aquariums_scope() {
		return 'all' === get_user_option( 'aqualog_dashboard_scope' );
	}

	/**
	 * Get the scope value for queries: 0 means all aquariums.
	 *
	 * @since 1.1.0
	 * @access private
	 * @return int Aquarium ID or 0 for all.
	 */
	private function get_scope_aquarium_id() {
		return $this->is_all_aquariums_scope() ? 0 : intval( $this->current_aquarium_id );
	}

	/**
	 * Get the per-user "upcoming days" value.
	 *
	 * Uses the per-user screen option when set (saved via the dashboard
	 * "Screen Options" tab), falling back to the plugin setting.
	 *
	 * @since 1.1.0
	 * @access private
	 * @return int Number of days (minimum 1).
	 */
	private function get_user_upcoming_days() {
		$days = intval( get_user_option( 'aqualog_upcoming_days' ) );
		if ( $days < 1 ) {
			$days = intval( $this->options->get_option( 'notification_upcoming_days' ) );
		}
		return max( 1, $days ?: 3 );
	}

	/**
	 * Save the "upcoming days" screen option.
	 *
	 * Registered via the "screen_settings" filter by WordPress when a
	 * per_page screen option is declared; persists the per-user value.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param mixed $status Screen option status.
	 * @param mixed $option Option name.
	 * @param mixed $value  Submitted value.
	 * @return int|mixed Saved value.
	 */
	public function filter_set_screen_option( $status, $option, $value ) {
		if ( 'aqualog_upcoming_days' === $option ) {
			return max( 1, intval( $value ) ?: 3 );
		}
		if ( 'aqualog_dashboard_scope' === $option ) {
			return 'all' === $value ? 'all' : 'current';
		}
		return $status;
	}

	/**
	 * Save the aquarium scope choice submitted with the Screen Options form.
	 *
	 * The core Screen Options handler only persists its own option pair and
	 * redirects before `admin_init`, so the radio field is saved on `wp_loaded`.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return void
	 */
	public function save_dashboard_scope() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified below on the same request.
		if ( ! isset( $_POST['aqualog_dashboard_scope'], $_POST['screenoptionnonce'] ) ) {
			return;
		}
		$scope = sanitize_key( wp_unslash( $_POST['aqualog_dashboard_scope'] ) );
		if ( ! check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' ) ) {
			return;
		}
		update_user_meta( get_current_user_id(), 'aqualog_dashboard_scope', 'all' === $scope ? 'all' : 'current' );
	}

	/**
	 * Render the "Today's Tasks" dashboard widget.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return void
	 */
	public function render_dashboard_widget_today() {
		$this->render_dashboard_widget(
			$this->get_today_tasks( $this->get_scope_aquarium_id() )
		);
	}

	/**
	 * Render the "Upcoming Tasks" dashboard widget.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return void
	 */
	public function render_dashboard_widget_upcoming() {
		$this->render_dashboard_widget(
			$this->get_upcoming_tasks( $this->get_scope_aquarium_id(), $this->get_user_upcoming_days() )
		);
	}

	/**
	 * Render a dashboard widget body with scope awareness.
	 *
	 * In "current aquarium" scope the aquarium name is shown at the top;
	 * in "all aquariums" scope each row gets the aquarium name.
	 *
	 * @since 1.1.0
	 * @access private
	 * @param array $tasks List of tasks from get_upcoming_tasks().
	 * @return void
	 */
	private function render_dashboard_widget( $tasks ) {
		$all_aquariums = $this->is_all_aquariums_scope();
		if ( ! $all_aquariums ) {
			printf(
				'<p class="aqualog-widget-aquarium"><span class="dashicons dashicons-buddicons-groups"></span> %s</p>',
				esc_html( get_the_title( $this->current_aquarium_id ) )
			);
		}
		if ( empty( $tasks ) ) {
			echo '<p>' . esc_html__( 'No tasks.', 'PLUGIN_NAME' ) . '</p>';
			return;
		}
		echo '<ul class="aqualog-widget-tasks">';
		foreach ( $tasks as $task ) {
			printf(
				'<li class="%1$s"><span class="aqualog-widget-task-date">%2$s</span> %3$s%4$s</li>',
				esc_attr( ! empty( $task['overdue'] ) ? 'is-overdue' : '' ),
				esc_html(
					date_i18n( get_option( 'date_format' ), strtotime( $task['date'] ) ) .
					( empty( $task['full_day'] ) && $task['time'] ? ' ' . $task['time'] : '' )
				),
				esc_html( $task['title'] ),
				$all_aquariums && ! empty( $task['aquarium_id'] )
					? ' <span class="aqualog-widget-task-aquarium">(' . esc_html( get_the_title( $task['aquarium_id'] ) ) . ')</span>'
					: ''
			);
		}
		echo '</ul>';
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=aqualog-calendar' ) ) . '">';
		echo esc_html__( 'View calendar', 'PLUGIN_NAME' );
		echo '</a></p>';
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
		$frequency       = sanitize_key( filter_input( INPUT_POST, 'frequency' ) );
		$repeat_days     = intval( filter_input( INPUT_POST, 'repeat_days', FILTER_VALIDATE_INT ) ?: 0 );
		$repeat_interval = $this->get_frequency_interval( $frequency );
		if ( 'custom' === $frequency && $repeat_days > 0 ) {
			$repeat_interval = $repeat_days;
		}
		$full_day       = filter_input( INPUT_POST, 'full_day', FILTER_VALIDATE_BOOLEAN );
		/**
		 * Full day events are stored at midnight: the date-only input
		 * value parses to 00:00:00 automatically.
		 */
		$start_date     = $date ? gmdate( 'Y-m-d H:i:s', strtotime( $date ) ) : current_time( 'mysql' );
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
				'aquarium_id'     => $id,
				'type'            => $type,
				'title'           => $title,
				'description'     => $description,
				'status'          => $status,
				'start_date'      => $start_date,
				'repeat_interval' => $repeat_interval,
				'full_day'        => $full_day ? 1 : 0,
			)
		);
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->aquarium_log_maintenance,
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', is_null( $repeat_interval ) ? null : '%d', '%d' )
		);
		if ( $result ) {
			/**
			 * Repeatable task: materialize occurrences as separate rows up
			 * to the configured horizon ("how far in advance should tasks
			 * be scheduled", in months). Each row keeps the series root ID
			 * so the whole series can be managed together.
			 */
			if ( ! is_null( $repeat_interval ) && $repeat_interval > 0 ) {
				$series_id = intval( $wpdb->insert_id );
				$horizon   = strtotime( sprintf( '+%d months', $this->get_schedule_months() ), strtotime( gmdate( 'Y-m-d 23:59:59', current_time( 'timestamp' ) ) ) );
				$current   = strtotime( $start_date . ' +' . $repeat_interval . ' days' );
				$rows      = array();
				while ( $current <= $horizon ) {
					$rows[] = array(
						'aquarium_id'     => $id,
						'series_id'       => $series_id,
						'type'            => $type,
						'title'           => $title,
						'description'     => $description,
						'status'          => $status,
						'start_date'      => gmdate( 'Y-m-d H:i:s', $current ),
						'repeat_interval' => null,
						'full_day'        => $full_day ? 1 : 0,
					);
					$current = strtotime( sprintf( '+%d days', $repeat_interval ), $current );
				}
				foreach ( $rows as $row ) {
					$wpdb->insert(
						$wpdb->aquarium_log_maintenance,
						$row,
						array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', null, '%d' )
					);
				}
			}
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
		$frequency       = sanitize_key( filter_input( INPUT_POST, 'frequency' ) );
		$repeat_days     = intval( filter_input( INPUT_POST, 'repeat_days', FILTER_VALIDATE_INT ) ?: 0 );
		$repeat_interval = $this->get_frequency_interval( $frequency );
		if ( 'custom' === $frequency && $repeat_days > 0 ) {
			$repeat_interval = $repeat_days;
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
				'type'            => $type,
				'title'           => $title,
				'description'     => $description,
				'status'          => $status,
				'start_date'      => $start_date,
				'repeat_interval' => $repeat_interval,
				'full_day'        => filter_input( INPUT_POST, 'full_day', FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
			)
		);		$result = $wpdb->update(
			$wpdb->aquarium_log_maintenance,
			$data,
			array( 'id' => $task_id ),
			array( '%s', '%s', '%s', '%s', '%s', is_null( $repeat_interval ) ? null : '%d', '%d' ),
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
	 * AJAX handler for deleting a maintenance task.
	 *
	 * Deletes a single task, or — for repeating tasks when requested —
	 * also all its future occurrences (per-occurrence statuses included).
	 *
	 * @since 1.1.0
	 * @access public
	 * @return void
	 */
	public function ajax_delete_maintenance_task() {
		check_ajax_referer( $this->get_meta_name( 'maintenance_save' ) );
		if ( ! current_user_can( $this->get_this_capability() ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'PLUGIN_NAME' ) );
		}
		$this->check_option_object();
		$task_id = intval( filter_input( INPUT_POST, 'task_id', FILTER_SANITIZE_NUMBER_INT ) );
		if ( $task_id < 1 ) {
			wp_send_json_error( esc_html__( 'Invalid task', 'PLUGIN_NAME' ) );
		}
		global $wpdb;
		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->aquarium_log_maintenance} WHERE id = %d",
				$task_id
			),
			ARRAY_A
		);
		if ( empty( $row ) || ! is_array( $row ) ) {
			wp_send_json_error( esc_html__( 'Invalid task', 'PLUGIN_NAME' ) );
		}
		$with_future = filter_input( INPUT_POST, 'with_future', FILTER_VALIDATE_BOOLEAN );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified above.
		$occurrence_date = sanitize_text_field( wp_unslash( $_POST['occurrence_date'] ?? '' ) );
		/**
		 * The series root stores series_id = NULL (only members carry
		 * the root ID), so when the deleted row is the root itself,
		 * the series ID is the row's own ID.
		 */
		$series_id = intval( $row['series_id'] ?? 0 );
		if ( 0 === $series_id && intval( $row['repeat_interval'] ?? 0 ) > 0 ) {
			$series_id = $task_id;
		}
		/**
		 * Occurrences are separate rows. "Delete future" removes this row
		 * plus every series row from the occurrence date onwards; past
		 * occurrences stay. Without "future" only this row is removed.
		 */
		if ( $with_future && $series_id > 0 ) {
			$from = ! empty( $occurrence_date ) ? gmdate( 'Y-m-d 00:00:00', strtotime( $occurrence_date ) ) : gmdate( 'Y-m-d 00:00:00', current_time( 'timestamp' ) );
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"DELETE FROM {$wpdb->aquarium_log_maintenance} WHERE ( id = %d OR series_id = %d ) AND start_date >= %s",
					$series_id,
					$series_id,
					$from
				)
			);
		} elseif ( $with_future ) {
			/**
			 * Legacy repeating row (no series): remove the row itself.
			 */
			$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->aquarium_log_maintenance,
				array( 'id' => $task_id ),
				array( '%d' )
			);
		} else {
			$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->aquarium_log_maintenance,
				array( 'id' => $task_id ),
				array( '%d' )
			);
		}
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->aquarium_log_maintenance_status,
			array( 'task_id' => $task_id ),
			array( '%d' )
		);
		$this->log_maintenance_deleted( intval( $row['aquarium_id'] ), $row['title'], $row['type'] );
		wp_send_json_success(
			array(
				'message' => esc_html__( 'Task deleted successfully', 'PLUGIN_NAME' ),
				'task_id' => $task_id,
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
	 * How far in advance repeating task occurrences are generated, in months.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return int Number of months (minimum 1).
	 */
	public function get_schedule_months() {
		$this->check_option_object();
		$months = intval( $this->options->get_option( 'maintenance_schedule_months' ) );
		return max( 1, $months ?: 3 );
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
	 * Provide upcoming tasks for the dashboard.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param array $tasks       Default (empty) task list.
	 * @param int   $aquarium_id Current aquarium ID.
	 * @param int   $days        Number of days ahead.
	 * @return array Upcoming tasks.
	 */
	public function filter_upcoming_tasks( $tasks, $aquarium_id, $days ) {
		return $this->get_upcoming_tasks( $aquarium_id, $days );
	}

	/**
	 * Get tasks due today for an aquarium.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param int $aquarium_id Aquarium ID.
	 * @param int $limit       Maximum number of tasks to return.
	 * @return array List of tasks due today.
	 */
	public function get_today_tasks( $aquarium_id, $limit = 10 ) {
		return $this->get_upcoming_tasks( $aquarium_id, 0, $limit );
	}

	/**
	 * Get upcoming tasks for an aquarium.
	 *
	 * Returns non-completed tasks due within the given number of days,
	 * including occurrences of repeating tasks (expanded day by day).
	 *
	 * @since 1.1.0
	 * @access public
	 * @param int $aquarium_id Aquarium ID.
	 * @param int $days        Number of days ahead to include.
	 * @param int $limit       Maximum number of tasks to return.
	 * @return array List of upcoming tasks.
	 */
	public function get_upcoming_tasks( $aquarium_id, $days = 7, $limit = 10 ) {
		global $wpdb;
		$all_aquariums = empty( $aquarium_id );
		$now           = current_time( 'timestamp' );
		if ( $days < 0 ) {
			$days = 0;
		}
		$to_time = strtotime( sprintf( '+%d days', $days ), $now );
		if ( $days < 1 ) {
			// "Today" — end of the current day.
			$to_time = strtotime( 'tomorrow midnight', $now ) - 1;
		}
		$where = ' WHERE status != %s AND start_date IS NOT NULL';
		$args  = array( 'completed' );
		if ( ! $all_aquariums ) {
			$where .= ' AND aquarium_id = %d';
			$args[] = $aquarium_id;
		}
		$sql     = sprintf(
			'SELECT * FROM %s%s ORDER BY start_date ASC',
			$wpdb->aquarium_log_maintenance,
			$where
		);
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return array();
		}
		$task_ids = wp_list_pluck( $rows, 'id' );
		$statuses = $this->get_occurrence_statuses( array_map( 'intval', $task_ids ) );
		$tasks    = array();
		foreach ( $rows as $row ) {
			$timestamp = strtotime( $row['start_date'] );
			if ( $timestamp < $now - DAY_IN_SECONDS ) {
				continue;
			}
			$date_key = gmdate( 'Y-m-d', $timestamp );
			$tasks[]  = array(
				'id'          => intval( $row['id'] ),
				'type'        => $row['type'],
				'title'       => $row['title'],
				'status'      => $statuses[ intval( $row['id'] ) ][ $date_key ] ?? $row['status'],
				'date'        => $date_key,
				'time'        => gmdate( 'H:i', $timestamp ),
				'full_day'    => ! empty( $row['full_day'] ),
				'overdue'     => $timestamp < $now,
				'series_id'   => intval( $row['series_id'] ?? 0 ),
				'aquarium_id' => intval( $row['aquarium_id'] ),
			);
			if ( count( $tasks ) >= $limit ) {
				break;
			}
		}
		usort(
			$tasks,
			function ( $a, $b ) {
				return strcmp( $a['date'] . $a['time'], $b['date'] . $b['time'] );
			}
		);
		return array_slice( $tasks, 0, $limit );
	}

	/**
	 * Get per-occurrence statuses for maintenance tasks.
	 *
	 * A repeating task is a single DB row expanded into many occurrences;
	 * the status of each occurrence is stored separately, keyed by
	 * task ID and occurrence date.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param array $task_ids List of task IDs.
	 * @return array Map: task_id => ( occurrence_date (Y-m-d) => status ).
	 */
	public function get_occurrence_statuses( $task_ids ) {
		global $wpdb;
		$task_ids = array_filter( array_map( 'intval', (array) $task_ids ) );
		if ( empty( $task_ids ) ) {
			return array();
		}
		$placeholders = implode( ', ', array_fill( 0, count( $task_ids ), '%d' ) );
		$sql          = sprintf(
			'SELECT task_id, occurrence_date, status FROM %s WHERE task_id IN ( %s )',
			$wpdb->aquarium_log_maintenance_status,
			$placeholders
		);
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, ...$task_ids ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return array();
		}
		$map = array();
		foreach ( $rows as $row ) {
			$map[ intval( $row['task_id'] ) ][ $row['occurrence_date'] ] = $row['status'];
		}
		return $map;
	}

	/**
	 * Save the status of a single task occurrence.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param int    $task_id   Task ID.
	 * @param string $date      Occurrence date (Y-m-d).
	 * @param string $status    Status (pending, in_progress, completed).
	 * @return bool True on success.
	 */
	public function save_occurrence_status( $task_id, $date, $status ) {
		global $wpdb;
		$task_id = intval( $task_id );
		$date    = gmdate( 'Y-m-d', strtotime( $date ) );
		if ( $task_id < 1 || empty( $date ) ) {
			return false;
		}
		$existing = intval(
			$wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->aquarium_log_maintenance_status} WHERE task_id = %d AND occurrence_date = %s",
					$task_id,
					$date
				)
			)
		);
		if ( $existing ) {
			$result = $wpdb->update(
				$wpdb->aquarium_log_maintenance_status,
				array(
					'status'  => $status,
					'user_id' => get_current_user_id(),
				),
				array( 'id' => $existing ),
				array( '%s', '%d' ),
				array( '%d' )
			);
			return false !== $result;
		}
		$result = $wpdb->insert(
			$wpdb->aquarium_log_maintenance_status,
			array(
				'task_id'         => $task_id,
				'occurrence_date' => $date,
				'status'          => $status,
				'user_id'         => get_current_user_id(),
			),
			array( '%d', '%s', '%s', '%d' )
		);
		return false !== $result;
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
	 * AJAX handler for changing a calendar event status.
	 *
	 * Updates the status of either a maintenance task or a reminder
	 * (both shown on the calendar), selected by the "kind" parameter.
	 *
	 * @since 1.1.0
	 */
	public function ajax_calendar_event_status() {
		check_ajax_referer( $this->get_meta_name( 'maintenance_save' ) );
		if ( ! current_user_can( $this->get_this_capability() ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'PLUGIN_NAME' ) );
		}
		$kind   = sanitize_key( filter_input( INPUT_POST, 'kind' ) );
		$id     = intval( filter_input( INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT ) );
		$status = sanitize_key( filter_input( INPUT_POST, 'status' ) );
		$allowed_status = array( 'pending', 'in_progress', 'completed' );
		if ( ! in_array( $status, $allowed_status, true ) ) {
			wp_send_json_error( esc_html__( 'Invalid status', 'PLUGIN_NAME' ) );
		}
		if ( $id < 1 ) {
			wp_send_json_error( esc_html__( 'Invalid event', 'PLUGIN_NAME' ) );
		}
		global $wpdb;
		if ( 'reminder' === $kind ) {
			$result = $wpdb->update(
				$wpdb->aquarium_log_reminder,
				array( 'reminder_status' => $status ),
				array( 'id' => $id ),
				array( '%s' ),
				array( '%d' )
			);
			if ( false === $result ) {
				wp_send_json_error( esc_html__( 'Failed to update status', 'PLUGIN_NAME' ) );
			}
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Status updated successfully', 'PLUGIN_NAME' ),
					'status'  => $status,
				)
			);
		}
		if ( 'task' !== $kind ) {
			wp_send_json_error( esc_html__( 'Invalid event kind', 'PLUGIN_NAME' ) );
		}
		$sql = sprintf(
			'SELECT * FROM %s WHERE id = %%d',
			$wpdb->aquarium_log_maintenance
		);
		$row = $wpdb->get_row( $wpdb->prepare( $sql, $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( empty( $row ) || ! is_array( $row ) ) {
			wp_send_json_error( esc_html__( 'Invalid event', 'PLUGIN_NAME' ) );
		}
		/**
		 * When an occurrence date is passed, only that occurrence is
		 * updated — repeating tasks expand into many events, and each
		 * keeps its own status. Without a date the base task is updated.
		 */
		$date = sanitize_text_field( filter_input( INPUT_POST, 'date' ) );
		if ( ! empty( $date ) ) {
			$updated = $this->save_occurrence_status( $id, $date, $status );
			if ( ! $updated ) {
				wp_send_json_error( esc_html__( 'Failed to update status', 'PLUGIN_NAME' ) );
			}
			$this->log_maintenance_updated( intval( $row['aquarium_id'] ), $row['title'], $row['type'], $status, $date );
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Status updated successfully', 'PLUGIN_NAME' ),
					'status'  => $status,
				)
			);
		}
		$result = $wpdb->update(
			$wpdb->aquarium_log_maintenance,
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
		if ( false === $result ) {
			wp_send_json_error( esc_html__( 'Failed to update status', 'PLUGIN_NAME' ) );
		}
		$this->log_maintenance_updated( intval( $row['aquarium_id'] ), $row['title'], $row['type'], $status );
		wp_send_json_success(
			array(
				'message' => esc_html__( 'Status updated successfully', 'PLUGIN_NAME' ),
				'status'  => $status,
			)
		);
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

	/**
	 * Log maintenance task deletion.
	 *
	 * @since 1.1.0
	 * @param int    $aquarium_id Aquarium ID.
	 * @param string $title       Task title.
	 * @param string $type        Task type key.
	 * @return void
	 */
	private function log_maintenance_deleted( $aquarium_id, $title, $type ) {
		$logger = new iworks_aqualog_logger();
		$logger->log_maintenance_deleted( $aquarium_id, $title, $type );
	}
}
