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
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
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
		'use_tabs'   => true,

		// Array of options fields
		'options'    => apply_filters(
			'aqualog/etc/config/options',
			array(
				array(
					'type'  => 'heading',
					'label' => __( 'Modules', 'aqualog' ),
					'since' => '1.0.0',
				),
				array(
					'name'              => 'module_chemistry',
					'type'              => 'checkbox',
					'th'                => __( 'Chemistry', 'aqualog' ),
					'description'       => __( 'Track water parameters and chemistry data.', 'aqualog' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_maintenance',
					'type'              => 'checkbox',
					'th'                => __( 'Maintenance', 'aqualog' ),
					'description'       => __( 'Schedule and track maintenance tasks.', 'aqualog' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_dosing',
					'type'              => 'checkbox',
					'th'                => __( 'Dosing', 'aqualog' ),
					'description'       => __( 'Manage fertilizers and supplement dosing.', 'aqualog' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_equipment',
					'type'              => 'checkbox',
					'th'                => __( 'Equipment', 'aqualog' ),
					'description'       => __( 'Track equipment status and schedules.', 'aqualog' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_animals',
					'type'              => 'checkbox',
					'th'                => __( 'Animals', 'aqualog' ),
					'description'       => __( 'Manage fish and aquatic livestock.', 'aqualog' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_plants',
					'type'              => 'checkbox',
					'th'                => __( 'Plants', 'aqualog' ),
					'description'       => __( 'Track aquatic plants and care needs.', 'aqualog' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),

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

	/**
	 * Chemistry parameters configuration.
	 *
	 * Defines all available water chemistry parameters with their properties.
	 * Includes name, unit, description, range, ideal values, and testing frequency.
	 *
	 * @since 1.0.0
	 */
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
			'show_name'   => false,
		),
		'ph'   => array(
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
		'gh'   => array(
			'name'        => 'GH',
			'unit'        => '°dH',
			'description' => esc_html__( 'General Hardness', 'aqualog' ),
			'range'       => array( 0, 30 ),
			'danger'      => array( 0, 30 ),
			'safety'      => array( 2, 12 ),
			'ideal'       => array( 2, 12 ),
			'frequency'   => 'weekly',
			'importance'  => 'recommended',
		),
		'kh'   => array(
			'name'        => 'KH',
			'unit'        => '°dH',
			'description' => esc_html__( 'Carbonate Hardness', 'aqualog' ),
			'range'       => array( 0, 20 ),
			'danger'      => array( 0, 20 ),
			'safety'      => array( 0, 20 ),
			'ideal'       => array( 0, 20 ),
			'frequency'   => 'weekly',
			'importance'  => 'recommended',
		),
		'no3'  => array(
			'name'        => 'NO₃',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Nitrate', 'aqualog' ),
			'range'       => array( 0, 100 ),
			'danger'      => array( 50, 100 ),
			'safety'      => array( 0, 50 ),
			'ideal'       => array( 0, 50 ),
			'frequency'   => 'weekly',
			'importance'  => 'recommended',
		),
		'no2'  => array(
			'name'        => 'NO₂',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Nitrite', 'aqualog' ),
			'range'       => array( 0, 2 ),
			'danger'      => array( 0, 2 ),
			'safety'      => array( 0, 0.25 ),
			'ideal'       => array( 0, 0 ),
			'frequency'   => 'daily',
			'importance'  => 'critical',
		),
		'nh3'  => array(
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
		'po4'  => array(
			'name'        => 'PO₄',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Phosphate', 'aqualog' ),
			'range'       => array( 0, 10 ),
			'danger'      => array( 2, 10 ),
			'safety'      => array( 0, 2 ),
			'ideal'       => array( 0, 2 ),
			'frequency'   => 'monthly',
			'importance'  => 'recommended',
		),
		'fe'   => array(
			'name'        => 'Fe',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Iron', 'aqualog' ),
			'range'       => array( 0, 5 ),
			'danger'      => array( 0, 5 ),
			'safety'      => array( 0, 5 ),
			'ideal'       => array( 0, 5 ),
			'frequency'   => 'monthly',
			'importance'  => 'recommended',
		),
		'ca'   => array(
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
		'mg'   => array(
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
		'k'    => array(
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
		'tds'  => array(
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
		'o2'   => array(
			'name'        => 'O₂',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Oxygen', 'aqualog' ),
			'range'       => array( 0, 15 ),
			'danger'      => array( 0, 15 ),
			'safety'      => array( 4, 10 ),
			'ideal'       => array( 6, 8 ),
			'importance'  => 'recommended',
		),
		'co2'  => array(
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
		'cl'   => array(
			'name'        => 'Cl',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Chlorine', 'aqualog' ),
			'range'       => array( 0, 5 ),
			'danger'      => array( 0, 5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
			'importance'  => 'critical',
		),
		'cu'   => array(
			'name'        => 'Cu',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Copper', 'aqualog' ),
			'range'       => array( 0, 2 ),
			'danger'      => array( 0, 2 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'zn'   => array(
			'name'        => 'Zn',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Zinc', 'aqualog' ),
			'range'       => array( 0, 0.5 ),
			'danger'      => array( 0, 0.5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'mn'   => array(
			'name'        => 'Mn',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Manganese', 'aqualog' ),
			'range'       => array( 0, 0.5 ),
			'danger'      => array( 0, 0.5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'mo'   => array(
			'name'        => 'Mo',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Molybdenum', 'aqualog' ),
			'range'       => array( 0, 0.1 ),
			'danger'      => array( 0, 0.1 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'b'    => array(
			'name'        => 'B',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Boron', 'aqualog' ),
			'range'       => array( 0, 10 ),
			'danger'      => array( 0, 10 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
	);

	$options['frequencies'] = array(
		'annually'        => esc_html__( 'Annually', 'aqualog' ),
		'daily'           => esc_html__( 'Daily', 'aqualog' ),
		'every_other_day' => esc_html__( 'Every other day', 'aqualog' ),
		'monthly'         => esc_html__( 'Monthly', 'aqualog' ),
		'on_demand'       => esc_html__( 'On demand', 'aqualog' ),
		'other'           => esc_html__( 'Other', 'aqualog' ),
		'quarterly'       => esc_html__( 'Quarterly', 'aqualog' ),
		'semi_annually'   => esc_html__( 'Semi-annually', 'aqualog' ),
		'twice_daily'     => esc_html__( 'Twice daily', 'aqualog' ),
		'twice_monthly'   => esc_html__( 'Twice monthly', 'aqualog' ),
		'weekly'          => esc_html__( 'Weekly', 'aqualog' ),
	);

	$options['maintenance_tasks'] = array(
		'algae_removal'          => array(
			'title'       => esc_html__( 'Algae Removal', 'aqualog' ),
			'description' => esc_html__( 'Remove algae from tank surfaces', 'aqualog' ),
			'frequency'   => 'on_demand',
		),
		'equipment_inspection'   => array(
			'title'       => esc_html__( 'Equipment Inspection', 'aqualog' ),
			'description' => esc_html__( 'Inspect and clean equipment', 'aqualog' ),
			'frequency'   => 'monthly',
		),
		'feeding'                => array(
			'title'       => esc_html__( 'Feeding', 'aqualog' ),
			'description' => esc_html__( 'Feed fish and monitor feeding schedule', 'aqualog' ),
			'frequency'   => 'daily',
		),
		'fertilization'          => array(
			'title'       => esc_html__( 'Fertilization', 'aqualog' ),
			'description' => esc_html__( 'Add fertilizers and nutrients', 'aqualog' ),
			'frequency'   => 'weekly',
		),
		'filter_cleaning'        => array(
			'title'       => esc_html__( 'Filter Cleaning', 'aqualog' ),
			'description' => esc_html__( 'Clean or replace filter media', 'aqualog' ),
			'frequency'   => 'monthly',
		),
		'glass_cleaning'         => array(
			'title'       => esc_html__( 'Glass Cleaning', 'aqualog' ),
			'description' => esc_html__( 'Clean aquarium glass and remove algae', 'aqualog' ),
			'frequency'   => 'weekly',
		),
		'plant_trimming'         => array(
			'title'       => esc_html__( 'Plant Trimming', 'aqualog' ),
			'description' => esc_html__( 'Trim and maintain aquatic plants', 'aqualog' ),
			'frequency'   => 'weekly',
		),
		'substrate_cleaning'     => array(
			'title'       => esc_html__( 'Substrate Cleaning', 'aqualog' ),
			'description' => esc_html__( 'Clean substrate and gravel', 'aqualog' ),
			'frequency'   => 'monthly',
		),
		'temperature_adjustment' => array(
			'title'       => esc_html__( 'Temperature Adjustment', 'aqualog' ),
			'description' => esc_html__( 'Check and adjust water temperature', 'aqualog' ),
			'frequency'   => 'daily',
		),
		'water_testing'          => array(
			'title'       => esc_html__( 'Water Testing', 'aqualog' ),
			'description' => esc_html__( 'Test water parameters', 'aqualog' ),
			'frequency'   => 'weekly',
		),
		'other'                  => array(
			'title'       => esc_html__( 'Other', 'aqualog' ),
			'description' => esc_html__( 'Other maintenance tasks', 'aqualog' ),
			'frequency'   => 'as_needed',
		),
	);

	// Return the complete options configuration
	return $options;
}
