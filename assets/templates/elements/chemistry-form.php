<?php
/**
 * iWorks Aquarium Log Chemistry Form Template
 *
 * This template displays a form for entering or editing chemistry parameter values.
 * It includes input fields for selected parameters with validation and submission handling.
 * Uses WordPress wp.template for JavaScript templating.
 *
 * @package    iWorks
 * @subpackage iWorks Aquarium Log
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
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
<script type="text/html" id="tmpl-aquarium-log-chemistry-form">
	<div class="aquarium-log-chemistry-form">
		<div class="aquarium-log-card">
<?php load_template( __DIR__ . '/chemistry-form-header.php' ); ?>
			<form id="aquarium-log-chemistry-measurement-form" data-aquarium-id="<?php echo esc_attr( $args['aquarium_id'] ); ?>">
<?php load_template( __DIR__ . '/chemistry-form-body.php' ); ?>
<?php load_template( __DIR__ . '/chemistry-form-actions.php' ); ?>
			</form>
		</div>
	</div>
</script>
