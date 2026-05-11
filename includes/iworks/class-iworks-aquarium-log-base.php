<?php
/**
 * iWorks iWorks Aquarium Log Base Class
 *
 * This is the base class for the iWorks Aquarium Log plugin, providing
 * common functionality and properties used throughout the plugin.
 *
 * @package    iWorks
 * @subpackage iWorks Aquarium Log
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prevent multiple class definitions.
 */
if ( class_exists( 'iworks_aquarium_log_base' ) ) {
	return;
}

/**
 * iWorks iWorks Aquarium Log Base Class
 *
 * This class provides the foundation for the iWorks Aquarium Log plugin,
 * offering essential properties and methods used throughout the plugin.
 *
 * @since 1.0.0
 */
class iworks_aquarium_log_base {

	/**
	 * Developer mode flag
	 *
	 * @since 1.0.0
	 * @var bool $dev Whether developer mode is enabled
	 */
	protected $dev;

	/**
	 * Meta data prefix
	 *
	 * @since 1.0.0
	 * @var string $meta_prefix Prefix for meta data keys
	 */
	protected $meta_prefix = '_iw';

	/**
	 * Base directory path
	 *
	 * @since 1.0.0
	 * @var string $base Absolute path to the plugin directory
	 */
	protected $base;

	/**
	 * Directory path
	 *
	 * @since 1.0.0
	 * @var string $dir Plugin directory path
	 */
	protected $dir;

	/**
	 * URL path
	 *
	 * @since 1.0.0
	 * @var string $url Plugin URL path
	 */
	protected $url;

	/**
	 * Plugin file name
	 *
	 * @since 1.0.0
	 * @var string $plugin_file Name of the plugin file
	 */
	protected $plugin_file;

	/**
	 * Plugin file directory
	 *
	 * @since 1.0.0
	 * @var string $plugin_file_dir Full path to the plugin directory
	 */
	protected $plugin_file_dir;

	/**
	 * Plugin file path
	 *
	 * @since 1.0.0
	 * @var string $plugin_file_path Full path to the plugin file
	 */
	protected $plugin_file_path;

	/**
	 * Plugin capability
	 *
	 * @since 1.0.0
	 * @var string $capability Required capability for plugin settings
	 */
	private string $capability = 'manage_options';

	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 * @var string $version Current plugin version
	 */
	protected string $version = 'PLUGIN_VERSION.BUILDTIMESTAMP';

	/**
	 * Includes directory
	 *
	 * @since 1.0.0
	 * @var string $includes_directory Path to plugin includes directory
	 */
	protected string $includes_directory;

	/**
	 * Debug mode flag
	 *
	 * @since 1.0.0
	 * @var bool $debug Whether debug mode is enabled
	 */
	protected $debug = false;

	/**
	 * End of line character
	 *
	 * @since 1.0.0
	 * @var string $eol End of line character for output
	 */
	protected string $eol = '';

	/**
	 * iWorks Options Class Object
	 *
	 * @since 1.0.0
	 * @var iworks_options $options Instance of the options class
	 */
	protected $options;

	/**
	 * Post type name
	 *
	 * @since 1.0.0
	 * @var string $post_type The post type being handled
	 */
	protected $post_type;

	/**
	 * WP Admin slug
	 *
	 * @since 1.0.0
	 * @var string $wp_admin_slug The WP Admin slug
	 */
	protected string $wp_admin_slug = 'aquarium-log-dashboard';

	/**
	 * Current aquarium ID.
	 *
	 * @since 1.0.0
	 * @var int|null $current_aquarium_id Current aquarium ID or null if not set.
	 */
	protected ?int $current_aquarium_id = null;


	protected object $logger;
	/**
	 * Constructor for the base class.
	 *
	 * Initializes all necessary properties and sets up the plugin environment
	 * including debug mode, directories, URLs, and WordPress hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		/**
		 * static settings
		 */
		$this->debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE );
		/**
		 * use minimized scripts if not debug
		 */
		$this->dev = $this->debug ? '' : '.min';
		/**
		 * add EOL if debug
		 */
		$this->eol = $this->debug ? PHP_EOL : '';
		/**
		 * directories and urls
		 */
		$this->base = __DIR__;
		$this->dir  = basename( dirname( $this->base, 2 ) );
		$this->url  = plugins_url( $this->dir );
		/**
		 * plugin ID
		 */
		$this->plugin_file_dir  = dirname( $this->base, 2 );
		$this->plugin_file_path = $this->plugin_file_dir . '/iworks-aquarium-log.php';
		$this->plugin_file      = plugin_basename( $this->plugin_file_path );
		/**
		 * plugin includes directory
		 */
		$this->includes_directory = __DIR__ . '/iworks-aquarium-log';
		/**
		 * WordPress Hooks
		 */
	}

	/**
	 * Get the plugin version.
	 *
	 * Returns either the current version or a timestamp/file hash in dev mode.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string|null $file Optional file path for hash generation.
	 * @return string Version string or timestamp/hash.
	 */
	public function get_version( $file = null ) {
		if ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE ) {
			if ( null !== $file ) {
				$file = dirname( $this->base ) . $file;
				if ( is_file( $file ) ) {
					return md5_file( $file );
				}
			}
			return time();
		}
		return $this->version;
	}

	/**
	 * Generate a meta key name.
	 *
	 * Creates a properly formatted meta key name using the prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $name Base name for the meta key.
	 * @return string Formatted meta key name.
	 */
	protected function get_meta_name( $name ) {
		return sprintf( '%s_%s', $this->meta_prefix, sanitize_title( $name ) );
	}

	/**
	 * Get the post type.
	 *
	 * Returns the current post type being handled.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Post type name.
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Get the plugin capability.
	 *
	 * Returns the required capability for plugin settings.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Capability name.
	 */
	public function get_this_capability() {
		return $this->capability;
	}

	/**
	 * Generate a slug name.
	 *
	 * Creates a URL-safe slug from the given name.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $name Input name to convert.
	 * @return string URL-safe slug.
	 */
	private function slug_name( $name ) {
		return preg_replace( '/[_ ]+/', '-', strtolower( __CLASS__ . '_' . $name ) );
	}

	/**
	 * Get post meta value.
	 *
	 * Retrieves a post meta value using the plugin's meta prefix.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int    $post_id  Post ID to get meta for.
	 * @param string $meta_key Meta key name.
	 * @return mixed Meta value.
	 */
	public function get_post_meta( $post_id, $meta_key ) {
		return get_post_meta( $post_id, $this->get_meta_name( $meta_key ), true );
	}

	/**
	 * Print table body for post meta fields.
	 *
	 * Generates an HTML table with form inputs for post meta fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param int   $post_id Post ID to display meta for.
	 * @param array $fields  Array of field definitions.
	 * @return void Outputs HTML directly.
	 */
	protected function print_table_body( $post_id, $fields ) {
		echo '<table class="widefat striped"><tbody>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( $fields as $name => $data ) {
			$key   = $this->get_meta_name( $name );
			$value = $this->get_post_meta( $post_id, $name );
			/**
			 * extra
			 */
			$extra = isset( $data['placeholder'] ) ? sprintf( ' placeholder="%s" ', esc_attr( $data['placeholder'] ) ) : '';
			foreach ( array( 'placeholder', 'style', 'class', 'id' ) as $extra_key ) {
				if ( isset( $data[ $extra_key ] ) ) {
					$extra .= sprintf( ' %s="%s" ', esc_attr( $extra_key ), esc_attr( $data[ $extra_key ] ) );
				}
			}
			/**
			 * start row
			 */
			echo '<tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<th scope="row" style="width: 130px">%s</th>', esc_html( $data['title'] ) );
			echo '<td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			switch ( $data['type'] ) {
				case 'number':
					foreach ( array( 'min', 'max', 'step' ) as $extra_key ) {
						if ( isset( $data[ $extra_key ] ) ) {
							$extra .= sprintf( ' %s="%d" ', $extra_key, intval( $data[ $extra_key ] ) );
						}
					}
					printf(
						'<input type="number" name="%s" value="%d" %s />',
						esc_attr( $key ),
						intval( $value ),
						// $extra contains already escaped HTML attributes
						$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
					break;
				case 'date':
					$date = intval( $this->get_post_meta( $post_id, $name ) );
					if ( empty( $date ) ) {
						$date = strtotime( 'now' );
					}
					printf(
						'<input type="text" class="datepicker" name="%s" value="%s" />',
						esc_attr( $this->get_meta_name( $name ) ),
						esc_attr( $date )
					);
					break;
			}
			echo '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</tbody></table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get module file path.
	 *
	 * Constructs and returns the full path to a module file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $filename The filename to locate.
	 * @param string $vendor   The vendor directory name. Default 'iworks'.
	 * @return string|false The full path to the file, or false if not found.
	 */
	protected function get_module_file( $filename, $vendor = 'iworks' ) {
		return realpath(
			sprintf(
				'%s/%s/%s/%s.php',
				$this->base,
				$vendor,
				$this->dir,
				$filename
			)
		);
	}

	/**
	 * Display HTML heading.
	 *
	 * Outputs a properly escaped WordPress admin heading.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $text The heading text to display.
	 * @return void
	 */
	protected function html_title( $text ) {
		printf( '<h1 class="wp-heading-inline">%s</h1>', esc_html( $text ) );
	}

	/**
	 * Check option object.
	 *
	 * Ensures the options object is properly initialized.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function check_option_object() {
		if ( is_a( $this->options, 'iworks_options' ) ) {
			return;
		}
		$this->options = iworks_aquarium_log_get_options();
	}

	/**
	 * Get plugin stub metadata.
	 *
	 * Returns an array containing the plugin's metadata including:
	 * - Publication date
	 * - Current version
	 * - GitHub repository URL
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *     Plugin metadata.
	 *
	 *     @type string $published The publication date in 'YYYY-MM-DD' format.
	 *     @type string $version   The current version of the plugin.
	 *     @type string $github    The GitHub repository URL.
	 * }
	 */
	public function get_stub_data() {
		return array(
			'published' => '2026-05-21',
			'version'   => 'PLUGIN_VERSION',
			'github'    => 'https://github.com/iworks/iworks-aquarium-log',
		);
	}

	/**
	 * Log message using Simple Logger.
	 *
	 * Logs a message using the Simple Logger plugin if available,
	 * including current user information.
	 *
	 * Read more: https://simple-history.com/docs/logging-api/#using-simpleLogger
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $message Log message.
	 * @param array $data Additional log data.
	 * @return void
	 */
	protected function simple_history_logger_helper( $message, $data, $level = 'notice' ) {
		/**
		 * Check if Simple History plugin is active
		 */
		if ( ! function_exists( 'SimpleLogger' ) ) {
			return;
		}
		/**
		 * add logged in user data to log
		 */
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$data = wp_parse_args(
				$data,
				array(
					'username'    => $user->display_name ?? $user->user_login,
					'_user_id'    => get_current_user_id(),
					'_user_login' => $user->user_login,
					'_user_email' => $user->user_email,
				)
			);
		}
		/**
		 * select level and write log
		 */
		switch ( $level ) {
			case 'debug':
				SimpleLogger()->debug( $message, $data );
				break;
			case 'warning':
				SimpleLogger()->warning( $message, $data );
				break;
			case 'notice':
				SimpleLogger()->notice( $message, $data );
				break;
			default:
				SimpleLogger()->notice( $message, $data );
				break;
		}
	}
	/**
	 * Enqueue dashboard styles.
	 *
	 * Loads the CSS styles for the dashboard page.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	public function admin_enqueue_assets() {
		$name = $this->dir . '-admin';
		wp_enqueue_style( $name );
		wp_enqueue_script( $name );
	}

	/**
	 * Set the current aquarium ID.
	 *
	 * Determines and sets the current aquarium ID based on query variables,
	 * default settings, or filter hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function set_current_aquarium_id() {
		$this->current_aquarium_id = 0;
		/**
		 * check _GET for aquarium_id
		 */
		$aquarium_id = intval( filter_input( INPUT_GET, 'aquarium_id', FILTER_VALIDATE_INT ) );
		if ( $aquarium_id ) {
			$this->current_aquarium_id = $aquarium_id;
			return;
		}
		/**
		 * check _POST for aquarium_id
		 */
		$aquarium_id = intval( filter_input( INPUT_POST, 'aquarium_id', FILTER_VALIDATE_INT ) );
		if ( $aquarium_id ) {
			$this->current_aquarium_id = $aquarium_id;
			return;
		}
		/**
		 * check COOKIE for aquarium_id
		 */
		$aquarium_id = intval( filter_input( INPUT_COOKIE, 'aquarium_id', FILTER_VALIDATE_INT ) );
		if ( $aquarium_id ) {
			$this->current_aquarium_id = $aquarium_id;
			return;
		}
		/**
		 * check options for default aquarium ID
		 */
		$this->check_option_object();
		$default_aquarium_id = $this->options->get_option( 'default_aquarium_id' );
		if ( ! empty( $default_aquarium_id ) ) {
			$this->current_aquarium_id = $default_aquarium_id;
			return;
		}
		$this->current_aquarium_id = apply_filters( 'iworks-aquarium-log/set/current_aquarium_id', 0 );
	}

	/**
	 * Get template file path.
	 *
	 * Constructs and returns the full path to a template file,
	 * with proper validation and sanitization.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $file   Template file name.
	 * @param string $group  Template group directory (optional).
	 * @return string|false Full path to template file or false if not found.
	 */
	protected function get_template_file( $file, $group = '' ) {
		$file_path = sprintf(
			'%s/assets/templates/%s%s%s.php',
			$this->plugin_file_dir,
			$group,
			'' === $group ? '' : '/',
			sanitize_title( $file )
		);
		$real_path = realpath( $file_path );
		if ( is_file( $real_path ) ) {
			return $real_path;
		}
		return false;
	}
	/**
	 * Get time elapsed text from measurement date.
	 *
	 * Calculates the time elapsed from the measurement date and returns
	 * a human-readable text format (today, yesterday, or N days ago).
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $measurement_date The measurement date in Y-m-d format.
	 * @return string Human-readable time elapsed text.
	 */
	protected function get_time_elapsed_text( $measurement_date ) {
		$measurement_timestamp = strtotime( $measurement_date );
		$current_timestamp     = current_time( 'timestamp' );

		// Calculate days difference
		$days_diff = intval( ( $current_timestamp - $measurement_timestamp ) / ( 24 * 60 * 60 ) );

		if ( $days_diff === 0 ) {
			return __( 'Today', 'iworks-aquarium-log' );
		}
		if ( $days_diff === 1 ) {
			return __( 'Yesterday', 'iworks-aquarium-log' );
		}
		if ( $days_diff > 6 ) {
			$weeks = floor( $days_diff / 7 );
			/* translators: %s: number of weeks */
			return sprintf( _n( '%s week ago', '%s weeks ago', $weeks, 'iworks-aquarium-log' ), number_format_i18n( $weeks ) );
		}
		/* translators: %s: number of days */
		return sprintf( _n( '%s day ago', '%s days ago', $days_diff, 'iworks-aquarium-log' ), number_format_i18n( $days_diff ) );
	}


	/**
	 * Get time elapsed text for dashboard display.
	 *
	 * @since 1.0.0
	 *
	 * @param string $datetime MySQL datetime string.
	 * @return string Time elapsed text.
	 */
	protected function get_time_elapsed_text_seconds( $datetime ) {
		if ( ! $datetime ) {
			return __( 'Never', 'iworks-aquarium-log' );
		}

		$time = strtotime( $datetime );
		$now  = current_time( 'timestamp' );
		$diff = $now - $time;

		if ( $diff < MINUTE_IN_SECONDS ) {
			/* translators: %s: number of seconds */
			return sprintf( _n( '%s second ago', '%s seconds ago', $diff, 'iworks-aquarium-log' ), number_format_i18n( $diff ) );
		}

		$minutes = floor( $diff / MINUTE_IN_SECONDS );
		if ( $minutes < 60 ) {
			/* translators: %s: number of minutes */
			return sprintf( _n( '%s minute ago', '%s minutes ago', $minutes, 'iworks-aquarium-log' ), number_format_i18n( $minutes ) );
		}

		$hours = floor( $diff / HOUR_IN_SECONDS );
		if ( $hours < 24 ) {
			/* translators: %s: number of hours */
			return sprintf( _n( '%s hour ago', '%s hours ago', $hours, 'iworks-aquarium-log' ), number_format_i18n( $hours ) );
		}

		$days = floor( $diff / DAY_IN_SECONDS );
		if ( $days === 1 ) {
			return __( 'Yesterday', 'iworks-aquarium-log' );
		}

		if ( $days > 6 ) {
			$weeks = floor( $days / 7 );
			/* translators: %s: number of weeks */
			return sprintf( _n( '%s week ago', '%s weeks ago', $weeks, 'iworks-aquarium-log' ), number_format_i18n( $weeks ) );
		}

		/* translators: %s: number of days */
		return sprintf( _n( '%s day ago', '%s days ago', $days, 'iworks-aquarium-log' ), number_format_i18n( $days ) );
	}

	/**
	 * Load a template file.
	 *
	 * @since 1.0.0
	 * @param string $file Template file name.
	 * @param string $group Template group name.
	 * @param bool $load_once Whether to load the template only once.
	 * @param array $args Arguments to pass to the template.
	 * @return void
	 */
	protected function load_template( $file, $group = '', $load_once = true, array $args = array() ) {
		$filename = $this->get_template_file( $file, $group );
		if ( $filename ) {
			load_template(
				$filename,
				apply_filters( 'iworks-aquarium-log/load/template/once', $load_once ),
				wp_parse_args(
					apply_filters( 'iworks-aquarium-log/load/template/args', $args ),
					array(
						'messages' => apply_filters( 'iworks-aquarium-log/wp-admin/messages/files', array() ),
						'counters' => array(
							'aquariums' => 0,
						),
					)
				)
			);
			return;
		}
		$this->simple_history_logger_helper(
			/* translators: {file}: template file name, {group}: template group name (do not translate placeholders)*/
			esc_html__( 'Template file not found: {file} ({group}).', 'iworks-aquarium-log' ),
			array(
				'file'  => $file,
				'group' => $group,
			),
			'error'
		);
		if ( current_user_can( 'administrator' ) ) {
			echo '<div class="notice notice-inline notice-error">';
			echo wp_kses_post(
				wpautop(
					sprintf(
						/* translators: %1$s: template file name, %2$s: template group name */
						esc_html__( 'Template file not found: %1$s (%2$s).', 'iworks-aquarium-log' ),
						esc_html( $file ),
						esc_html( $group )
					)
				)
			);
			echo '</div>';
		}
	}

	protected function is_module_enabled( $module ) {
		$this->check_option_object();
		if ( ! preg_match( '/^module_/', $module ) ) {
			$module = 'module_' . $module;
		}
		return boolval( $this->options->get_option( $module ) );
	}

	protected function get_snitized_nonce_value( $nonce_name ) {
		$value = sanitize_text_field( wp_unslash( filter_input( INPUT_POST, $nonce_name ) ) );
		if ( $value ) {
			return false;
		}
		return sanitize_text_field( wp_unslash( filter_input( INPUT_GET, $nonce_name ) ) );
	}
}
