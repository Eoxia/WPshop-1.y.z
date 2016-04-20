<?php
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
		if( !empty($current_user_def) && $current_user_def->ID != 0 && array_key_exists('administrator', $current_user_def->caps) && is_admin() ) {
			if ( !empty($_GET['download_users']) ) {
				$this->list_customers( $_GET['download_users'] );
			} elseif ( !empty($_GET['download_orders']) ) {
				$this->list_orders( $_GET['download_orders'] );
			}
		}
	}
	
	function add_scripts() {
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
					if( !empty($_GET['bdte']) && !empty($_GET['edte']) ) {
						$bdte = $_GET['bdte'];
						$edte = $_GET['edte'];
						$filetitle = "users_registered_" . $bdte . "_to_" . $edte;
						$array = $wps_export_mdl->get_customers($option, $bdte, $edte);
					}
					break;
				case 'orders':
					if( !empty($_GET['free_order']) && $_GET['free_order'] == 'yes' ) {
						$filetitle = "users_order_with_free_orders";
						$array = $wps_export_mdl->get_customers($option, true, true);
					}
					if( !empty($_GET['minp'] ) ) {
						$minp = $_GET['minp'];
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
				if( !empty($_GET['bdte']) && !empty($_GET['edte']) ) {
					$bdte = $_GET['bdte'];
					$edte = $_GET['edte'];
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
		$filename = $filetitle . '.csv';
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
		header("Content-Disposition: attachment; filename=".$filename);
		readfile($filename);

		unlink( $filename );
		exit;
	}
	
}