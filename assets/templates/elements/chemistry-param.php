<?php
/**
 * AquaLog Chemistry Parameter Template
 *
 * This template displays a single chemistry parameter card with its value,
 * scale visualization, and importance indicator. It's used within the
 * chemistry page to show individual water chemistry parameters.
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
 *     @type string $key        Parameter key identifier.
 *     @type string $name       Display name of the parameter.
 *     @type string $value      Current measured value.
 *     @type string $unit       Unit of measurement.
 *     @type array  $range      Min and max range values [min, max].
 *     @type string $importance Importance level (critical, important, recommended, default).
 *     @type string $icon       Dashicon class name for the parameter.
 * }
 */

defined( 'ABSPATH' ) || exit;

$classes = array(
    'aqualog-chemistry-item',
    'aqualog-chemistry-item--' . $args['key'],
    'param-importance-' . $args['importance'],
);
if ( empty( $args['last_test_date'] ) ) {
    $classes[] = 'no-last-test-date';
}
$icon1 = '';
switch( $args['importance'] ) {
    case 'critical':
    case 'important':
        $icon1 = 'dashicons dashicons-info';
        break;
    case 'recommended':
        $icon1 = 'dashicons dashicons-info-outline';
        break;
}
?>
<div class="<?php echo implode( ' ', $classes ); ?>">
    <div class="aqualog-chemistry-item-header">
        <?php if ( ! empty( $icon1 ) ) { ?>
        <span class="<?php echo esc_attr( $icon1 ); ?>"></span>
        <?php } ?>
        <h3><?php echo esc_html( $args['description'] ); ?> (<?php echo esc_html( $args['name'] ); ?>)</h3>
    </div>
    <div class="aqualog-chemistry-item-body">
        <p class="param-value"><?php echo esc_html( $args['value'] ); ?></p>
        <p class="param-last-test-date">
            <?php echo esc_html( $args['last_test_date'] ); ?>
            <?php if ( ! empty( $args['frequency'] ) ) { ?>
            <span class="param-frequency">(<?php echo esc_html( $args['frequency'] ); ?>)</span>
            <?php } ?>
        </p>
        <div class="aqualog-chemistry-item-body-scale">
            <div class="aqualog-chemistry-item-body-scale-char" data-min="<?php echo esc_attr( $args['range'][0] ); ?>" data-max="<?php echo esc_attr( $args['range'][1] ); ?>">
                <?php echo aqualog_chemistry_scale_item( $args, 'danger' ); ?>
                <?php echo aqualog_chemistry_scale_item( $args, 'safety' ); ?>
                <?php echo aqualog_chemistry_scale_item( $args, 'ideal' ); ?>
            </div>
            <div class="aqualog-chemistry-item-body-scale-legend">
                <span class="legend-item--min"><?php echo esc_html( $args['range'][0] ); ?> <?php echo esc_html( $args['unit'] ); ?></span>
                <span class="legend-item--max"><?php echo esc_html( $args['range'][1] ); ?> <?php  echo esc_html( $args['unit'] ); ?></span>
            </div>
        </div>
    </div>
</div>