<?php
/**
 * Aqualog Maintenance Form Template
 *
 * This template displays a form for adding maintenance tasks.
 * Uses WordPress wp.template for JavaScript templating and is rendered
 * inside a modal by the maintenance JavaScript (the same way the
 * chemistry form is rendered).
 *
 * @package    iWorks
 * @subpackage Aqualog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 *
 * @var array $args {
 *     Array of template arguments.
 *
 *     @type int $aquarium_id Current aquarium ID.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- WordPress wp.template script for maintenance form -->
<script type="text/html" id="tmpl-aqualog-maintenance-form">
	<div class="aqualog-maintenance-form">
		<div class="aqualog-card">
			<div class="aqualog-card-header">
				<h2><?php esc_html_e( 'Add Task', 'PLUGIN_NAME' ); ?></h2>
				<div class="aqualog-card-header-date">
					<input name="date" type="datetime-local" class="aqualog-card-header-date__input" value="<?php echo esc_attr( date_i18n( 'Y-m-d\TH:i' ) ); ?>">
				</div>
			</div>
			<form id="aqualog-maintenance-task-form" data-aquarium-id="<?php echo esc_attr( $args['aquarium_id'] ); ?>">
				<div class="aqualog-form-field">
					<label for="aqualog-maintenance-task-type"><?php esc_html_e( 'Task Type', 'PLUGIN_NAME' ); ?></label>
					<select id="aqualog-maintenance-task-type" name="type" class="aqualog-maintenance-task-type">
						<# _.each( window.iworks_aqualog.maintenance.tasks, function( task, key ) { #>
						<option value="{{ key }}">{{ task.title }}</option>
						<# } ); #>
					</select>
				</div>
				<div class="aqualog-form-field">
					<label for="aqualog-maintenance-task-title"><?php esc_html_e( 'Title', 'PLUGIN_NAME' ); ?></label>
					<input type="text" id="aqualog-maintenance-task-title" name="title" class="aqualog-maintenance-task-title" placeholder="{{ window.iworks_aqualog.maintenance.tasks[ Object.keys( window.iworks_aqualog.maintenance.tasks )[0] ].title }}">
				</div>
				<div class="aqualog-form-field">
					<label for="aqualog-maintenance-task-description"><?php esc_html_e( 'Description', 'PLUGIN_NAME' ); ?></label>
					<textarea id="aqualog-maintenance-task-description" name="description" class="aqualog-maintenance-task-description" rows="4"></textarea>
				</div>
				<div class="aqualog-form-field">
					<label for="aqualog-maintenance-task-status"><?php esc_html_e( 'Status', 'PLUGIN_NAME' ); ?></label>
					<select id="aqualog-maintenance-task-status" name="status" class="aqualog-maintenance-task-status">
						<option value="pending"><?php esc_html_e( 'Pending', 'PLUGIN_NAME' ); ?></option>
						<option value="in_progress"><?php esc_html_e( 'In Progress', 'PLUGIN_NAME' ); ?></option>
						<option value="completed"><?php esc_html_e( 'Completed', 'PLUGIN_NAME' ); ?></option>
					</select>
				</div>
				<div class="aqualog-form-actions">
					<button type="submit" class="button button-primary aqualog-form-submit">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Task', 'PLUGIN_NAME' ); ?>
					</button>
					<button type="button" class="button aqualog-form-cancel" onclick="window.iworks_aqualog.maintenance.closeForm()">
						<?php esc_html_e( 'Cancel', 'PLUGIN_NAME' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</script>
