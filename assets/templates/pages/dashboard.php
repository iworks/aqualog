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
	<?php do_action( 'iworks/aqualog/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Aqualog Dashboard', 'PLUGIN_NAME' ); ?></h1>
	<?php do_action( 'iworks/aqualog/dashboard/before' ); ?>
			
	<section class="aqualog-dashboard-section aqualog-dashboard-grid">
		<!-- Statistics Cards -->
		<div class="aqualog-recent">
			<h2><?php esc_html_e( 'Recent Aquariums', 'PLUGIN_NAME' ); ?></h2>
<?php
if ( $args['recent_aquariums'] ) {
	echo '<div class="aqualog-aquariums-list">';
	foreach ( $args['recent_aquariums'] as $aquarium ) {
		setup_postdata( $aquarium );
		$post_id      = $aquarium->ID;
		$title        = $aquarium->post_title;
		$permalink    = get_permalink( $post_id );
		$updated_at   = get_post_meta( $post_id, '_related_updated_at', true );
		$last_updated = $updated_at ? $updated_at : esc_html__( 'Never', 'PLUGIN_NAME' );

		// Get aquarium type
		$types     = wp_get_post_terms( $post_id, 'iw_aquarium_group' );
		$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';

		// Get aquarium capacity if available
		$capacity         = get_post_meta( $post_id, 'capacity', true );
		$capacity_display = $capacity ? sprintf( '%s L', number_format_i18n( $capacity ) ) : '';
		$url              = remove_query_arg(
			'change',
			add_query_arg(
				array(
					'aquarium_id' => $post_id,
					'_wpnonce'    => $args['nonces']['set_current_aquarium'],
				)
			)
		);
		$classes          = array( 'aqualog-aquarium-item' );
		if ( isset( $args['aquarium_id'] ) && $post_id === $args['aquarium_id'] ) {
			$classes[] = 'current';
			$url       = remove_query_arg( 'change', add_query_arg( 'page', 'aqualog-current-tank', $url ) );
		}
		?>
					<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_url( $url ); ?>" data-aquarium-id="<?php echo esc_attr( $post_id ); ?>">
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
				echo '</div>';
} else {
	?>
	<p><?php esc_html_e( 'No recent aquariums found.', 'PLUGIN_NAME' ); ?></p>
	<?php
}
?>
		</section>
		
		<!-- Statistics Cards -->
		<section class="aqualog-dashboard-section aqualog-stats">
			<h2><?php esc_html_e( 'Statistics', 'PLUGIN_NAME' ); ?></h2>
			<div class="aqualog-stats-grid">
				<?php do_action( 'iworks/aqualog/dashboard/statistics' ); ?>
			</div>
		</section>

		<!-- Recent Aquariums -->
		<section class="aqualog-dashboard-section aqualog-recent-aquariums-section">
			<?php do_action( 'iworks/aqualog/dashboard/recent_aquariums' ); ?>
		</section>

		<!-- Recent Activity -->
		<section class="aqualog-dashboard-section aqualog-activity-section">
			<h2><?php esc_html_e( 'Recent Activity', 'PLUGIN_NAME' ); ?></h2>
			<div class="aqualog-card">
				<div class="aqualog-activity-list">
					<?php do_action( 'iworks/aqualog/dashboard/recent_activity' ); ?>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="aqualog-quick-actions-section">
			<h2><?php esc_html_e( 'Quick Actions', 'PLUGIN_NAME' ); ?></h2>
			<div class="aqualog-card">
				<div class="aqualog-actions-grid">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
						<span class="dashicons dashicons-plus-alt"></span>
						<span><?php esc_html_e( 'Add Aquarium', 'PLUGIN_NAME' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
						<span class="dashicons dashicons-list-view"></span>
						<span><?php esc_html_e( 'View All', 'PLUGIN_NAME' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( add_query_arg( 'page', 'iworks_aqualog_index', 'admin.php' ) ) ); ?>" class="aqualog-action-card">
						<span class="dashicons dashicons-admin-settings"></span>
						<span><?php esc_html_e( 'Settings', 'PLUGIN_NAME' ); ?></span>
					</a>
					<a href="<?php echo esc_url( 'https://wordpress.org/plugins/aqualog/' ); ?>" target="_blank" class="aqualog-action-card">
						<span class="dashicons dashicons-external"></span>
						<span><?php esc_html_e( 'Documentation', 'PLUGIN_NAME' ); ?></span>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>