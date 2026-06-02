<?php
/**
 * iWorks Aquarium Log Activity Class
 *
 * Handles activity-related functionality for the iWorks Aquarium Log plugin.
 * This includes displaying recent activity and managing activity logs.
 *
 * @package    iWorks
 * @subpackage iWorks Aquarium Log
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __DIR__, 2 ) . '/class-iworks-aquarium-log-base.php';

/**
 * iWorks Aquarium Log Activity Class
 *
 * Manages activity logging and display for the iWorks Aquarium Log plugin.
 *
 * @since 1.0.0
 */
class iworks_aquarium_log_wp_admin_activity extends iworks_aquarium_log_base {

	/**
	 * Class constructor.
	 *
	 * Initializes the activity class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'iworks-aquarium-log/dashboard/recent_activity', array( $this, 'render_recent_activity' ) );
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
		$recent_posts = get_posts(
			array(
				'post_type'      => 'iw_aquarium',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

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
		return apply_filters( 'iworks-aquarium-log/activity/recent_items', $activities, $limit );
	}

	/**
	 * Render recent activity.
	 *
	 * Displays recent activity items on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function render_recent_activity() {
		$activities = $this->get_recent_activity();

		if ( empty( $activities ) ) {
			echo '<p>' . esc_html__( 'No recent activity found.', 'iworks-aquarium-log' ) . '</p>';
			return;
		}

		foreach ( $activities as $activity ) {
			?>
			<div class="aquarium-log-activity-item">
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
				<span class="aquarium-log-activity-meta">
					<?php
					printf(
						/* translators: %1$s: date, %2$s: time */
						esc_html__( 'Created on %1$s at %2$s', 'iworks-aquarium-log' ),
						esc_html( $activity['date'] ),
						esc_html( $activity['time'] )
					);
					?>
				</span>
			</div>
			<?php
		}
	}
}