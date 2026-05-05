<?php
/**
 * AquaLog Options Configuration
 *
 * This file contains the configuration options for the AquaLog plugin.
 * It defines the structure of the plugin's options and settings pages.
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

/**
 * Get plugin options configuration.
 *
 * Returns an array containing the configuration for the plugin's options pages
 * and settings, including main settings, chemistry parameters, and page structure.
 *
 * @since 1.0.0
 * @return array Array of options configuration.
 */
function iworks_aqualog_options() {
	// Initialize empty options array
	$options = array();

	// Set parent page for submenu items
	$parent = 'aqualog-dashboard';

	// Main settings configuration
	// Defines the structure of the main options page including:
	// - Version number
	// - Page title
	// - Menu type
	// - Options array
	// - Metaboxes array
	// - Subpages array
	$options['index'] = array(
		// Current version of the options configuration
		'version'    => '0.0',

		// Title of the options page
		'page_title' => __( 'Settings', 'aqualog' ),

		// Menu type for the options page
		// Possible values: 'options', 'submenu', 'management', 'theme', 'posts',
		// 'pages', 'users', 'plugins', 'comments', 'dashboard', 'settings',
		// 'media', 'custom'
		'menu'       => 'submenu',

		// Parent page for submenu items
		// Required when 'menu' is set to 'submenu'
		'parent'     => $parent,

		// Use tabs for options page
		// true - use tabs, false - options shown flat on one screen
		'use_tabs'   => false,

		// Array of options fields
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

		// Array of metaboxes
		'metaboxes'  => apply_filters( 'aqualog/etc/config/metaboxes', array() ),

		// Array of subpages
		'pages'      => apply_filters( 'aqualog/etc/config/pages', array() ),
	);

	// Chemistry parameters configuration
	// Defines all available water chemistry parameters with their properties
	// including name, unit, description, range, ideal values, and testing frequency
	$options['chemistry'] = array(
		'temp' => array(
			'name'        => 'Temperature',
			'unit'        => '°C',
			'description' => esc_html__( 'Water temperature', 'aqualog' ),
			'range'       => array( 10, 40 ),
			'danger'      => array( 15, 32 ),
			'safety'      => array( 20, 28 ),
			'ideal'       => array( 24, 26 ),
			'frequency'   => 'daily',
			'importance'  => 'important',
		),
		'ph' => array(
			'name'        => 'pH',
			'unit'        => 'pH',
			'description' => esc_html__( 'Acidity/Alkalinity level', 'aqualog' ),
			'range'       => array( 4, 10 ),
			'danger'      => array( 4, 10 ),
			'safety'      => array( 5.5, 7.5 ),
			'ideal'       => array( 6, 7 ),
			'frequency'   => 'daily',
			'importance'  => 'critical',
		),
		'gh' => array(
			'name'        => 'GH',
			'unit'        => '°dH',
			'description' => esc_html__( 'General Hardness', 'aqualog' ),
			'range'       => array( 0, 30 ),
			'danger'      => array( 0, 30 ),
			'safety'      => array( 2, 12 ),
			'ideal'       => array( 3, 8 ),
			'frequency'   => 'biweekly',
			'importance'  => 'important',
		),
		'kh' => array(
			'name'        => 'KH',
			'unit'        => '°dH',
			'description' => esc_html__( 'Carbonate Hardness', 'aqualog' ),
			'range'       => array( 0, 25 ),
			'danger'      => array( 0, 25),
			'safety'      => array( 1, 10 ),
			'ideal'       => array( 3, 6 ),
			'frequency'   => 'biweekly',
			'importance'  => 'important',
		),
		'no3' => array(
			'name'        => 'NO₃',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Nitrate', 'aqualog' ),
			'range'       => array( 0, 100 ),
			'danger'      => array( 0, 100 ),
			'safety'      => array( 5, 40 ),
			'ideal'       => array( 10, 25 ),
			'frequency'   => 'weekly',
			'importance'  => 'important',
		),
		'no2' => array(
			'name'        => 'NO₂',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Nitrite', 'aqualog' ),
			'range'       => array( 0, 2 ),
			'danger'      => array( 0.25, 2 ),
			'safety'      => array( 0, 0.25 ),
			'ideal'       => array( 0, 0 ),
			'frequency'   => 'daily',
			'importance'  => 'critical',
		),
		'nh3' => array(
			'name'        => 'NH₃',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Ammonia', 'aqualog' ),
			'range'       => array( 0, 2 ),
			'danger'      => array( 0.02, 2 ),
			'safety'      => array( 0, 0.02 ),
			'ideal'       => array( 0, 0 ),
			'frequency'   => 'daily',
			'importance'  => 'critical',
		),
		'po4' => array(
			'name'        => 'PO₄',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Phosphate', 'aqualog' ),
			'range'       => array( 0, 5 ),
			'danger'      => array( 0, 5 ),
			'safety'      => array( 0.1, 3 ),
			'ideal'       => array( 0.5, 2 ),
			'frequency'   => 'weekly',
			'importance'  => 'important',
		),
		'fe' => array(
			'name'        => 'Fe',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Iron', 'aqualog' ),
			'range'       => array( 0, 1.5 ),
			'danger'      => array( 0, 1.5 ),
			'safety'      => array( 0.01, 0.5 ),
			'ideal'       => array( 0.05, 0.2 ),
			'frequency'   => 'weekly',
			'importance'  => 'important',
		),
		'ca' => array(
			'name'        => 'Ca',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Calcium', 'aqualog' ),
			'range'       => array( 0, 200 ),
			'danger'      => array( 0, 200 ),
			'safety'      => array( 10, 100 ),
			'ideal'       => array( 20, 60 ),
			'frequency'   => 'monthly',
			'importance'  => 'recommended',
		),
		'mg' => array(
			'name'        => 'Mg',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Magnesium', 'aqualog' ),
			'range'       => array( 0, 100 ),
			'danger'      => array( 0, 100 ),
			'safety'      => array( 2, 40 ),
			'ideal'       => array( 5, 20 ),
			'frequency'   => 'monthly',
			'importance'  => 'recommended',
		),
		'k' => array(
			'name'        => 'K',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Potassium', 'aqualog' ),
			'range'       => array( 0, 100 ),
			'danger'      => array( 0, 100 ),
			'safety'      => array( 5, 50 ),
			'ideal'       => array( 10, 30 ),
			'frequency'   => 'weekly',
			'importance'  => 'important',
		),
		'tds' => array(
			'name'        => 'TDS',
			'unit'        => 'ppm',
			'description' => esc_html__( 'Total Dissolved Solids', 'aqualog' ),
			'range'       => array( 0, 1000 ),
			'danger'      => array( 25, 1000 ),
			'safety'      => array( 50, 350 ),
			'ideal'       => array( 100, 250 ),
			'frequency'   => 'monthly',
			'importance'  => 'recommended',
		),
		'o2' => array(
			'name'        => 'O₂',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Oxygen', 'aqualog' ),
			'range'       => array( 0, 15 ),
			'danger'      => array( 0, 15 ),
			'safety'      => array( 4, 10 ),
			'ideal'       => array( 6, 8 ),
			'importance'  => 'recommended',
		),
		'co2' => array(
			'name'        => 'CO₂',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Carbon Dioxide', 'aqualog' ),
			'range'       => array( 0, 50 ),
			'danger'      => array( 0, 50 ),
			'safety'      => array( 15, 40 ),
			'ideal'       => array( 20, 35 ),
			'frequency'   => 'daily',
			'importance'  => 'critical',
		),
		'cl' => array(
			'name'        => 'Cl',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Chlorine', 'aqualog' ),
			'range'       => array( 0, 5 ),
			'danger'      => array( 0, 5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
			'importance'  => 'critical',
		),
		'cu' => array(
			'name'        => 'Cu',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Copper', 'aqualog' ),
			'range'       => array( 0, 2 ),
			'danger'      => array( 0, 2 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'zn' => array(
			'name'        => 'Zn',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Zinc', 'aqualog' ),
			'range'       => array( 0, 0.5 ),
			'danger'      => array( 0, 0.5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'mn' => array(
			'name'        => 'Mn',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Manganese', 'aqualog' ),
			'range'       => array( 0, 0.5 ),
			'danger'      => array( 0, 0.5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'mo' => array(
			'name'        => 'Mo',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Molybdenum', 'aqualog' ),
			'range'       => array( 0, 0.1 ),
			'danger'      => array( 0, 0.1 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'b' => array(
			'name'        => 'B',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Boron', 'aqualog' ),
			'range'       => array( 0, 10 ),
			'danger'      => array( 0, 10 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
	);

	// Return the complete options configuration
	return $options;
}
