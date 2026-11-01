<?php
/**
 * Aqualog Maintenance Tasks List Table
 *
 * Displays maintenance tasks (wp_aquarium_log_maintenance rows)
 * using the WordPress WP_List_Table API.
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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Aqualog Maintenance Tasks List Table Class
 *
 * @since 1.0.0
 */
class iworks_aqualog_maintenance_list_table extends WP_List_Table {

private $options;

	/**
	 * Allowed status values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $statuses = array( 'pending', 'in_progress', 'completed' );

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'maintenance-task',
				'plural'   => 'maintenance-tasks',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get list of columns.
	 *
	 * @since 1.0.0
	 * @return array Columns.
	 */
	public function get_columns() {
		return array(
			'cb'                 => '<input type="checkbox" />',
			'title'              => esc_html__( 'Title', 'PLUGIN_NAME' ),
			'type'               => esc_html__( 'Type', 'PLUGIN_NAME' ),
			'aquarium_id'        => esc_html__( 'Aquarium', 'PLUGIN_NAME' ),
			'status'             => esc_html__( 'Status', 'PLUGIN_NAME' ),
			'start_date'         => esc_html__( 'Start Date', 'PLUGIN_NAME' ),
			'next_schedule_date' => esc_html__( 'Next Schedule', 'PLUGIN_NAME' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @since 1.0.0
	 * @return array Sortable columns.
	 */
	protected function get_sortable_columns() {
		return array(
			'title'              => array( 'title', false ),
			'type'               => array( 'type', false ),
			'aquarium_id'        => array( 'aquarium_id', false ),
			'status'             => array( 'status', false ),
			'start_date'         => array( 'start_date', true ),
			'next_schedule_date' => array( 'next_schedule_date', false ),
		);
	}

	/**
	 * Get bulk actions.
	 *
	 * @since 1.0.0
	 * @return array Bulk actions.
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => esc_html__( 'Delete', 'PLUGIN_NAME' ),
		);
	}

	/**
	 * Get status views (All / Pending / In Progress / Completed).
	 *
	 * @since 1.0.0
	 * @return array Views.
	 */
	protected function get_views() {
		global $wpdb;
		$current = sanitize_key( filter_input( INPUT_GET, 'status' ) );
		$counts  = $wpdb->get_results( sprintf( 'SELECT status, COUNT(*) AS num FROM %s GROUP BY status', $wpdb->aquarium_log_maintenance ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$by      = array();
		$total   = 0;
		foreach ( (array) $counts as $row ) {
			$by[ $row['status'] ] = intval( $row['num'] );
			$total               += intval( $row['num'] );
		}
		$base  = remove_query_arg( array( 'status', 'paged', 's', 'action', 'action2', 'task', '_wpnonce' ) );
		$views = array(
			'all' => sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( $base ),
				'' === $current ? ' class="current" aria-current="page"' : '',
				esc_html__( 'All', 'PLUGIN_NAME' ),
				$total
			),
		);
		foreach ( $this->statuses as $status ) {
			if ( ! isset( $by[ $status ] ) ) {
				continue;
			}
			$views[ $status ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( add_query_arg( 'status', $status, $base ) ),
				$status === $current ? ' class="current" aria-current="page"' : '',
				esc_html( ucwords( str_replace( '_', ' ', $status ) ) ),
				$by[ $status ]
			);
		}
		return apply_filters( 'iworks/aqualog/maintenance/list/views', $views );
	}

	/**
	 * Process single and bulk delete actions.
	 *
	 * @since 1.0.0
	 */
	public function process_actions() {
		global $wpdb;
		$action = $this->current_action();
		if ( empty( $action ) ) {
			return;
		}
		$ids = array();
		if ( 'delete' === $action ) {
			$task = filter_input( INPUT_GET, 'task', FILTER_SANITIZE_NUMBER_INT );
			if ( $task ) {
				$ids = array( intval( $task ) );
				check_admin_referer( 'aqualog_maintenance_delete' );
			} else {
				$ids = array_map( 'intval', (array) filter_input( INPUT_POST, 'maintenance-tasks', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ) );
				check_admin_referer( 'bulk-maintenance-tasks' );
			}
		}
		$ids = array_filter( $ids );
		if ( empty( $ids ) ) {
			return;
		}
		foreach ( $ids as $id ) {
			$wpdb->delete( $wpdb->aquarium_log_maintenance, array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
		}
		do_action( 'iworks/aqualog/maintenance/tasks_deleted', $ids );
		wp_safe_redirect( add_query_arg( 'deleted', count( $ids ), remove_query_arg( array( 'action', 'action2', 'task', '_wpnonce', '_wp_http_referer' ) ) ) );
		exit;
	}

	/**
	 * Prepare items for display: actions, filters, pagination and query.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		global $wpdb;
		$per_page = 20;
		/**
		 * Filters the number of tasks shown per page.
		 *
		 * @since 1.0.0
		 * @param int $per_page Number of tasks per page.
		 */
		$per_page = intval( apply_filters( 'iworks/aqualog/maintenance/list/per_page', $per_page ) );
		/**
		 * columns
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		/**
		 * filtering
		 */
		$where  = array( '1=1' );
		$params = array();
		$status = sanitize_key( filter_input( INPUT_GET, 'status' ) );
		if ( in_array( $status, $this->statuses, true ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}
		$search = sanitize_text_field( filter_input( INPUT_GET, 's' ) );
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '( title LIKE %s OR description LIKE %s )';
			$params[] = $like;
			$params[] = $like;
		}
		/**
		 * ordering
		 */
		$orderby         = sanitize_key( filter_input( INPUT_GET, 'orderby' ) );
		$allowed_orderby = array( 'title', 'type', 'aquarium_id', 'status', 'start_date', 'next_schedule_date' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'start_date';
		}
		$order   = 'desc' === strtolower( sanitize_key( filter_input( INPUT_GET, 'order' ) ) ) ? 'DESC' : 'ASC';
		$orderby = sprintf( 'COALESCE( %s, created_at ) %s', $orderby, $order );
		/**
		 * total + pagination
		 */
		$sql_count   = sprintf( 'SELECT COUNT(*) FROM %s WHERE %s', $wpdb->aquarium_log_maintenance, implode( ' AND ', $where ) );
		$total_items = intval( $wpdb->get_var( 0 === count( $params ) ? $sql_count : $wpdb->prepare( $sql_count, $params ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$paged       = max( 1, intval( filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) ) );
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => intval( ceil( $total_items / $per_page ) ),
			)
		);
		/**
		 * items
		 */
		$params[] = $per_page;
		$params[] = ( $paged - 1 ) * $per_page;
		$sql      = sprintf(
			'SELECT * FROM %s WHERE %s ORDER BY %s LIMIT %%d OFFSET %%d',
			$wpdb->aquarium_log_maintenance,
			implode( ' AND ', $where ),
			$orderby
		);
		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		if ( empty( $this->items ) ) {
			$this->items = array();
		}
	}

	/**
	 * Message when no items found.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No maintenance tasks found.', 'PLUGIN_NAME' );
	}

	/**
	 * Checkbox column.
	 *
	 * @since 1.0.0
	 * @param array $item Row data.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="maintenance-tasks[]" value="%d" />', intval( $item['id'] ) );
	}

	/**
	 * Title column with row actions.
	 *
	 * @since 1.0.0
	 * @param array $item Row data.
	 * @return string
	 */
	protected function column_title( $item ) {
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'delete',
					'task'   => intval( $item['id'] ),
					'paged'  => filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ),
				)
			),
			'aqualog_maintenance_delete'
		);
		$actions = array(
			'delete' => sprintf( '<a href="%s">%s</a>', esc_url( $delete_url ), esc_html__( 'Delete', 'PLUGIN_NAME' ) ),
		);
		return sprintf(
			'<strong>%s</strong>%s',
			esc_html( $item['title'] ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Default column rendering.
	 *
	 * @since 1.0.0
	 * @param array  $item        Row data.
	 * @param string $column_name Column name.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		$this->check_option_object();
		switch ( $column_name ) {
			case 'type':
				$title = $item['type'];
				$config = $this->options->get_group( 'maintenance_tasks' );
				if ( isset( $config[ $item['type'] ]['title'] ) ) {
					$title = $config[ $item['type'] ]['title'];
				}
				return esc_html( $title );
			case 'aquarium_id':
				$aquarium_id = intval( $item['aquarium_id'] );
				$title       = get_the_title( $aquarium_id );
				$edit_link   = get_edit_post_link( $aquarium_id );
				if ( $title && $edit_link ) {
					return sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html( $title ) );
				}
				return esc_html( $title ? $title : sprintf( '#%d', $aquarium_id ) );
			case 'status':
				return sprintf(
					'<span class="aqualog-task-status status-%1$s">%2$s</span>',
					esc_attr( $item['status'] ),
					esc_html( ucwords( str_replace( '_', ' ', $item['status'] ) ) )
				);
			case 'start_date':
			case 'next_schedule_date':
				if ( empty( $item[ $column_name ] ) ) {
					return '&mdash;';
				}
				return esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', strtotime( $item[ $column_name ] ) ) );
		}
		return '';
	}

	/**
	 * Check option object.
	 *
	 * Ensures the options object is properly initialized.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	private function check_option_object() {
		if ( is_a( $this->options, 'iworks_options' ) ) {
			return;
		}
		$this->options = iworks_aqualog_get_options();
	}
}
