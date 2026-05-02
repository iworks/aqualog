<?php
/**
 * AquaLog Aquarium Post Type Class
 *
 * This class handles the registration and management of the Aquarium custom post type
 * and its associated taxonomy for the AquaLog plugin.
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

require_once 'class-iworks-aqualog-posttype.php';

/**
 * AquaLog Aquarium Post Type Class
 *
 * Handles the registration and management of the Aquarium custom post type
 * and its associated taxonomy.
 *
 * @since 1.0.0
 */
class iworks_aqualog_posttype_aquarium extends iworks_aqualog_posttype {

	/**
	 * Terms list cache.
	 *
	 * @since 1.0.0
	 * @var array $list Cached array of taxonomy terms.
	 */
	private $list = array();

	/**
	 * Constructor.
	 *
	 * Sets up the post type name, taxonomy name, and registers WordPress hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Set post type name.
		 */
		$this->posttype_name = preg_replace( '/^iworks_aqualog_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name, 'iw' );

		/**
		 * Set taxonomy name.
		 */
		$this->taxonomy_name = preg_replace( '/^iworks_aqualog_posttype_/', '', __CLASS__ );
		$this->register_class_custom_taxonomy_name( $this->taxonomy_name, 'iw', 'group' );

		/**
		 * Register WordPress hooks.
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_custom_column', array( $this, 'action_add_menu_order_value' ), 10, 2 );
		add_filter( 'iworks_post_type_aquarium_terms_options_list', array( $this, 'get_options_list_array' ) );
		add_filter( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_columns', array( $this, 'filter_add_menu_order_column' ) );
		add_filter( 'wp_localize_script_iworks_theme', array( $this, 'filter_wp_localize_script_iworks_theme' ) );
	}

	/**
	 * Initialize settings.
	 *
	 * Placeholder method for settings initialization.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function action_init_settings() {
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'aquarium-size' => array(
				'title'  => __( 'Size', 'aqualog' ),
				'fields' => array(
					array(
						'name'  => 'width',
						'type'  => 'number',
						'label' => esc_html__( 'Width', 'aqualog' ),
					),
					array(
						'name'  => 'height',
						'type'  => 'number',
						'label' => esc_html__( 'Height', 'aqualog' ),
					),
					array(
						'name'  => 'length',
						'type'  => 'number',
						'label' => esc_html__( 'Length', 'aqualog' ),
					),
				),
			),
		);
	}

	/**
	 * Register Aquarium custom post type.
	 *
	 * Registers the Aquarium post type with WordPress.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'               => _x( 'Aquariums', 'Post Type General Name', 'aqualog' ),
			'singular_name'      => _x( 'Aquarium', 'Post Type Singular Name', 'aqualog' ),
			'menu_name'          => _x( 'Aquariums', 'Menu Name', 'aqualog' ),
			'name_admin_bar'     => _x( 'Aquarium', 'Admin Bar Name', 'aqualog' ),
			'parent_item_colon'  => __( 'Parent Aquarium:', 'aqualog' ),
			'all_items'          => __( 'Aquariums', 'aqualog' ),
			'add_new_item'       => __( 'Add New Aquarium', 'aqualog' ),
			'add_new'            => __( 'Add New', 'aqualog' ),
			'new_item'           => __( 'New Aquarium', 'aqualog' ),
			'edit_item'          => __( 'Edit Aquarium', 'aqualog' ),
			'update_item'        => __( 'Update Aquarium', 'aqualog' ),
			'view_item'          => __( 'View Aquarium', 'aqualog' ),
			'search_items'       => __( 'Search Aquarium', 'aqualog' ),
			'not_found'          => __( 'Not found', 'aqualog' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'aqualog' ),
		);

		$args = array(
			'label'               => __( 'Aquarium', 'aqualog' ),
			'description'         => __( 'Aquariums', 'aqualog' ),
			'labels'              => apply_filters( 'iworks/theme/register_post_type/aquarium/labels', $labels ),
			'supports'            => apply_filters(
				'iworks/theme/register_post_type/aquarium/subpackage',
				array(
					'title',
					'editor',
				),
			),
			'hierarchical'        => true,
			'public'              => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => admin_url( add_query_arg( 'page', $this->wp_admin_slug, 'admin.php' ) ),
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rest_base'           => apply_filters(
				'iworks/theme/register_post_type/aquarium/rest_base',
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'aquariums' : __( 'aquariums', 'aqualog' )
			),
		);

		register_post_type(
			$this->posttypes_names[ $this->posttype_name ],
			apply_filters(
				'iworks/theme/register_post_type/aquarium/arguments',
				$args
			)
		);
	}

	/**
	 * Register Aquarium Group custom taxonomy.
	 *
	 * Registers the taxonomy for organizing aquariums into groups.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function action_init_register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Aquarium Types', 'Taxonomy General Name', 'aqualog' ),
			'singular_name'              => _x( 'Aquarium Type', 'Taxonomy Singular Name', 'aqualog' ),
			'menu_name'                  => __( 'Types', 'aqualog' ),
			'all_items'                  => __( 'All Aquarium Types', 'aqualog' ),
			'parent_item'                => __( 'Parent Aquarium Type', 'aqualog' ),
			'parent_item_colon'          => __( 'Parent Aquarium Type:', 'aqualog' ),
			'new_item_name'              => __( 'New Aquarium Type Name', 'aqualog' ),
			'add_new_item'               => __( 'Add New Aquarium Type', 'aqualog' ),
			'edit_item'                  => __( 'Edit Aquarium Type', 'aqualog' ),
			'update_item'                => __( 'Update Aquarium Type', 'aqualog' ),
			'view_item'                  => __( 'View Aquarium Type', 'aqualog' ),
			'separate_items_with_commas' => __( 'Separate Aquarium Types with commas', 'aqualog' ),
			'add_or_remove_items'        => __( 'Add or remove Aquarium Types', 'aqualog' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'aqualog' ),
			'popular_items'              => __( 'Popular Aquarium Types', 'aqualog' ),
			'search_items'               => __( 'Search Aquarium Types', 'aqualog' ),
			'not_found'                  => __( 'Not Found', 'aqualog' ),
		);

		$args = array(
			'labels'              => apply_filters( 'iworks/theme/register_post_type/aquarium/labels', $labels ),
			'hierarchical'        => true,
			'public'              => true,
			'exclude_from_search' => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_admin_column'   => true,
			'show_in_nav_menus'   => true,
			'show_tagcloud'       => false,
			'show_in_rest'        => true,
			'show_in_menu'        => admin_url( add_query_arg( 'page', $this->wp_admin_slug, 'admin.php' ) ),
			'rest_base'           => apply_filters(
				'iworks/theme/register_taxonomy/aquarium/rest_base',
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'aquarium_types' : __( 'aquarium_types', 'aqualog' )
			),
		);
		register_taxonomy(
			$this->get_taxonomy( $this->posttype_name ),
			array( $this->posttypes_names[ $this->posttype_name ] ),
			apply_filters( 'iworks/theme/register_taxonomy/aquarium/arguments', $args )
		);
	}
	/**
	 * Filter WordPress localize script data.
	 *
	 * Adds aquarium-specific translations to the localized script data.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $data The existing localized data.
	 * @return array The modified localized data with aquarium translations.
	 */
	public function filter_wp_localize_script_iworks_theme( $data ) {
		$data['i18n']['modules']['aquarium'] = array(
			'button' => array(
				'expand_all'   => esc_html__( 'Expand All', 'aqualog' ),
				'collapse_all' => esc_html__( 'Collapse All', 'aqualog' ),
			),
		);
		return $data;
	}

	/**
	 * Get taxonomy options list array.
	 *
	 * Retrieves and caches the aquarium group taxonomy terms as an options array.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $list The existing list (for filter compatibility).
	 * @return array The options array with taxonomy terms.
	 */
	public function get_options_list_array( $list ) {
		if ( ! empty( $this->list ) ) {
			return $this->list;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $this->taxonomies_names[ $this->posttype_name ],
				'hide_empty' => false,
			)
		);

		$list = array(
			'0' => esc_html__( '&mdash; Select &mdash;', 'aqualog' ),
		);

		foreach ( $terms as $term ) {
			$list[ $term->term_id ] = $term->name;
		}

		$this->list = $list;
		return $list;
	}
}
