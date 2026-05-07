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
 * Manages statistics and data analysis for the AquaLog plugin dashboard.
 * Provides statistical cards and data visualization components.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_statistics extends iworks_aqualog_base {

	/**
	 * Class constructor.
	 *
	 * Initializes the statistics class and sets up WordPress hooks
	 * for rendering dashboard statistics components.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * WordPress action hooks for dashboard statistics rendering.
		 *
		 * @since 1.0.0
		 */
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_aquariums' ), 10 );
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_water_entries' ), 20 );
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_ph_readings' ), 30 );
		add_action( 'aqualog/dashboard/statistics', array( $this, 'render_maintenance_tasks' ), 40 );
	}

	/**
	 * Renders a statistics card component.
	 *
	 * Creates and displays a statistics card with the specified class,
	 * title, value, and icon using the template system.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $class CSS class name for the card.
	 * @param string $title Title text for the card.
	 * @param int|string $value Value to display in the card.
	 * @param string $icon Icon identifier for the card.
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

	/**
	 * Renders total aquariums statistics card.
	 *
	 * Displays the total number of published aquarium posts
	 * in a statistics card on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function render_aquariums() {
		$this->render_card(
			'aquariums',
			/* translators: Statistics card title for total aquariums */
			__( 'Total Aquariums', 'aqualog' ),
			wp_count_posts( 'iw_aquarium' )->publish,
			'button'
		);
	}

	/**
	 * Renders water entries statistics card.
	 *
	 * Displays the total number of water chemistry entries
	 * in a statistics card on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @global wpdb $wpdb WordPress database object.
	 * @return void
	 */
	public function render_water_entries() {
		if ( false === apply_filters( 'aqualog/load/wp-admin/statistics', false ) ) {
			return;
		}
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->aqualog_chemistry}" );
		$this->render_card(
			'water-entries',
			/* translators: Statistics card title for water entries */
			__( 'Water Entries', 'aqualog' ),
			$count,
			'chart-line'
		);
	}

	/**
	 * Renders pH readings statistics card.
	 *
	 * Displays the total number of pH measurements
	 * in a statistics card on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @global wpdb $wpdb WordPress database object.
	 * @return void
	 */
	public function render_ph_readings() {
		if ( false === apply_filters( 'aqualog/load/wp-admin/statistics', false ) ) {
			return;
		}
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->aqualog_chemistry} WHERE param_key = 'ph'" );
		$this->render_card(
			'ph-readings',
			/* translators: Statistics card title for pH readings */
			__( 'pH Readings', 'aqualog' ),
			$count,
			'clipboard'
		);
	}

	/**
	 * Renders maintenance tasks statistics card.
	 *
	 * Displays maintenance task statistics
	 * in a statistics card on the dashboard.
	 *
	 * @since 1.0.0
	 * @access public
	 * @todo Implement actual maintenance task counting logic.
	 * @return void
	 */
	public function render_maintenance_tasks() {
		if ( false === apply_filters( 'aqualog/load/wp-admin/maintenance', false ) ) {
			return;
		}
		$this->render_card(
			'maintenance',
			/* translators: Statistics card title for maintenance tasks */
			__( 'Maintenance Tasks', 'aqualog' ),
			0,
			'hammer'
		);
	}
}
