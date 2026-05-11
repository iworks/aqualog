<?php
/**
 * Chemistry Form Header Template
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
<div class="aquarium-log-card-header">
	<h2>{{ data.description }} ({{ data.name }})</h2>
	<span class="aquarium-log-card-header__date"><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></span>
</div>