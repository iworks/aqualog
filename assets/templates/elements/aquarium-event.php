<?php
/**
 * Chemistry Form Actions Template
 *
 * @package    iWorks
 * @subpackage Aqualog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="aquarium-event aquarium-event--<?php echo esc_attr( $args['type'] ); ?>">
	<span class="aquarium-event-icon"></span>
	<div class="aquarium-event-content">
	<div class="aquarium-event-header">
		<h3 class="aquarium-event-title"><?php echo esc_html( $args['date'] ); ?></h3>
		<span class="aquarium-event-date"><?php echo esc_html( $args['message'] ); ?></span>
	</div>
	<div class="aquarium-event-content">
		<p><?php echo esc_html( $args['content'] ); ?></p>
	</div>
	</div>
</div>