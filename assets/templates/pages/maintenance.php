<?php
/**
 * Maintenance page template.
 *
 * Displays maintenance interface for iWorks Aquarium Log plugin.
 * Includes forms for adding and managing maintenance tasks.
 *
 * @package    iWorks\iWorks Aquarium Log
 * @subpackage Templates
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap aquarium-log-maintenance">
	<?php do_action( 'iworks-aquarium-log/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Maintenance', 'iworks-aquarium-log' ); ?></h1>
	
	<div class="aquarium-log-dashboard-grid">
		<div class="aquarium-log-stats-grid">
			<div class="aquarium-log-stat-card">
				<span class="dashicons dashicons-clock"></span>
				<div class="aquarium-log-stat-number"><?php echo esc_html( count( $args['tasks'] ?? 0 ) ); ?></div>
				<div class="aquarium-log-stat-label"><?php esc_html_e( 'Total Tasks', 'iworks-aquarium-log' ); ?></div>
			</div>
			
			<div class="aquarium-log-stat-card">
				<span class="dashicons dashicons-yes-alt"></span>
				<div class="aquarium-log-stat-number"><?php echo esc_html( count( $args['completed'] ?? 0 ) ); ?></div>
				<div class="aquarium-log-stat-label"><?php esc_html_e( 'Completed', 'iworks-aquarium-log' ); ?></div>
			</div>
			
			<div class="aquarium-log-stat-card">
				<span class="dashicons dashicons-calendar-alt"></span>
				<div class="aquarium-log-stat-number"><?php echo esc_html( count( $args['scheduled'] ?? 0 ) ); ?></div>
				<div class="aquarium-log-stat-label"><?php esc_html_e( 'Scheduled', 'iworks-aquarium-log' ); ?></div>
			</div>
		</div>

		<div class="aquarium-log-activity-section">
			<div class="aquarium-log-card">
				<h2><?php esc_html_e( 'Recent Tasks', 'iworks-aquarium-log' ); ?></h2>
				<div class="aquarium-log-tasks-list">
					<?php if ( ! empty( $args['tasks'] ) ) : ?>
						<?php foreach ( $args['tasks'] as $iworks_aquarium_log_task ) : ?>
							<div class="aquarium-log-task-item">
								<div class="task-header">
									<h4><?php echo esc_html( $iworks_aquarium_log_task['title'] ); ?></h4>
									<span class="task-status status-<?php echo esc_attr( $iworks_aquarium_log_task['status'] ); ?>">
										<?php echo esc_html( $iworks_aquarium_log_task['status'] ); ?>
									</span>
									<span class="task-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $iworks_aquarium_log_task['date'] ) ) ); ?></span>
								</div>
								<div class="task-description">
									<?php echo wp_kses_post( $iworks_aquarium_log_task['description'] ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php esc_html_e( 'No maintenance tasks yet. Start tracking your aquarium maintenance by adding your first task.', 'iworks-aquarium-log' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="aquarium-log-quick-actions-section">
			<div class="aquarium-log-card">
				<h2><?php esc_html_e( 'Quick Actions', 'iworks-aquarium-log' ); ?></h2>
				<div class="aquarium-log-actions-grid">
					<a href="#" class="aquarium-log-action-card">
						<span class="dashicons dashicons-plus"></span>
						<span><?php esc_html_e( 'Add Task', 'iworks-aquarium-log' ); ?></span>
					</a>
					
					<a href="#" class="aquarium-log-action-card">
						<span class="dashicons dashicons-list-view"></span>
						<span><?php esc_html_e( 'View All Tasks', 'iworks-aquarium-log' ); ?></span>
					</a>
					
					<a href="#" class="aquarium-log-action-card">
						<span class="dashicons dashicons-backup"></span>
						<span><?php esc_html_e( 'Export Tasks', 'iworks-aquarium-log' ); ?></span>
					</a>
				</div>
			</div>
		</div>
		
	</div>
</div>
