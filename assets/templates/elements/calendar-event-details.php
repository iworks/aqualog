<?php
/**
 * Aqualog Calendar Event Details Template
 *
 * This template displays read-only details of a calendar entry
 * (maintenance task or reminder). Uses WordPress wp.template for
 * JavaScript templating and is rendered inside a modal by the
 * calendar JavaScript.
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
<!-- WordPress wp.template script for calendar entry details -->
<script type="text/html" id="tmpl-aqualog-calendar-event-details">
	<div class="aqualog-calendar-details">
		<div class="aqualog-card">
			<div class="aqualog-card-header">
				<h2>{{ data.title }}</h2>
				<span class="aqualog-task-status status-{{ data.status }}">{{ data.status_label }}</span>
			</div>
			<dl class="aqualog-calendar-details-list">
				<# if ( data.type_label ) { #>
				<dt><?php esc_html_e( 'Task Type', 'PLUGIN_NAME' ); ?></dt>
				<dd>{{ data.type_label }}</dd>
				<# } #>
				<dt><?php esc_html_e( 'Date', 'PLUGIN_NAME' ); ?></dt>
				<dd>{{ data.date }}<# if ( data.time ) { #>, {{ data.time }}<# } #></dd>
				<# if ( data.kind === 'reminder' && data.interval > 0 ) { #>
				<dt><?php esc_html_e( 'Repeats', 'PLUGIN_NAME' ); ?></dt>
				<dd>{{ data.repeat_label }}</dd>
				<# } #>
				<# if ( data.aquarium ) { #>
				<dt><?php esc_html_e( 'Aquarium', 'PLUGIN_NAME' ); ?></dt>
				<dd>{{ data.aquarium }}</dd>
				<# } #>
				<# if ( data.kind === 'task' && data.description ) { #>
				<dt><?php esc_html_e( 'Description', 'PLUGIN_NAME' ); ?></dt>
				<dd>{{ data.description }}</dd>
				<# } #>
			</dl>
			<div class="aqualog-form-actions">
				<# if ( data.kind === 'task' ) { #>
				<button type="button" class="button button-primary aqualog-calendar-details-edit">
					<span class="dashicons dashicons-edit"></span>
					<?php esc_html_e( 'Edit Task', 'PLUGIN_NAME' ); ?>
				</button>
				<# } #>
				<button type="button" class="button aqualog-calendar-details-close">
					<?php esc_html_e( 'Close', 'PLUGIN_NAME' ); ?>
				</button>
			</div>
		</div>
	</div>
</script>
