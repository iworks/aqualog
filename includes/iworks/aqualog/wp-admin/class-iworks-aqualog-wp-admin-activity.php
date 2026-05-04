<?php
/**
 * AquaLog Activity Class
 *
 * Handles activity-related functionality for the AquaLog plugin.
 * This includes displaying recent activity and managing activity logs.
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
 * AquaLog Activity Class
 *
 * Manages activity logging and display for the AquaLog plugin.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_activity extends iworks_aqualog_base {

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
		add_action( 'aqualog/dashboard/recent_activity', array( $this, 'render_recent_activity' ) );
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
						esc_html__( 'Created on %s at %s', 'aqualog' ),
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
