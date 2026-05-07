<?php
/**
 * AquaLog Chemistry Class
 *
 * Handles all chemistry-related functionality for the AquaLog plugin.
 * This includes managing water parameter measurements, calculations,
 * and chemistry data storage/retrieval.
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
 * AquaLog Chemistry Class
 *
 * Manages water chemistry parameters, measurements, and calculations
 * for aquarium tracking and analysis.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_maintenance extends iworks_aqualog_base {

	/**
	 * Available maintenance parameters with their properties.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $parameters = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the chemistry class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Aqualog plugin action hook for chemistry page rendering.
		 *
		 * @since 1.0.0
		 */
		add_action( 'aqualog/wp-admin/maintenance_page', array( $this, 'render_page' ) );
		add_filter( 'aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	/**
	 * Filter WordPress localize script data for chemistry page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Localize script data.
	 * @return array Filtered localize script data.
	 */
	public function filter_wp_localize_script( $data ) {
		$this->set_current_aquarium_id();
		$data['nonces']['maintenance'] = array(
			'add'  => wp_create_nonce( $this->get_meta_name( 'maintenance_add' ) ),
			'save' => wp_create_nonce( $this->get_meta_name( 'maintenance_save' ) ),
		);
		return $data;
	}

	/**
	 * Render chemistry page.
	 *
	 * @since 1.0.0
	 */
	public function render_page() {
		$this->set_current_aquarium_id();
		$this->load_template(
			'maintenance',
			'pages',
			true,
			array(
				'aquarium_id' => $this->current_aquarium_id,
				'messages'    => apply_filters( 'aqualog/wp-admin/messages/files', array() ),
				'meta'        => get_post_meta( $this->current_aquarium_id ),
				'tasks'       => array(),
				'completed'   => array(),
				'scheduled'   => array(),
			)
		);
	}
}
