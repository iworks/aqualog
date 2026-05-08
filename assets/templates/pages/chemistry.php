<?php
/**
 * Chemistry page template.
 *
 * @package Aqualog
 * @subpackage Templates
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap aqualog-chemistry">
	<?php do_action( 'aqualog/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Chemistry', 'aqualog' ); ?></h1>
<?php
if ( empty( $args['aquarium_id'] ) ) {
	if ( $args['counters']['aquariums'] === 0 ) {
		load_template( $args['messages']['create-aquarium-first'] );
	} else {
		load_template( $args['messages']['select-aquarium'], true, $args );
	}
} else {
	if ( empty( $args['latest_measurements'] ) ) {
		load_template( $args['messages']['chemistry-no-measurements'] );
	}
}
if ( false ) {
	?>
	<!-- Quick Actions -->
	<div class="aqualog-quick-actions-section">
		<div class="aqualog-card">
			<h2><?php esc_html_e( 'Quick Actions', 'aqualog' ); ?></h2>
			<div class="aqualog-actions-grid">
				<a href="#" class="aqualog-action-card">
					<span class="dashicons dashicons-color-picker"></span>
					<span><?php esc_html_e( 'Add Measurement Results', 'aqualog' ); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>
	<div class="aqualog-chemistry-container">
		<?php
		foreach ( $args['params'] as $param_key => $param ) {
			load_template( dirname( __DIR__, 1 ) . '/elements/chemistry-param.php', false, $param );
			?>
		<?php } ?>
	</div>
</div>
<?php
// Load the chemistry form template
load_template( dirname( __DIR__, 1 ) . '/elements/chemistry-form.php', false, $args );


