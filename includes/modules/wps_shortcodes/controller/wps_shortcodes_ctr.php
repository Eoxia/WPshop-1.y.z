<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_shortcodes_ctr
{
	/*	Define the database table used in the current class	*/
	const dbTable = '';
	/*	Define the url listing slug used in the current class	*/
	const urlSlugListing = WPSHOP_URL_SLUG_SHORTCODES;
	/*	Define the url edition slug used in the current class	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_SHORTCODES;
	/*	Define the current entity code	*/
	const currentPageCode = 'shortcodes';
	/*	Define the page title	*/
	const pageContentTitle = 'Shortcodes';
	/*	Define the page title when adding an attribute	*/
	const pageAddingTitle = 'Add a shortcode';
	/*	Define the page title when editing an attribute	*/
	const pageEditingTitle = 'Shortcode "%s" edit';
	/*	Define the page title when editing an attribute	*/
	const pageTitle = 'Shortcodes list';

	/*	Define the path to page main icon	*/
	public $pageIcon = '';
	/*	Define the message to output after an action	*/
	public $pageMessage = '';

	/**
	 *	Get the url listing slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function setMessage($message){
		$this->pageMessage = $message;
	}
	/**
	 *	Get the url listing slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function getListingSlug(){
		return self::urlSlugListing;
	}
	/**
	 *	Get the url edition slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function getEditionSlug(){
		return self::urlSlugEdition;
	}
	/**
	 *	Get the database table of the current class
	 *
	 *	@return string The table of the class
	 */
	/**
	 *	Define the title of the page
	 *
	 *	@return string $title The title of the page looking at the environnement
	 */
	function pageTitle(){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';
		$objectInEdition = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';
		$page = !empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		$title = __(self::pageTitle, 'wpshop' );
		if($action != ''){
			if(($action == 'edit') || ($action == 'delete')){
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label) . '&nbsp;(' . $editedItem->code . ')');
			}
			elseif($action == 'add'){
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($page == self::getEditionSlug())){
			$title = __(self::pageAddingTitle, 'wpshop');
		}
		return $title;
	}

	function elementAction(){

	}

	/** Définition des shortcodes pour les afficher dans le template list-shortcode */
	public static function shortcode_definition(){
		$shortcodes = array();

		/*	Product tab	*/
		$shortcodes['simple_product']['main_title'] = __('Simple product shortcode', 'wpshop');
		$shortcodes['simple_product']['main_code'] = 'wpshop_product';
		$shortcodes['simple_product']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['simple_product']['attrs_def']['type'] = 'list|grid';
		$shortcodes['simple_product']['attrs_exemple']['pid'] = '12';
		$shortcodes['simple_product']['attrs_exemple']['type'] = 'list';

		$shortcodes['wpshop_product_title']['main_title'] = __( 'Product title', 'wpshop' );
		$shortcodes['wpshop_product_title']['main_code'] = 'wpshop_product_title';
		$shortcodes['wpshop_product_title']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['wpshop_product_title']['attrs_exemple']['pid'] = '12';

		$shortcodes['wpshop_product_content']['main_title'] = __( 'Product content', 'wpshop' );
		$shortcodes['wpshop_product_content']['main_code'] = 'wpshop_product_content';
		$shortcodes['wpshop_product_content']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['wpshop_product_content']['attrs_exemple']['pid'] = '12';

		$shortcodes['wpshop_product_thumbnail']['main_title'] = __( 'Product thumbnail', 'wpshop' );
		$shortcodes['wpshop_product_thumbnail']['main_code'] = 'wpshop_product_thumbnail';
		$shortcodes['wpshop_product_thumbnail']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['wpshop_product_thumbnail']['attrs_def']['size'] = 'TAILLE_DU_PRODUIT';
		$shortcodes['wpshop_product_thumbnail']['attrs_exemple']['pid'] = '12';
		$shortcodes['wpshop_product_thumbnail']['attrs_exemple']['size'] = 'small|medium|full';

		$shortcodes['product_listing']['main_title'] = __('Product listing', 'wpshop');
		$shortcodes['product_listing']['main_code'] = 'wpshop_products';
		$shortcodes['product_listing']['attrs_def']['limit'] = 'NB_MAX_PRODUIT_A_AFFICHER';
		$shortcodes['product_listing']['attrs_def']['order'] = 'title|date|price|rand';
		$shortcodes['product_listing']['attrs_def']['sorting'] = 'asc|desc';
		$shortcodes['product_listing']['attrs_def']['display'] = 'normal|mini';
		$shortcodes['product_listing']['attrs_def']['type'] = 'list|grid';
		$shortcodes['product_listing']['attrs_def']['pagination'] = 'NB_PRODUIT_PAR_PAGE';
		$shortcodes['product_listing']['attrs_exemple']['pid'] = '20';
		$shortcodes['product_listing']['attrs_exemple']['order'] = 'price';
		$shortcodes['product_listing']['attrs_exemple']['sorting'] = 'desc';
		$shortcodes['product_listing']['attrs_exemple']['display'] = 'normal';
		$shortcodes['product_listing']['attrs_exemple']['type'] = 'grid';
		$shortcodes['product_listing']['attrs_exemple']['pagination'] = '5';

		$shortcodes['product_listing_specific']['main_title'] = __('Product listing with specific product', 'wpshop');
		$shortcodes['product_listing_specific']['main_code'] = 'wpshop_products';
		$shortcodes['product_listing_specific']['attrs_def']['pid'] = 'ID_DU_PRODUIT_1,ID_DU_PRODUIT_2,ID_DU_PRODUIT_3,...';
		$shortcodes['product_listing_specific']['attrs_def']['type'] = 'list|grid';
		$shortcodes['product_listing_specific']['attrs_exemple']['pid'] = '12,25,4,98';
		$shortcodes['product_listing_specific']['attrs_exemple']['type'] = 'list';

		$shortcodes['product_by_attribute']['main_title'] = __('Product listing for a given attribute value', 'wpshop');
		$shortcodes['product_by_attribute']['main_code'] = 'wpshop_products';
		$shortcodes['product_by_attribute']['attrs_def']['att_name'] = 'ATTRIBUTE_CODE';
		$shortcodes['product_by_attribute']['attrs_def']['att_value'] = 'ATTRIBUTE_VALUE';
		$shortcodes['product_by_attribute']['attrs_def']['type'] = 'list|grid';
		$shortcodes['product_by_attribute']['attrs_exemple']['att_name'] = 'tx_tva';
		$shortcodes['product_by_attribute']['attrs_exemple']['att_value'] = '19.6';
		$shortcodes['product_by_attribute']['attrs_exemple']['type'] = 'list';

		$shortcodes['related_products']['main_title'] = __('Related products', 'wpshop');
		$shortcodes['related_products']['main_code'] = 'wpshop_related_products';
		$shortcodes['related_products']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['related_products']['attrs_exemple']['pid'] = '12';

		/*	Category tab	*/
		$shortcodes['simple_category']['main_title'] = __('Complete category output', 'wpshop');
		$shortcodes['simple_category']['main_code'] = 'wpshop_category';
		$shortcodes['simple_category']['attrs_def']['cid'] = 'ID_DE_LA_CATEGORIE|ID_CATEGORY_1,ID_CATEGORY_2,ID_CATEGORY_3,...';
		$shortcodes['simple_category']['attrs_def']['type'] = 'list|grid';
		$shortcodes['simple_category']['attrs_def']['display'] = 'only_cat|only_products (default: empty)';
		$shortcodes['simple_category']['attrs_exemple']['cid'] = '12';
		$shortcodes['simple_category']['attrs_exemple']['type'] = 'list';
		$shortcodes['simple_category']['attrs_exemple']['display'] = '';

		/*	Attribute tab	*/
		$shortcodes['simple_attribute']['main_title'] = __('Display an attribute value', 'wpshop');
		$shortcodes['simple_attribute']['main_code'] = 'wpshop_att_val';
		$shortcodes['simple_attribute']['attrs_def']['attid'] = 'ID_DE_LATTRIBUT';
		$shortcodes['simple_attribute']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['simple_attribute']['attrs_exemple']['attid'] = '3';
		$shortcodes['simple_attribute']['attrs_exemple']['pid'] = '98';

		$shortcodes['attributes_set']['main_title'] = __('Display a complete attribute set', 'wpshop');
		$shortcodes['attributes_set']['main_code'] = 'wpshop_att_group';
		$shortcodes['attributes_set']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['attributes_set']['attrs_def']['sid'] = 'ID_DE_LA_SECTION';
		$shortcodes['attributes_set']['attrs_exemple']['pid'] = '98';
		$shortcodes['attributes_set']['attrs_exemple']['sid'] = '2';

		/*	Widget tab	*/
		$shortcodes['widget_cart_full']['main_title'] = __('Display the complete cart', 'wpshop');
		$shortcodes['widget_cart_full']['main_code'] = 'wpshop_cart';

		$shortcodes['widget_cart_mini']['main_title'] = __('Display the cart widget', 'wpshop');
		$shortcodes['widget_cart_mini']['main_code'] = 'wpshop_mini_cart';

		$shortcodes['wpshop_button_add_to_cart']['main_title'] = __('Display the button add to cart', 'wpshop');
		$shortcodes['wpshop_button_add_to_cart']['main_code'] = 'wpshop_button_add_to_cart';
		$shortcodes['wpshop_button_add_to_cart']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['wpshop_button_add_to_cart']['attrs_def']['use_button'] = 'true|false (default: true)';
		$shortcodes['wpshop_button_add_to_cart']['attrs_exemple']['pid'] = '98';
		$shortcodes['wpshop_button_add_to_cart']['attrs_exemple']['use_button'] = 'true';

		$shortcodes['widget_checkout']['main_title'] = __('Display the checkout page content', 'wpshop');
		$shortcodes['widget_checkout']['main_code'] = 'wpshop_checkout';

		$shortcodes['widget_account']['main_title'] = __('Display the customer account page', 'wpshop');
		$shortcodes['widget_account']['main_code'] = 'wpshop_myaccount';

		$shortcodes['widget_shop']['main_title'] = __('Display the shop page content', 'wpshop');
		$shortcodes['widget_shop']['main_code'] = 'wpshop_products';

		$shortcodes['widget_custom_search']['main_title'] = __('Display a custom search form', 'wpshop');
		$shortcodes['widget_custom_search']['main_code'] = 'wpshop_custom_search';

		$shortcodes['widget_filter_search']['main_title'] = __('Display a filter search in category', 'wpshop');
		$shortcodes['widget_filter_search']['main_code'] = 'wpshop_filter_search';

		$shortcodes['widget_wps_breadcrumb']['main_title'] = __('Display a breadcrumb', 'wpshop');
		$shortcodes['widget_wps_breadcrumb']['main_code'] = 'wpshop_breadcrumb';

		return $shortcodes;
	}

	public static function output_shortcode($shortcode_code, $specific_shorcode = '', $more_class_shortcode_helper = ''){
		$shortcode = ( empty($specific_shorcode) ? self::shortcode_definition() : $specific_shorcode );

		$shortcode_main_title = ( !empty($shortcode[$shortcode_code]['main_title']) ? $shortcode[$shortcode_code]['main_title'] : '' );
		$shorcode_main_code = ( !empty($shortcode[$shortcode_code]['main_code']) ? $shortcode[$shortcode_code]['main_code'] : '' );
		$shorcode_attributes_def = ' ';
		if(!empty($shortcode[$shortcode_code]['attrs_def'])){
			foreach($shortcode[$shortcode_code]['attrs_def'] as $attr_name => $attr_values){
				$shorcode_attributes_def .= $attr_name.'="'.$attr_values.'" ';
			}
		}
		$shorcode_attributes_def = substr($shorcode_attributes_def, 0, -1);
		$shorcode_attributes_exemple = ' ';
		if(!empty($shortcode[$shortcode_code]['attrs_exemple'])){
			foreach($shortcode[$shortcode_code]['attrs_exemple'] as $attr_name => $attr_values){
				$shorcode_attributes_exemple .= $attr_name.'="'.$attr_values.'" ';
			}
		}
		$shorcode_attributes_exemple = substr($shorcode_attributes_exemple, 0, -1);

		require( wpshop_tools::get_template_part( WPS_SHORTCODES_DIR, WPS_SHORTCODES_TEMPLATES_MAIN_DIR, "backend", 'shortcode_help.tpl' ) );
	}

	public static function wysiwyg_button() {
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;
		//if ( get_user_option('rich_editing') == 'true') :
		/*add_filter('mce_external_plugins', array('wps_shortcodes_ctr', 'add_button_to_wysiwyg'));
		add_filter('mce_buttons', array('wps_shortcodes_ctr', 'register_wysiwyg_button'));*/
		//endif;
	}
	function refresh_wysiwyg() {
		$ver += 3;
		return $ver;
	}
	public static function add_button_to_wysiwyg($plugin_array){
		$plugin_array['wpshop_wysiwyg_shortcodes'] = WPSHOP_JS_URL . 'pages/wysiwyg_editor.js';
		return $plugin_array;
	}
	public static function register_wysiwyg_button($existing_button){
		array_push($existing_button, "|", "wpshop_wysiwyg_shortcodes");
		return $existing_button;
	}


	/**
	 * Récupères le contenu de tous les shortcodes
	 *
	 * @return string
	 */
	function elementList(){
		$shortcode_list = '';
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SHORTCODES_DIR, WPS_SHORTCODES_TEMPLATES_MAIN_DIR, "backend", 'list', 'shortcode' ) );
		$shortcode_list = ob_get_contents();
		ob_end_clean();

		return $shortcode_list;
	}


	public function __construct() {
		add_action('init', array($this, 'help_tab_shortcodes'));
		add_action( "admin_head", array( $this, 'shortcode_text_localization' ) );
	}

	/**
	 * Localize Script
	 */
	function shortcode_text_localization() {
	    $plugin_url = plugins_url( '/', __FILE__ );
	?>
	<!-- TinyMCE Shortcode Plugin -->
	<script type='text/javascript'>
	var wpshop_mce_shortcode = {
	    'url': "<?php _e('Insert shortcode into page content', 'wpshop' ); ?>",
	    'product_listing': "<?php _e('Product listing', 'wpshop' ); ?>",
	    'url': "<?php _e('By product ID', 'wpshop' ); ?>",
	    'url': "<?php _e('By attribute value', 'wpshop' ); ?>",
	    'url': "<?php _e('Wpshop categories', 'wpshop' ); ?>",
	    'url': "<?php _e('Attribute value', 'wpshop' ); ?>",
	    'url': "<?php _e('Custom message content', 'wpshop' ); ?>",
	    'url': "<?php _e('Customer first name', 'wpshop' ); ?>",
	    'url': "<?php _e('Customer last name', 'wpshop' ); ?>",
	    'url': "<?php _e('Customer email', 'wpshop' ); ?>",
	    'url': "<?php _e('Order identifer', 'wpshop' ); ?>",
	    'url': "<?php _e('Paypal transaction ID', 'wpshop' ); ?>",
	    'url': "<?php _e('Order content', 'wpshop' ); ?>",
	    'url': "<?php _e('Customer personnal informations', 'wpshop' ); ?>",
	    'url': "<?php _e('Order addresses', 'wpshop' ); ?>",
	    'url': "<?php _e('Billing order address', 'wpshop' ); ?>",
	    'url': "<?php _e('Shipping order address', 'wpshop' ); ?>",
	    'url': "<?php _e('Shipping method', 'wpshop' ); ?>",
	    'url': "<?php _e('order payment_method', 'wpshop' ); ?>",
	    'url': "<?php _e('Order customer comment', 'wpshop' ); ?>",
	    'url': "<?php _e('Wpshop custom tags', 'wpshop' ); ?>",
	    'url': "<?php _e('Cart', 'wpshop' ); ?>",
	    'url': "<?php _e('Cart widget', 'wpshop' ); ?>",
	    'url': "<?php _e('Checkout', 'wpshop' ); ?>",
	    'url': "<?php _e('Customer account', 'wpshop' ); ?>",
	    'url': "<?php _e('Shop', 'wpshop' ); ?>",
	    'url': "<?php _e('Advanced search', 'wpshop' ); ?>",
	};
	</script>
	<!-- TinyMCE Shortcode Plugin -->
	    <?php
	}

	public function shortcode_xmlloader() {
		$shortcodes_list = array();
		$ini_get_checking = ini_get( 'allow_url_fopen' );
		if ( $ini_get_checking != 0 ) {
			$content = @file_get_contents( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/lshortcodes.xml' );
			$list_shortcodes_xml = ( $content !== false ) ? new SimpleXmlElement( $content ) : null;
			if ( !empty($list_shortcodes_xml) && !empty($list_shortcodes_xml->channel) )  {
				global $shortcode_tags;
				foreach( $list_shortcodes_xml->channel->category as $i => $category ) {
					$shortcodes_list[(String) $category->title]['desc_cat_' . (String) $category->title] = (String) $category->description;
					foreach( $category->item as $j => $item_xml ) {
						$item = array();
						$item['title'] = (String) $item_xml->title;
						$item['description'] = (String) $item_xml->description;
						foreach( $item_xml->args as $argument) {
							$item['args'][(String) $argument->identifier] = (String) $argument->params;
						}
						$item['active'] = ( array_key_exists((String) $item_xml->identifier, $shortcode_tags) ) ? 'true' : 'false';
						$shortcodes_list[(String) $category->title]['items'][(String) $item_xml->identifier] = $item;
					}
				}
			}
		}
		return $shortcodes_list;
	}

	public function help_tab_shortcodes() {
		if( current_user_can('edit_posts') &&  current_user_can('edit_pages') && get_user_option('rich_editing') == 'true' ) {
			add_filter( 'mce_external_plugins', array( $this, 'wpshop_add_buttons' ) );
			add_filter( 'mce_buttons', array( $this, 'wpshop_register_buttons' ) );
			add_action( 'admin_footer', array( $this, 'wpshop_get_shortcodes' ) );
		}
		/*$shortcodes = $this->shortcode_xmlloader();
		ob_start();
		require( wpshop_tools::get_template_part( WPS_SHORTCODES_DIR, WPS_SHORTCODES_TEMPLATES_MAIN_DIR, "backend", 'shortcode_help_tab' ) );
		$content = ob_get_contents();
		ob_end_clean();
		global $wps_help_tabs;
		$wps_help_tabs->set_help_tab( 'shortcodes', __( 'Shortcodes', 'wpshop' ), $content, array('edit-post', 'post', 'edit-page', 'page', 'edit-comments', 'comments', 'edit-wpshop_product', 'wpshop_product', 'edit-wpshop_product_category') );*/
	}

	public function wpshop_register_buttons( $buttons ) {
		array_push( $buttons, 'separator', 'pushortcodes' );
		return $buttons;
	}

	public function wpshop_add_buttons( $plugin_array ) {
		$plugin_array['pushortcodes'] = WPSHOP_JS_URL . 'pages/shortcode-tinymce-button.js';
		return $plugin_array;
	}

	public function wpshop_get_shortcodes() {
		echo '<script type="text/javascript">var shortcodes_button = new Array();';
		$count = 0;
		foreach( $this->shortcode_xmlloader() as $category ) {
			foreach( $category['items'] as $shortcode => $shortcode_args ) {
				echo "shortcodes_button[{$count}] = { text: '[$shortcode]', content: '[$shortcode";
				if( isset( $shortcode_args['args'] ) ) {
					foreach( $shortcode_args['args'] as $argument => $parameter ) {
						echo ' ' . $argument . '="' . urlencode( $parameter ) . '"';
					}
				}
				echo "]' };";
				$count++;
			}
		}
		echo '</script>';
	}

	public static function wps_shortcodes_wysiwyg_dialog(){
		global $wpdb;
		require( wpshop_tools::get_template_part( WPS_SHORTCODES_DIR, WPS_SHORTCODES_TEMPLATES_MAIN_DIR, "backend", 'wysiwyg_dialog', 'shortcode' ) );
		die();
	}
}
