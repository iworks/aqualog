<?php
/**


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

require_once dirname( __DIR__, 2 ) . '/class-iworks-aqualog-base.php';

abstract class iworks_aqualog_taxonomy extends iworks_aqualog_base {

	/**
	 * Taxonomy Name
	 *
	 * @since 1.0.0
	 */
	protected string $taxonomy_name;


	/**
	 * post meta prefix
	 */
	protected $post_meta_prefix = '_';

	/**
	 * Load admin assets
	 *
	 * @since 1.0.0
	 */
	protected bool $load_plugin_admin_assets = false;

	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'action_init_settings' ), 0 );
		add_action( 'init', array( $this, 'action_init_register_taxonomy' ), 1 );
	}

	abstract public function action_init_register_taxonomy();
	abstract public function action_init_settings();

	/**
	 * Register the Taxonomy Name in the Class Parent Class.
	 *
	 * @since 1.0.0
	 */
	protected function register_class_custom_taxonomy_name( $taxonomy_name, $prefix = '', $sufix = '' ) {
		if ( ! empty( $prefix ) ) {
			$prefix = sprintf( '%s_', $prefix );
		}
		if ( ! empty( $sufix ) ) {
			$sufix = sprintf( '_%s', $sufix );
		}
		$this->taxonomies_names[ $taxonomy_name ] = $prefix . $taxonomy_name . $sufix;
	}

	protected function get_taxonomy( $taxonomy_name ) {
		if ( ! isset( $this->taxonomies_names[ $taxonomy_name ] ) ) {
			$this->taxonomies_names = apply_filters(
				'iworks/aqualog/taxonomies_names/array',
				$this->taxonomies_names
			);
		}
		if ( isset( $this->taxonomies_names[ $taxonomy_name ] ) ) {
			return $this->taxonomies_names[ $taxonomy_name ];
		}
		return new WP_Error( 'taxonomy', esc_html__( 'Selected Taxonomy dosn\'t exists.', 'PLUGIN_NAME' ) );
	}
}
