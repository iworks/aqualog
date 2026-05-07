<?php
/**
 * Maintenance page template.
 *
 * Displays maintenance interface for AquaLog plugin.
 * Includes forms for adding and managing maintenance tasks.
 *
 * @package    iWorks\AquaLog
 * @subpackage Templates
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap aqualog-maintenance">
	<?php do_action( 'aqualog/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Maintenance', 'aqualog' ); ?></h1>
	
	<div class="aqualog-dashboard-grid">
		<div class="aqualog-stats-grid">
			<div class="aqualog-stat-card">
				<span class="dashicons dashicons-clock"></span>
				<div class="aqualog-stat-number"><?php echo esc_html( count( $args['tasks'] ?? 0 ) ); ?></div>
				<div class="aqualog-stat-label"><?php esc_html_e( 'Total Tasks', 'aqualog' ); ?></div>
			</div>
			
			<div class="aqualog-stat-card">
				<span class="dashicons dashicons-yes-alt"></span>
				<div class="aqualog-stat-number"><?php echo esc_html( count( $args['completed'] ?? 0 ) ); ?></div>
				<div class="aqualog-stat-label"><?php esc_html_e( 'Completed', 'aqualog' ); ?></div>
			</div>
			
			<div class="aqualog-stat-card">
				<span class="dashicons dashicons-calendar-alt"></span>
				<div class="aqualog-stat-number"><?php echo esc_html( count( $args['scheduled'] ?? 0 ) ); ?></div>
				<div class="aqualog-stat-label"><?php esc_html_e( 'Scheduled', 'aqualog' ); ?></div>
			</div>
		</div>

		<div class="aqualog-activity-section">
			<div class="aqualog-card">
				<h2><?php esc_html_e( 'Recent Tasks', 'aqualog' ); ?></h2>
				<div class="aqualog-tasks-list">
					<?php if ( ! empty( $args['tasks'] ) ) : ?>
						<?php foreach ( $args['tasks'] as $task ) : ?>
							<div class="aqualog-task-item">
								<div class="task-header">
									<h4><?php echo esc_html( $task['title'] ); ?></h4>
									<span class="task-status status-<?php echo esc_attr( $task['status'] ); ?>">
										<?php echo esc_html( $task['status'] ); ?>
									</span>
									<span class="task-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $task['date'] ) )); ?></span>
								</div>
								<div class="task-description">
									<?php echo wp_kses_post( $task['description'] ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php esc_html_e( 'No maintenance tasks yet. Start tracking your aquarium maintenance by adding your first task.', 'aqualog' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="aqualog-quick-actions-section">
			<div class="aqualog-card">
				<h2><?php esc_html_e( 'Quick Actions', 'aqualog' ); ?></h2>
				<div class="aqualog-actions-grid">
					<a href="#" class="aqualog-action-card">
						<span class="dashicons dashicons-plus"></span>
						<span><?php esc_html_e( 'Add Task', 'aqualog' ); ?></span>
					</a>
					
					<a href="#" class="aqualog-action-card">
						<span class="dashicons dashicons-list-view"></span>
						<span><?php esc_html_e( 'View All Tasks', 'aqualog' ); ?></span>
					</a>
					
					<a href="#" class="aqualog-action-card">
						<span class="dashicons dashicons-backup"></span>
						<span><?php esc_html_e( 'Export Tasks', 'aqualog' ); ?></span>
					</a>
				</div>
			</div>
		</div>
		
	</div>
</div>
