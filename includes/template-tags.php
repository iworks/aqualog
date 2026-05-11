<?php
/**
 * iWorks Aquarium Log Template Tags
 *
 * This file contains template tags and utility functions for the iWorks Aquarium Log plugin.
 * These functions can be used throughout the plugin templates and views.
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
function iworks_aquarium_log_chemistry_scale_item( $one, $range ) {
	$min    = $one['range'][0];
	$max    = $one['range'][1];
	$length = ( $max - $min ) * 1000;
	$start  = ( $one[ $range ][0] - $min ) * 100000 / $length;
	$end    = ( $one[ $range ][1] - $min ) * 100000 / $length;
	return array( $start, $end );
}

function iworks_aquarium_log_get_scale( $args ) {
	$danger   = iworks_aquarium_log_chemistry_scale_item( $args, 'danger' );
	$safety   = iworks_aquarium_log_chemistry_scale_item( $args, 'safety' );
	$ideal    = iworks_aquarium_log_chemistry_scale_item( $args, 'ideal' );
	$content  = sprintf(
		'<div class="aquarium-log-chemistry-item-body-scale-char"
		data-range-min="%s"
		data-range-max="%s"
		data-range-step="%s"',
		esc_attr( $args['range'][0] ),
		esc_attr( $args['range'][1] ),
		esc_attr( ( $args['range'][1] - $args['range'][0] ) / 100 )
	);
	$content .= ' ';
	$content .= sprintf(
		'style="background: linear-gradient(
			to right,
			var(--aquarium-log-settings-danger) %1$f%% %2$f%%,
			var(--aquarium-log-settings-safety) %2$f%% %3$f%%,
			var(--aquarium-log-settings-ideal) %3$f%% %4$f%%,
			var(--aquarium-log-settings-safety) %4$f%% %5$f%%,
			var(--aquarium-log-settings-danger) %5$f%% %6$f%%
		);"',
		$danger[0],
		$safety[0],
		$ideal[0],
		$ideal[1],
		$safety[1],
		$danger[1]
	);
	$content .= '>';
	if ( '' !== $args['value'] ) {
		$content .= sprintf(
			'<span tabindex="0" class="ui-slider-handle ui-corner-all ui-state-default" style="left: %d%%;"></span>',
			esc_attr( ( ( $args['value'] - $args['range'][0] ) * 100 ) / ( $args['range'][1] - $args['range'][0] ) )
		);
	}
	$content .= '</div>';
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
