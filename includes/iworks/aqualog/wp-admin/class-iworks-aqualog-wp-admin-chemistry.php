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
	private $parameters = array();

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
		add_action( 'wp_ajax_aqualog_chemistry_add_param', array( $this, 'ajax_add_chemistry_param' ) );
		/**
		 * Aqualog plugin action hook for chemistry page rendering.
		 *
		 * @since 1.0.0
		 */
		add_action( 'aqualog/wp-admin/chemistry_page', array( $this, 'render_page' ) );
		add_filter( 'aqualog/wp-admin/wp_localize_script', array( $this, 'filter_wp_localize_script' ) );
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
				array(
					'aquarium_id' => $this->current_aquarium_id,
					'messages'    => apply_filters( 'aqualog/wp-admin/messages/files', array() ),
					'meta'        => get_post_meta( $this->current_aquarium_id ),
					'params'      => $this->get_parameters(),
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
						'last_test_date' => esc_html__( 'Never tested!', 'aqualog' ),
						'frequency'      => '',
						'value'          => '',
						'value_class'    => 'unknown',
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
		$this->parameters[ $this->current_aquarium_id ] = apply_filters( 'aqualog/chemistry/parameters', $parameters );
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
		global $wpdb;
		$sql     = "SELECT * FROM {$wpdb->aqualog_chemistry} WHERE aquarium_id = %d GROUP BY param_key ORDER BY measurement_date DESC";
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $this->current_aquarium_id ), ARRAY_A );
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
				'message' => __( 'Unknown parameter', 'aqualog' ),
			);
		}

		$numeric_value = floatval( $value );
		$range         = $parameter['range'];

		if ( $numeric_value < $range[0] || $numeric_value > $range[1] ) {
			return array(
				'status'  => 'warning',
				'message' => sprintf(
					/* translators: %1$s: range min, %2$s: range max, %3$s: unit */
					__( 'Value is outside typical range (%1$s - %2$s %3$s)', 'aqualog' ),
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
					__( 'Value is outside ideal range (%1$s - %2$s %3$s)', 'aqualog' ),
					$ideal[0],
					$ideal[1],
					$parameter['unit']
				),
			);
		}

		return array(
			'status'  => 'success',
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
						if ( ! $param ) {
							continue;
						}
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

	public function ajax_add_chemistry_param() {
		check_ajax_referer( $this->get_meta_name( 'chemistry_add_param' ) );
		$this->check_option_object();
		$value = sanitize_text_field( $_POST['value'] );
		$key   = sanitize_key( $_POST['key'] );
		$id    = intval( $_POST['id'] );
		/**
		 * sanitize
		 */
		$config = $this->options->get_group( 'chemistry' );
		if ( ! array_key_exists( $key, $config ) ) {
			wp_send_json_error( __( 'Invalid parameter', 'aqualog' ) );
		}
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->aqualog_chemistry,
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
			wp_send_json_success(
				array(
					'message' => __( 'Parameter added successfully', 'aqualog' ),
				)
			);
		}
		wp_send_json_error( __( 'Failed to add parameter', 'aqualog' ) );
	}
}
