<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_highlighting_ctr {

	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_HIGHLIGHTING_DIR;

	function __construct() {
		$this->template_dir = WPS_HIGHLIGHTING_PATH . WPS_HIGHLIGHTING_DIR . "/templates/";
		add_action( 'init', array($this, 'register_post_type') );
		add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
		add_action( 'save_post', array($this, 'save_post_action') );

		/**	Red�finition de l'ordre des menus / Arrangements for displaying menu under wpshop menu	*/
		add_action( 'menu_order', array( $this, 'admin_menu_order' ), 11 );

		add_shortcode( 'wps_highlighting', array( $this, 'display_highlightings' ) );
	}

	/**
	 * Register Post type
	 */
	function register_post_type() {
		$labels = array(
				'name'               => __( 'Highlighting', 'wps_highlighting' ),
				'singular_name'      => __( 'Highlighting', 'wps_highlighting' ),
				'menu_name'          => __( 'Highlightings', 'wps_highlighting' ),
				'add_new'            => __( 'Add new highlighting', 'wps_highlighting' ),
				'add_new_item'       => __( 'Add new highlighting', 'wps_highlighting' ),
				'new_item'           => __( 'New Highlighting', 'wps_highlighting' ),
				'edit_item'          => __( 'Edit Highlighting', 'wps_highlighting' ),
				'view_item'          => __( 'View Highlighting', 'wps_highlighting' ),
				'all_items'          => __( 'All Highlightings', 'wps_highlighting' ),
				'search_items'       => __( 'Search Highlighting', 'wps_highlighting' ),
				'parent_item_colon'  => __( 'Parent Highlighting :', 'wps_highlighting' ),
				'not_found'          => __( 'No Highlighting found.', 'wps_highlighting' ),
				'not_found_in_trash' => __( 'No Highlightings found in Trash.', 'wps_highlighting' ),
		);

		$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'menu_icon'			 => 'dashicons-star-filled',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => true,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING, $args );
	}

	/**
	 * WP HOOK - Reorder the admin menu for placing POS addon just below shop menu
	 *
	 * @param array $current_menu_order The current defined menu order we want to change
	 *
	 * @return array The new admin menu order with the POS addon placed
	 */
	function admin_menu_order( $current_menu_order ) {
		/**	Create a new menu order	*/
		$wps_pos_menu_ordered = array();

		/**	Read the current existing menu order for rearrange it	*/
		foreach ( $current_menu_order as $menu_item ) {
			if ( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS == $menu_item ) {
				$wps_pos_menu_ordered[] = 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
				$wps_pos_menu_ordered[] = 'edit.php?post_type=wps_highlighting';

				unset( $current_menu_order[ array_search( 'edit.php?post_type=wps_highlighting', $current_menu_order ) ] );
			}
			else if ( 'edit.php?post_type=wps_highlighting' != $menu_item ) {
				$wps_pos_menu_ordered[] = $menu_item;
			}
		}

		return $wps_pos_menu_ordered;
	}

	/**
	 * Add Meta Box
	 */
	function add_meta_boxes() {
		add_meta_box( 'wps_highlighting_meta_box', __( 'Select the hook', 'wps_highlighting'), array( $this, 'meta_box_content' ), WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING, 'side', 'default');
		add_meta_box( 'wps_highlighting_meta_box_link', __( 'Link of Highlighting', 'wps_highlighting'), array( $this, 'meta_box_content_link' ), WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING, 'side', 'default');
	}

	/**
	 * Meta Box content
	 */
	function meta_box_content() {
		global $post;

		$hook = get_post_meta( $post->ID, '_wps_highlighting_hook', true );
		$output  = '<select name="wps_highlighting_hook">';
		$output .= '<option value="sidebar" ' .( ( !empty($hook) && $hook == 'sidebar' ) ? 'selected="selected"' : '' ). '>' .__( 'Sidebar', 'wps_highlighting' ). '</option>';
		$output .= '<option value="home" ' .( ( !empty($hook) && $hook == 'home' ) ? 'selected="selected"' : '' ). '>' .__( 'HomePage Content', 'wps_highlighting' ). '</option>';
		$output .= '</select>';
		$output .= '<hr/>';
		$output .= '<div style="padding : 5px; background #CCC;"><u><strong>' .__( 'shortcode for display Highlightings', 'wpshop'). '</strong></u><ul><li><u>Home page content :</u> [wps_highlighting hook_name="home"]</li><li><u>Sidebar :</u> [wps_highlighting hook_name="sidebar"]</li><ul></div>';
		echo $output;
	}

	function meta_box_content_link() {
		global $post;
		$link = get_post_meta( $post->ID, '_wps_highlighting_link', true );
		$output  = '<label for="wps_highlighting_link">' .__( 'Link of Highlighting', 'wps_highlighting' ). '</label><br/>';
		$output .= '<input type="text" id="wps_highlighting_link" name="wps_highlighting_link" value="' .$link. '" />';
		echo $output;
	}

	/**
	 * Save action
	 */
	function save_post_action() {
		$post_type = !empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
		if( !empty($post_type) && !empty($post_type) && $post_type == WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING ) {
			$wps_highlighting_hook = !empty( $_POST['wps_highlighting_hook'] ) ? sanitize_text_field( $_POST['wps_highlighting_hook'] ) : '';
			$wps_highlighting_link = !empty( $_POST['wps_highlighting_link'] ) ? sanitize_text_field( $_POST['wps_highlighting_link'] ) : '';
			$post_ID = !empty( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;
			if( !empty($wps_highlighting_hook) ) {
				update_post_meta( $post_ID, '_wps_highlighting_hook', $wps_highlighting_hook );
			}
			update_post_meta( $post_ID, '_wps_highlighting_link', $wps_highlighting_link );
		}
	}

	function get_data_for_hook( $hook ) {
		$highlightings_datas = array();
		if( !empty($hook) ) {
			$wps_highlighting_mdl = new wps_highlighting_model();
			$highlightings = $wps_highlighting_mdl->get_highlighting( $hook );
			if( !empty($highlightings) ) {
				foreach( $highlightings as $highlighting ) {
					$wps_highlighting = new wps_highlighting_model( $highlighting['post_data']->ID, $highlighting['post_data']->post_title, $highlighting['post_meta']['hook'], $highlighting['post_meta']['link'] );
					$wps_highlighting->post_content = $highlighting['post_data']->post_content;
					$highlightings_datas[] = $wps_highlighting;
				}
			}
		}
		return $highlightings_datas;
	}

	function display_highlightings( $args ) {
		$output = $highlightings = '';
		if( !empty($args) && !empty($args['hook_name']) ) {
			$datas = $this->get_data_for_hook( $args['hook_name'] );
			//Display in Template
			if( !empty($datas) ) {
				foreach( $datas as $data ) {
					ob_start();
					require( wpshop_tools::get_template_part( WPS_HIGHLIGHTING_DIR, $this->template_dir,"frontend", "highlighting") );
					$highlightings .= ob_get_contents();
					ob_end_clean();
				}
			}
		}
		return $highlightings;
	}

}
