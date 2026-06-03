<?php
/**
 * Aqualog Aquarium Class
 *
 * Handles all aquarium-related functionality for the Aqualog plugin.
 * This includes managing aquarium widgets, statistics display, and
 * aquarium message handling.
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
 * Aqualog Aquarium Class
 *
 * Manages aquarium functionality including statistics, recent activity,
 * and aquarium message handling for the Aqualog plugin.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_aquarium extends iworks_aqualog_base {

	/**
	 * Available aquarium widgets with their properties.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $widgets = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the aquarium class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * WordPress hooks.
		 */
		/**
		 * Aqualog plugin action hooks for aquarium functionality.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks/aqualog/wp-admin/page/aquarium', array( $this, 'render_page' ) );
	}

	public function render_page() {
		$this->set_current_aquarium_id();
		$args = apply_filters(
			'iworks/aqualog/wp-admin/aquarium/args',
			array()
		);
		$this->load_template(
			'aquarium',
			'pages',
			true,
			$args,
		);
	}

}
