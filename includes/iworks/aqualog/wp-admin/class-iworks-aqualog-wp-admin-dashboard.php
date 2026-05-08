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
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
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
		 * WordPress hooks.
		 */
		add_action( 'admin_notices', array( $this, 'display_admin_dashboard_message' ) );
		/**
		 * Aqualog plugin action hooks for dashboard functionality.
		 *
		 * @since 1.0.0
		 */
		add_action( 'aqualog/wp-admin/page/dashboard', array( $this, 'render_page' ) );
		add_filter( 'aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	public function render_page() {
		// Get recent aquariums data
		$recent_aquariums = $this->get_recent_aquariums( 5 );
		$all_aquariums    = $this->get_recent_aquariums( -1 ); // Get all aquariums

		$this->load_template(
			'dashboard',
			'pages',
			true,
			array(
				'recent_aquariums' => $recent_aquariums,
				'all_aquariums'    => $all_aquariums,
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
		$data['nonces']['dashboard'] = array(
			'dismiss_message' => wp_create_nonce( 'iworks_aqualog_dismiss_message' ),
		);
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

	/**
	 * Display admin dashboard message.
	 *
	 * Shows a dismissible admin notice with welcome content. The message
	 * will only be displayed if the user hasn't dismissed it previously.
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
		$user_id   = get_current_user_id();
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
