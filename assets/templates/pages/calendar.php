<?php
/**
 * Calendar page template.
 *
 * Displays a month or week grid of maintenance tasks and reminders
 * for the current aquarium. Server-rendered, no external calendar
 * library. Navigation uses plain links (view=month|week&date=YYYY-MM-DD).
 *
 * @package    iWorks\Aqualog
 * @subpackage Templates
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

$iworks_aqualog_view  = $args['view'];
$iworks_aqualog_range = $args['range'];
$iworks_aqualog_today = gmdate( 'Y-m-d', current_time( 'timestamp' ) );

$iworks_aqualog_link = function ( $view, $date ) {
	return esc_url(
		add_query_arg(
			array(
				'page' => 'aqualog-calendar',
				'view' => $view,
				'date' => $date,
			),
			admin_url( 'admin.php' )
		)
	);
};
?>

<div class="wrap aqualog-calendar">
	<?php do_action( 'iworks/aqualog/wp-admin/current-aquarium-bar' ); ?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Calendar', 'PLUGIN_NAME' ); ?></h1>

	<div class="aqualog-calendar-nav">
		<div class="aqualog-calendar-nav-period">
			<a class="button" href="<?php echo call_user_func( $iworks_aqualog_link, $iworks_aqualog_view, $iworks_aqualog_range['prev'] ); ?>">&lsaquo;</a>
			<a class="button" href="<?php echo call_user_func( $iworks_aqualog_link, $iworks_aqualog_view, $iworks_aqualog_today ); ?>"><?php esc_html_e( 'Today', 'PLUGIN_NAME' ); ?></a>
			<a class="button" href="<?php echo call_user_func( $iworks_aqualog_link, $iworks_aqualog_view, $iworks_aqualog_range['next'] ); ?>">&rsaquo;</a>
			<span class="aqualog-calendar-title"><?php echo esc_html( $iworks_aqualog_range['title'] ); ?></span>
		</div>
		<div class="aqualog-calendar-nav-views">
			<a class="button<?php echo 'month' === $iworks_aqualog_view ? ' button-primary' : ''; ?>" href="<?php echo call_user_func( $iworks_aqualog_link, 'month', $args['date'] ); ?>"><?php esc_html_e( 'Month', 'PLUGIN_NAME' ); ?></a>
			<a class="button<?php echo 'week' === $iworks_aqualog_view ? ' button-primary' : ''; ?>" href="<?php echo call_user_func( $iworks_aqualog_link, 'week', $args['date'] ); ?>"><?php esc_html_e( 'Week', 'PLUGIN_NAME' ); ?></a>
		</div>
	</div>

	<table class="aqualog-calendar-grid view-<?php echo esc_attr( $iworks_aqualog_view ); ?>">
		<thead>
			<tr>
				<?php foreach ( $args['weekdays'] as $iworks_aqualog_weekday ) : ?>
					<th><?php echo esc_html( $iworks_aqualog_weekday ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( array_chunk( $iworks_aqualog_range['cells'], 7 ) as $iworks_aqualog_week ) : ?>
				<tr>
					<?php foreach ( $iworks_aqualog_week as $iworks_aqualog_cell ) : ?>
						<?php
						$iworks_aqualog_day_events = $args['events'][ $iworks_aqualog_cell['date'] ] ?? array();
						$iworks_aqualog_classes    = array( 'aqualog-calendar-day' );
						if ( ! $iworks_aqualog_cell['in_month'] ) {
							$iworks_aqualog_classes[] = 'out-of-range';
						}
						if ( $iworks_aqualog_cell['date'] === $iworks_aqualog_today ) {
							$iworks_aqualog_classes[] = 'today';
						}
						?>
						<td class="<?php echo esc_attr( implode( ' ', $iworks_aqualog_classes ) ); ?>">
							<div class="aqualog-calendar-day-header">
								<span class="aqualog-calendar-day-number"><?php echo esc_html( intval( substr( $iworks_aqualog_cell['date'], 8, 2 ) ) ); ?></span>
								<button type="button" class="button-link aqualog-calendar-add" data-date="<?php echo esc_attr( $iworks_aqualog_cell['date'] ); ?>" title="<?php esc_attr_e( 'Add Task', 'PLUGIN_NAME' ); ?>">
									<span class="dashicons dashicons-plus-alt"></span>
								</button>
							</div>
							<?php foreach ( $iworks_aqualog_day_events as $iworks_aqualog_event ) : ?>
								<?php
								$iworks_aqualog_event_classes = array( 'aqualog-calendar-event' );
								if ( 'reminder' === $iworks_aqualog_event['kind'] ) {
									$iworks_aqualog_event_classes[] = 'reminder';
								} else {
									$iworks_aqualog_event_classes[] = 'task-' . sanitize_html_class( $iworks_aqualog_event['status'] );
								}
								$iworks_aqualog_entry = array(
									'kind'         => $iworks_aqualog_event['kind'],
									'id'           => $iworks_aqualog_event['id'],
									'title'        => $iworks_aqualog_event['title'],
									'status'       => $iworks_aqualog_event['status'],
									'status_label' => ucwords( str_replace( '_', ' ', $iworks_aqualog_event['status'] ) ),
									'type'         => $iworks_aqualog_event['type'] ?? '',
									'date'         => date_i18n( get_option( 'date_format' ), strtotime( $iworks_aqualog_event['date'] ) ),
									'time'         => $iworks_aqualog_event['time'],
									'aquarium'     => get_the_title( $iworks_aqualog_event['aquarium_id'] ),
									'description'  => $iworks_aqualog_event['description'] ?? '',
									'interval'     => intval( $iworks_aqualog_event['interval'] ?? 0 ),
									'date_key'     => $iworks_aqualog_event['date'],
								);
								?>
								<button type="button" class="<?php echo esc_attr( implode( ' ', $iworks_aqualog_event_classes ) ); ?>" data-entry="<?php echo esc_attr( wp_json_encode( $iworks_aqualog_entry ) ); ?>">
									<span class="aqualog-calendar-event-time"><?php echo esc_html( $iworks_aqualog_event['time'] ); ?></span>
									<?php echo esc_html( $iworks_aqualog_event['title'] ); ?>
								</button>
							<?php endforeach; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php
// Load the maintenance form templates (wp.template blocks, rendered by JS into a modal).
load_template( dirname( __DIR__, 1 ) . '/elements/maintenance-form.php', false, $args );
load_template( dirname( __DIR__, 1 ) . '/elements/maintenance-reminder-form.php', false, $args );
// Load the calendar entry details template (wp.template block, rendered by JS into a modal).
load_template( dirname( __DIR__, 1 ) . '/elements/calendar-event-details.php', false, $args );
?>
