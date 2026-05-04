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
<div class="aqualog-stat-card">
	<span class="dashicons <?php echo esc_attr( $args['icon'] ); ?>"></span>
	<div class="aqualog-stat-number"><?php echo esc_html( $args['count'] ); ?></div>
	<div class="aqualog-stat-label"><?php echo esc_html( $args['label'] ); ?></div>
</div>