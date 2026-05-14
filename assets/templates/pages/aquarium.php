<?php
/**
 * Aquarium dashboard page template.
 *
 * This template displays the aquarium dashboard with information about a specific
 * aquarium, including its dimensions, operation period, and recent events.
 *
 * @package    iWorks_Aquarium_Log
 * @subpackage Templates
 * @since      1.0.0
 *
 * @var array $args {
 *     Template arguments.
 *
 *     @type int $aquarium_id The ID of the aquarium to display.
 * }
 */

defined( 'ABSPATH' ) || exit;
if ( empty( $args ) || ! is_array( $args ) || ! isset( $args['aquarium_id'] ) ) {
	esc_html_e( 'Invalid aquarium ID.', 'iworks-aquarium-log' );
	return;
}


if ( ! $args['aquarium']['post'] ) {
	esc_html_e( 'Aquarium not found.', 'iworks-aquarium-log' );
	return;
}


?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'iWorks Aquarium Log Dashboard', 'iworks-aquarium-log' ); ?></h1>
	<?php
	/**
	 * Fires before the aquarium dashboard content.
	 *
	 * @since 1.0.0
	 */
	do_action( 'iworks-aquarium-log/dashboard/before' );


	?>

	<section class="aquarium-log-dashboard-section aquarium-log-dashboard-grid">
		<div class="aquarium-log-aquarium-info">
			<h2 class="aquarium-log-aquarium-title"><?php echo esc_html( $args['aquarium']['post']['post_title'] ); ?></h2>
			<div class="aquarium-log-aquarium-info-card aquarium-log-aquarium-info-card--period">
				<h3><?php esc_html_e( 'Period of operation', 'iworks-aquarium-log' ); ?></h3>
				<div class="aquarium-log-aquarium-info-card-row">
					<dl class="aquarium-log-aquarium-info-card-row__start">
						<dt><?php esc_html_e( 'Start Date:', 'iworks-aquarium-log' ); ?></dt>
						<dd><?php echo date_i18n( get_option( 'date_format' ), strtotime( $args['aquarium']['post']['post_date'] ) ); ?></dd>
					</dl>
					<dl class="aquarium-log-aquarium-info-card-row__update">
						<dt><?php esc_html_e( 'Last Update:', 'iworks-aquarium-log' ); ?></dt>
						<dd><?php echo date_i18n( get_option( 'date_format' ), strtotime( $args['aquarium']['meta']['_iw_last_update'] ) ); ?></dd>
					</dl>
					<dl class="aquarium-log-aquarium-info-card-row__end">
						<dt><?php esc_html_e( 'Aquarium End Date:', 'iworks-aquarium-log' ); ?></dt>
						<dd><?php esc_html_e( 'Aquarium End Date', 'iworks-aquarium-log' ); ?></dd>
					</dl>
				</div>
			</div>
			<div class="aquarium-log-aquarium-info-card aquarium-log-aquarium-info-card--dimensions">
				<h3><?php esc_html_e( 'Aquarium Dimensions', 'iworks-aquarium-log' ); ?></h3>
				<p><?php esc_html_e( 'Tank Capacity', 'iworks-aquarium-log' ); ?></p>
				<p><?php esc_html_e( 'Water Volume in Tank', 'iworks-aquarium-log' ); ?></p>
				<p><?php esc_html_e( 'Tank Width', 'iworks-aquarium-log' ); ?></p>
				<p><?php esc_html_e( 'Tank Height', 'iworks-aquarium-log' ); ?></p>
				<p><?php esc_html_e( 'Tank Depth', 'iworks-aquarium-log' ); ?></p>
			</div>
		</div>
	</section>

	<!-- Statistics Cards -->
	<section class="aquarium-log-dashboard-section aquarium-log-events">
		<h2><?php esc_html_e( 'Events', 'iworks-aquarium-log' ); ?></h2>
		<div class="aquarium-log-events-grid">
			<?php
			/**
			 * Fires to display events on the aquarium dashboard.
			 *
			 * @since 1.0.0
			 */
			do_action( 'iworks-aquarium-log/dashboard/events' );
			?>
		</div>
	</section>

	<!-- Recent Aquariums -->
	<section class="aquarium-log-dashboard-section aquarium-log-recent-aquariums-section">
		<?php
		/**
		 * Fires to display recent aquariums on the aquarium dashboard.
		 *
		 * @since 1.0.0
		 */
		do_action( 'iworks-aquarium-log/dashboard/recent_aquariums' );
		?>
	</section>

</div>
<?php

d( $args['aquarium'] );