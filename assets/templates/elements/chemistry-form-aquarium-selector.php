<?php
/**
 * AquaLog Chemistry Form Aquarium Selector Template
 *
 * This template displays the aquarium selector for chemistry forms.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */
?>
<div class="aqualog-aquarium-selector">
    <label for="aqualog-aquarium-id"><?php esc_html_e( 'Aquarium', 'aqualog' ); ?></label>
    <select id="aqualog-aquarium-id" name="aqualog_aquarium_id">
        <?php foreach ( $args['aquariums'] as $aquarium ) : ?>
            <option value="<?php echo esc_attr( $aquarium->ID ); ?>"><?php echo esc_html( $aquarium->post_title ); ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php