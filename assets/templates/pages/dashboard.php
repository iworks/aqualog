<?php
/**
 * Dashboard page template.
 *
 * @package Aqualog
 * @subpackage Templates
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'iWorks Aquarium Log Dashboard', 'iworks-aquarium-log' ); ?></h1>
	<?php do_action( 'iworks-aquarium-log/dashboard/before' ); ?>
			
	<section class="aquarium-log-dashboard-section aquarium-log-dashboard-grid">
		<!-- Statistics Cards -->
		<div class="aquarium-log-recent">
			<h2><?php esc_html_e( 'Recent Aquariums', 'iworks-aquarium-log' ); ?></h2>
<?php
if ( $args['recent_aquariums'] ) {
	echo '<div class="aquarium-log-aquariums-list">';
	foreach ( $args['recent_aquariums'] as $aquarium ) {
		setup_postdata( $aquarium );
		$post_id      = $aquarium->ID;
		$title        = $aquarium->post_title;
		$permalink    = get_permalink( $post_id );
		$updated_at   = get_post_meta( $post_id, '_related_updated_at', true );
		$last_updated = $updated_at ? $updated_at : esc_html__( 'Never', 'iworks-aquarium-log' );

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
				echo '</div>';
} else {
	?>
	<p><?php esc_html_e( 'No recent aquariums found.', 'iworks-aquarium-log' ); ?></p>
	<?php
}
?>
		</section>
		
		<!-- Statistics Cards -->
		<section class="aquarium-log-dashboard-section aquarium-log-stats">
			<h2><?php esc_html_e( 'Statistics', 'iworks-aquarium-log' ); ?></h2>
			<div class="aquarium-log-stats-grid">
				<?php do_action( 'iworks-aquarium-log/dashboard/statistics' ); ?>
			</div>
		</section>

		<!-- Recent Aquariums -->
		<section class="aquarium-log-dashboard-section aquarium-log-recent-aquariums-section">
			<?php do_action( 'iworks-aquarium-log/dashboard/recent_aquariums' ); ?>
		</section>

		<!-- Recent Activity -->
		<section class="aquarium-log-dashboard-section aquarium-log-activity-section">
			<h2><?php esc_html_e( 'Recent Activity', 'iworks-aquarium-log' ); ?></h2>
			<div class="aquarium-log-card">
				<div class="aquarium-log-activity-list">
					<?php do_action( 'iworks-aquarium-log/dashboard/recent_activity' ); ?>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="aquarium-log-quick-actions-section">
			<h2><?php esc_html_e( 'Quick Actions', 'iworks-aquarium-log' ); ?></h2>
			<div class="aquarium-log-card">
				<div class="aquarium-log-actions-grid">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aquarium-log-action-card">
						<span class="dashicons dashicons-plus-alt"></span>
						<span><?php esc_html_e( 'Add Aquarium', 'iworks-aquarium-log' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=iw_aquarium' ) ); ?>" class="aquarium-log-action-card">
						<span class="dashicons dashicons-list-view"></span>
						<span><?php esc_html_e( 'View All', 'iworks-aquarium-log' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( add_query_arg( 'page', 'iworks_aquarium_log_index', 'admin.php' ) ) ); ?>" class="aquarium-log-action-card">
						<span class="dashicons dashicons-admin-settings"></span>
						<span><?php esc_html_e( 'Settings', 'iworks-aquarium-log' ); ?></span>
					</a>
					<a href="<?php echo esc_url( 'https://wordpress.org/plugins/iworks-aquarium-log/' ); ?>" target="_blank" class="aquarium-log-action-card">
						<span class="dashicons dashicons-external"></span>
						<span><?php esc_html_e( 'Documentation', 'iworks-aquarium-log' ); ?></span>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>