<?php
/**
 * AquaLog Dashboard Class
 *
 * Handles all dashboard-related functionality for the AquaLog plugin.
 * This includes managing dashboard widgets, statistics display, and
 * dashboard message handling.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @version    1.0.0
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __DIR__, 2 ) . '/class-iworks-aqualog-base.php';

/**
 * AquaLog Dashboard Class
 *
 * Manages dashboard functionality including statistics, recent activity,
 * and dashboard message handling for the AquaLog plugin.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_dashboard extends iworks_aqualog_base {

	/**
	 * Available dashboard widgets with their properties.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $widgets = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the dashboard class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Aqualog plugin action hooks for dashboard functionality.
		 *
		 * @since 1.0.0
		 */
		add_action( 'aqualog/wp-admin/dashboard_page', array( $this, 'render_page' ) );
		add_filter( 'aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	public function render_page() {
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_statistics' ) );
		add_action( 'aqualog/dashboard/recent_activity', array( $this, 'render_recent_activity' ) );
		add_action( 'aqualog/dashboard/before', array( $this, 'display_admin_dashboard_message' ) );
		$file = $this->get_template_file( 'dashboard', 'pages' );
		if ( $file ) {
			load_template( $file );
		}
	}

	/**
	 * Filter WordPress localize script data for dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Localize script data.
	 * @return array Filtered localize script data.
	 */
	public function filter_wp_localize_script( $data ) {
		$this->set_current_aquarium_id();
		$data['nonces']['dashboard'] = array(
			'dismiss_message' => wp_create_nonce( 'iworks_aqualog_dismiss_message' ),
		);
		return $data;
	}

	/**
	 * Render dashboard statistics.
	 *
	 * @since 1.0.0
	 */
	public function render_statistics() {
		$this->set_current_aquarium_id();
		// Statistics rendering logic here
	}

	/**
	 * Render recent activity.
	 *
	 * @since 1.0.0
	 */
	public function render_recent_activity() {
		$activities = $this->get_recent_activity();

		if ( empty( $activities ) ) {
			echo '<p>' . esc_html__( 'No recent activity found.', 'aqualog' ) . '</p>';
			return;
		}

		foreach ( $activities as $activity ) {
			?>
			<div class="aqualog-activity-item">
				<strong>
					<?php 
					if ( ! empty( $activity['edit_link'] ) ) {
						echo '<a href="' . esc_url( $activity['edit_link'] ) . '">' . esc_html( $activity['title'] ) . '</a>';
					} else {
						echo esc_html( $activity['title'] );
					}
					?>
				</strong>
				<br>
				<span class="aqualog-activity-meta">
					<?php 
					printf(
						/* translators: %1$s: date, %2$s: time */
						esc_html__( 'Created on %1$s at %2$s', 'aqualog' ),
						esc_html( $activity['date'] ),
						esc_html( $activity['time'] )
					);
					?>
				</span>
			</div>
			<?php
		}
	}

	/**
	 * Get recent activity items.
	 *
	 * Retrieves recent activity items for display on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $limit Number of items to retrieve (optional, default 5).
	 * @return array Array of recent activity items.
	 */
	public function get_recent_activity( $limit = 5 ) {
		// Get recent aquarium posts
		$recent_posts = get_posts( array(
			'post_type'      => 'iw_aquarium',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$activities = array();

		if ( ! empty( $recent_posts ) ) {
			foreach ( $recent_posts as $post ) {
				$activities[] = array(
					'title'     => get_the_title( $post->ID ),
					'date'      => get_the_date( get_option( 'date_format' ), $post->ID ),
					'time'      => get_the_date( get_option( 'time_format' ), $post->ID ),
					'type'      => 'aquarium_created',
					'post_id'   => $post->ID,
					'edit_link' => get_edit_post_link( $post->ID ),
				);
			}
		}

		/**
		 * Filter the recent activity items.
		 *
		 * @since 1.0.0
		 * @param array $activities Array of activity items.
		 * @param int   $limit      Number of items requested.
		 */
		return apply_filters( 'aqualog/activity/recent_items', $activities, $limit );
	}

	/**
	 * Display admin dashboard message.
	 *
	 * Shows a dismissible admin notice with welcome content. The message
	 * will only be displayed if the user hasn't dismissed it previously.
	 * Includes AJAX handler for dismissal functionality.
	 *
	 * @since 1.0.0
	 * @action admin_notices
	 * @return  void
	 */
	public function display_admin_dashboard_message() {
		// Only show on admin dashboard page
		global $pagenow;
		if ( 'index.php' !== $pagenow ) {
			return;
		}

		// Only show to users with proper capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if user has dismissed this message
		$user_id = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'iworks_aqualog_dashboard_message_dismissed', true );

		if ( $dismissed ) {
			return;
		}
		?>
		<div id="iworks-aqualog-dashboard-message" class="notice notice-info is-dismissible">
			<h3><?php esc_html_e( 'Welcome to AquaLog!', 'aqualog' ); ?></h3>
			<p>
				<?php 
				esc_html_e( 'Thank you for installing AquaLog! This powerful plugin helps you manage and track your water-related activities with ease. Here are some quick tips to get you started:', 'aqualog' ); 
				?>
			</p>
			<ul>
				<li><?php esc_html_e( 'Navigate to the AquaLog settings page to configure your preferences', 'aqualog' ); ?></li>
				<li><?php esc_html_e( 'Add your first water entry to start tracking your daily consumption', 'aqualog' ); ?></li>
				<li><?php esc_html_e( 'Check out the analytics dashboard to view your water usage patterns', 'aqualog' ); ?></li>
				<li><?php esc_html_e( 'Set up reminders to help you stay hydrated throughout the day', 'aqualog' ); ?></li>
			</ul>
			<p>
				<strong><?php esc_html_e( 'Pro Tip:', 'aqualog' ); ?></strong> 
				<?php esc_html_e( 'Regular water tracking can improve your health and wellbeing. Make it a daily habit!', 'aqualog' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aqualog/admin/index.php' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Get Started', 'aqualog' ); ?>
				</a>
				<a href="<?php echo esc_url( _x( 'https://wordpress.org/plugins/aqualog/', 'plugin homepage', 'aqualog' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'Learn More', 'aqualog' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

}
