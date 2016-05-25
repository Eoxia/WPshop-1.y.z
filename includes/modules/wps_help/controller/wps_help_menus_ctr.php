<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_help_menus_ctr {
	public $sub_menus;

	public function __construct() {
		$this->set_submenu_page_help( __( 'Shortcodes', 'wpshop' ), __( 'Shortcodes', 'wpshop' ), WPSHOP_URL_SLUG_SHORTCODES, array( 'wpshop_display', 'display_page' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'menu_order', array( $this, 'admin_menu_order' ), 12 );
		add_action( 'custom_menu_order', array( $this, 'admin_custom_menu_order' ) );
	}

	/**
	 * Ajout du menu pour le logiciel de caisse dans le backend / Create a new administration menu into backend
	 */
	public function admin_menu() {
		add_menu_page( __( 'Help', 'wpshop' ), __( 'Help', 'wpshop' ), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_ABOUT, array($this, 'render_menu'), 'dashicons-editor-help' );

		$this->create_submenu_page_help();
	}

	/**
	* Appelle le template dans wps_installer backend about / Call the template in wps_installer backend about
	*/
	public function render_menu() {
		require_once( wpshop_tools::get_template_part( WPS_INSTALLER_DIR, WPSINSTALLER_TPL_DIR, "backend", "about" ) );
	}

	/**
	 * WP HOOK - Reorder the admin menu for placing POS addon just below shop menu
	 *
	 * @param array $current_menu_order The current defined menu order we want to change
	 *
	 * @return array The new admin menu order with the POS addon placed
	 */
	public function admin_menu_order( $current_menu_order ) {
		/**	Create a new menu order	*/
		$wps_pos_menu_ordered = array();

		/**	Read the current existing menu order for rearrange it	*/
		foreach ( $current_menu_order as $menu_item ) {
			if ( 'edit.php?post_type=wps_highlighting' == $menu_item ) {
				$wps_pos_menu_ordered[] = 'edit.php?post_type=wps_highlighting';
				$wps_pos_menu_ordered[] = WPSHOP_URL_SLUG_ABOUT;

				unset( $current_menu_order[ array_search( WPSHOP_URL_SLUG_ABOUT, $current_menu_order ) ] );
			}
			else if ( WPSHOP_URL_SLUG_ABOUT != $menu_item ) {
				$wps_pos_menu_ordered[] = $menu_item;
			}
		}

		return $wps_pos_menu_ordered;
	}

	/**
	 * WP HOOK - Define the capability to have to change admin menu order
	 *
	 * @return boolean A boolean var defining if we apply admin menu reorder for current user
	 */
	public function admin_custom_menu_order() {
		return current_user_can( 'manage_options' );
	}

	public function create_submenu_page_help() {
		if( is_array( $this->sub_menus ) ) {
		foreach ( $this->sub_menus as $slug => $data )
			{
				add_submenu_page(
				WPSHOP_URL_SLUG_ABOUT
						, $data['page_title']
						, $data['menu_title']
						, 'wpshop_view_dashboard'
						, $slug, $data['callback']
				);
			}
		}
	}

	public function set_submenu_page_help( $page_title, $menu_title, $menu_slug, $callback ) {
		$this->sub_menus[$menu_slug] = array(
			'page_title'	=> $page_title
			,'menu_title'	=> $menu_title
			,'callback'		=> $callback
		);
	}
}
