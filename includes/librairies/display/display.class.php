<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Plugin tools librairies file.
 *
 * This file contains the different common tools used in all the plugin
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_display {

	/**
	*	Returns the header display of a classical HTML page.
	*
	*	@see afficherFinPage
	*
	*	@param string $pageTitle Title of the page.
	*	@param string $pageIcon Path of the icon.
	*	@param string $iconTitle Title attribute of the icon.
	*	@param string $iconAlt Alt attribute of the icon.
	*	@param boolean $hasAddButton Define if there must be a "add" button for this page
	*	@param string $actionInformationMessage A message to display in case of action is send
	*
	*	@return string Html code composing the page header
	*/
	function displayPageHeader($pageTitle, $pageIcon, $iconTitle, $iconAlt, $hasAddButton = true, $addButtonLink = '', $actionInformationMessage = '', $current_page_slug = ''){
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_page_header.tpl.php');
	}

	/**
	*	Returns the end of a classical page
	*
	*	@see displayPageHeader
	*
	*	@return string Html code composing the page footer
	*/
	function displayPageFooter($formActionButton){
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_page_footer.tpl.php');
	}

	/**
	*	Return The complete output page code
	*
	*	@return string The complete html page output
	*/
	function display_page(){
		$pageAddButton = false;
		$pageMessage = $addButtonLink = $pageFormButton = $pageIcon = $pageIconTitle = $pageIconAlt = $objectType = '';
		$outputType = 'listing';
		$objectToEdit = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';
		$pageSlug = isset($_REQUEST['page']) ? wpshop_tools::varSanitizer($_REQUEST['page']) : '';
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';

		/*	Select the content to add to the page looking for the parameter	*/
		switch($pageSlug){
			case WPSHOP_URL_SLUG_ATTRIBUTE_LISTING:
				$objectType = new wpshop_attributes();
				$current_user_can_edit = current_user_can('wpshop_edit_attributes');
				$current_user_can_add = current_user_can('wpshop_add_attributes');
				$current_user_can_delete = current_user_can('wpshop_delete_attributes');
				if(current_user_can('wpshop_add_attributes')){
					$pageAddButton = true;
				}
			break;
			case WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING:
				$objectType = new wpshop_attributes_set();
				$current_user_can_edit = current_user_can('wpshop_edit_attribute_set');
				$current_user_can_add = current_user_can('wpshop_add_attribute_set');
				$current_user_can_delete = current_user_can('wpshop_delete_attribute_set');
				if(current_user_can('wpshop_add_attribute_set')){
					$pageAddButton = true;
				}
			break;
			case WPSHOP_URL_SLUG_SHORTCODES:
				$pageAddButton = false;
				$current_user_can_edit = false;
				$objectType = new wpshop_shortcodes();
			break;
			case WPSHOP_URL_SLUG_MESSAGES:
				$pageAddButton = false;
				$objectType = new wpshop_messages();
				$current_user_can_edit = true;
				if(!empty($_GET['mid'])){
					$action = 'edit';
				}
			break;
			case WPSHOP_URL_SLUG_DASHBOARD:
				$pageAddButton = false;
				$pageTitle = __('Shop dashboard', 'wpshop');
				$pageContent = wpshop_dashboard::display_dashboard();
			break;
			default:{
				$pageTitle = sprintf(__('You have to add this page into %s at line %s', 'wpshop'), __FILE__, (__LINE__ - 4));
				$pageAddButton = false;
			}
			break;
		}

		if($objectType != ''){
			if(($action != '') && ((($action == 'edit') && $current_user_can_edit) || (($action == 'add') && $current_user_can_add) || (($action == 'delete') && $current_user_can_delete))){
				$outputType = 'adding';
			}
			$objectType->elementAction();

			$pageIcon = self::getPageIconInformation('path', $objectType);
			$pageIconTitle = self::getPageIconInformation('title', $objectType);
			$pageIconAlt = self::getPageIconInformation('alt', $objectType);

			if($outputType == 'listing'){
				$pageContent = $objectType->elementList();
			}
			elseif($outputType == 'adding'){
				$pageAddButton = false;

				$pageFormButton = $objectType->getPageFormButton($objectToEdit);

				$pageContent = $objectType->elementEdition($objectToEdit);
			}

			$pageTitle = $objectType->pageTitle();
			$pageMessage = $objectType->pageMessage;
			$addButtonLink = admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&amp;page=' . $objectType->getEditionSlug() . '&amp;action=add');
		}

		/*	Page content header	*/
		wpshop_display::displayPageHeader($pageTitle, $pageIcon, $pageIconTitle, $pageIconAlt, $pageAddButton, $addButtonLink, $pageMessage, $pageSlug);

		/*	Page content	*/
		echo $pageContent;

		/*	Page content footer	*/
		wpshop_display::displayPageFooter($pageFormButton);
	}

	/**
	 * Define the wat to display admin page: tabs shape or bloc shape
	 *
	 * @param array $content
	 * @param string $output_type The type of output for the
	 *
	 * @return string The output builded from selected type
	 */
	function custom_page_output_builder($content, $output_type='tab') {
		$output_custom_layout = '';

		switch ( $output_type ) {
			case 'separated_bloc':
				foreach ( $content as $element_type => $element_type_details ) {
					$output_custom_layout.='
	<div class="wpshop_separated_bloc wpshop_separated_bloc_'.$element_type.'" >';
					foreach ( $element_type_details as $element_type_key => $element_type_content ) {
						$output_custom_layout.='
		<div class="wpshop_admin_box wpshop_admin_box_'.$element_type.' wpshop_admin_box_'.$element_type.'_'.$element_type_key.'" >
			<h3>' . $element_type_content['title'] . '</h3>' . $element_type_content['content'] . '
		</div>';
					}
					$output_custom_layout.='
	</div>';
				}
			break;
			case 'tab':
				$tab_list=$tab_content_list='';
				foreach ( $content as $element_type => $element_type_details ) {
					foreach ( $element_type_details as $element_type_key => $element_type_content ) {
						$tab_list.='
		<li><a href="#wpshop_'.$element_type.'_'.$element_type_key.'" >'.$element_type_content['title'].'</a></li>';
						$tab_content_list.='
		<div id="wpshop_'.$element_type.'_'.$element_type_key.'" class="wpshop_admin_box wpshop_admin_box_'.$element_type.' wpshop_admin_box_'.$element_type.'_'.$element_type_key.'" >'.$element_type_content['content'].'
		</div>';
					}
				}
				$output_custom_layout.='
	<div id="wpshopFormManagementContainer" class="wpshop_tabs wpshop_full_page_tabs wpshop_'.$element_type.'_tabs" >
		<ul>' . $tab_list . '</ul>' . $tab_content_list . '
	</div>';
					break;
		}

		return $output_custom_layout;
	}



	/*
	* Return a complete html table with header, body and content
	*
	*	@param string $tableId The unique identifier of the table in the document
	*	@param array $tableTitles An array with the different element to put into the table's header and footer
	*	@param array $tableRows An array with the different value to put into the table's body
	*	@param array $tableClasses An array with the different class to affect to table rows and cols
	*	@param array $tableRowsId An array with the different identifier for table lines
	*	@param string $tableSummary A summary for the table
	*	@param boolean $withFooter Allow to define if the table must be create with a footer or not
	*
	*	@return string $table The html code of the table to output
	*/
	function getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary = '', $withFooter = true){
		$tableTitleBar = $tableBody = '';

		/*	Create the header and footer row	*/
		for($i=0; $i<count($tableTitles); $i++){
			$tableTitleBar .= '
				<th class="' . $tableClasses[$i] . '" scope="col" >' . $tableTitles[$i] . '</th>';
		}

		/*	Create each table row	*/
		for($lineNumber=0; $lineNumber<count($tableRows); $lineNumber++){
			$tableRow = $tableRows[$lineNumber];
			$tableBody .= '
		<tr id="' . $tableRowsId[$lineNumber] . '" class="tableRow" >';
			for($i=0; $i<count($tableRow); $i++){
				$tableBody .= '
			<td class="' . $tableClasses[$i] . ' ' . $tableRow[$i]['class'] . '" >' . $tableRow[$i]['value'] . '</td>';
			}
			$tableBody .= '
		</tr>';
		}

		/*	Create the table output	*/
		$table = '
<table id="' . $tableId . '" cellspacing="0" cellpadding="0" class="widefat post fixed" >';
		if($tableTitleBar != ''){
			$table .= '
	<thead>
			<tr class="tableTitleHeader" >' . $tableTitleBar . '
			</tr>
	</thead>';
			if($withFooter){
				$table .= '
	<tfoot>
			<tr class="tableTitleFooter" >' . $tableTitleBar . '
			</tr>
	</tfoot>';
			}
		}
		$table .= '
	<tbody>' . $tableBody . '
	</tbody>
</table>';

		return $table;
	}

	/**
	 * Define the icon informations for the page
	 *
	 * @param string $infoType The information type we want to get Could be path / alt / title
	 *
	 * @return string $pageIconInformation The information to output in the page
	 */
	function getPageIconInformation($infoType, $object){
		switch($infoType){
			case 'path':
				$pageIconInformation = $object->pageIcon;
			break;
			case 'alt':
			case 'title':
			default:
				$pageIconInformation = $object->pageTitle();
			break;
		}

		return $pageIconInformation;
	}



	/**
	 * Check if the templates file are available from the current theme. If not present return the default templates files
	 *
	 * @param string $file_name The file name to check if exists in current theme
	 * @param string $dir_name Optionnal The directory name of the file to check Default : wpshop
	 *
	 * @return string $file_path The good filepath to include
	 */
	function get_template_file($file_name, $default_dir = WPSHOP_TEMPLATES_DIR, $dir_name = 'wpshop', $usage_type = 'include'){
		$file_path = '';
		$the_file = $dir_name . '/' . $file_name;

		if(is_file(get_stylesheet_directory() . '/' . $the_file)){
			$default_dir = str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, get_stylesheet_directory());
			if($usage_type == 'include'){
				$default_dir = get_stylesheet_directory();
			}
			$file_path = $default_dir . '/' . $the_file;
		}
		else{
			$file_path = $default_dir . $the_file;
		}

		return $file_path;
	}


	function check_way_for_template($template_part, $default_template_dir = 'wpshop') {
		$old_file_to_take_care = false;
		$old_file_to_take_care_url = null;

		/*
		 * Directory containing custom templates
		 */
		$custom_template_part = get_stylesheet_directory() . '/' . $default_template_dir . '/';

		/*
		 * Let support the old way of template managing
		 */
		switch ( $template_part ) {
			case 'category_mini_list':
					$old_file_to_take_care_url = 'category-mini-list.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'category_mini_grid':
					$old_file_to_take_care_url = 'category-mini-grid.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_complete_tpl':
					$old_file_to_take_care_url = 'product.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_mini_list':
					$old_file_to_take_care_url = 'product-mini-list.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_mini_grid':
					$old_file_to_take_care_url = 'product-mini-grid.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_listing_sorting':
					$old_file_to_take_care_url = 'product_listing_sorting.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'unavailable_product_button':
					$old_file_to_take_care_url = 'not_available_product_button.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'add_to_cart_button':
					$old_file_to_take_care_url = 'available_product_button.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'ask_quotation_button':
					$old_file_to_take_care_url = 'quotation_button.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'mini_cart_content':
					$old_file_to_take_care_url = 'wpshop_mini_cart.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_is_new_sticker':
					$old_file_to_take_care_url = 'product-is-new.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_is_featured_sticker':
					$old_file_to_take_care_url = 'product-is-featured.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attribute_container':
					$old_file_to_take_care_url = 'product-attribute-front-display-main-container.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attribute_tabs':
					$old_file_to_take_care_url = 'product-attribute-front-display-tabs.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attribute_tabs_detail':
					$old_file_to_take_care_url = 'product-attribute-front-display-tabs-content.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attachment_picture_galery':
					$old_file_to_take_care_url = 'product_picture_galery.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attachment_galery':
					$old_file_to_take_care_url = 'product_document_library.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attachment_item_picture':
					$old_file_to_take_care_url = 'product_attachment_picture_line.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_attachment_item_document':
					$old_file_to_take_care_url = 'product_attachment_document_line.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;
			case 'product_added_to_cart_message':
					$old_file_to_take_care_url = 'product_added_to_cart_message.tpl.php';
					if ( is_file($custom_template_part . $old_file_to_take_care_url ) ) :
						$old_file_to_take_care = true;
					endif;
				break;


			case 'product_attribute_display':
			case 'product_attribute_unit':
			case 'product_attribute_value_internal':
			default:
					$old_file_to_take_care = false;
					$old_file_to_take_care_url = null;
				break;
		}

		return array($old_file_to_take_care, $old_file_to_take_care_url);
	}

	function display_template_element($template_part, $template_part_component, $extras_args = array(), $default_template_dir = 'wpshop', $template_elements_file = 'wpshop_elements_template.tpl.php') {
		/*	Get the defined template	*/
		$template = unserialize(WPSHOP_TEMPLATE);

		/*	Set the template element to return by default before checking if custom exists in order to be sure to return something	*/
		$tpl_element_to_return = !empty($template[$default_template_dir]['default'][$template_part]) ? $template[$default_template_dir]['default'][$template_part] : '';

		/*	Check if the file have been duplicated into theme directory for customization	*/
		if ( !empty( $template[$default_template_dir]['custom'] ) && !empty( $template[$default_template_dir]['custom'][$template_part] ) ) {
			$tpl_element_to_return = $template[$default_template_dir]['custom'][$template_part];
		}

		return self::feed_template($tpl_element_to_return, $template_part_component);
	}

	/**
	 * Fill a template with given element. Replace some code by content before output the html
	 *
	 * @param string $template_to_fill The complete html code we want to display with element to change
	 * @param array $feed The different element to put in place of the code into the tempalte part
	 *
	 * @return string The html code to display
	 */
	function feed_template($template_to_fill, $feed) {
		/* Add general element	*/
		$feed['CURRENCY'] = wpshop_tools::wpshop_get_currency();
		$feed['CART_LINK'] = get_permalink(get_option('wpshop_cart_page_id'));

		$available_key = array();
		foreach ($feed as $element => $value) {
			$available_key[] = '{WPSHOP_'.$element.'}';
			$template_to_fill = str_replace('{WPSHOP_'.$element.'}', $value, $template_to_fill);
		}
		if (WPSHOP_DISPLAY_AVAILABLE_KEYS_FOR_TEMPLATE) $template_to_fill = '<!-- Available keys : ' . implode(' / ', $available_key) . ' -->' . $template_to_fill;

		return $template_to_fill;
	}

	/**
	 * Check if template file exist in current theme directory. If not the case copy all template files into
	 *
	 * @param boolean $force_replacement Define if we overwrite in all case or just if it not exist
	 */
	function check_template_file( $force_replacement = false ) {
		$wpshop_directory = get_stylesheet_directory() . '/wpshop';

		/*	Add different file template	*/
		if(!is_dir($wpshop_directory)){
			@mkdir($wpshop_directory, 0755, true);
		}
		/* On s'assure que le dossier principal est bien en 0755	*/
		@chmod($wpshop_directory, 0755);
		$upload_dir = wp_upload_dir();

		/*	Add the category template	*/
		if(!is_file(get_stylesheet_directory() . '/taxonomy-wpshop_product_category.php') || ($force_replacement)){
			@copy(WPSHOP_TEMPLATES_DIR . 'taxonomy-wpshop_product_category.php', get_stylesheet_directory() . '/taxonomy-wpshop_product_category.php');
		}
	}


/**
 * Taxonomy display
 */
	/**
	 * Transform product taxonomy descrition field into a wysiwyg editor
	 */
	function wpshop_rich_text_tags() {
		global $wpdb, $user, $current_user, $pagenow, $wp_version;

		/*	Check if user is on taxonomy edition page	*/
		if ($pagenow == 'edit-tags.php') {

			if(!user_can_richedit()) { return; }

			$taxonomies = get_taxonomies();

			foreach ($taxonomies as $tax) {
				if ( in_array($tax, array(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES)) ) {
					add_action($tax . '_edit_form_fields', array('wpshop_display','wpshop_add_form'));
					add_action($tax . '_add_form_fields', array('wpshop_display','wpshop_add_form'));
				}
			}

			if ($pagenow == 'edit-tags.php' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && empty($_REQUEST['taxonomy'])) {
				add_action('edit_term',array('wpshop_display','wpshop_rt_taxonomy_save'));
			}

			foreach ( array( 'pre_term_description', 'pre_link_description', 'pre_link_notes', 'pre_user_description' ) as $filter ) {
				remove_filter( $filter, 'wp_filter_kses' );
			}

		}

		/*	Enable shortcodes in category, taxonomy, tag descriptions */
		if(function_exists('term_description')) {
			add_filter('term_description', 'do_shortcode');
		}
		else {
			add_filter('category_description', 'do_shortcode');
		}
	}

	/**
	 * Save the category description field
	 */
	function wpshop_rt_taxonomy_save() {
		global $tag_ID;

		$a = array('description');
		foreach ($a as $v) {
			wp_update_term($tag_ID,$v,$_POST[$v]);
		}
	}

	/**
	 * Definition for the wyswiwyg editor
	 *
	 * @param object $object The type of element currently edited
	 */
	function wpshop_add_form($object = '') {
		global $pagenow;

		$content = is_object($object) && isset($object->description) ? html_entity_decode($object->description) : '';

		if( in_array($pagenow, array('edit-tags.php')) ) {
			$editor_id = 'tag_description';
			$editor_selector = 'description';
		}
		else {
			$editor_id = $editor_selector = 'category_description';
		}


		/*
		 * Template parameters
		*/
		$template_part = 'wpshop_transform_taxonomy_description_field_into_wysiwyg';
		if ( !empty($_GET['action']) && ($_GET['action'] == 'edit') ) {
			$template_part = 'wpshop_transform_taxonomy_description_field_into_wysiwyg_for_full_page';
		}
		$tpl_component = array();
		ob_start();
		wp_editor($content, $editor_id, array(
			'textarea_name' => $editor_selector,
			'editor_css' => wpshop_display::display_template_element('wpshop_taxonomy_wysiwyg_editor_css', array(), array(), 'admin'),
		));
		$tpl_component['ADMIN_TAXONOMY_WYSIWYG'] = ob_get_contents();
		ob_end_clean();
		echo wpshop_display::display_template_element($template_part, $tpl_component, array(), 'admin');
		unset($tpl_component);
	}


/**
 * Product display
 */
	/**
	 * Change output for product page
	 *
	 * @param string $content The content of a post
	 * @return Ambigous <mixed, string>|unknown
	 */
	function products_page( $content = '' ) {
		global $wp_query;

		if (!empty($wp_query->queried_object) && !empty($wp_query->queried_object->post_type) && ($wp_query->queried_object->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)) {
			return wpshop_products::product_complete_sheet_output($content, $wp_query->post->ID);
		}
		else {
			return $content;
		}
	}

	/**
	 * Format a string before outputting
	 *
	 * @param string $output_type The output type needed
	 * @param mixed $value The value to format
	 *
	 * @return string The formated value
	 */
	function format_field_output( $output_type, $value ) {
		$formated_value = $value;

		switch ( $output_type ) {
			case 'wpshop_product_price':
				$formated_value = number_format($value, 2, ',', ' ');
			break;
			case 'date':
				$formated_value = mysql2date('d/F/Y', $value, true);;
			break;
		}

		return $formated_value;
	}

}