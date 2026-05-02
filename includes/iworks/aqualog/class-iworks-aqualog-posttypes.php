<?php
/**
 * AquaLog Post Types Class
 *
 * This class handles the loading and management of custom post types
 * for the AquaLog.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026-PLUGIN_TILL_YEAR Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prevent multiple class definitions
 */
if ( class_exists( 'iworks_wordpress_plugin_posttypes' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/class-iworks-aqualog-base.php';
/**
 * iWorks WordPress Plugin Post Types Class
 *
 * This class manages the loading and initialization of custom post types
 * for the AquaLog.
 *
 * @since 1.0.0
 */
class iworks_aqualog_posttypes extends iworks_aqualog_base {
	/**
	 * Array of post type objects
	 *
	 * Stores instances of all loaded post type classes
	 *
	 * @since 1.0.0
	 * @var array $posttype_objects
	 */
	protected $posttype_objects = array();

	/**
	 * Constructor for the post types class
	 *
	 * Automatically loads and initializes all available post type classes
	 * based on the filter settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Load post types from the posttypes directory
		 */
		$posttypes_classes_dir = $this->includes_directory . '/posttypes/';
		/**
		 * Iterate through all PHP files in the posttypes directory
		 */
		foreach ( glob( $posttypes_classes_dir . 'class*.php' ) as $filename_with_path ) {
			/**
			 * Get the base filename
			 */
			$filename = basename( $filename_with_path );
			/**
			 * Validate the filename format
			 * Only process files that match the expected pattern
			 */
			if ( ! preg_match( '/^class-iworks-aqualog-posttype-([a-z]+).php$/', $filename, $matches ) ) {
				continue;
			}

			/**
			 * Extract the post type name from the filename
			 */
			$posttype_name = $matches[1];
			/**
			 * Create the filter name for this post type
			 */
			$filter = sprintf(
				'aqualog/load/posttype/%s',
				$posttype_name
			);
			/**
			 * Check if this post type should be loaded
			 * Only load if the filter returns true
			 */
			if ( apply_filters( $filter, false ) ) {
				/**
				 * Include the post type class file
				 */
				include_once $posttypes_classes_dir . $filename;

				/**
				 * Generate the class name
				 */
				$class_name = sprintf( 'iworks_aqualog_posttype_%s', $posttype_name );

				/**
				 * Initialize the post type class
				 */
				$this->posttype_objects[ $posttype_name ] = new $class_name();
			}
		}
	}
}
