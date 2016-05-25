<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */

if ( !class_exists('wps_modal') ) {
	/** Template Global vars **/
	DEFINE('WPS_MODAL_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_MODAL_PATH', str_replace( "\\", "/", str_replace( WPS_MODAL_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_MODAL_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_MODAL_PATH ) );

	class wps_modal {
		/** Define the main directory containing the template for the current plugin
		* @var string
		*/
		private $template_dir;
		/**
		 * Define the directory name for the module in order to check into frontend
		 * @var string
		 */
		private $plugin_dirname = WPS_MODAL_DIR;

		function __construct() {
			/** Template Load **/
			$this->template_dir = WPS_MODAL_PATH . WPS_MODAL_DIR . "/templates/";
			add_action('wp_enqueue_scripts', array( $this, 'add_scripts') );
			add_action( 'wp_footer', array( $this, 'display_modal') );
		}

		function add_scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wps_modal_script', plugins_url( 'assets/js/wps_modal.js' , __FILE__ ) );
		}

		function display_modal() {
			$output = '';
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_MODAL_DIR, $this->template_dir, "frontend", "modal") );
			$output = ob_get_contents();
			ob_end_clean();
			echo $output;
		}
	}

}
if ( class_exists('wps_modal') ) {
	$wps_modal = new wps_modal();
}
