<?php
/**
 * AquaLog Template Tags
 *
 * This file contains template tags and utility functions for the AquaLog plugin.
 * These functions can be used throughout the plugin templates and views.
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
 * Generate chemistry scale item HTML.
 *
 * Creates a span element for the chemistry parameter scale with proper positioning
 * and width based on the parameter range and values.
 *
 * @since 1.0.0
 *
 * @param array $one   Parameter data containing range and values.
 * @param string $range Type of range (danger, safety, ideal).
 * @return string HTML span element with inline styles for positioning.
 */
function aqualog_chemistry_scale_item( $one, $range ) {
	$min = $one['range'][0];
	$max = $one['range'][1];
	$length = ( $max - $min ) * 1000;
	$start = ( $one[$range][0] - $min ) * 100000 / $length;
	$end = ( $one[$range][1] - $min ) * 100000 / $length - $start;
	
	return sprintf(
		'<span class="scale-item scale-item--%s" style="left: %f%%;width: %f%%;" data-min="%f" data-max="%f"></span>',
		esc_attr( $range ),
		$start,
		$end,
		$one[$range][0],
		$one[$range][1]
	);
}
