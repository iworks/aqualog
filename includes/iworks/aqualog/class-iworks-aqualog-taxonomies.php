<?php
/**
 * iWorks Aquarium Log Taxonomies Class
 *
 * This class handles the loading and management of custom taxonomies
 * for the iWorks Aquarium Log.
 *
 * @package    iWorks
 * @subpackage iWorks Aquarium Log
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026-PLUGIN_TILL_YEAR Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prevent multiple class definitions
 */
if ( class_exists( 'iworks_aqualog_taxonomies' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/class-iworks-aqualog-base.php';
/**
 * iWorks WordPress Plugin Taxonomies Class
 *
 * This class manages the loading and initialization of custom taxonomies
 * for the iWorks Aquarium Log.
 *
 * @since 1.0.0
 */
class iworks_aqualog_taxonomies extends iworks_aqualog_base {

	/**
	 * Array of taxonomy objects
	 *
	 * Stores instances of all loaded taxonomy classes
	 *
	 * @since 1.0.0
	 * @var array $taxonomy_objects
	 */
	protected $taxonomy_objects = array();

	/**
	 * Constructor for the taxonomies class
	 *
	 * Automatically loads and initializes all available taxonomy classes
	 * based on the filter settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Load taxonomies from the taxonomies directory
		 */
		$taxonomies_classes_dir = $this->includes_directory . '/taxonomies/';
		/**
		 * Iterate through all PHP files in the taxonomies directory
		 */
		foreach ( glob( $taxonomies_classes_dir . 'class*.php' ) as $filename_with_path ) {
			/**
			 * Get the base filename
			 */
			$filename = basename( $filename_with_path );
			/**
			 * Validate the filename format
			 * Only process files that match the expected pattern
			 */
			if ( ! preg_match( '/^class-iworks-aqualog-taxonomy-([a-z]+).php$/', $filename, $matches ) ) {
				continue;
			}

			/**
			 * Extract the taxonomy name from the filename
			 */
			$taxonomy_name = $matches[1];
			/**
			 * Create the filter name for this taxonomy
			 */
			$filter = sprintf(
				'iworks/aqualog/load/taxonomy/%s',
				$taxonomy_name
			);
			/**
			 * Check if this taxonomy should be loaded
			 * Only load if the filter returns true
			 */
			if ( apply_filters( $filter, false ) ) {
				/**
				 * Include the taxonomy class file
				 */
				include_once $taxonomies_classes_dir . $filename;

				/**
				 * Generate the class name
				 */
				$class_name = sprintf( 'iworks_aqualog_taxonomy_%s', $taxonomy_name );

				/**
				 * Initialize the taxonomy class
				 */
				$this->taxonomy_objects[ $taxonomy_name ] = new $class_name();
			}
		}
	}
}
