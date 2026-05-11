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
			
	<div class="aquarium-log-dashboard-grid">
		<!-- Statistics Cards -->
		<div class="aquarium-log-stats-grid">
			<?php do_action( 'iworks-aquarium-log/dashboard/statistics' ); ?>
		</div>

		<!-- Recent Aquariums -->
		<div class="aquarium-log-recent-aquariums-section">
			<?php do_action( 'iworks-aquarium-log/dashboard/recent_aquariums' ); ?>
		</div>

		<!-- Recent Activity -->
		<div class="aquarium-log-activity-section">
			<div class="aquarium-log-card">
				<h2><?php esc_html_e( 'Recent Activity', 'iworks-aquarium-log' ); ?></h2>
				<div class="aquarium-log-activity-list">
					<?php do_action( 'iworks-aquarium-log/dashboard/recent_activity' ); ?>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="aquarium-log-quick-actions-section">
			<div class="aquarium-log-card">
				<h2><?php esc_html_e( 'Quick Actions', 'iworks-aquarium-log' ); ?></h2>
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