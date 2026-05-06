<?php
/**
 * AquaLog Chemistry Form Template
 *
 * This template displays a form for entering or editing chemistry parameter values.
 * It includes input fields for selected parameters with validation and submission handling.
 * Uses WordPress wp.template for JavaScript templating.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @version    1.0.0
 * @since      1.0.0
 *
 * @var array $args {
 *     Array of template arguments.
 *
 *     @type int    $aquarium_id Current aquarium ID.
 *     @type array  $params     Available chemistry parameters.
 *     @type array  $values     Current values for editing (optional).
 *     @type string $form_title Form title.
 *     @type string $submit_text Submit button text.
 *     @type string $action     Form action URL.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- WordPress wp.template script for chemistry form -->
<script type="text/html" id="tmpl-aqualog-chemistry-form">
	<div class="aqualog-chemistry-form">
		<div class="aqualog-card">
			<div class="aqualog-card-header">
				<h2>{{ data.description }} ({{ data.name }})</h2>
				<span class="aqualog-card-header__date"><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></span>
			</div>
			
			<form id="aqualog-chemistry-measurement-form" data-aquarium-id="<?php echo esc_attr( $args['aquarium_id'] ); ?>">
				<div class="aqualog-chemistry-item-body-scale-buttons">
					<button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="-{{data.step_big}}"><span class="dashicons dashicons-remove"></span>{{data.step_big}}</button>
					<button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="-{{data.step_small}}"><span class="dashicons dashicons-minus"></span>{{data.step_small}}</button>
					<div class="aqualog-chemistry-item-body-scale-value-container">
						<input type="number" name="measurement" class="aqualog-chemistry-item-body-scale-value" value="">
						<span class="aqualog-chemistry-item-body-scale-value-unit">{{data.unit}}</span>
					</div>
					<button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="{{data.step_small}}"><span class="dashicons dashicons-plus"></span>{{data.step_small}}</button>
					<button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="{{data.step_big}}"><span class="dashicons dashicons-insert"></span>{{data.step_big}}</button>
				</div>

				<div class="aqualog-chemistry-item-body-scale">
					<div class="aqualog-chemistry-item-body-scale-char aqualog-chemistry-item-body-scale-slider" data-range-min="{{data.range[0]}}" data-range-max="{{data.range[1]}}" data-range-step="{{data.range_step}}" style="{{data.style}}"></div>
					<div class="aqualog-chemistry-item-body-scale-legend">
						<span class="legend-item--min">{{data.range[0]}} {{data.unit}}</span>
						<span class="legend-item--max">{{data.range[1]}} {{data.unit}}</span>
					</div>
				</div>
				
				<div class="aqualog-form-actions">
					<button type="submit" class="button button-primary aqualog-form-submit">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Measurements', 'aqualog' ); ?>
					</button>
					
					<button type="button" class="button aqualog-form-cancel" onclick="window.aqualog.chemistry.closeForm()">
						<?php esc_html_e( 'Cancel', 'aqualog' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</script>
