<?php
/**
 * iWorks Aquarium Log Chemistry Class
 *
 * Handles all chemistry-related functionality for the iWorks Aquarium Log plugin.
 * This includes managing water parameter measurements, calculations,
 * and chemistry data storage/retrieval.
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

require_once dirname( __DIR__, 2 ) . '/class-iworks-aquarium-log-base.php';

/**
 * iWorks Aquarium Log Chemistry Class
 *
 * Manages water chemistry parameters, measurements, and calculations
 * for aquarium tracking and analysis.
 *
 * @since 1.0.0
 */
class iworks_aquarium_log_wp_admin_chemistry extends iworks_aquarium_log_base {

	/**
	 * Available chemistry parameters with their properties.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $parameters = array();

	/**
	 * Latest measurements for each parameter.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $latest_measurements = array();

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
		/**
		 * AJAX handler for adding chemistry parameters.
		 */
		add_action( 'wp_ajax_aquarium_log_chemistry_add_param', array( $this, 'ajax_add_chemistry_param' ) );
		/**
		 * Aqualog plugin action hook for chemistry page rendering.
		 *
		 * @since 1.0.0
		 */
		add_action( 'iworks-aquarium-log/wp-admin/chemistry_page', array( $this, 'render_page' ) );
		add_filter( 'iworks-aquarium-log/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
	}

	/**
	 * Filter WordPress localize script data for chemistry page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Localize script data.
	 * @return array Filtered localize script data.
	 */
	public function filter_wp_localize_script( $data ) {
		$this->set_current_aquarium_id();
		$data['chemistry']           = array(
			'params' => $this->get_parameters(),
		);
		$data['nonces']['chemistry'] = array(
			'save'      => wp_create_nonce( $this->get_meta_name( 'chemistry_save' ) ),
			'add_param' => wp_create_nonce( $this->get_meta_name( 'chemistry_add_param' ) ),
		);
		return $data;
	}
	/**
	 * Render chemistry page.
	 *
	 * @since 1.0.0
	 */
	public function render_page() {
		$this->set_current_aquarium_id();
		$this->load_template(
			'chemistry',
			'pages',
			true,
			apply_filters(
				'iworks-aquarium-log/wp-admin/chemistry/args',
				array(
					'aquarium_id'         => $this->current_aquarium_id,
					'meta'                => get_post_meta( $this->current_aquarium_id ),
					'params'              => $this->get_parameters(),
					'latest_measurements' => $this->get_latest_measurements(),
				)
			)
		);
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
		if ( isset( $this->parameters[ $this->current_aquarium_id ] ) ) {
			return $this->parameters[ $this->current_aquarium_id ];
		}
		$this->check_option_object();
		$config              = $this->options->get_group( 'chemistry' );
		$latest_measurements = $this->get_latest_measurements();
		$parameters          = array();
		foreach ( $config as $key => $value ) {
			$meta_name = 'chemistry_check_' . $key;
			if ( 'yes' === $this->get_post_meta( $this->current_aquarium_id, $meta_name ) ) {
				$parameters[ $key ] = wp_parse_args(
					$value,
					array(
						'importance'     => 'default',
						'key'            => $key,
						'last_test_date' => esc_html__( 'Never tested!', 'iworks-aquarium-log' ),
						'frequency'      => '',
						'value'          => '',
						'value_class'    => 'unknown',
						'show_name'      => true,
					)
				);
				if ( isset( $latest_measurements[ $key ] ) ) {
					$parameters[ $key ]['last_test_date'] = $latest_measurements[ $key ]['since'];
					$parameters[ $key ]['value']          = $latest_measurements[ $key ]['param_value'];
				}
				if ( $parameters[ $key ]['value'] && is_numeric( $parameters[ $key ]['value'] ) ) {
					$parameters[ $key ]['value_class'] = 'danger';
					$v                                 = (float) $parameters[ $key ]['value'];
					if (
						$parameters[ $key ]['ideal'][0] <= $v &&
						$parameters[ $key ]['ideal'][1] >= $v
					) {
						$parameters[ $key ]['value_class'] = 'ideal';
					} elseif (
						$parameters[ $key ]['safety'][0] <= $v &&
						$parameters[ $key ]['safety'][1] >= $v
					) {
						$parameters[ $key ]['value_class'] = 'safety';
					}
				}
			}
		}
		uasort( $parameters, array( $this, 'sort_parameters' ) );
		$this->parameters[ $this->current_aquarium_id ] = apply_filters( 'iworks-aquarium-log/chemistry/parameters', $parameters );
		return $this->parameters[ $this->current_aquarium_id ];
	}

	/**
	 * Sort parameters by importance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param array $a First parameter.
	 * @param array $b Second parameter.
	 * @return int Comparison result.
	 */
	private function sort_parameters( $a, $b ) {
		// Define importance order
		$importance_order = array(
			'critical'    => 1,
			'important'   => 2,
			'recommended' => 3,
			'default'     => 4,
		);

		$importance_a = isset( $importance_order[ $a['importance'] ] ) ? $importance_order[ $a['importance'] ] : 5;
		$importance_b = isset( $importance_order[ $b['importance'] ] ) ? $importance_order[ $b['importance'] ] : 5;

		if ( $importance_a === $importance_b ) {
			return strcmp( $a['key'], $b['key'] );
		}
		return ( $importance_a < $importance_b ) ? -1 : 1;
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
	 * Get latest measurements for all parameters.
	 *
	 * Retrieves the most recent measurement for each parameter
	 * for a specific aquarium.
	*
	 * @since 1.0.0
	 * @access public
	 * @return array Array of latest measurements.
	 */
	public function get_latest_measurements() {
		if ( empty( $this->current_aquarium_id ) ) {
			return array();
		}
		if ( isset( $this->latest_measurements[ $this->current_aquarium_id ] ) ) {
			return $this->latest_measurements[ $this->current_aquarium_id ];
		}
		global $wpdb;
		$sql     = "SELECT t1.* FROM {$wpdb->aquarium_log_chemistry} t1 WHERE t1.aquarium_id = %d and t1.measurement_date = ( SELECT MAX(t2.measurement_date) FROM {$wpdb->aquarium_log_chemistry} t2 WHERE t2.param_key = t1.param_key)";
		$query   = $wpdb->prepare( $sql, $this->current_aquarium_id );
		$results = $wpdb->get_results( $query, ARRAY_A );
		if ( empty( $results ) || ! is_array( $results ) ) {
			return array();
		}
		// Convert to associative array with param_key as key
		$assoc_results = array();
		foreach ( $results as $result ) {
			if ( isset( $result['param_key'] ) ) {
				// Add "since" field with time elapsed text
				$result['since']                       = $this->get_time_elapsed_text( $result['measurement_date'] );
				$assoc_results[ $result['param_key'] ] = $result;
			}
		}

		return $assoc_results;
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
				'status'  => 'error',
				'message' => __( 'Unknown parameter', 'iworks-aquarium-log' ),
			);
		}

		$numeric_value = floatval( $value );
		$range         = $parameter['range'];

		if ( $numeric_value < $range[0] || $numeric_value > $range[1] ) {
			return array(
				'status'  => 'warning',
				'message' => sprintf(
					/* translators: %1$s: range min, %2$s: range max, %3$s: unit */
					__( 'Value is outside typical range (%1$s - %2$s %3$s)', 'iworks-aquarium-log' ),
					$range[0],
					$range[1],
					$parameter['unit']
				),
			);
		}

		$ideal = $parameter['ideal'];
		if ( $numeric_value < $ideal[0] || $numeric_value > $ideal[1] ) {
			return array(
				'status'  => 'info',
				'message' => sprintf(
					/* translators: %1$s: ideal min, %2$s: ideal max, %3$s: unit */
					__( 'Value is outside ideal range (%1$s - %2$s %3$s)', 'iworks-aquarium-log' ),
					$ideal[0],
					$ideal[1],
					$parameter['unit']
				),
			);
		}

		return array(
			'status'  => 'success',
			'message' => __( 'Value is within ideal range', 'iworks-aquarium-log' ),
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
		<div class="aquarium-log-chemistry-interface">
			<div class="aquarium-log-chemistry-overview">
				<h3><?php esc_html_e( 'Latest Measurements', 'iworks-aquarium-log' ); ?></h3>
				<div class="aquarium-log-grid">
					<?php foreach ( $latest_measurements as $measurement ) : ?>
						<?php
						$param = $this->get_parameter( $measurement->param );
						if ( ! $param ) {
							continue;
						}
						$validation = $this->validate_value( $measurement->param, $measurement->value );
						?>
						<div class="aquarium-log-card aquarium-log-card-hover">
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

			<div class="aquarium-log-chemistry-form">
				<h3><?php esc_html_e( 'Add Measurement', 'iworks-aquarium-log' ); ?></h3>
				<form id="aquarium-log-chemistry-form" class="aquarium-log-form">
					<div class="form-row">
						<div class="form-group">
							<label for="chemistry-param"><?php esc_html_e( 'Parameter', 'iworks-aquarium-log' ); ?></label>
							<select id="chemistry-param" name="param" required>
								<option value=""><?php esc_html_e( 'Select parameter', 'iworks-aquarium-log' ); ?></option>
								<?php foreach ( $parameters as $key => $param ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>">
										<?php echo esc_html( $param['name'] ); ?> (<?php echo esc_html( $param['unit'] ); ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label for="chemistry-value"><?php esc_html_e( 'Value', 'iworks-aquarium-log' ); ?></label>
							<input type="number" id="chemistry-value" name="value" step="0.01" required>
						</div>
						<div class="form-group">
							<label for="chemistry-date"><?php esc_html_e( 'Date', 'iworks-aquarium-log' ); ?></label>
							<input type="datetime-local" id="chemistry-date" name="date" required>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="aquarium-log-button aquarium-log-button-primary">
							<?php esc_html_e( 'Save Measurement', 'iworks-aquarium-log' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function ajax_add_chemistry_param() {
		check_ajax_referer( $this->get_meta_name( 'chemistry_add_param' ) );
		$this->check_option_object();
		$value = sanitize_text_field( filter_input( INPUT_POST, 'value' ) );
		$key   = sanitize_key( filter_input( INPUT_POST, 'key' ) );
		$id    = intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) );
		/**
		 * sanitize
		 */
		$config = $this->options->get_group( 'chemistry' );
		if ( ! array_key_exists( $key, $config ) ) {
			wp_send_json_error( __( 'Invalid parameter', 'iworks-aquarium-log' ) );
		}
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->aquarium_log_chemistry,
			array(
				'aquarium_id'      => $id,
				'param_key'        => $key,
				'param_value'      => $value,
				'measurement_date' => current_time( 'mysql' ),
			),
			array(
				'%d',
				'%s',
				'%f',
				'%s',
			)
		);
		if ( $result ) {
			// Log the chemistry measurement addition
			$this->log_chemistry_measurement( $id, $key, $value );

			wp_send_json_success(
				array(
					'message' => __( 'Parameter added successfully', 'iworks-aquarium-log' ),
				)
			);
		}
		wp_send_json_error( __( 'Failed to add parameter', 'iworks-aquarium-log' ) );
	}

	/**
	 * Log chemistry measurement addition.
	 *
	 * @since 1.0.0
	 * @param int    $aquarium_id Aquarium ID.
	 * @param string $param_key   Parameter key.
	 * @param float  $param_value Parameter value.
	 * @return void
	 */
	private function log_chemistry_measurement( $aquarium_id, $param_key, $param_value ) {

		// Log the measurement
		$measurement_date = current_time( 'mysql' );
		$logger           = new iworks_aquarium_log_logger();
		$logger->log_chemistry_measurement_added( $aquarium_id, $param_key, $param_value, $measurement_date );
	}
}
