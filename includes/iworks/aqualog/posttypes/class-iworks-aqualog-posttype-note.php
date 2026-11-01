<?php
/**
 * Aqualog Note Post Type Class
 *
 * This class handles the registration and management of the Note custom post type
 * for the Aqualog plugin. Notes allow users to track observations, maintenance logs,
 * and other important information related to their aquariums.
 *
 * @package    iWorks
 * @subpackage Aqualog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */


defined( 'ABSPATH' ) || exit;

require_once 'class-iworks-aqualog-posttype.php';

/**
 * Aqualog Note Post Type Class
 *
 * Handles the registration and management of the Note custom post type.
 * Notes can be associated with specific aquariums for tracking purposes.
 *
 * @since 1.0.0
 */
class iworks_aqualog_posttype_note extends iworks_aqualog_posttype {

	/**
	 * Constructor.
	 *
	 * Sets up the post type name and registers WordPress hooks.
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
		 * Register WordPress hooks.
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_custom_column', array( $this, 'action_add_custom_column_value' ), 10, 2 );
		add_filter( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_columns', array( $this, 'filter_add_custom_column' ) );
		add_action( 'load-post.php', array( $this, 'post_type_admin_enqueue_assets' ) );
		add_action( 'load-post-new.php', array( $this, 'post_type_admin_enqueue_assets' ) );
		/**
		 * AJAX handler for adding notes from frontend
		 */
		add_action( 'wp_ajax_aqualog_add_note', array( $this, 'ajax_add_note' ) );
		/**
		 * Logging hooks.
		 */
		add_action( 'save_post_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'log_note_changes' ), 10, 3 );
		add_action( 'wp_trash_post', array( $this, 'log_note_deletion' ) );
	}

	/**
	 * Initialize settings.
	 *
	 * Defines meta boxes for the note post type including
	 * note details, related aquarium, and date information.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function action_init_settings() {
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'note_details' => array(
				'title'    => esc_html__( 'Note Details', 'PLUGIN_NAME' ),
				'context'  => 'normal',
				'priority' => 'high',
				'fields'   => array(
					'aquarium_id' => array(
						'name'    => 'aquarium_id',
						'type'    => 'select',
						'label'   => esc_html__( 'Related Aquarium', 'PLUGIN_NAME' ),
						'options' => $this->get_select_array( 'iw_aquarium' ),
					),
					'note_date'   => array(
						'name'  => 'note_date',
						'type'  => 'date',
						'label' => esc_html__( 'Note Date', 'PLUGIN_NAME' ),
					),
					'note_type'   => array(
						'name'    => 'note_type',
						'type'    => 'select',
						'label'   => esc_html__( 'Note Type', 'PLUGIN_NAME' ),
						'options' => array(
							'observation' => esc_html__( 'Observation', 'PLUGIN_NAME' ),
							'maintenance' => esc_html__( 'Maintenance', 'PLUGIN_NAME' ),
							'reminder'   => esc_html__( 'Reminder', 'PLUGIN_NAME' ),
							'general'    => esc_html__( 'General', 'PLUGIN_NAME' ),
						),
					),
				),
			),
		);
	}

	/**
	 * Register Note custom post type.
	 *
	 * Registers the Note post type with WordPress.
	 *
	 * @since 1.0.0
	 * @action init
	 * @access public
	 * @return void
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'               => esc_html_x( 'Notes', 'Post Type General Name', 'PLUGIN_NAME' ),
			'singular_name'      => esc_html_x( 'Note', 'Post Type Singular Name', 'PLUGIN_NAME' ),
			'menu_name'          => esc_html_x( 'Notes', 'Menu Name', 'PLUGIN_NAME' ),
			'name_admin_bar'     => esc_html_x( 'Note', 'Admin Bar Name', 'PLUGIN_NAME' ),
			'all_items'          => esc_html__( 'All Notes', 'PLUGIN_NAME' ),
			'add_new_item'       => esc_html__( 'Add New Note', 'PLUGIN_NAME' ),
			'add_new'            => esc_html__( 'Add New', 'PLUGIN_NAME' ),
			'new_item'           => esc_html__( 'New Note', 'PLUGIN_NAME' ),
			'edit_item'          => esc_html__( 'Edit Note', 'PLUGIN_NAME' ),
			'update_item'        => esc_html__( 'Update Note', 'PLUGIN_NAME' ),
			'view_item'          => esc_html__( 'View Note', 'PLUGIN_NAME' ),
			'search_items'       => esc_html__( 'Search Notes', 'PLUGIN_NAME' ),
			'not_found'          => esc_html__( 'Not found', 'PLUGIN_NAME' ),
			'not_found_in_trash' => esc_html__( 'Not found in Trash', 'PLUGIN_NAME' ),
		);

		$args = array(
			'label'               => esc_html__( 'Note', 'PLUGIN_NAME' ),
			'description'         => esc_html__( 'Aquarium notes and observations', 'PLUGIN_NAME' ),
			'labels'              => apply_filters( 'iworks/theme/register_post_type/note/labels', $labels ),
			'supports'            => apply_filters(
				'iworks/theme/register_post_type/note/supports',
				array(
					'title',
					'editor',
					'author',
					'excerpt',
				),
			),
			'hierarchical'        => false,
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
				'iworks/theme/register_post_type/note/rest_base',
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'note' : esc_attr( _x( 'notes', 'rest base', 'PLUGIN_NAME' ) )
			),
		);

		register_post_type(
			$this->posttypes_names[ $this->posttype_name ],
			apply_filters(
				'iworks/theme/register_post_type/note/arguments',
				$args
			)
		);
	}

	/**
	 * Register Note taxonomy (optional).
	 *
	 * Notes do not require a taxonomy by default, but this method
	 * is required by the abstract parent class.
	 *
	 * @since 1.0.0
	 * @action init
	 * @access public
	 * @return void
	 */
	public function action_init_register_taxonomy() {
		// Notes do not have a taxonomy by default
		// This method is required by the abstract parent class
	}

	/**
	 * Add custom columns to note post list.
	 *
	 * Adds aquarium and note type columns to the note post type list.
	 *
	 * @since 1.0.0
	 * @filter manage_{post_type}_posts_columns
	 * @access public
	 *
	 * @param array $columns The existing columns array.
	 * @return array Modified columns array with custom columns.
	 */
	public function filter_add_custom_column( $columns ) {
		$columns['aquarium'] = esc_html__( 'Aquarium', 'PLUGIN_NAME' );
		$columns['note_type'] = esc_html__( 'Type', 'PLUGIN_NAME' );
		$columns['note_date'] = esc_html__( 'Date', 'PLUGIN_NAME' );
		return $columns;
	}

	/**
	 * Display custom column values in note post list.
	 *
	 * Handles display of aquarium, note type, and date columns.
	 *
	 * @since 1.0.0
	 * @action manage_{post_type}_posts_custom_column
	 * @access public
	 *
	 * @param string $column  The column name.
	 * @param int    $post_id The post ID.
	 * @return void
	 */
	public function action_add_custom_column_value( $column, $post_id ) {
		switch ( $column ) {
			case 'aquarium':
				$aquarium_id = get_post_meta( $post_id, '_iw_note_details_aquarium_id', true );
				if ( $aquarium_id ) {
					$aquarium = get_post( $aquarium_id );
					if ( $aquarium ) {
						echo '<a href="' . esc_url( get_edit_post_link( $aquarium_id ) ) . '">' . esc_html( $aquarium->post_title ) . '</a>';
					} else {
						echo '&mdash;';
					}
				} else {
					echo '&mdash;';
				}
				break;
			case 'note_type':
				$note_type = get_post_meta( $post_id, '_iw_note_details_note_type', true );
				if ( $note_type ) {
					$types = array(
						'observation' => esc_html__( 'Observation', 'PLUGIN_NAME' ),
						'maintenance' => esc_html__( 'Maintenance', 'PLUGIN_NAME' ),
						'reminder'   => esc_html__( 'Reminder', 'PLUGIN_NAME' ),
						'general'    => esc_html__( 'General', 'PLUGIN_NAME' ),
					);
					echo isset( $types[ $note_type ] ) ? esc_html( $types[ $note_type ] ) : esc_html( $note_type );
				} else {
					echo '&mdash;';
				}
				break;
			case 'note_date':
				$note_date = get_post_meta( $post_id, '_iw_note_details_note_date', true );
				if ( $note_date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $note_date ) ) );
				} else {
					echo '&mdash;';
				}
				break;
		}
	}

	/**
	 * Enqueue admin assets for note post type.
	 *
	 * Loads admin styles and scripts when editing note posts.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
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
	 * Log note changes on save.
	 *
	 * Logs note creation or update events using the logger class.
	 *
	 * @since 1.0.0
	 * @action save_post_{post_type}
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an update.
	 * @return void
	 */
	public function log_note_changes( $post_id, $post, $update ) {
		$nonce_value  = $this->get_nonce_value_and_sanitize_it();
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

		// Get logger instance
		if ( ! class_exists( 'iworks_aqualog_logger' ) ) {
			require_once dirname( __DIR__ ) . '/class-iworks-aqualog-logger.php';
		}
		$logger = new iworks_aqualog_logger();

		if ( $update ) {
			$logger->log_note_updated( $post_id, $post->post_title );
		} else {
			$logger->log_note_created( $post_id, $post->post_title );
		}
	}

	/**
	 * Log note deletion.
	 *
	 * Logs note deletion events using the logger class.
	 *
	 * @since 1.0.0
	 * @action wp_trash_post
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function log_note_deletion( $post_id ) {
		$post = get_post( $post_id );

		// Check if this is our post type
		if ( ! $post || $this->posttypes_names[ $this->posttype_name ] !== $post->post_type ) {
			return;
		}

		// Get logger instance
		if ( ! class_exists( 'iworks_aqualog_logger' ) ) {
			require_once dirname( __DIR__ ) . '/class-iworks-aqualog-logger.php';
		}
		$logger = new iworks_aqualog_logger();

		$logger->log_note_deleted( $post_id, $post->post_title );
	}

	/**
	 * AJAX handler for adding notes from frontend.
	 *
	 * Processes the AJAX request to create a new note post from the maintenance page.
	 *
	 * @since 1.0.0
	 * @action wp_ajax_aqualog_add_note
	 * @return void
	 */
	public function ajax_add_note() {
		// Verify nonce
		if ( ! isset( $_POST['aqualog_note_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aqualog_note_nonce'] ) ), 'aqualog_add_note' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'PLUGIN_NAME' ) ) );
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to add notes.', 'PLUGIN_NAME' ) ) );
		}

		// Get and sanitize form data
		$note_title   = isset( $_POST['note_title'] ) ? sanitize_text_field( wp_unslash( $_POST['note_title'] ) ) : '';
		$note_content = isset( $_POST['note_content'] ) ? wp_kses_post( wp_unslash( $_POST['note_content'] ) ) : '';
		$aquarium_id  = isset( $_POST['aquarium_id'] ) ? intval( $_POST['aquarium_id'] ) : 0;
		$note_date    = isset( $_POST['note_date'] ) ? sanitize_text_field( wp_unslash( $_POST['note_date'] ) ) : '';
		$note_type    = isset( $_POST['note_type'] ) ? sanitize_text_field( wp_unslash( $_POST['note_type'] ) ) : 'general';

		// Validate required fields
		if ( empty( $note_title ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Title is required.', 'PLUGIN_NAME' ) ) );
		}

		// Create the note post
		$post_data = array(
			'post_title'   => $note_title,
			'post_content' => $note_content,
			'post_status'  => 'publish',
			'post_type'    => $this->posttypes_names[ $this->posttype_name ],
			'post_author'  => get_current_user_id(),
		);

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Error creating note.', 'PLUGIN_NAME' ) ) );
		}

		// Save meta fields
		if ( $aquarium_id > 0 ) {
			update_post_meta( $post_id, '_iw_note_details_aquarium_id', $aquarium_id );
		}
		if ( ! empty( $note_date ) ) {
			update_post_meta( $post_id, '_iw_note_details_note_date', $note_date );
		}
		update_post_meta( $post_id, '_iw_note_details_note_type', $note_type );

		wp_send_json_success( array(
			'message' => esc_html__( 'Note added successfully.', 'PLUGIN_NAME' ),
			'post_id' => $post_id,
		) );
	}
}
