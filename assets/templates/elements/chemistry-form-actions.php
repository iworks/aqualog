
<?php
/**
 * Chemistry Form Actions Template
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="aqualog-form-actions">
    <button type="submit" class="button button-primary aqualog-form-submit">
        <span class="dashicons dashicons-saved"></span>
        <?php esc_html_e( 'Save Measurements', 'aqualog' ); ?>
    </button>
    
    <button type="button" class="button aqualog-form-cancel" onclick="window.aqualog.chemistry.closeForm()">
        <?php esc_html_e( 'Cancel', 'aqualog' ); ?>
    </button>
</div>