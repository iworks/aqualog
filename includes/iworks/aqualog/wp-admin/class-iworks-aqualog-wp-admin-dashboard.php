<?php
/**
 * Aqualog Dashboard Class
 *
 * Handles all dashboard-related functionality for the Aqualog plugin.
 * This includes managing dashboard widgets, statistics display, and
 * dashboard message handling.
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
 * Aqualog Dashboard Class
 *
 * Manages dashboard functionality including statistics, recent activity,
 * and dashboard message handling for the Aqualog plugin.
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
		/**
		 * Aqualog plugin action hooks for dashboard functionality.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks/aqualog/wp-admin/page/dashboard', array( $this, 'render_page' ) );
		add_filter( 'iworks/aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	public function render_page() {
		$this->set_current_aquarium_id();
		$args = apply_filters(
			'iworks/aqualog/wp-admin/dashboard/args',
			array()
		);
		$this->load_template(
			'dashboard',
			'pages',
			true,
			$args,
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
}
