<?php
/*
Plugin Name: iWorks Aquarium Log
Text Domain: iworks-aquarium-log
Plugin URI: PLUGIN_URI
Description: PLUGIN_TAGLINE
Version: PLUGIN_VERSION
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Copyright 2026-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Define static options and constants for the plugin
 */
// Define plugin version constant
define( 'IWORKS_AQUALOG_VERSION', 'PLUGIN_VERSION' );
// Define prefix for all plugin options and functions
define( 'IWORKS_AQUALOG_PREFIX', 'iworks_aquarium_log_' );
// Get the base directory path
$iworks_aquarium_log_base = __DIR__;
// Set vendor directory path (where core classes are located)
$iworks_aquarium_log_vendor = $iworks_aquarium_log_base . '/includes';

/**
 * Load the main plugin class if it doesn't exist
 * This is the core class that handles all plugin functionality
 */
if ( ! class_exists( 'iworks_aquarium_log' ) ) {
	// Load the main plugin class from the includes directory
	require_once $iworks_aquarium_log_vendor . '/iworks/class-iworks-aquarium-log.php';
}

/**
 * Load configuration options
 * This file contains all plugin configuration settings
 */
require_once $iworks_aquarium_log_base . '/etc/options.php';

/**
 * Load the options class if it doesn't exist
 * This class handles all plugin options and settings
 */
if ( ! class_exists( 'iworks_options' ) ) {
	// Load the options class from the includes directory
	require_once $iworks_aquarium_log_vendor . '/iworks/options/options.php';
}

require_once $iworks_aquarium_log_base . '/includes/template-tags.php';

/**
 * Initialize and get plugin options
 * This function creates and returns the options object
 *
 * @return iworks_options The plugin options object
 */
function iworks_aquarium_log_get_options() {
	// Use global variable to store options object
	global $iworks_aquarium_log_options;

	// Return existing options object if it exists
	if ( is_object( $iworks_aquarium_log_options ) ) {
		return $iworks_aquarium_log_options;
	}

	// Create new options object if it doesn't exist
	$iworks_aquarium_log_options = new iworks_options();

	// Set the function name for options
	$iworks_aquarium_log_options->set_option_function_name( 'iworks_aquarium_log_options' );
	// Set the option prefix for all plugin options
	$iworks_aquarium_log_options->set_option_prefix( IWORKS_AQUALOG_PREFIX );

	// Set the plugin file name if the method exists
	if ( method_exists( $iworks_aquarium_log_options, 'set_plugin' ) ) {
		$iworks_aquarium_log_options->set_plugin( basename( __FILE__ ) );
	}

	// Initialize the options
	$iworks_aquarium_log_options->options_init();

	// Return the options object
	return $iworks_aquarium_log_options;
}

// Initialize the main plugin class
$iworks_aquarium_log = new iworks_aquarium_log();

/**
 * Register plugin activation and deactivation hooks
 */
// Register activation hook to run when plugin is activated
register_activation_hook( __FILE__, array( $iworks_aquarium_log, 'register_activation_hook' ) );
// Register deactivation hook to run when plugin is deactivated
register_deactivation_hook( __FILE__, array( $iworks_aquarium_log, 'register_deactivation_hook' ) );
