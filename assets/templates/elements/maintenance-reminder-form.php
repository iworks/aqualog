<?php
/**
 * Aqualog Maintenance Reminder Form Template
 *
 * This template displays a form for adding maintenance reminders.
 * Uses WordPress wp.template for JavaScript templating and is rendered
 * inside a modal by the maintenance JavaScript (the same way the
 * task form is rendered).
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
<!-- WordPress wp.template script for maintenance reminder form -->
<script type="text/html" id="tmpl-aqualog-maintenance-reminder-form">
	<div class="aqualog-maintenance-form">
		<div class="aqualog-card">
			<div class="aqualog-card-header">
				<h2><?php esc_html_e( 'Add Reminder', 'PLUGIN_NAME' ); ?></h2>
				<div class="aqualog-card-header-date">
					<input name="date" type="datetime-local" class="aqualog-card-header-date__input" value="<?php echo esc_attr( date_i18n( 'Y-m-d\TH:i' ) ); ?>">
				</div>
			</div>
			<form id="aqualog-maintenance-reminder-form" data-aquarium-id="<?php echo esc_attr( $args['aquarium_id'] ); ?>">
				<div class="aqualog-form-field">
					<label for="aqualog-maintenance-reminder-type"><?php esc_html_e( 'Task Type', 'PLUGIN_NAME' ); ?></label>
					<select id="aqualog-maintenance-reminder-type" name="type" class="aqualog-maintenance-reminder-type">
						<# _.each( window.iworks_aqualog.maintenance.tasks, function( task, key ) { #>
						<option value="{{ key }}">{{ task.title }}</option>
						<# } ); #>
					</select>
				</div>
				<div class="aqualog-form-field">
					<label for="aqualog-maintenance-reminder-frequency"><?php esc_html_e( 'Repeat', 'PLUGIN_NAME' ); ?></label>
					<select id="aqualog-maintenance-reminder-frequency" name="frequency" class="aqualog-maintenance-reminder-frequency">
						<# _.each( window.iworks_aqualog.maintenance.frequencies, function( label, key ) { #>
						<option value="{{ key }}">{{ label }}</option>
						<# } ); #>
					</select>
				</div>
				<div class="aqualog-form-actions">
					<button type="submit" class="button button-primary aqualog-form-submit">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Reminder', 'PLUGIN_NAME' ); ?>
					</button>
					<button type="button" class="button aqualog-form-cancel" onclick="window.iworks_aqualog.maintenanceReminder.closeForm()">
						<?php esc_html_e( 'Cancel', 'PLUGIN_NAME' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</script>
