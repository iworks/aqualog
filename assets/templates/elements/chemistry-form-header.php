<?php
/**
 * Chemistry Form Header Template
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
<div class="aqualog-card-header">
	<h2>{{ data.description }} ({{ data.name }})</h2>
	<div class="aqualog-card-header-date">
		<input name="date" type="datetime-local" class="aqualog-card-header-date__input" value="<?php echo esc_attr( date_i18n( 'Y-m-d\TH:i' ) ); ?>">
	</div>
</div>