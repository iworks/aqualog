<?php
/**
 * Maintenance page template.
 *
 * Displays maintenance interface for Aqualog plugin.
 * Includes forms for adding and managing maintenance tasks.
 *
 * @package    iWorks\Aqualog
 * @subpackage Templates
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap aqualog-maintenance">
	<?php do_action( 'iworks/aqualog/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Maintenance', 'PLUGIN_NAME' ); ?></h1>
	
	<div class="aqualog-dashboard-grid">
		<div class="aqualog-stats-grid">
			<div class="aqualog-stat-card">
				<span class="dashicons dashicons-clock"></span>
				<div class="aqualog-stat-number"><?php echo esc_html( count( $args['tasks'] ?? 0 ) ); ?></div>
				<div class="aqualog-stat-label"><?php esc_html_e( 'Total Tasks', 'PLUGIN_NAME' ); ?></div>
			</div>
			
			<div class="aqualog-stat-card">
				<span class="dashicons dashicons-yes-alt"></span>
				<div class="aqualog-stat-number"><?php echo esc_html( count( $args['completed'] ?? 0 ) ); ?></div>
				<div class="aqualog-stat-label"><?php esc_html_e( 'Completed', 'PLUGIN_NAME' ); ?></div>
			</div>
			
			<div class="aqualog-stat-card">
				<span class="dashicons dashicons-calendar-alt"></span>
				<div class="aqualog-stat-number"><?php echo esc_html( count( $args['scheduled'] ?? 0 ) ); ?></div>
				<div class="aqualog-stat-label"><?php esc_html_e( 'Scheduled', 'PLUGIN_NAME' ); ?></div>
			</div>
		</div>

		<div class="aqualog-activity-section">
			<div class="aqualog-card">
				<h2><?php esc_html_e( 'Recent Tasks', 'PLUGIN_NAME' ); ?></h2>
				<div class="aqualog-tasks-list">
					<?php if ( ! empty( $args['tasks'] ) ) : ?>
						<?php foreach ( $args['tasks'] as $iworks_aqualog_task ) : ?>
							<div class="aqualog-task-item">
								<div class="task-header">
									<h4><?php echo esc_html( $iworks_aqualog_task['title'] ); ?></h4>
									<span class="task-status status-<?php echo esc_attr( $iworks_aqualog_task['status'] ); ?>">
										<?php echo esc_html( $iworks_aqualog_task['status'] ); ?>
									</span>
									<span class="task-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $iworks_aqualog_task['date'] ) ) ); ?></span>
								</div>
								<div class="task-description">
									<?php echo wp_kses_post( $iworks_aqualog_task['description'] ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php esc_html_e( 'No maintenance tasks yet. Start tracking your aquarium maintenance by adding your first task.', 'PLUGIN_NAME' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="aqualog-quick-actions-section">
			<div class="aqualog-card">
				<h2><?php esc_html_e( 'Quick Actions', 'PLUGIN_NAME' ); ?></h2>
				<div class="aqualog-actions-grid">
					<a href="#" class="aqualog-action-card">
						<span class="dashicons dashicons-plus"></span>
						<span><?php esc_html_e( 'Add Task', 'PLUGIN_NAME' ); ?></span>
					</a>
					
					<a href="#" class="aqualog-action-card">
						<span class="dashicons dashicons-list-view"></span>
						<span><?php esc_html_e( 'View All Tasks', 'PLUGIN_NAME' ); ?></span>
					</a>
					
					<a href="#" class="aqualog-action-card">
						<span class="dashicons dashicons-backup"></span>
						<span><?php esc_html_e( 'Export Tasks', 'PLUGIN_NAME' ); ?></span>
					</a>
				</div>
			</div>
		</div>
		
	</div>
</div>
