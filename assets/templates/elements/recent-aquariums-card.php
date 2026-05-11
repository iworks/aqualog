<?php
/**
 * Recent Aquariums Card Template
 *
 * Displays a card with the 5 most recently updated aquariums
 * and a dropdown list for additional aquariums if there are more.
 *
 * @since 1.0.0
 * @package iWorks Aquarium Log
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
	if ( empty( $recent_aquariums ) ) {
		$recent_aquariums = $all_aquariums;
	}
}

// Check if we have more than 5 aquariums total
$has_more             = count( $all_aquariums ) > 5;
$additional_aquariums = $has_more ? array_slice( $all_aquariums, 5 ) : array();
?>

	<h2><?php esc_html_e( 'Select Aquarium', 'iworks-aquarium-log' ); ?></h2>
<div class="aquarium-log-card aquarium-log-recent-aquariums-card">
	<div class="aquarium-log-card-header">
		<h3 class="aquarium-log-card-title">
			<span class="dashicons dashicons-buddicons-groups"></span>
			<?php esc_html_e( 'Recent Aquariums', 'iworks-aquarium-log' ); ?>
		</h3>
		<?php if ( $has_more ) : ?>
			<div class="aquarium-log-card-actions">
				<div class="aquarium-log-dropdown">
					<button class="aquarium-log-dropdown-toggle aquarium-log-button aquarium-log-button-small aquarium-log-button-outline" type="button">
						<span class="dashicons dashicons-arrow-down-alt2"></span>
						<?php esc_html_e( 'More', 'iworks-aquarium-log' ); ?>
					</button>
					<div class="aquarium-log-dropdown-menu">
						<div class="aquarium-log-dropdown-header">
							<?php esc_html_e( 'All Aquariums', 'iworks-aquarium-log' ); ?>
						</div>
						<div class="aquarium-log-dropdown-content">
							<?php foreach ( $additional_aquariums as $aquarium ) : ?>
								<a href="<?php echo esc_url( get_permalink( $aquarium->ID ) ); ?>" class="aquarium-log-dropdown-item">
									<div class="aquarium-log-dropdown-item-title">
										<?php echo esc_html( $aquarium->post_title ); ?>
									</div>
									<div class="aquarium-log-dropdown-item-meta">
										<?php
										$updated_at = get_post_meta( $aquarium->ID, '_related_updated_at', true );
										if ( $updated_at ) {
											echo esc_html( $this->get_time_elapsed_text_seconds( $updated_at ) );
										} else {
											esc_html_e( 'Never updated', 'iworks-aquarium-log' );
										}
										?>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
						<div class="aquarium-log-dropdown-footer">
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=iw_aquarium' ) ); ?>" class="aquarium-log-button aquarium-log-button-small">
								<?php esc_html_e( 'View All Aquariums', 'iworks-aquarium-log' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="aquarium-log-card-content">
		<?php
		if ( ! empty( $recent_aquariums ) ) :
			?>
			<div class="aquarium-log-aquariums-list">
				<?php
				foreach ( $recent_aquariums as $aquarium ) {
					setup_postdata( $aquarium );
					?>
					<?php
					$post_id      = $aquarium->ID;
					$title        = $aquarium->post_title;
					$permalink    = get_permalink( $post_id );
					$updated_at   = get_post_meta( $post_id, '_related_updated_at', true );
					$last_updated = $updated_at ? $updated_at : __( 'Never', 'iworks-aquarium-log' );

					// Get aquarium type
					$types     = wp_get_post_terms( $post_id, 'iw_aquarium_group' );
					$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';

					// Get aquarium capacity if available
					$capacity         = get_post_meta( $post_id, 'capacity', true );
					$capacity_display = $capacity ? sprintf( '%s L', number_format_i18n( $capacity ) ) : '';
					$url              = remove_query_arg( 'change', add_query_arg( 'aquarium_id', $post_id ) );
					?>
					<a class="aquarium-log-aquarium-item" href="<?php echo esc_url( $url ); ?>" data-aquarium-id="<?php echo esc_attr( $post_id ); ?>">
						<div class="aquarium-log-aquarium-thumbnail <?php echo has_post_thumbnail( $post_id ) ? 'has-thumbnail' : 'no-thumbnail'; ?>">
						<?php
						if ( has_post_thumbnail( $post_id ) ) {
							echo get_the_post_thumbnail( $post_id, 'thumbnail', array( 'class' => 'aquarium-log-aquarium-thumbnail-img' ) );
						}
						?>
							</div>
						<div class="aquarium-log-aquarium-info">
							<h3 class="aquarium-log-aquarium-title"><?php echo esc_html( $title ); ?></h3>
							<?php if ( $type_name ) : ?>
								<p class="aquarium-log-aquarium-type"><?php echo esc_html( $type_name ); ?></p>
							<?php endif; ?>
							<div class="aquarium-log-aquarium-meta">
								<?php if ( $capacity_display ) : ?>
									<span class="aquarium-log-aquarium-capacity">
										<span class="dashicons dashicons-volume"></span>
										<?php echo esc_html( $capacity_display ); ?>
									</span>
								<?php endif; ?>
								<span class="aquarium-log-aquarium-updated">
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
			<div class="aquarium-log-empty-state">
				<p><?php esc_html_e( 'No aquariums found.', 'iworks-aquarium-log' ); ?></p>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aquarium-log-button aquarium-log-button-primary">
						<?php esc_html_e( 'Create Your First Aquarium', 'iworks-aquarium-log' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('.aquarium-log-dropdown-toggle').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $dropdown = $(this).closest('.aquarium-log-dropdown');
		var $menu = $dropdown.find('.aquarium-log-dropdown-menu');
		
		// Close other dropdowns
		$('.aquarium-log-dropdown-menu').not($menu).removeClass('is-open');
		
		// Toggle current dropdown
		$menu.toggleClass('is-open');
	});
	
	// Close dropdowns when clicking outside
	$(document).on('click', function() {
		$('.aquarium-log-dropdown-menu').removeClass('is-open');
	});
	
	// Prevent dropdown menu clicks from closing the menu
	$('.aquarium-log-dropdown-menu').on('click', function(e) {
		e.stopPropagation();
	});
});
</script>
