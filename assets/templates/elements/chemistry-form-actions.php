<?php
/**
 * Chemistry Form Actions Template
 *
 * @package    iWorks
 * @subpackage iWorks Aquarium Log
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="aquarium-log-form-actions">
	<button type="submit" class="button button-primary aquarium-log-form-submit">
		<span class="dashicons dashicons-saved"></span>
		<?php esc_html_e( 'Save Measurements', 'iworks-aquarium-log' ); ?>
	</button>
	
	<button type="button" class="button aquarium-log-form-cancel" onclick="window.iworks_aquarium_log.chemistry.closeForm()">
		<?php esc_html_e( 'Cancel', 'iworks-aquarium-log' ); ?>
	</button>
</div>