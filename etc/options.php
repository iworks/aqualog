<?php
/**
 * AquaLog Options Configuration
 *
 * This file contains the configuration options for the AquaLog.
 * It defines the structure of the plugin's options and settings pages.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026-PLUGIN_TILL_YEAR Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get plugin options configuration
 *
 * Returns an array containing the configuration for the plugin's options pages
 * and settings.
 *
 * @return array Array of options configuration
 * @since 1.0.0
 */
function iworks_aqualog_options() {
	/**
	 * Initialize empty options array
	 */
	$options = array();

	/**
	 * Parent page placeholder (uncomment and set as needed)
	 */
	$parent = admin_url( add_query_arg( 'page', 'aqualog-dashboard', 'admin.php' ) );
	$parent = add_query_arg( 'page', 'aqualog-dashboard', 'admin.php' );
	$parent = 'aqualog-dashboard';

	/**
	 * Main settings configuration
	 *
	 * Defines the structure of the main options page including:
	 * - Version number
	 * - Page title
	 * - Menu type
	 * - Options array
	 * - Metaboxes array
	 * - Subpages array
	 */
	$options['index'] = array(
		/**
		 * Current version of the options configuration
		 */
		'version'    => '0.0',

		/**
		 * Title of the options page
		 */
		'page_title' => __( 'Settings', 'aqualog' ),

/**
 * Menu type for the options page
 *
 * Possible values:
 * - 'options'      - Add as a top-level menu item (default)
 * - 'submenu'      - Add as a submenu item (requires 'parent' to be set)
 * - 'management'   - Add under Tools menu
 * - 'theme'        - Add under Appearance menu
 * - 'posts'        - Add under Posts menu
 * - 'pages'        - Add under Pages menu
 * - 'users'        - Add under Users menu (or Profile for single)
 * - 'plugins'      - Add under Plugins menu
 * - 'comments'     - Add under Comments menu
 * - 'dashboard'    - Add under Dashboard menu
 * - 'settings'     - Add under Settings menu
 * - 'media'        - Add under Media menu
 * - 'custom'       - Custom menu position (requires 'menu_slug' to be set)
 */
'menu'       => 'submenu',

/**
 * Parent page for submenu items
 *
 * Required when 'menu' is set to 'submenu' or when nesting under another menu.
 * Can be one of the following:
 * - The file name of a standard WordPress admin page (e.g., 'edit.php' for Posts)
 * - The value of 'menu_slug' from another options page
 * - The plugin file if you want to nest under a plugin's main menu
 *
 * Common WordPress admin page values:
 * - 'index.php'                  - Dashboard
 * - 'edit.php'                   - Posts
 * - 'upload.php'                 - Media
 * - 'edit.php?post_type=page'    - Pages
 * - 'edit-comments.php'          - Comments
 * - 'themes.php'                 - Appearance
 * - 'plugins.php'                - Plugins
 * - 'users.php'                  - Users
 * - 'tools.php'                  - Tools
 * - 'options-general.php'        - Settings
 * - 'options-general.php?page=YOUR_PAGE' - Custom settings page
 *
 * Example for nesting under a plugin's main menu:
 * 'parent' => 'my-plugin-slug',
 */
		'parent' => $parent,

		/**
		 * Use tabs for options page
		 *
		 * possible values:
		 * - true - use tabs
		 * - false - options will be shown flat on one screen
		 */
		'use_tabs' => false,

		/**
		 * Array of options fields
		 */
		'options'    => apply_filters(
			'aqualog/etc/config/options',
			array(
				array(
					'type'  => 'heading',
					'label' => __( 'Aquarium Settings', 'aqualog' ),
					'since' => '1.0.0',
				),
				array(
					'name'              => 'default_aquarium_id',
					'type'              => 'select',
					'th'                => __( 'Default Aquarium', 'aqualog' ),
					'description'       => __( 'Select the default aquarium to display.', 'aqualog' ),
					'classes'           => array( 'small-text' ),
					'sanitize_callback' => 'intval',
					'since'             => '1.0.0',
					'options'           => array(
						'' => esc_html__( '--- Select ---', 'aqualog' ),
					),
				),
			)
		),

		/**
		 * Array of metaboxes
		 */
		'metaboxes'  => apply_filters( 'aqualog/etc/config/metaboxes', array() ),

		/**
		 * Array of subpages
		 */
		'pages'      => apply_filters( 'aqualog/etc/config/pages', array() ),

		'chemistry'  => array(
		'ph' => array(
			'name' => 'pH',
			'unit' => '',
			'description' => esc_html__( 'Acidity/Alkalinity level', 'aqualog' ),
			'range' => array( 0, 14 ),
			'ideal' => array( 6.5, 7.5 ),
		),
		'gh' => array(
			'name' => 'GH',
			'unit' => '°dH',
			'description' => esc_html__( 'General Hardness', 'aqualog' ),
			'range' => array( 0, 30 ),
			'ideal' => array( 4, 12 ),
		),
		'kh' => array(
			'name' => 'KH',
			'unit' => '°dH',
			'description' => esc_html__( 'Carbonate Hardness', 'aqualog' ),
			'range' => array( 0, 20 ),
			'ideal' => array( 3, 8 ),
		),
		'no3' => array(
			'name' => 'NO₃',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Nitrate', 'aqualog' ),
			'range' => array( 0, 200 ),
			'ideal' => array( 5, 20 ),
		),
		'no2' => array(
			'name' => 'NO₂',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Nitrite', 'aqualog' ),
			'range' => array( 0, 5 ),
			'ideal' => array( 0, 0.1 ),
		),
		'nh3' => array(
			'name' => 'NH₃',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Ammonia', 'aqualog' ),
			'range' => array( 0, 5 ),
			'ideal' => array( 0, 0.02 ),
		),
		'po4' => array(
			'name' => 'PO₄',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Phosphate', 'aqualog' ),
			'range' => array( 0, 10 ),
			'ideal' => array( 0, 1 ),
		),
		'fe' => array(
			'name' => 'Fe',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Iron', 'aqualog' ),
			'range' => array( 0, 2 ),
			'ideal' => array( 0.1, 0.5 ),
		),
		'ca' => array(
			'name' => 'Ca',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Calcium', 'aqualog' ),
			'range' => array( 0, 500 ),
			'ideal' => array( 300, 450 ),
		),
		'mg' => array(
			'name' => 'Mg',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Magnesium', 'aqualog' ),
			'range' => array( 0, 2000 ),
			'ideal' => array( 1200, 1500 ),
		),
		'k' => array(
			'name' => 'K',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Potassium', 'aqualog' ),
			'range' => array( 0, 100 ),
			'ideal' => array( 10, 30 ),
		),
		'temperature' => array(
			'name' => 'Temperature',
			'unit' => '°C',
			'description' => esc_html__( 'Water Temperature', 'aqualog' ),
			'range' => array( 0, 35 ),
			'ideal' => array( 24, 26 ),
		),
		'tds' => array(
			'name' => 'TDS',
			'unit' => 'ppm',
			'description' => esc_html__( 'Total Dissolved Solids', 'aqualog' ),
			'range' => array( 0, 1000 ),
			'ideal' => array( 100, 500 ),
		),
		'o2' => array(
			'name' => 'O₂',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Oxygen', 'aqualog' ),
			'range' => array( 0, 15 ),
			'ideal' => array( 6, 8 ),
		),
		'co2' => array(
			'name' => 'CO₂',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Carbon Dioxide', 'aqualog' ),
			'range' => array( 0, 50 ),
			'ideal' => array( 20, 30 ),
		),
		'cl' => array(
			'name' => 'Cl',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Chlorine', 'aqualog' ),
			'range' => array( 0, 5 ),
			'ideal' => array( 0, 0 ),
		),
		'cu' => array(
			'name' => 'Cu',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Copper', 'aqualog' ),
			'range' => array( 0, 0.5 ),
			'ideal' => array( 0, 0 ),
		),
		'zn' => array(
			'name' => 'Zn',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Zinc', 'aqualog' ),
			'range' => array( 0, 5 ),
			'ideal' => array( 0, 0.1 ),
		),
		'mn' => array(
			'name' => 'Mn',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Manganese', 'aqualog' ),
			'range' => array( 0, 2 ),
			'ideal' => array( 0, 0.05 ),
		),
		'mo' => array(
			'name' => 'Mo',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Molybdenum', 'aqualog' ),
			'range' => array( 0, 1 ),
			'ideal' => array( 0, 0.01 ),
		),
		'b' => array(
			'name' => 'B',
			'unit' => 'mg/L',
			'description' => esc_html__( 'Boron', 'aqualog' ),
			'range' => array( 0, 5 ),
			'ideal' => array( 0.1, 0.5 ),
		),
		),
	);

	/**
	 * Return the complete options configuration
	 */
	return $options;
}
