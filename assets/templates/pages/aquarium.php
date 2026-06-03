<?php
/**
 * Aquarium dashboard page template.
 *
 * This template displays the aquarium dashboard with information about a specific
 * aquarium, including its dimensions, operation period, and recent events.
 *
 * @package    iworks_aqualog
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
	esc_html_e( 'Invalid aquarium ID.', 'PLUGIN_NAME' );
	return;
}
if ( ! $args['aquarium']['post'] ) {
	esc_html_e( 'Aquarium not found.', 'PLUGIN_NAME' );
	return;
}
?>
<div class="wrap">
	<?php do_action( 'iworks/aqualog/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Aqualog Dashboard', 'PLUGIN_NAME' ); ?></h1>
	<?php
	/**
	 * Fires before the aquarium dashboard content.
	 *
	 * @since 1.0.0
	 */
	do_action( 'iworks/aqualog/dashboard/before' );
	?>

	<section class="aqualog-dashboard-section aqualog-dashboard-grid">
		<div class="aqualog-aquarium-info">
			<h2 class="aqualog-aquarium-title"><?php echo esc_html( $args['aquarium']['post']['post_title'] ); ?></h2>
			<div class="aqualog-info-cards">
				<div class="aqualog-info-card aqualog-info-card--period">
					<h3><?php esc_html_e( 'Period of operation', 'PLUGIN_NAME' ); ?></h3>
					<div class="aqualog-info-card-row">
						<div class="aqualog-info-card-cell aqualog-info-card-cell__start">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM320 256C306.7 256 296 266.7 296 280L296 328L248 328C234.7 328 224 338.7 224 352C224 365.3 234.7 376 248 376L296 376L296 424C296 437.3 306.7 448 320 448C333.3 448 344 437.3 344 424L344 376L392 376C405.3 376 416 365.3 416 352C416 338.7 405.3 328 392 328L344 328L344 280C344 266.7 333.3 256 320 256z"/></svg>
							<span class="label"><?php esc_html_e( 'Start Date:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_date_started'] ); ?></span>
						</div>
						<div class="aqualog-info-card-cell aqualog-info-card-cell__update">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64zM438 225.7C427.3 217.9 412.3 220.3 404.5 231L285.1 395.2L233 343.1C223.6 333.7 208.4 333.7 199.1 343.1C189.8 352.5 189.7 367.7 199.1 377L271.1 449C276.1 454 283 456.5 289.9 456C296.8 455.5 303.3 451.9 307.4 446.2L443.3 259.2C451.1 248.5 448.7 233.5 438 225.7z"/></svg>
							<span class="label"><?php esc_html_e( 'Last Update:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_date_updated'] ); ?></span>
						</div>
						<div class="aqualog-info-card-cell aqualog-info-card-cell__end">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM387.9 284.1C378.5 274.7 363.3 274.7 354 284.1L320.1 318L286.2 284.1C276.8 274.7 261.6 274.7 252.3 284.1C243 293.5 242.9 308.7 252.3 318L286.2 351.9L252.3 385.8C242.9 395.2 242.9 410.4 252.3 419.7C261.7 429 276.9 429.1 286.2 419.7L320.1 385.8L354 419.7C363.4 429.1 378.6 429.1 387.9 419.7C397.2 410.3 397.3 395.1 387.9 385.8L354 351.9L387.9 318C397.3 308.6 397.3 293.4 387.9 284.1z"/></svg>
							<span class="label"><?php esc_html_e( 'Aquarium End Date:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_date_closed'] ); ?></span>
						</div>
					</div>
				</div>
				<div class="aqualog-info-card aqualog-info-card--dimensions">
					<h3><?php esc_html_e( 'Aquarium Dimensions', 'PLUGIN_NAME' ); ?></h3>
					<div class="aqualog-info-card-row aqualog-info-card-row--capacity">
						<div class="aqualog-info-card-cell aqualog-info-card-cell__capacity">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M288.3 61.5C308.1 50.1 332.5 50.1 352.3 61.5L528.2 163C548 174.4 560.2 195.6 560.2 218.4L560.2 421.4C560.2 444.3 548 465.4 528.2 476.8L352.3 578.5C332.5 589.9 308.1 589.9 288.3 578.5L112.5 477C92.7 465.6 80.5 444.4 80.5 421.6L80.5 218.6C80.5 195.7 92.7 174.6 112.5 163.2L288.3 61.5zM496.1 421.5L496.1 255.4L352.3 338.4L352.3 504.5L496.1 421.5z"/></svg>
							<span class="label"><?php esc_html_e( 'Tank Capacity:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_size_capacity'] ); ?> <span><?php echo esc_html_x( 'L', 'liters abbreviation', 'PLUGIN_NAME' ); ?></span></span>
						</div>
						<div class="aqualog-info-card-cell aqualog-info-card-cell__volume">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M320 576C214 576 128 490 128 384C128 292.8 258.2 109.9 294.6 60.5C300.5 52.5 309.8 48 319.8 48L320.2 48C330.2 48 339.5 52.5 345.4 60.5C381.8 109.9 512 292.8 512 384C512 490 426 576 320 576zM240 376C240 362.7 229.3 352 216 352C202.7 352 192 362.7 192 376C192 451.1 252.9 512 328 512C341.3 512 352 501.3 352 488C352 474.7 341.3 464 328 464C279.4 464 240 424.6 240 376z"/></svg>
							<span class="label"><?php esc_html_e( 'Water Volume in Tank:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_size_water_volume'] ); ?> <span><?php echo esc_html_x( 'L', 'liters abbreviation', 'PLUGIN_NAME' ); ?></span></span>
						</div>
					</div>
					<div class="aqualog-info-card-row aqualog-info-card-row--dimensions">
						<div class="aqualog-info-card-cell aqualog-info-card-cell__width">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free 7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M80 448C53.5 448 32 426.5 32 400L32 240C32 213.5 53.5 192 80 192L104 192L104 296C104 309.3 114.7 320 128 320C141.3 320 152 309.3 152 296L152 192L200 192L200 264C200 277.3 210.7 288 224 288C237.3 288 248 277.3 248 264L248 192L296 192L296 296C296 309.3 306.7 320 320 320C333.3 320 344 309.3 344 296L344 192L392 192L392 264C392 277.3 402.7 288 416 288C429.3 288 440 277.3 440 264L440 192L488 192L488 296C488 309.3 498.7 320 512 320C525.3 320 536 309.3 536 296L536 192L560 192C586.5 192 608 213.5 608 240L608 400C608 426.5 586.5 448 560 448L80 448z"/></svg>
							<span class="label"><?php esc_html_e( 'Tank Width:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_size_width'] ); ?> <span><?php echo esc_html_x( 'cm', 'centimeters abbreviation', 'PLUGIN_NAME' ); ?></span></span>
						</div>
						<div class="aqualog-info-card-cell aqualog-info-card-cell__height">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 80C192 53.5 213.5 32 240 32L400 32C426.5 32 448 53.5 448 80L448 104L344 104C330.7 104 320 114.7 320 128C320 141.3 330.7 152 344 152L448 152L448 200L376 200C362.7 200 352 210.7 352 224C352 237.3 362.7 248 376 248L448 248L448 296L344 296C330.7 296 320 306.7 320 320C320 333.3 330.7 344 344 344L448 344L448 392L376 392C362.7 392 352 402.7 352 416C352 429.3 362.7 440 376 440L448 440L448 488L344 488C330.7 488 320 498.7 320 512C320 525.3 330.7 536 344 536L448 536L448 560C448 586.5 426.5 608 400 608L240 608C213.5 608 192 586.5 192 560L192 80z"/></svg>
							<span class="label"><?php esc_html_e( 'Tank Height:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_size_height'] ); ?> <span><?php echo esc_html_x( 'cm', 'centimeters abbreviation', 'PLUGIN_NAME' ); ?></span></span>
						</div>
						<div class="aqualog-info-card-cell aqualog-info-card-cell__depth">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M97 505.7C101.5 527.5 120.8 544 144 544L496 544C522.5 544 544 522.5 544 496L544 400C544 373.5 522.5 352 496 352L448 352L448 424C448 437.3 437.3 448 424 448C410.7 448 400 437.3 400 424L400 352L336 352L336 424C336 437.3 325.3 448 312 448C298.7 448 288 437.3 288 424L288 352L216 352C202.7 352 192 341.3 192 328C192 314.7 202.7 304 216 304L288 304L288 240L216 240C202.7 240 192 229.3 192 216C192 202.7 202.7 192 216 192L288 192L288 144C288 117.5 266.5 96 240 96L144 96C117.5 96 96 117.5 96 144L96 496C96 499.3 96.3 502.6 97 505.7z"/></svg>
							<span class="label"><?php esc_html_e( 'Tank Depth:', 'PLUGIN_NAME' ); ?></span>
							<span class="value"><?php echo esc_html( $args['aquarium']['meta']['_iw_size_depth'] ); ?> <span><?php echo esc_html_x( 'cm', 'centimeters abbreviation', 'PLUGIN_NAME' ); ?></span></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Statistics Cards -->
	<section class="aqualog-dashboard-section aqualog-events">
		<h2><?php esc_html_e( 'Events', 'PLUGIN_NAME' ); ?></h2>
		<div class="aqualog-events-rows">
			<?php
			/**
			 * Fires to display events on the aquarium dashboard.
			 *
			 * @since 1.0.0
			 *
			 * @param int $aquarium_id The aquarium ID.
			 */
			do_action( 'iworks/aqualog/aquarium/events/latest', $args['aquarium_id'] );
			?>
		</div>
	</section>

	<!-- Recent Aquariums -->
	<section class="aqualog-dashboard-section aqualog-recent-aquariums-section">
		<?php
		/**
		 * Fires to display recent aquariums on the aquarium dashboard.
		 *
		 * @since 1.0.0
		 */
		do_action( 'iworks/aqualog/dashboard/recent_aquariums' );
		?>
	</section>

</div>
<?php
