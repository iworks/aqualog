<?php
/**
 * Statistic card template.
 *
 * @package Aqualog
 * @subpackage Templates
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="aquarium-log-stat-card aquarium-log-stat-card--<?php echo esc_attr( $args['class'] ); ?>">
	<span class="dashicons dashicons-<?php echo esc_attr( $args['icon'] ); ?>"></span>
	<div class="aquarium-log-stat-number"><?php echo esc_html( $args['value'] ); ?></div>
	<div class="aquarium-log-stat-label"><?php echo esc_html( $args['title'] ); ?></div>
</div>