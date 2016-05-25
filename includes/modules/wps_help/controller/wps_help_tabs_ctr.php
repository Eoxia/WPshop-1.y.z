<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_help_tabs_ctr
{
	private $help_tabs;

	public function __construct() {
		add_action( "load-{$GLOBALS['pagenow']}", array( $this, 'create_help_tabs' ), 20 );
	}

	public function create_help_tabs()
	{
		if( is_array( $this->help_tabs ) ) {
			$screen = get_current_screen();
			foreach ( $this->help_tabs as $id => $data )
			{
				if( is_array($data['pages']) && in_array( $screen->id, $data['pages'] ) ) {
					$screen->add_help_tab( array(
						'id'        => $id
						,'title'    => $data['title']
						,'content'  => $this->help_tab_static()
						,'callback' => array( $this, 'help_tab_content' )
					) );
				}
			}
		}
	}

	public function help_tab_static()
    {
		ob_start();
    	require( wpshop_tools::get_template_part( WPS_HELP_DIR, WPS_HELP_TEMPLATES_MAIN_DIR, "backend", "wps_tabs_static_tpl") );
		$result .= ob_get_contents();
		ob_end_clean();
		return $result;
	}
	
	public function help_tab_content( $screen, $tab )
	{
		$id = $tab['id'];
		$title = $tab['callback'][0]->help_tabs[ $tab['id'] ]['title'];
		$content = $tab['callback'][0]->help_tabs[ $tab['id'] ]['content'];
		$pages = $tab['callback'][0]->help_tabs[ $tab['id'] ]['pages'];
		require( wpshop_tools::get_template_part( WPS_HELP_DIR, WPS_HELP_TEMPLATES_MAIN_DIR, "backend", "wps_tab_content_tpl") );
	}
	
	public function set_help_tab( $ID, $title, $content, $pages ) {
		$this->help_tabs[$ID] = array( 'title' => $title, 'content' => $content, 'pages' => $pages );
	}
	
	/**
	 * FOR DEV FUNCTION, JUST KNOW ID TO USE FOR A NEW TAB
	 * @return string
	 */
	public static function display_current_id_page() {
		add_action( "load-{$GLOBALS['pagenow']}", array( 'wps_help_tabs_ctr', 'get_current_id_page' ), 20 );
	}
	public static function get_current_id_page() {
		$screen = get_current_screen();
		$id = $screen->id;
		echo '<pre>'; print_r($id); echo '</pre>';
	}
}