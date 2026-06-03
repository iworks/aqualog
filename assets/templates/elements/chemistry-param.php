<?php
/**
 * Aqualog Chemistry Parameter Template
 *
 * This template displays a single chemistry parameter card with its value,
 * scale visualization, and importance indicator. It's used within the
 * chemistry page to show individual water chemistry parameters.
 *
 * @package    iWorks
 * @subpackage Aqualog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
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
		'aqualog-param-importance-' . $args['importance'],
	);
	if ( empty( $args['last_test_date'] ) ) {
		$classes[] = 'no-last-test-date';
	}
	$tooltip = $icon = '';
	switch ( $args['importance'] ) {
		case 'critical':
			$icon    = 'dashicons dashicons-info';
			$tooltip = esc_html__( 'Critical parameter - requires close monitoring', 'PLUGIN_NAME' );
			break;
		case 'important':
			$icon    = 'dashicons dashicons-info';
			$tooltip = esc_html__( 'Important parameter - monitor regularly', 'PLUGIN_NAME' );
			break;
		case 'recommended':
			$icon    = 'dashicons dashicons-info-outline';
			$tooltip = esc_html__( 'Recommended parameter - good to track', 'PLUGIN_NAME' );
			break;
	}
	?>
<div
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	data-key="<?php echo esc_attr( $args['key'] ); ?>"
	data-value="<?php echo esc_attr( $args['value'] ); ?>"
>
	<div class="aqualog-chemistry-item-header">
		<?php if ( ! empty( $icon ) ) { ?>
		<span class="<?php echo esc_attr( $icon ); ?>" title="<?php echo esc_attr( $tooltip ); ?>"></span>
		<?php } ?>
		<h3>
		<?php
			echo esc_html( $args['description'] );
		if ( $args['show_name'] ) {
			echo ' (' . esc_html( $args['name'] ) . ')';
		}
		?>
			</h3>
	</div>
	<div class="aqualog-chemistry-item-body">
		<p class="param-value param-value--<?php echo esc_attr( $args['value_class'] ); ?>">
			<?php
			if ( '' === $args['value'] ) {
				echo '—';
			} else {
				if ( is_numeric( $args['value'] ) ) {
					if ( $args['value'] == floor( $args['value'] ) ) {
						echo esc_html( number_format_i18n( $args['value'] ) );
					} else {
						if ( 0 === $args['value'] * 100 % 10 ) {
							echo esc_html( number_format_i18n( $args['value'], 1 ) );
						} else {
							echo esc_html( number_format_i18n( $args['value'], 2 ) );
						}
					}
				} else {
					echo esc_html( $args['value'] );
				}
				echo ' ';
				echo esc_html( $args['unit'] );
			}
			?>
		</p>
		<p class="param-last-test-date">
			<?php echo esc_html( $args['last_test_date'] ); ?>
			<?php if ( ! empty( $args['frequency'] ) ) { ?>
			<span class="param-frequency">(<?php echo esc_html( $args['frequency'] ); ?>)</span>
			<?php } ?>
		</p>
		<div class="aqualog-chemistry-item-body-scale">
			<?php iworks_aqualog_get_scale( $args ); ?>
			<div class="aqualog-chemistry-item-body-scale-legend">
				<span class="legend-item--min"><?php echo esc_html( $args['range'][0] ); ?> <?php echo esc_html( $args['unit'] ); ?></span>
				<span class="legend-item--max"><?php echo esc_html( $args['range'][1] ); ?> <?php echo esc_html( $args['unit'] ); ?></span>
			</div>
		</div>
	</div>
</div>