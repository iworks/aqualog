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
	<h1 class="wp-heading-inline"><?php esc_html_e( 'AquaLog Dashboard', 'aqualog' ); ?></h1>
	<?php do_action( 'aqualog/dashboard/before' ); ?>
			
	<div class="aqualog-dashboard-grid">
		<!-- Statistics Cards -->
		<div class="aqualog-stats-grid">
			<?php do_action( 'aqualog/dashboard/statistics' ); ?>
		</div>

		<!-- Recent Aquariums -->
		<div class="aqualog-recent-aquariums-section">
			<?php do_action( 'aqualog/dashboard/recent_aquariums' ); ?>
		</div>

		<!-- Recent Activity -->
		<div class="aqualog-activity-section">
			<div class="aqualog-card">
				<h2><?php esc_html_e( 'Recent Activity', 'aqualog' ); ?></h2>
				<div class="aqualog-activity-list">
					<?php do_action( 'aqualog/dashboard/recent_activity' ); ?>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="aqualog-quick-actions-section">
			<div class="aqualog-card">
				<h2><?php esc_html_e( 'Quick Actions', 'aqualog' ); ?></h2>
				<div class="aqualog-actions-grid">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
						<span class="dashicons dashicons-plus-alt"></span>
						<span><?php esc_html_e( 'Add Aquarium', 'aqualog' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=iw_aquarium' ) ); ?>" class="aqualog-action-card">
						<span class="dashicons dashicons-list-view"></span>
						<span><?php esc_html_e( 'View All', 'aqualog' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( add_query_arg( 'page', 'iworks_aqualog_index', 'admin.php' ) ) ); ?>" class="aqualog-action-card">
						<span class="dashicons dashicons-admin-settings"></span>
						<span><?php esc_html_e( 'Settings', 'aqualog' ); ?></span>
					</a>
					<a href="<?php echo esc_url( 'https://wordpress.org/plugins/aqualog/' ); ?>" target="_blank" class="aqualog-action-card">
						<span class="dashicons dashicons-external"></span>
						<span><?php esc_html_e( 'Documentation', 'aqualog' ); ?></span>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>
<?php
// Load the chemistry form template
load_template( dirname( __DIR__, 1 ) . '/elements/chemistry-form-single-param.php', false, $args );