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
						<span class="dashicons dashicons-plus"></span>
						<span><?php esc_html_e( 'Add Reminder', 'PLUGIN_NAME' ); ?></span>
					</a>

					<button type="button" class="aqualog-action-card" id="aqualog-add-note-trigger">
						<span class="dashicons dashicons-plus"></span>
						<span><?php esc_html_e( 'Add Note', 'PLUGIN_NAME' ); ?></span>
					</button>

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

<!-- Add Note Modal -->
<div id="aqualog-add-note-modal" class="aqualog-modal" style="display: none;">
	<div class="aqualog-modal-content">
		<div class="aqualog-modal-header">
			<h2><?php esc_html_e( 'Add Note', 'PLUGIN_NAME' ); ?></h2>
			<button type="button" class="aqualog-modal-close">&times;</button>
		</div>
		<div class="aqualog-modal-body">
			<form id="aqualog-add-note-form" method="post">
				<?php wp_nonce_field( 'aqualog_add_note', 'aqualog_note_nonce' ); ?>
				<input type="hidden" name="action" value="aqualog_add_note">

				<div class="aqualog-form-field">
					<label for="note-title"><?php esc_html_e( 'Title', 'PLUGIN_NAME' ); ?></label>
					<input type="text" id="note-title" name="note_title" required>
				</div>

				<div class="aqualog-form-field">
					<label for="note-content"><?php esc_html_e( 'Content', 'PLUGIN_NAME' ); ?></label>
					<textarea id="note-content" name="note_content" rows="5"></textarea>
				</div>

				<div class="aqualog-form-field">
					<label for="note-aquarium"><?php esc_html_e( 'Related Aquarium', 'PLUGIN_NAME' ); ?></label>
					<select id="note-aquarium" name="aquarium_id">
						<option value=""><?php esc_html_e( 'Select Aquarium', 'PLUGIN_NAME' ); ?></option>
						<?php
						$aquariums = get_posts(
							array(
								'post_type'      => 'iw_aquarium',
								'posts_per_page' => -1,
								'post_status'    => 'publish',
							)
						);
						foreach ( $aquariums as $aquarium ) :
							?>
							<option value="<?php echo esc_attr( $aquarium->ID ); ?>"><?php echo esc_html( $aquarium->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="aqualog-form-field">
					<label for="note-date"><?php esc_html_e( 'Note Date', 'PLUGIN_NAME' ); ?></label>
					<input type="date" id="note-date" name="note_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>">
				</div>

				<div class="aqualog-form-field">
					<label for="note-type"><?php esc_html_e( 'Note Type', 'PLUGIN_NAME' ); ?></label>
					<select id="note-type" name="note_type">
						<option value="observation"><?php esc_html_e( 'Observation', 'PLUGIN_NAME' ); ?></option>
						<option value="maintenance"><?php esc_html_e( 'Maintenance', 'PLUGIN_NAME' ); ?></option>
						<option value="reminder"><?php esc_html_e( 'Reminder', 'PLUGIN_NAME' ); ?></option>
						<option value="general"><?php esc_html_e( 'General', 'PLUGIN_NAME' ); ?></option>
					</select>
				</div>

				<div class="aqualog-form-actions">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Add Note', 'PLUGIN_NAME' ); ?></button>
					<button type="button" class="button aqualog-modal-cancel"><?php esc_html_e( 'Cancel', 'PLUGIN_NAME' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

