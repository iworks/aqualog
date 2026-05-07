<?php
/**
 * AquaLog Statistics Class
 *
 * Handles statistics-related functionality for the AquaLog plugin.
 * This includes dashboard statistics, data analysis, and reporting.
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
 * AquaLog Statistics Class
 *
 * Manages statistics and data analysis for the AquaLog plugin.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_statistics extends iworks_aqualog_base {

	/**
	 * Class constructor.
	 *
	 * Initializes the statistics class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_aquariums' ), 10 );
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_water_entries' ), 20 );
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_ph_readings' ), 30 );
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_maintenance_tasks' ), 40 );
	}

	/**
	 * Render statistics.
	 *
	 * Displays statistics cards on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	private function render_card( $class, $title, $value, $icon ) {
		$this->load_template(
			'statistic-card',
			'elements',
			false,
			array(
				'class' => $class,
				'title' => $title,
				'value' => $value,
				'icon'  => $icon,
			)
		);
	}

	public function render_aquariums() {
		$this->render_card(
			'aquariums',
			__( 'Total Aquariums', 'aqualog' ),
			wp_count_posts( 'iw_aquarium' )->publish,
			'button'
		);
	}

	public function render_water_entries() {
		$this->render_card(
			'water-entries',
			__( 'Water Entries', 'aqualog' ),
			0,
			'chart-line'
		);
	}

	public function render_ph_readings() {
		$this->render_card(
			'ph-readings',
			__( 'pH Readings', 'aqualog' ),
			0,
			'clipboard'
		);
	}

	public function render_maintenance_tasks() {
		$this->render_card(
			'maintenance',
			__( 'Maintenance Tasks', 'aqualog' ),
			0,
			'hammer'
		);
	}
}
