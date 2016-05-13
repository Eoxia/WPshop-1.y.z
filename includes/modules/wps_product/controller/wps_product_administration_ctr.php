<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main controller file for product administration module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for product administration module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_product_administration_ctr extends wps_product_mdl {

	/**
	 * Instanciate the module
	 */
	function __construct() {
	}

	/**
	 * Generate product sheet datas
	 *
	 * @param integer $product_id THe product identifier to generate the sheet for
	 *
	 * @return string
	 */
	function generate_product_sheet_datas( $product_id ) {
		$product = get_post( $product_id );
		$shop_type = get_option( 'wpshop_shop_type' );
		$product_price_data = get_post_meta( $product_id, '_wps_price_infos', true );

		// Attributes Def
		$product_atts_def = $this->get_product_atts_def( $product_id );

		// Attach CSS datas
		ob_start();
		require( wpshop_tools::get_template_part( WPS_PRODUCT_DIR, WPS_PRODUCT_TEMPLATES_MAIN_DIR, "backend", "product_sheet_datas_style") );
		$output = ob_get_contents();
		ob_end_clean();

		ob_start();
		require( wpshop_tools::get_template_part( WPS_PRODUCT_DIR, WPS_PRODUCT_TEMPLATES_MAIN_DIR, "backend", "product_sheet") );
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}

}

?>