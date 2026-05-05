<?php
/**
 * AquaLog Chemistry Class
 *
 * Handles all chemistry-related functionality for the AquaLog plugin.
 * This includes managing water parameter measurements, calculations,
 * and chemistry data storage/retrieval.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @version    1.0.0
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __DIR__, 2 ) . '/class-iworks-aqualog-base.php';

/**
 * AquaLog Chemistry Class
 *
 * Manages water chemistry parameters, measurements, and calculations
 * for aquarium tracking and analysis.
 *
 * @since 1.0.0
 */
class iworks_aqualog_wp_admin_chemistry extends iworks_aqualog_base {

	/**
	 * Available chemistry parameters with their properties.
	 *
	 * @since 1.0.0
	 * @var array
	 */

	/**
	 * Class constructor.
	 *
	 * Initializes the chemistry class and sets up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'wp_ajax_aqualog_save_chemistry', array( $this, 'ajax_save_chemistry' ) );
		add_action( 'wp_ajax_aqualog_get_chemistry_data', array( $this, 'ajax_get_chemistry_data' ) );
		add_action( 'wp_ajax_aqualog_delete_chemistry', array( $this, 'ajax_delete_chemistry' ) );

		add_action( 'aqualog/wp-admin/chemistry_page', array( $this, 'render_page' ) );
	}
	
	public function render_page() {
		$this->set_current_aquarium_id();
		$file = $this->get_template_file( 'chemistry', 'pages' );
		if ( $file ) {
			load_template(
				$file, 
				true, 
				array(
					'aquarium_id'         => $this->current_aquarium_id,
					'latest_measurements' => $this->get_latest_measurements( $this->current_aquarium_id ),
					'messages'            => apply_filters( 'aqualog/wp-admin/messages/files', array() ),
					'meta'                => get_post_meta( $this->current_aquarium_id ),
					'params'              => $this->get_parameters(),
				)
			);
		}
	}

	/**
	 * Get available chemistry parameters.
	 *
	 * Returns the list of all available chemistry parameters
	 * with their properties.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Available parameters with their properties.
	 */
	public function get_parameters() {
		$this->check_option_object();
		$config = $this->options->get_group( 'chemistry' );
		$parameters = array();
		foreach( $config as $key => $value ) {
			$meta_name = 'chemistry_check_' . $key ;
			if ( 'yes' === $this->get_post_meta( $this->current_aquarium_id, $meta_name ) ) {
				$parameters[ $key ] = wp_parse_args(
					$value,
					array(
						'importance' => 'default',
						'key' => $key,
						'last_test_date' => esc_html__( 'Never tested!', 'aqualog' ),
						'frequency' => '',
					)
				);
			}
		}
		return apply_filters( 'aqualog/chemistry/parameters', $parameters );
	}

	/**
	 * Get parameter information.
	 *
	 * Retrieves detailed information about a specific parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $param The parameter name.
	 * @return array|false Parameter information or false if not found.
	 */
	public function get_parameter( $param ) {
		$parameters = $this->get_parameters();
		return isset( $parameters[ $param ] ) ? $parameters[ $param ] : false;
	}

	/**
	 * Save chemistry measurement.
	 *
	 * Saves a new chemistry measurement to the database.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int    $aquarium_id The aquarium ID.
	 * @param string $param       The parameter name.
	 * @param string $value       The measured value.
	 * @param string $date        The measurement date (optional).
	 * @return int|false The measurement ID or false on failure.
	 */
	public function save_measurement( $aquarium_id, $param, $value, $date = '' ) {
		global $wpdb;

		if ( ! $this->get_parameter( $param ) ) {
			return false;
		}

		$table_name = $wpdb->prefix . 'aqualog_chemistry';
		
		$data = array(
			'aquarium_id' => intval( $aquarium_id ),
			'param' => sanitize_key( $param ),
			'value' => sanitize_text_field( $value ),
			'date' => ! empty( $date ) ? $date : current_time( 'mysql' ),
		);

		$format = array( '%d', '%s', '%s', '%s' );

		$result = $wpdb->insert( $table_name, $data, $format );

		if ( $result ) {
			$measurement_id = $wpdb->insert_id;
			do_action( 'aqualog/chemistry/measurement_saved', $measurement_id, $data );
			return $measurement_id;
		}

		return false;
	}

	/**
	 * Get chemistry measurements for an aquarium.
	 *
	 * Retrieves measurements for a specific aquarium and parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int    $aquarium_id The aquarium ID.
	 * @param string $param       The parameter name (optional).
	 * @param int    $limit       Maximum number of records (optional).
	 * @param string $order       Order direction (ASC/DESC, optional).
	 * @return array Array of measurements.
	 */
	public function get_measurements( $aquarium_id, $param = '', $limit = 50, $order = 'DESC' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'aqualog_chemistry';
		
		$where = $wpdb->prepare( 'aquarium_id = %d', $aquarium_id );
		if ( ! empty( $param ) ) {
			$where .= $wpdb->prepare( ' AND param = %s', $param );
		}

		$sql = "SELECT * FROM $table_name WHERE $where ORDER BY date $order";
		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get latest measurements for all parameters.
	 *
	 * Retrieves the most recent measurement for each parameter
	 * for a specific aquarium.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $aquarium_id The aquarium ID.
	 * @return array Array of latest measurements.
	 */
	public function get_latest_measurements( $aquarium_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'aqualog_chemistry';
		
		$sql = "SELECT param, value, date
				FROM $table_name 
				WHERE aquarium_id = %d 
				AND id IN (
					SELECT MAX(id) 
					FROM $table_name 
					WHERE aquarium_id = %d 
					GROUP BY param
				)
				ORDER BY param ASC";

		return $wpdb->get_results( $wpdb->prepare( $sql, $aquarium_id, $aquarium_id ) );
	}

	/**
	 * Delete chemistry measurement.
	 *
	 * Deletes a specific chemistry measurement.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $measurement_id The measurement ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete_measurement( $measurement_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'aqualog_chemistry';
		
		$result = $wpdb->delete(
			$table_name,
			array( 'id' => intval( $measurement_id ) ),
			array( '%d' )
		);

		if ( $result ) {
			do_action( 'aqualog/chemistry/measurement_deleted', $measurement_id );
			return true;
		}

		return false;
	}

	/**
	 * Validate parameter value.
	 *
	 * Checks if a value is within the acceptable range for a parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $param The parameter name.
	 * @param string $value The value to validate.
	 * @return array Validation result with status and message.
	 */
	public function validate_value( $param, $value ) {
		$parameter = $this->get_parameter( $param );
		
		if ( ! $parameter ) {
			return array(
				'status' => 'error',
				'message' => __( 'Unknown parameter', 'aqualog' ),
			);
		}

		$numeric_value = floatval( $value );
		$range = $parameter['range'];

		if ( $numeric_value < $range[0] || $numeric_value > $range[1] ) {
			return array(
				'status' => 'warning',
				'message' => sprintf(
					__( 'Value is outside typical range (%s - %s %s)', 'aqualog' ),
					$range[0],
					$range[1],
					$parameter['unit']
				),
			);
		}

		$ideal = $parameter['ideal'];
		if ( $numeric_value < $ideal[0] || $numeric_value > $ideal[1] ) {
			return array(
				'status' => 'info',
				'message' => sprintf(
					__( 'Value is outside ideal range (%s - %s %s)', 'aqualog' ),
					$ideal[0],
					$ideal[1],
					$parameter['unit']
				),
			);
		}

		return array(
			'status' => 'success',
			'message' => __( 'Value is within ideal range', 'aqualog' ),
		);
	}

	/**
	 * Render chemistry interface.
	 *
	 * Outputs the chemistry management interface for the current aquarium.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function render_chemistry_interface() {
		$this->set_current_aquarium_id();
		if ( ! $this->current_aquarium_id ) {
			return;
		}



		$parameters = $this->get_parameters();
		
		?>
		<div class="aqualog-chemistry-interface">
			<div class="aqualog-chemistry-overview">
				<h3><?php esc_html_e( 'Latest Measurements', 'aqualog' ); ?></h3>
				<div class="aqualog-grid">
					<?php foreach ( $latest_measurements as $measurement ) : ?>
						<?php 
						$param = $this->get_parameter( $measurement->param );
						if ( ! $param ) continue;
						$validation = $this->validate_value( $measurement->param, $measurement->value );
						?>
						<div class="aqualog-card aqualog-card-hover">
							<div class="parameter-header">
								<h4><?php echo esc_html( $param['name'] ); ?></h4>
								<span class="parameter-unit"><?php echo esc_html( $param['unit'] ); ?></span>
							</div>
							<div class="parameter-value">
								<span class="value"><?php echo esc_html( $measurement->value ); ?></span>
								<span class="status status-<?php echo esc_attr( $validation['status'] ); ?>"></span>
							</div>
							<div class="parameter-date">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $measurement->date ) ) ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="aqualog-chemistry-form">
				<h3><?php esc_html_e( 'Add Measurement', 'aqualog' ); ?></h3>
				<form id="aqualog-chemistry-form" class="aqualog-form">
					<div class="form-row">
						<div class="form-group">
							<label for="chemistry-param"><?php esc_html_e( 'Parameter', 'aqualog' ); ?></label>
							<select id="chemistry-param" name="param" required>
								<option value=""><?php esc_html_e( 'Select parameter', 'aqualog' ); ?></option>
								<?php foreach ( $parameters as $key => $param ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>">
										<?php echo esc_html( $param['name'] ); ?> (<?php echo esc_html( $param['unit'] ); ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label for="chemistry-value"><?php esc_html_e( 'Value', 'aqualog' ); ?></label>
							<input type="number" id="chemistry-value" name="value" step="0.01" required>
						</div>
						<div class="form-group">
							<label for="chemistry-date"><?php esc_html_e( 'Date', 'aqualog' ); ?></label>
							<input type="datetime-local" id="chemistry-date" name="date" required>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="aqualog-button aqualog-button-primary">
							<?php esc_html_e( 'Save Measurement', 'aqualog' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler for saving chemistry measurements.
	 *
	 * Handles the AJAX request to save a new chemistry measurement.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function ajax_save_chemistry() {
		check_ajax_referer( 'aqualog_chemistry_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'aqualog' ) );
		}

		$aquarium_id = intval( $_POST['aquarium_id'] );
		$param = sanitize_key( $_POST['param'] );
		$value = sanitize_text_field( $_POST['value'] );
		$date = sanitize_text_field( $_POST['date'] );

		$measurement_id = $this->save_measurement( $aquarium_id, $param, $value, $date );

		if ( $measurement_id ) {
			wp_send_json_success( array(
				'message' => __( 'Measurement saved successfully', 'aqualog' ),
				'measurement_id' => $measurement_id,
			) );
		} else {
			wp_send_json_error( __( 'Failed to save measurement', 'aqualog' ) );
		}
	}

	/**
	 * AJAX handler for retrieving chemistry data.
	 *
	 * Handles the AJAX request to get chemistry measurements.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function ajax_get_chemistry_data() {
		check_ajax_referer( 'aqualog_chemistry_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'aqualog' ) );
		}

		$aquarium_id = intval( $_POST['aquarium_id'] );
		$param = isset( $_POST['param'] ) ? sanitize_key( $_POST['param'] ) : '';
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 50;

		$measurements = $this->get_measurements( $aquarium_id, $param, $limit );

		wp_send_json_success( array(
			'measurements' => $measurements,
		) );
	}

	/**
	 * AJAX handler for deleting chemistry measurements.
	 *
	 * Handles the AJAX request to delete a chemistry measurement.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function ajax_delete_chemistry() {
		check_ajax_referer( 'aqualog_chemistry_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'aqualog' ) );
		}

		$measurement_id = intval( $_POST['measurement_id'] );

		if ( $this->delete_measurement( $measurement_id ) ) {
			wp_send_json_success( array(
				'message' => __( 'Measurement deleted successfully', 'aqualog' ),
			) );
		} else {
			wp_send_json_error( __( 'Failed to delete measurement', 'aqualog' ) );
		}
	}
}
