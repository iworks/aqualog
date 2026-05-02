<?php
/**
 * Class for custom Post Type: FAQ
 *
 * @since 1.0.0

Copyright 2026-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

defined( 'ABSPATH' ) || exit;

require_once 'class-iworks-aqualog-posttype.php';

class iworks_aqualog_posttype_aquarium extends iworks_aqualog_posttype {

	private $list = array();

	public function __construct() {
		parent::__construct();
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->posttype_name = preg_replace( '/^iworks_aqualog_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name, 'iw' );
		/**
		 * Taxonomy name
		 */
		$this->taxonomy_name = preg_replace( '/^iworks_aqualog_posttype_/', '', __CLASS__ );
		$this->register_class_custom_taxonomy_name( $this->taxonomy_name, 'iw', 'group' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_custom_column', array( $this, 'action_add_menu_order_value' ), 10, 2 );
		add_filter( 'iworks_post_type_faq_terms_options_list', array( $this, 'get_options_list_array' ) );
		add_filter( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_columns', array( $this, 'filter_add_menu_order_column' ) );
		add_filter( 'wp_localize_script_iworks_theme', array( $this, 'filter_wp_localize_script_iworks_theme' ) );
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
	}

	/**
	 * Register FAQs custom post type
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
			'labels'              => apply_filters( 'iworks/theme/register_post_type/faq/labels', $labels ),
			'supports'            => apply_filters(
				'iworks/theme/register_post_type/faq/subpackage',
				array(
					'title',
					'editor',
				),
			),
			'hierarchical'        => true,
			'public'              => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 10,
			'show_in_admin_bar'   => true,
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
				'iworks/theme/register_post_type/faq/arguments',
				$args
			)
		);
	}

	/**
	 * Register FAQ Group custom taxonomy
	 */
	public function action_init_register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Aquarium Groups', 'Taxonomy General Name', 'aqualog' ),
			'singular_name'              => _x( 'Aquarium Group', 'Taxonomy Singular Name', 'aqualog' ),
			'menu_name'                  => __( 'Groups', 'aqualog' ),
			'all_items'                  => __( 'All Aquarium Groups', 'aqualog' ),
			'parent_item'                => __( 'Parent Aquarium Group', 'aqualog' ),
			'parent_item_colon'          => __( 'Parent Aquarium Group:', 'aqualog' ),
			'new_item_name'              => __( 'New Aquarium Group Name', 'aqualog' ),
			'add_new_item'               => __( 'Add New Aquarium Group', 'aqualog' ),
			'edit_item'                  => __( 'Edit Aquarium Group', 'aqualog' ),
			'update_item'                => __( 'Update Aquarium Group', 'aqualog' ),
			'view_item'                  => __( 'View Aquarium Group', 'aqualog' ),
			'separate_items_with_commas' => __( 'Separate Aquarium Groups with commas', 'aqualog' ),
			'add_or_remove_items'        => __( 'Add or remove Aquarium Groups', 'aqualog' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'aqualog' ),
			'popular_items'              => __( 'Popular Aquarium Groups', 'aqualog' ),
			'search_items'               => __( 'Search Aquarium Groups', 'aqualog' ),
			'not_found'                  => __( 'Not Found', 'aqualog' ),
		);

		$args = array(
			'labels'              => apply_filters( 'iworks/theme/register_post_type//labels', $labels ),
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
			'rest_base'           => apply_filters(
				'iworks/theme/register_taxonomy/aquarium/rest_base',
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'aquarium_groups' : __( 'aquarium_groups', 'aqualog' )
			),
		);

		register_taxonomy(
			$this->get_taxonomy( $this->posttype_name ),
			array( $this->posttypes_names[ $this->posttype_name ] ),
			apply_filters( 'iworks/theme/register_taxonomy/aquarium/arguments', $args )
		);
	}


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
	 * Get taxonomy list
	 *
	 * @param array $list options list
	 *
	 * @return string $content
	 */
	public function get_options_list_array( $list ) {
		if ( ! empty( $this->list ) ) {
			return $this->list;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $this->taxonomy_name['aquarium'],
				'hide_empty' => false,
			)
		);
		$list  = array(
			'0' => esc_html__( '&mdash; Select &mdash;', 'aqualog' ),
		);
		foreach ( $terms as $term ) {
			$list[ $term->term_id ] = $term->name;
		}
		$this->list = $list;
		return $list;
	}
}


