<?php
/**
 * iWorks Aquarium Log Dashboard Class
 *
 * Handles all dashboard-related functionality for the iWorks Aquarium Log plugin.
 * This includes managing dashboard widgets, statistics display, and
 * dashboard message handling.
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
 * iWorks Aquarium Log Dashboard Class
 *
 * Manages dashboard functionality including statistics, recent activity,
 * and dashboard message handling for the iWorks Aquarium Log plugin.
 *
 * @since 1.0.0
 */
class iworks_aquarium_log_wp_admin_dashboard extends iworks_aquarium_log_base {

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
		 * WordPress hooks.
		 */
		/**
		 * Aqualog plugin action hooks for dashboard functionality.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks-aquarium-log/wp-admin/page/dashboard', array( $this, 'render_page' ) );
		add_filter( 'iworks-aquarium-log/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	public function render_page() {
		// Get recent aquariums data
		$recent_aquariums = $this->get_recent_aquariums( 5 );
		$all_aquariums    = $this->get_recent_aquariums( -1 ); // Get all aquariums

		$this->load_template(
			'dashboard',
			'pages',
			true,
			apply_filters(
				'iworks-aquarium-log/dashboard/template_args',
				array(
					'recent_aquariums' => $recent_aquariums,
					'all_aquariums'    => $all_aquariums,
				)
			)
		);
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
		return $data;
	}

	/**
	 * Get recent aquariums sorted by last update time.
	 *
	 * @since 1.0.0
	 * @param int $limit Number of aquariums to retrieve (-1 for all).
	 * @return array Array of aquarium post objects.
	 */
	private function get_recent_aquariums( $limit = 5 ) {
		$args = array(
			'post_type'      => 'iw_aquarium',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_related_updated_at',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
		);

		// If no meta value exists, fall back to post date
		$args_allback = array(
			'post_type'      => 'iw_aquarium',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$posts = get_posts( $args );

		// If no posts found with meta value, try fallback
		if ( empty( $posts ) && $limit > 0 ) {
			$posts = get_posts( $args_allback );
		}

		return $posts;
	}
}
