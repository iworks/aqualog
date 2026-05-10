<?php
/**
 * Recent Aquariums Card Template
 *
 * Displays a card with the 5 most recently updated aquariums
 * and a dropdown list for additional aquariums if there are more.
 *
 * @since 1.0.0
 * @package AquaLog
 */

defined( 'ABSPATH' ) || exit;

// Get recent aquariums (limit to 5)
$recent_aquariums = array();
$all_aquariums    = array();

if ( isset( $args['recent_aquariums'] ) && is_array( $args['recent_aquariums'] ) ) {
	$recent_aquariums = $args['recent_aquariums'];
}

if ( isset( $args['all_aquariums'] ) && is_array( $args['all_aquariums'] ) ) {
	$all_aquariums = $args['all_aquariums'];
}

// Check if we have more than 5 aquariums total
$has_more             = count( $all_aquariums ) > 5;
$additional_aquariums = $has_more ? array_slice( $all_aquariums, 5 ) : array();
?>

	<h2><?php esc_html_e( 'Select Aquarium', 'aqualog' ); ?></h2>
<div class="aqualog-card aqualog-recent-aquariums-card">
	<div class="aqualog-card-header">
		<h3 class="aqualog-card-title">
			<span class="dashicons dashicons-buddicons-groups"></span>
			<?php esc_html_e( 'Recent Aquariums', 'aqualog' ); ?>
		</h3>
		<?php if ( $has_more ) : ?>
			<div class="aqualog-card-actions">
				<div class="aqualog-dropdown">
					<button class="aqualog-dropdown-toggle aqualog-button aqualog-button-small aqualog-button-outline" type="button">
						<span class="dashicons dashicons-arrow-down-alt2"></span>
						<?php esc_html_e( 'More', 'aqualog' ); ?>
					</button>
					<div class="aqualog-dropdown-menu">
						<div class="aqualog-dropdown-header">
							<?php esc_html_e( 'All Aquariums', 'aqualog' ); ?>
						</div>
						<div class="aqualog-dropdown-content">
							<?php foreach ( $additional_aquariums as $aquarium ) : ?>
								<a href="<?php echo esc_url( get_permalink( $aquarium->ID ) ); ?>" class="aqualog-dropdown-item">
									<div class="aqualog-dropdown-item-title">
										<?php echo esc_html( $aquarium->post_title ); ?>
									</div>
									<div class="aqualog-dropdown-item-meta">
										<?php
										$updated_at = get_post_meta( $aquarium->ID, '_related_updated_at', true );
										if ( $updated_at ) {
											echo esc_html( $this->get_time_elapsed_text_seconds( $updated_at ) );
										} else {
											esc_html_e( 'Never updated', 'aqualog' );
										}
										?>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
						<div class="aqualog-dropdown-footer">
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-button aqualog-button-small">
								<?php esc_html_e( 'View All Aquariums', 'aqualog' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="aqualog-card-content">
		<?php
		if ( ! empty( $recent_aquariums ) ) :
			?>
			<div class="aqualog-aquariums-list">
				<?php
				foreach ( $recent_aquariums as $aquarium ) {
					setup_postdata( $aquarium );
					?>
					<?php
					$post_id      = $aquarium->ID;
					$title        = $aquarium->post_title;
					$permalink    = get_permalink( $post_id );
					$updated_at   = get_post_meta( $post_id, '_related_updated_at', true );
					$last_updated = $updated_at ? $updated_at : __( 'Never', 'aqualog' );

					// Get aquarium type
					$types     = wp_get_post_terms( $post_id, 'iw_aquarium_group' );
					$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';

					// Get aquarium capacity if available
					$capacity         = get_post_meta( $post_id, 'capacity', true );
					$capacity_display = $capacity ? sprintf( '%s L', number_format_i18n( $capacity ) ) : '';
					$url              = remove_query_arg( 'change', add_query_arg( 'aquarium_id', $post_id ) );
					?>
					<a class="aqualog-aquarium-item" href="<?php echo esc_url( $url ); ?>" data-aquarium-id="<?php echo esc_attr( $post_id ); ?>">
						<div class="aqualog-aquarium-thumbnail <?php echo has_post_thumbnail( $post_id ) ? 'has-thumbnail' : 'no-thumbnail'; ?>">
						<?php
						if ( has_post_thumbnail( $post_id ) ) {
							echo get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'aqualog-aquarium-thumbnail-img' ) );
						}
						?>
							</div>
						<div class="aqualog-aquarium-info">
							<h3 class="aqualog-aquarium-title"><?php echo esc_html( $title ); ?></h3>
							<?php if ( $type_name ) : ?>
								<p class="aqualog-aquarium-type"><?php echo esc_html( $type_name ); ?></p>
							<?php endif; ?>
							<div class="aqualog-aquarium-meta">
								<?php if ( $capacity_display ) : ?>
									<span class="aqualog-aquarium-capacity">
										<span class="dashicons dashicons-volume"></span>
										<?php echo esc_html( $capacity_display ); ?>
									</span>
								<?php endif; ?>
								<span class="aqualog-aquarium-updated">
									<span class="dashicons dashicons-clock"></span>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $last_updated ) ) ); ?>
								</span>
							</div>
							
						</div>
					</a>
					<?php
				}
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="aqualog-empty-state">
				<p><?php esc_html_e( 'No aquariums found.', 'aqualog' ); ?></p>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-button aqualog-button-primary">
						<?php esc_html_e( 'Create Your First Aquarium', 'aqualog' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('.aqualog-dropdown-toggle').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $dropdown = $(this).closest('.aqualog-dropdown');
		var $menu = $dropdown.find('.aqualog-dropdown-menu');
		
		// Close other dropdowns
		$('.aqualog-dropdown-menu').not($menu).removeClass('is-open');
		
		// Toggle current dropdown
		$menu.toggleClass('is-open');
	});
	
	// Close dropdowns when clicking outside
	$(document).on('click', function() {
		$('.aqualog-dropdown-menu').removeClass('is-open');
	});
	
	// Prevent dropdown menu clicks from closing the menu
	$('.aqualog-dropdown-menu').on('click', function(e) {
		e.stopPropagation();
	});
});
</script>
