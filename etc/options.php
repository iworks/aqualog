<?php
/**
 * iWorks Aquarium Log Options Configuration
 *
 * This file contains the configuration options for the iWorks Aquarium Log plugin.
 * It defines the structure of the plugin's options and settings pages.
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
		'page_title' => esc_html__( 'Settings', 'PLUGIN_NAME' ),

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
			'iworks/aqualog/etc/config/options',
			array(
				array(
					'type'  => 'heading',
					'label' => esc_html__( 'Modules', 'PLUGIN_NAME' ),
					'since' => '1.0.0',
				),
				array(
					'name'              => 'module_chemistry',
					'type'              => 'checkbox',
					'th'                => esc_html__( 'Chemistry', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Track water parameters and chemistry data.', 'PLUGIN_NAME' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_maintenance',
					'type'              => 'checkbox',
					'th'                => esc_html__( 'Maintenance', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Schedule and track maintenance tasks.', 'PLUGIN_NAME' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_dosing',
					'type'              => 'checkbox',
					'th'                => esc_html__( 'Dosing', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Manage fertilizers and supplement dosing.', 'PLUGIN_NAME' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_equipment',
					'type'              => 'checkbox',
					'th'                => esc_html__( 'Equipment', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Track equipment status and schedules.', 'PLUGIN_NAME' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_animals',
					'type'              => 'checkbox',
					'th'                => esc_html__( 'Animals', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Manage fish and aquatic livestock.', 'PLUGIN_NAME' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'module_plants',
					'type'              => 'checkbox',
					'th'                => esc_html__( 'Plants', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Track aquatic plants and care needs.', 'PLUGIN_NAME' ),
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'classes'           => array( 'switch-button' ),
					'group'             => 'modules',
					'since'             => '1.0.0',
				),

				array(
					'type'  => 'heading',
					'label' => esc_html__( 'Aquarium Settings', 'PLUGIN_NAME' ),
					'since' => '1.0.0',
				),
				array(
					'name'              => 'default_aquarium_id',
					'type'              => 'select',
					'th'                => esc_html__( 'Default Aquarium', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Select the default aquarium to display.', 'PLUGIN_NAME' ),
					'classes'           => array( 'small-text' ),
					'sanitize_callback' => 'intval',
					'since'             => '1.0.0',
					'options'           => array(
						'' => esc_html__( '--- Select ---', 'PLUGIN_NAME' ),
					),
				),
				array(
					'type'  => 'heading',
					'label' => esc_html__( 'Plugin Settings', 'PLUGIN_NAME' ),
					'since' => '1.0.0',
				),
				array(
					'name'              => 'menu_position',
					'type'              => 'number',
					'th'                => esc_html__( 'Menu Position', 'PLUGIN_NAME' ),
					'description'       => esc_html__( 'Set the position of the main menu item.', 'PLUGIN_NAME' ),
					'classes'           => array( 'small-text' ),
					'sanitize_callback' => 'intval',
					'since'             => '1.0.0',
					'default'           => 59,
				),
			)
		),

		// Array of metaboxes
		'metaboxes'  => apply_filters( 'iworks/aqualog/etc/config/metaboxes', array() ),

		// Array of subpages
		'pages'      => apply_filters( 'iworks/aqualog/etc/config/pages', array() ),
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
			'description' => esc_html__( 'Water temperature', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Acidity/Alkalinity level', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'General Hardness', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Carbonate Hardness', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Nitrate', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Nitrite', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Ammonia', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Phosphate', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Iron', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Calcium', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Magnesium', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Potassium', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Total Dissolved Solids', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Oxygen', 'PLUGIN_NAME' ),
			'range'       => array( 0, 15 ),
			'danger'      => array( 0, 15 ),
			'safety'      => array( 4, 10 ),
			'ideal'       => array( 6, 8 ),
			'importance'  => 'recommended',
		),
		'co2'  => array(
			'name'        => 'CO₂',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Carbon Dioxide', 'PLUGIN_NAME' ),
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
			'description' => esc_html__( 'Chlorine', 'PLUGIN_NAME' ),
			'range'       => array( 0, 5 ),
			'danger'      => array( 0, 5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
			'importance'  => 'critical',
		),
		'cu'   => array(
			'name'        => 'Cu',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Copper', 'PLUGIN_NAME' ),
			'range'       => array( 0, 2 ),
			'danger'      => array( 0, 2 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'zn'   => array(
			'name'        => 'Zn',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Zinc', 'PLUGIN_NAME' ),
			'range'       => array( 0, 0.5 ),
			'danger'      => array( 0, 0.5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'mn'   => array(
			'name'        => 'Mn',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Manganese', 'PLUGIN_NAME' ),
			'range'       => array( 0, 0.5 ),
			'danger'      => array( 0, 0.5 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'mo'   => array(
			'name'        => 'Mo',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Molybdenum', 'PLUGIN_NAME' ),
			'range'       => array( 0, 0.1 ),
			'danger'      => array( 0, 0.1 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
		'b'    => array(
			'name'        => 'B',
			'unit'        => 'mg/L',
			'description' => esc_html__( 'Boron', 'PLUGIN_NAME' ),
			'range'       => array( 0, 10 ),
			'danger'      => array( 0, 10 ),
			'safety'      => array( 0, 0 ),
			'ideal'       => array( 0, 0 ),
		),
	);

	$options['frequencies'] = array(
		'annually'        => esc_html__( 'Annually', 'PLUGIN_NAME' ),
		'daily'           => esc_html__( 'Daily', 'PLUGIN_NAME' ),
		'every_other_day' => esc_html__( 'Every other day', 'PLUGIN_NAME' ),
		'monthly'         => esc_html__( 'Monthly', 'PLUGIN_NAME' ),
		'on_demand'       => esc_html__( 'On demand', 'PLUGIN_NAME' ),
		'other'           => esc_html__( 'Other', 'PLUGIN_NAME' ),
		'quarterly'       => esc_html__( 'Quarterly', 'PLUGIN_NAME' ),
		'semi_annually'   => esc_html__( 'Semi-annually', 'PLUGIN_NAME' ),
		'twice_daily'     => esc_html__( 'Twice daily', 'PLUGIN_NAME' ),
		'twice_monthly'   => esc_html__( 'Twice monthly', 'PLUGIN_NAME' ),
		'weekly'          => esc_html__( 'Weekly', 'PLUGIN_NAME' ),
	);

	$options['maintenance_tasks'] = array(
		'algae_removal'          => array(
			'title'       => esc_html__( 'Algae Removal', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Remove algae from tank surfaces', 'PLUGIN_NAME' ),
			'frequency'   => 'on_demand',
		),
		'equipment_inspection'   => array(
			'title'       => esc_html__( 'Equipment Inspection', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Inspect and clean equipment', 'PLUGIN_NAME' ),
			'frequency'   => 'monthly',
		),
		'feeding'                => array(
			'title'       => esc_html__( 'Feeding', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Feed fish and monitor feeding schedule', 'PLUGIN_NAME' ),
			'frequency'   => 'daily',
		),
		'fertilization'          => array(
			'title'       => esc_html__( 'Fertilization', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Add fertilizers and nutrients', 'PLUGIN_NAME' ),
			'frequency'   => 'weekly',
		),
		'filter_cleaning'        => array(
			'title'       => esc_html__( 'Filter Cleaning', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Clean or replace filter media', 'PLUGIN_NAME' ),
			'frequency'   => 'monthly',
		),
		'glass_cleaning'         => array(
			'title'       => esc_html__( 'Glass Cleaning', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Clean aquarium glass and remove algae', 'PLUGIN_NAME' ),
			'frequency'   => 'weekly',
		),
		'plant_trimming'         => array(
			'title'       => esc_html__( 'Plant Trimming', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Trim and maintain aquatic plants', 'PLUGIN_NAME' ),
			'frequency'   => 'weekly',
		),
		'substrate_cleaning'     => array(
			'title'       => esc_html__( 'Substrate Cleaning', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Clean substrate and gravel', 'PLUGIN_NAME' ),
			'frequency'   => 'monthly',
		),
		'temperature_adjustment' => array(
			'title'       => esc_html__( 'Temperature Adjustment', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Check and adjust water temperature', 'PLUGIN_NAME' ),
			'frequency'   => 'daily',
		),
		'water_testing'          => array(
			'title'       => esc_html__( 'Water Testing', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Test water parameters', 'PLUGIN_NAME' ),
			'frequency'   => 'weekly',
		),
		'other'                  => array(
			'title'       => esc_html__( 'Other', 'PLUGIN_NAME' ),
			'description' => esc_html__( 'Other maintenance tasks', 'PLUGIN_NAME' ),
			'frequency'   => 'as_needed',
		),
	);

	// Return the complete options configuration
	return $options;
}
