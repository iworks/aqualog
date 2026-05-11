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
<div class="wrap aquarium-log-chemistry">
	<?php do_action( 'iworks-aquarium-log/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Chemistry', 'iworks-aquarium-log' ); ?></h1>
<?php
if ( 'aquarium' === filter_input( INPUT_GET, 'change' ) ) {
	load_template( $args['messages']['select-aquarium'], true, $args );
} else {
	if ( empty( $args['aquarium_id'] ) ) {
		if ( $args['counters']['aquariums'] === 0 ) {
			load_template( $args['messages']['create-aquarium-first'] );
		} else {
			load_template( $args['messages']['select-aquarium'], true, $args );
		}
	} elseif ( empty( $args['latest_measurements'] ) ) {
		load_template( $args['messages']['chemistry-no-measurements'] );
	}
	?>
	<div class="aquarium-log-chemistry-container">
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
}
