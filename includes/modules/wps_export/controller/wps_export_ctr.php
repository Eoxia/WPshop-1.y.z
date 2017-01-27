<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_export_ctr {

	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_EXPORT_DIR;

	function __construct() {
		$this->template_dir = WPS_EXPORT_PATH . WPS_EXPORT_DIR . "/templates/";
		add_action( 'admin_init', array( $this, 'wps_export_admin_int_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
	}

	function wps_export_admin_int_actions() {
		$current_user_def = wp_get_current_user();
		$download_users = !empty( $_GET['download_users'] ) ? sanitize_text_field( $_GET['download_users'] ) : '';
		$download_orders = !empty( $_GET['download_orders'] ) ? sanitize_text_field( $_GET['download_orders'] ) : '';
		if( !empty($current_user_def) && $current_user_def->ID != 0 && array_key_exists('administrator', $current_user_def->caps) && is_admin() ) {
			if ( !empty( $download_users) ) {
				$this->list_customers( $download_users );
			} elseif ( !empty( $download_orders ) ) {
				$this->list_orders( sanitize_text_field( $download_orders ) );
			}
		}
	}

	function add_scripts($hook) {
		if ( $hook != 'toplevel_page_wpshop_dashboard' )
			return;

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wps_export_script', WPS_EXPORT_URL . WPS_EXPORT_DIR . "/assets/backend/js/wps_export.js");
	}

	function wps_export_tpl() {
		require( wpshop_tools::get_template_part( WPS_EXPORT_DIR, $this->template_dir, "backend", "wps_export_tpl") );
	}

	/**
	 * Get, set and select correct value for download customers
	 * @param string $option
	 */
	function list_customers($option) {
		$wps_export_mdl = new wps_export_mdl();
		if( !empty($option) ) {
			switch ($option) {
				case 'users_all':
					$filetitle = "users_all";
					$array = $wps_export_mdl->get_customers($option);
					break;
				case 'customers_all':
					$filetitle = "customers_all";
					$array = $wps_export_mdl->get_customers($option);
					break;
				case 'newsletters_site':
					$filetitle = "users_newsletters_site";
					$array = $wps_export_mdl->get_customers($option);
					break;
				case 'newsletters_site_partners':
					$filetitle = "users_newsletters_site_partners";
					$array = $wps_export_mdl->get_customers($option);
					break;
				case 'date':
					$bdte = !empty( $_GET['bdte'] ) ? sanitize_text_field( $_GET['bdte'] ) : '';
					$edte = !empty( $_GET['edte'] ) ? sanitize_text_field( $_GET['edte'] ) : '';
					if( !empty($bdte) && !empty($edte) ) {
						$filetitle = "users_registered_" . $bdte . "_to_" . $edte;
						$array = $wps_export_mdl->get_customers($option, $bdte, $edte);
					}
					break;
				case 'orders':
					$free_order = !empty( $_GET['free_order'] ) ? sanitize_text_field( $_GET['free_order'] ) : '';
					if( !empty( $free_order ) && $free_order == 'yes' ) {
						$filetitle = "users_order_with_free_orders";
						$array = $wps_export_mdl->get_customers($option, true, true);
					}
					$minp = !empty( $_GET['minp'] ) ? sanitize_text_field( $_GET['minp'] ) : '';
					if( !empty( $minp ) ) {
						$filetitle = "users_order_higher_than_" . $minp;
						$array = $wps_export_mdl->get_customers($option, $minp);
					}
					break;
			}
		}

		if( empty($array) || !is_array($array) ) {
			$array = '';
		}

		$this->download_csv( $filetitle, $array );
	}

	/**
	 * Get, set and select correct value for download orders
	 * @param string $option
	 */
	function list_orders($option) {
		$wps_export_mdl = new wps_export_mdl();
		switch ($option) {
			case 'date':
				$bdte = !empty( $_GET['bdte'] ) ? sanitize_text_field( $_GET['bdte'] ) : '';
				$edte = !empty( $_GET['edte'] ) ? sanitize_text_field( $_GET['edte'] ) : '';
				if( !empty($bdte) && !empty($edte) ) {
					$filetitle = "commands_registered_" . $bdte . "_to_" . $edte;
					$array = $wps_export_mdl->get_orders($option, $bdte, $edte);
				}
				break;
		}

		if( empty($array) || !is_array($array) ) {
			$array = '';
		}

		$this->download_csv( $filetitle, $array );
	}

	/**
	 * Create a file to download
	 * @param string $filetitle
	 * @param array $array
	 */
	function download_csv( $filetitle, $array ) {
		$wp_upload_dir = wp_upload_dir();
		$final_export_dir = $wp_upload_dir[ 'basedir' ] . '/wpshop/export/';
		wp_mkdir_p( $final_export_dir );
		$htaccess_file = $final_export_dir . '.htaccess';
		if ( !is_file( $htaccess_file ) ) {
			file_put_contents( $htaccess_file, "order deny,allow
deny from all" );
		}
		$filename = $final_export_dir . $filetitle . '.csv';
		$fp = fopen( $filename, 'w' );

		if ( !empty( $array ) ) {
			foreach ($array as $fields) {
				fputcsv($fp, $fields);
			}
		}
		else {
			fputcsv($fp, array( __( 'No data selected', 'wpshop' ), ));
		}

		fclose($fp);
		header("Content-type: application/force-download");
		header("Content-Disposition: attachment; filename=" . basename( $filename ) );
		readfile($filename);

		unlink( $filename );
		exit;
	}

}
