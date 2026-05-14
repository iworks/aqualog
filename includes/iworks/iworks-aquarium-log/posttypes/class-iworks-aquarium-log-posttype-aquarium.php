<?php
/**
 * iWorks Aquarium Log Aquarium Post Type Class
 *
 * This class handles the registration and management of the Aquarium custom post type
 * and its associated taxonomy for the iWorks Aquarium Log plugin.
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

require_once 'class-iworks-aquarium-log-posttype.php';

/**
 * iWorks Aquarium Log Aquarium Post Type Class
 *
 * Handles the registration and management of the Aquarium custom post type
 * and its associated taxonomy.
 *
 * @since 1.0.0
 */
class iworks_aquarium_log_posttype_aquarium extends iworks_aquarium_log_posttype {

	/**
	 * Terms list cache.
	 *
	 * @since 1.0.0
	 * @var array $list Cached array of taxonomy terms.
	 */
	private $list = array();


	private string $meta_name_related_updated_at = '_related_updated_at';

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
		$this->posttype_name = preg_replace( '/^iworks_aquarium_log_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name, 'iw' );

		/**
		 * Set taxonomy name.
		 */
		$this->taxonomy_name = preg_replace( '/^iworks_aquarium_log_posttype_/', '', __CLASS__ );
		$this->register_class_custom_taxonomy_name( $this->taxonomy_name, 'iw', 'group' );

		/**
		 * Register WordPress hooks.
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_custom_column', array( $this, 'action_add_menu_order_value' ), 10, 2 );
		add_filter( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_columns', array( $this, 'filter_add_menu_order_column' ) );
		add_filter( 'wp_localize_script_iworks_theme', array( $this, 'filter_wp_localize_script_iworks_theme' ) );
		add_action( 'load-post.php', array( $this, 'post_type_admin_enqueue_assets' ) );
		add_action( 'load-post-new.php', array( $this, 'post_type_admin_enqueue_assets' ) );
		/**
		 * Logging hooks.
		 */

		add_action( 'save_post_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'log_aquarium_changes' ), 10, 3 );
		add_action( 'wp_trash_post', array( $this, 'log_aquarium_deletion' ) );
		/**
		 * iworks option class hooks.
		 */
		add_filter( 'index_iworks_aquarium_log_default_aquarium_id_data', array( $this, 'filter_index_iworks_aquarium_log_default_aquarium_data' ), 10, 3 );
		/**
		 * iWorks Aquarium Log plugin hooks.
		 */
		add_filter( 'iworks-aquarium-log/set/current_aquarium_id', array( $this, 'filter_set_current_aquarium_id' ) );
		add_action( 'iworks-aquarium-log/dashboard/aquariums', array( $this, 'action_dashboard_aquariums' ) );
		add_action( 'iworks-aquarium-log/update/aquarium/related_updated', array( $this, 'action_update_aquarium_related_updated' ) );
		add_filter( 'iworks-aquarium-log/load/template/args', array( $this, 'add_page_args' ) );
	}

	/**
	 * Add page arguments to the template.
	 *
	 * @param array $args The page arguments.
	 * @return array The updated page arguments.
	 */
	public function add_page_args( $args ) {
		$this->set_current_aquarium_id();
		return wp_parse_args(
			$args,
			array(
				'aquarium_id'      => $this->current_aquarium_id,
				'recent_aquariums' => $this->get_last(),
				'all_aquariums'    => $this->get_all(),
				'counters'         => array(
					'aquariums' => $this->get_aquariums_count(),
				),
			)
		);
	}

	/**
	 * Get the count of aquariums.
	*
	 * @return int The count of aquariums.
	 */
	public function get_aquariums_count() {
		$count = wp_count_posts( $this->posttypes_names[ $this->posttype_name ] );
		return $count ? $count->publish : 0;
	}

	public function action_update_aquarium_related_updated( $aquarium_id ) {
		update_post_meta( $aquarium_id, $this->meta_name_related_updated_at, current_time( 'mysql' ) );
	}

	private function get_last( $limit = 10 ) {
		$wp_query_args = array(
			'post_type'      => $this->posttypes_names[ $this->posttype_name ],
			'posts_per_page' => $limit,
			'meta_key'       => $this->meta_name_related_updated_at,
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
		);
		return get_posts( $wp_query_args );
	}

	private function get_all() {
		$wp_query_args = array(
			'post_type'      => $this->posttypes_names[ $this->posttype_name ],
			'posts_per_page' => -1,
		);
		return get_posts( $wp_query_args );
	}

	public function action_dashboard_aquariums() {
		$posts = $this->get_last();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				$this->render_dashboard_aquarium_item();
			}
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No aquariums found.', 'iworks-aquarium-log' ) . '</p>';
		}
	}

	public function action_dashboard_aquariums_after() {
		// TODO: Implement dashboard aquariums after action
	}

	/**
	 * Render aquarium item for dashboard display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function render_dashboard_aquarium_item() {
		$post_id      = get_the_ID();
		$title        = get_the_title();
		$permalink    = get_permalink();
		$updated_at   = get_post_meta( $post_id, $this->meta_name_related_updated_at, true );
		$last_updated = $updated_at ? $this->get_time_elapsed_text( $updated_at ) : esc_html__( 'Never', 'iworks-aquarium-log' );

		// Get aquarium type
		$types     = wp_get_post_terms( $post_id, $this->taxonomy_name );
		$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';

		// Get aquarium capacity if available
		$capacity         = get_post_meta( $post_id, 'capacity', true );
		$capacity_display = $capacity ? sprintf( '%s L', number_format_i18n( $capacity ) ) : '';
		?>
		<div class="aquarium-log-aquarium-item">
			<div class="aquarium-log-aquarium-item-header">
				<h3 class="aquarium-log-aquarium-title">
					<a href="<?php echo esc_url( $permalink ); ?>">
						<?php echo esc_html( $title ); ?>
					</a>
				</h3>
				<?php if ( $type_name ) : ?>
					<span class="aquarium-log-aquarium-type"><?php echo esc_html( $type_name ); ?></span>
				<?php endif; ?>
			</div>
			<div class="aquarium-log-aquarium-item-content">
				<div class="aquarium-log-aquarium-info">
					<?php if ( $capacity_display ) : ?>
						<div class="aquarium-log-aquarium-capacity">
							<span class="dashicons dashicons-volume"></span>
							<?php echo esc_html( $capacity_display ); ?>
						</div>
					<?php endif; ?>
					<div class="aquarium-log-aquarium-updated">
						<span class="dashicons dashicons-clock"></span>
						<span class="label"><?php esc_html_e( 'Last updated:', 'iworks-aquarium-log' ); ?></span>
						<span class="value"><?php echo esc_html( $last_updated ); ?></span>
					</div>
				</div>
				<div class="aquarium-log-aquarium-actions">
					<a href="<?php echo esc_url( $permalink ); ?>" class="aquarium-log-button aquarium-log-button-small">
						<?php esc_html_e( 'View Details', 'iworks-aquarium-log' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>" class="aquarium-log-button aquarium-log-button-small aquarium-log-button-outline">
						<?php esc_html_e( 'Edit', 'iworks-aquarium-log' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	public function filter_index_iworks_aquarium_log_default_aquarium_data( $data, $option_name, $default ) {
		$args     = array(
			'post_type'      => $this->posttypes_names[ $this->posttype_name ],
			'posts_per_page' => -1,
		);
		$wp_query = new WP_Query( $args );
		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			$data[ get_the_ID() ] = get_the_title();
		}
		wp_reset_postdata();
		// TODO: Implement filter logic
		return $data;
	}

	public function post_type_admin_enqueue_assets() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && $this->posttypes_names[ $this->posttype_name ] === $screen->post_type ) {
			$this->admin_enqueue_assets();
		}
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
				'title'  => esc_html__( 'Size', 'iworks-aquarium-log' ),
				'fields' => array(
					array(
						'name'  => 'width',
						'type'  => 'number',
						'label' => esc_html__( 'Width', 'iworks-aquarium-log' ),
						'sufix' => 'cm',
					),
					array(
						'name'  => 'height',
						'type'  => 'number',
						'label' => esc_html__( 'Height', 'iworks-aquarium-log' ),
						'sufix' => 'cm',
					),
					array(
						'name'  => 'length',
						'type'  => 'number',
						'label' => esc_html__( 'Length', 'iworks-aquarium-log' ),
						'sufix' => 'cm',
					),
					'capacity'     => array(
						'name'  => 'capacity',
						'type'  => 'number',
						'label' => esc_html__( 'Capacity', 'iworks-aquarium-log' ),
						'sufix' => 'L',
					),
					'water_volume' => array(
						'name'  => 'water_volume',
						'type'  => 'number',
						'label' => esc_html__( 'Water Volume', 'iworks-aquarium-log' ),
						'sufix' => 'L',
					),
				),
			),
			'aquarium-data' => array(
				'title'  => esc_html__( 'Data', 'iworks-aquarium-log' ),
				'fields' => array(
					array(
						'name'  => 'start_date',
						'type'  => 'date',
						'label' => esc_html__( 'Start Date', 'iworks-aquarium-log' ),
					),
				),
			),
			'chemistry'     => array(
				'title'  => esc_html__( 'Chemistry', 'iworks-aquarium-log' ),
				'fields' => array(
					array(
						'name'  => 'check_temp',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Temperature', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_co2',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check CO₂', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_ph',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check pH', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_gh',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check GH', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_kh',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check KH', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_no3',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Nitrate (NO₃)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_po4',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Phosphate (PO₄)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_k',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Potassium (K)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_fe',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Iron (Fe)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_ca',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Calcium (Ca)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_mg',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Magnesium (Mg)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_nh3',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Ammonia (NH₃)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_no2',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Nitrite (NO₂)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_cl',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Chlorine (Cl)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_cu',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Copper (Cu)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_zn',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Zinc (Zn)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_mn',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Manganese (Mn)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_mo',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Molybdenum (Mo)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_zn',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Zinc (Zn)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_b',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Boron (B)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_o2',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Oxygen (O₂)', 'iworks-aquarium-log' ),
					),
					array(
						'name'  => 'check_tds',
						'type'  => 'checkbox',
						'label' => esc_html__( 'Check Total Dissolved Solids (TDS)', 'iworks-aquarium-log' ),
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
			'name'               => esc_html_x( 'Aquariums', 'Post Type General Name', 'iworks-aquarium-log' ),
			'singular_name'      => esc_html_x( 'Aquarium', 'Post Type Singular Name', 'iworks-aquarium-log' ),
			'menu_name'          => esc_html_x( 'Aquariums', 'Menu Name', 'iworks-aquarium-log' ),
			'name_admin_bar'     => esc_html_x( 'Aquarium', 'Admin Bar Name', 'iworks-aquarium-log' ),
			'parent_item_colon'  => esc_html__( 'Parent Aquarium:', 'iworks-aquarium-log' ),
			'all_items'          => esc_html__( 'Aquariums', 'iworks-aquarium-log' ),
			'add_new_item'       => esc_html__( 'Add New Aquarium', 'iworks-aquarium-log' ),
			'add_new'            => esc_html__( 'Add New', 'iworks-aquarium-log' ),
			'new_item'           => esc_html__( 'New Aquarium', 'iworks-aquarium-log' ),
			'edit_item'          => esc_html__( 'Edit Aquarium', 'iworks-aquarium-log' ),
			'update_item'        => esc_html__( 'Update Aquarium', 'iworks-aquarium-log' ),
			'view_item'          => esc_html__( 'View Aquarium', 'iworks-aquarium-log' ),
			'search_items'       => esc_html__( 'Search Aquarium', 'iworks-aquarium-log' ),
			'not_found'          => esc_html__( 'Not found', 'iworks-aquarium-log' ),
			'not_found_in_trash' => esc_html__( 'Not found in Trash', 'iworks-aquarium-log' ),
		);

		$args = array(
			'label'               => esc_html__( 'Aquarium', 'iworks-aquarium-log' ),
			'description'         => esc_html__( 'Aquariums', 'iworks-aquarium-log' ),
			'labels'              => apply_filters( 'iworks/theme/register_post_type/aquarium/labels', $labels ),
			'supports'            => apply_filters(
				'iworks/theme/register_post_type/aquarium/subpackage',
				array(
					'title',
					'editor',
					'thumbnail',
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
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'aquariums' : esc_attr__( 'aquariums', 'iworks-aquarium-log' )
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
			'name'                       => esc_html_x( 'Aquarium Types', 'Taxonomy General Name', 'iworks-aquarium-log' ),
			'singular_name'              => esc_html_x( 'Aquarium Type', 'Taxonomy Singular Name', 'iworks-aquarium-log' ),
			'menu_name'                  => esc_html__( 'Types', 'iworks-aquarium-log' ),
			'all_items'                  => esc_html__( 'All Aquarium Types', 'iworks-aquarium-log' ),
			'parent_item'                => esc_html__( 'Parent Aquarium Type', 'iworks-aquarium-log' ),
			'parent_item_colon'          => esc_html__( 'Parent Aquarium Type:', 'iworks-aquarium-log' ),
			'new_item_name'              => esc_html__( 'New Aquarium Type Name', 'iworks-aquarium-log' ),
			'add_new_item'               => esc_html__( 'Add New Aquarium Type', 'iworks-aquarium-log' ),
			'edit_item'                  => esc_html__( 'Edit Aquarium Type', 'iworks-aquarium-log' ),
			'update_item'                => esc_html__( 'Update Aquarium Type', 'iworks-aquarium-log' ),
			'view_item'                  => esc_html__( 'View Aquarium Type', 'iworks-aquarium-log' ),
			'separate_items_with_commas' => esc_html__( 'Separate Aquarium Types with commas', 'iworks-aquarium-log' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove Aquarium Types', 'iworks-aquarium-log' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'iworks-aquarium-log' ),
			'popular_items'              => esc_html__( 'Popular Aquarium Types', 'iworks-aquarium-log' ),
			'search_items'               => esc_html__( 'Search Aquarium Types', 'iworks-aquarium-log' ),
			'not_found'                  => esc_html__( 'Not Found', 'iworks-aquarium-log' ),
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
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'aquarium_types' : esc_attr__( 'aquarium_types', 'iworks-aquarium-log' )
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
				'expand_all'   => esc_html__( 'Expand All', 'iworks-aquarium-log' ),
				'collapse_all' => esc_html__( 'Collapse All', 'iworks-aquarium-log' ),
			),
		);
		return $data;
	}

	/**
	 * Add custom columns to aquarium post list.
	 *
	 * Adds capacity column to the aquarium post type list.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $columns The existing columns array.
	 * @return array Modified columns array with capacity column.
	 */
	public function filter_add_menu_order_column( $columns ) {
		$columns['capacity'] = esc_html__( 'Capacity', 'iworks-aquarium-log' );
		return $columns;
	}

	/**
	 * Display custom column values in aquarium post list.
	 *
	 * Handles display of capacity and menu order columns.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $column The column name.
	 * @param int    $post_id The post ID.
	 * @return void
	 */
	public function action_add_menu_order_value( $column, $post_id ) {
		switch ( $column ) {
			case 'capacity':
				$capacity = get_post_meta( $post_id, '_iw_aquarium-size_capacity', true );
				if ( $capacity ) {
					echo esc_html( $capacity ) . ' ' . esc_html__( 'L', 'iworks-aquarium-log' );
				} else {
					echo '&mdash;';
				}
				break;
		}
	}

	/**
	 * Set current aquarium ID.
	 *
	 * Sets the current aquarium ID based on the post type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param int $current_aquarium_id The current aquarium ID.
	 * @return int The modified aquarium ID.
	 */
	public function filter_set_current_aquarium_id( $current_aquarium_id ) {
		if ( 0 < $current_aquarium_id ) {
			return $current_aquarium_id;
		}
		$count = wp_count_posts( $this->posttypes_names[ $this->posttype_name ] );
		if ( 1 === intval( $count->publish ) ) {
			$query = new WP_Query(
				array(
					'post_type'      => $this->posttypes_names[ $this->posttype_name ],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);
			if ( $query->have_posts() ) {
				$current_aquarium_id = $query->posts[0];
			}
			wp_reset_postdata();
		}
		return $current_aquarium_id;
	}

	/**
	 * Log aquarium changes on save.
	 *
	 * @since 1.0.0
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an update.
	 * @return void
	 */
	public function log_aquarium_changes( $post_id, $post, $update ) {
		$nonce_value  = $this->get_snitized_nonce_value();
		$nonce_action = 'update-post_' . $post_id;

		// Verify nonce for security
		if ( ! wp_verify_nonce( $nonce_value, $nonce_action ) ) {
			return;
		}

		// Skip revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if this is our post type
		if ( $this->posttypes_names[ $this->posttype_name ] !== $post->post_type ) {
			return;
		}

		$this->action_update_aquarium_related_updated( $post_id );

		// Get logger instance
		if ( ! class_exists( 'iworks_aquarium_log_logger' ) ) {
			require_once dirname( __DIR__ ) . '/class-iworks-aquarium-log-logger.php';
		}
		$logger = new iworks_aquarium_log_logger();

		if ( $update ) {
			// Log update
			$changes = $this->get_post_changes( $post_id );
			$logger->log_aquarium_updated( $post_id, $post->post_title, $changes );
		} else {
			// Log creation
			$logger->log_aquarium_created( $post_id, $post->post_title );
		}
	}

	/**
	 * Log aquarium deletion.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function log_aquarium_deletion( $post_id ) {
		$post = get_post( $post_id );

		// Check if this is our post type
		if ( ! $post || $this->posttypes_names[ $this->posttype_name ] !== $post->post_type ) {
			return;
		}

		// Get logger instance
		if ( ! class_exists( 'iworks_aquarium_log_logger' ) ) {
			require_once dirname( __DIR__ ) . '/class-iworks-aquarium-log-logger.php';
		}
		$logger = new iworks_aquarium_log_logger();

		$logger->log_aquarium_deleted( $post_id, $post->post_title );
	}

	/**
	 * Get post changes for logging.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return array Array of changes.
	 */
	private function get_post_changes( $post_id ) {
		$post_before = get_post( $post_id );
		$post_after  = $_POST; // This contains the submitted form data

		$changes = array();

		// Check title change
		if ( isset( $post_after['post_title'] ) && $post_before->post_title !== $post_after['post_title'] ) {
			$changes['title'] = array(
				'old' => $post_before->post_title,
				'new' => wp_kses_post( $post_after['post_title'] ),
			);
		}

		// Check content change
		if ( isset( $post_after['content'] ) && $post_before->post_content !== $post_after['content'] ) {
			$changes['content'] = array(
				'old' => $post_before->post_content,
				'new' => wp_kses_post( $post_after['content'] ),
			);
		}

		// Check taxonomy changes
		if ( isset( $post_after['tax_input'][ $this->taxonomy_name ] ) ) {
			$old_terms = wp_get_post_terms( $post_id, $this->taxonomy_name, array( 'fields' => 'names' ) );
			$new_terms = $post_after['tax_input'][ $this->taxonomy_name ];

			if ( is_array( $new_terms ) ) {
				$new_term_names = array();
				foreach ( $new_terms as $term_id ) {
					/**
					 * sanitize term_id (from _POST)
					 */
					$term_id = intval( $term_id );
					$term    = get_term( $term_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$new_term_names[] = $term->name;
					}
				}

				if ( $old_terms !== $new_term_names ) {
					$changes['taxonomy'] = array(
						'old' => $old_terms,
						'new' => array_map( 'wp_kses_post', $new_term_names ),
					);
				}
			}
		}

		return $changes;
	}
}
