<?php if ( !defined( 'ABSPATH' ) ) exit;

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
	public static function displayPageHeader($pageTitle, $pageIcon, $iconTitle, $iconAlt, $hasAddButton = true, $addButtonLink = '', $actionInformationMessage = '', $current_page_slug = ''){
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_page_header.tpl.php');
	}

	/**
	*	Returns the end of a classical page
	*
	*	@see displayPageHeader
	*
	*	@return string Html code composing the page footer
	*/
	public static function displayPageFooter($formActionButton){
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_page_footer.tpl.php');
	}

	/**
	*	Return The complete output page code
	*
	*	@return string The complete html page output
	*/
	public static function display_page(){

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
				$objectType = new wps_shortcodes_ctr();
			break;
			case WPSHOP_URL_SLUG_MESSAGES:
				$pageAddButton = false;
				$objectType = new wpshop_messages();
				$current_user_can_edit = true;
				$mid = !empty( $_GET['mid'] ) ? sanitize_text_field( $_GET['mid'] ) : '';
				if(!empty($mid)){
					$action = 'edit';
				}
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
			if ( in_array( $objectType->getEditionSlug(), array(WPSHOP_URL_SLUG_ATTRIBUTE_LISTING, WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING) ) ) {
				$addButtonLink = admin_url('admin.php?page=' . $objectType->getEditionSlug() . '&amp;action=add');
			}
			else {
				$addButtonLink = admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&amp;page=' . $objectType->getEditionSlug() . '&amp;action=add');
			}
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
	public static function custom_page_output_builder($content, $output_type='tab') {
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

	/**
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
	public static function getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary = '', $withFooter = true){
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
	public static function getPageIconInformation($infoType, $object){
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
	public static function get_template_file($file_name, $default_dir = WPSHOP_TEMPLATES_DIR, $dir_name = 'wpshop', $usage_type = 'include', $check_only_custom = false){
		$file_path = '';
		$the_file = $dir_name . '/' . $file_name;

		if (is_file(get_stylesheet_directory() . '/' . $the_file)) {
			$default_dir = str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, get_stylesheet_directory());
			if($usage_type == 'include'){
				$default_dir = get_stylesheet_directory();
			}
			$file_path = $default_dir . '/' . $the_file;
		}
		else if ( !$check_only_custom ) {
			$file_path = $default_dir . $the_file;
		}

		return $file_path;
	}

	/**
	 * Check if the current shop use the first method for templates. One file per element to display
	 *
	 * @param string $template_part The part to take display for, will be usefull to check what file take in care if there were a file in old method
	 * @param string $default_template_dirThe part of website to check template for. Possible values : wpshop / admin
	 *
	 * @return array First index represent if there is a file for old version support, Second index represent the file to get for support old version
	 */
	public static function check_way_for_template($template_part, $default_template_dir = 'wpshop') {
		$old_file_to_take_care = false;
		$old_file_to_take_care_url = null;

		/** Directory containing custom templates	*/
		$custom_template_part = get_stylesheet_directory() . '/' . $default_template_dir . '/';

		/** Let support the old way of template managing	*/
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

	/**
	 * Return a template already fiiled with the good element to be displayed
	 *
	 * @param string $template_part The template element we want to display
	 * @param array $template_part_component The different element to put into template to fill it before display
	 * @param array $extras_args Optionnal Allows to define some parameters to spot a specific template for example
	 * @param string $default_template_dir Optionnal The part of shop where to display the given template element
	 *
	 * @return string The template to display
	 */
	public static function display_template_element($template_part, $template_part_component, $extras_args = array(), $default_template_dir = 'wpshop') {
		/**	Set the template element to return by default before checking if custom exists in order to be sure to return something	*/
		$default_template_element = wpshop_display::check_template_to_display( 'default', $template_part, $extras_args, $default_template_dir );

		/**	Check in custom template if there is not a custom element to display for current 	*/
		$custom_template_element = wpshop_display::check_template_to_display( 'custom', $template_part, $extras_args, $default_template_dir );
		$tpl_element_to_return = !empty($custom_template_element) ? $custom_template_element : $default_template_element;

		$template_part_component = apply_filters( 'wps_filter_display_' . $template_part, $template_part_component, $extras_args, $default_template_dir );

		return self::feed_template($tpl_element_to_return, $template_part_component);
	}

	/**
	 * Load the different template file and store all template elements into an array and in a super global variable
	 */
	function load_template() {
		/*	Load template component	*/
		/*	Get default admin template	*/
		require_once(WPSHOP_TEMPLATES_DIR . 'admin/main_elements.tpl.php');
		$wpshop_template['admin']['default'] = ($tpl_element);unset($tpl_element);
		/*	Get custom admin template	*/
		if ( is_file(get_stylesheet_directory() . '/admin/main_elements.tpl.php') ) {
			require_once(get_stylesheet_directory() . '/admin/main_elements.tpl.php');
			if (!empty($tpl_element))
				$wpshop_template['admin']['custom'] = ($tpl_element);unset($tpl_element);
		}
		if ( is_file(get_stylesheet_directory() . '/admin/wpshop_elements_template.tpl.php') ) {
			require_once(get_stylesheet_directory() . '/admin/wpshop_elements_template.tpl.php');
			if (!empty($tpl_element))
				$wpshop_template['admin']['custom'] = ($tpl_element);unset($tpl_element);
		}
		/*	Get default frontend template	*/
		require_once(WPSHOP_TEMPLATES_DIR . 'wpshop/main_elements.tpl.php');
		$wpshop_template['wpshop']['default'] = ($tpl_element);unset($tpl_element);
		/*	Get custom frontend template	*/
		if ( is_file(get_stylesheet_directory() . '/wpshop/main_elements.tpl.php') ) {
			require_once(get_stylesheet_directory() . '/wpshop/main_elements.tpl.php');
			if (!empty($tpl_element))
				$wpshop_template['wpshop']['custom'] = ($tpl_element);unset($tpl_element);
		}
		if ( is_file(get_stylesheet_directory() . '/wpshop/wpshop_elements_template.tpl.php') ) {
			require_once(get_stylesheet_directory() . '/wpshop/wpshop_elements_template.tpl.php');
			if (!empty($tpl_element))
				$wpshop_template['wpshop']['custom'] = ($tpl_element);unset($tpl_element);
		}
		foreach ( $wpshop_template as $site_side => $types ) {
			foreach ( $types as $type => $tpl_component ) {
				foreach ( $tpl_component as $tpl_key => $tpl_content ) {
					$wpshop_template[$site_side][$type][$tpl_key] = str_replace("
", '', $tpl_content);
				}
			}
		}

		$wpshop_template = apply_filters( 'wpshop_custom_template', $wpshop_template);

		DEFINE( 'WPSHOP_TEMPLATE', serialize($wpshop_template) );
	}

	/**
	 * Read a given array defining template in order to add them to the existing templates
	 *
	 * @param array $tpl_element The template to add to existing
	 * @param array $templates Exsiting templates
	 *
	 * @return array The new array with all elment, internal and module templates
	 */
	public static function add_modules_template_to_internal( $tpl_element, $templates ) {
		if ( !empty($tpl_element) ) {
			foreach ( $tpl_element as $template_part => $template_part_content) {
				if ( !empty($template_part_content) && is_array($template_part_content) ) {
					foreach ( $template_part_content as $template_type => $template_type_content) {
						foreach ( $template_type_content as $template_key => $template) {
							$templates[$template_part][$template_type][$template_key] = $template;
						}
					}
				}
			}
		}

		return $templates;
	}

	/**
	 * Check in the different defined template which ne to take for current template to display
	 *
	 * @param string $part The part of shop where to display the given template element
	 * @param string $template_part The template element we want to display
	 * @param array $extras_args Allows to define some parameters to spot a specific template for example
	 *
	 * @return string The good template to take in care, regarding on the given parameters
	 */
	public static function check_template_to_display( $part, $template_part, $extras_args, $default_template_dir  ) {
		$tpl_element_to_return = '';

		/**	Get the defined template	*/
		$template = defined("WPSHOP_TEMPLATE") ? unserialize(WPSHOP_TEMPLATE) : array();

		if ( !empty($extras_args['type']) && !empty($extras_args['id']) && !empty( $template[$default_template_dir][$part]) && !empty($extras_args['page']) && !empty( $template[$default_template_dir][$part][$extras_args['page']] ) && !empty( $template[$default_template_dir][$part][$extras_args['page']][$extras_args['type']]) && !empty( $template[$default_template_dir][$part][$extras_args['page']][$extras_args['type']][$extras_args['id']] ) && !empty( $template[$default_template_dir][$part][$extras_args['page']][$extras_args['type']][$extras_args['id']][$template_part] ) ) {
			$tpl_element_to_return = $template[$default_template_dir][$part][$extras_args['page']][$extras_args['type']][$extras_args['id']][$template_part];
		}
		elseif ( !empty($extras_args['type']) && !empty($extras_args['id']) && !empty( $template[$default_template_dir][$part][$extras_args['type']]) && !empty( $template[$default_template_dir][$part][$extras_args['type']][$extras_args['id']] ) && !empty( $template[$default_template_dir][$part][$extras_args['type']][$extras_args['id']][$template_part] ) ) {
			$tpl_element_to_return = $template[$default_template_dir][$part][$extras_args['type']][$extras_args['id']][$template_part];
		}
		/**	Check if the file have been duplicated into theme directory for customization	*/
		elseif ( !empty( $template[$default_template_dir][$part] ) && !empty( $template[$default_template_dir][$part][$template_part] ) ) {
			$tpl_element_to_return = $template[$default_template_dir][$part][$template_part];
		}

		return $tpl_element_to_return;
	}

	/**
	 * Fill a template with given element. Replace some code by content before output the html
	 *
	 * @param string $template_to_fill The complete html code we want to display with element to change
	 * @param array $feed The different element to put in place of the code into the tempalte part
	 *
	 * @return string The html code to display
	 */
	public static function feed_template($template_to_fill, $feed) {
		/* Add general element	*/
		$feed['CURRENCY'] = wpshop_tools::wpshop_get_currency();
		$feed['CURRENCY_CHOOSEN'] = wpshop_tools::wpshop_get_currency();
		$feed['CURRENCY_SELECTOR'] = wpshop_attributes_unit::wpshop_shop_currency_list_field();
		$feed['CART_LINK'] = get_permalink( wpshop_tools::get_page_id( get_option('wpshop_cart_page_id') ) );

		$available_key = array();
		foreach ($feed as $element => $value) {
			$available_key[] = '{WPSHOP_'.$element.'}';
			if ( !is_array($value) ) {
				$template_to_fill = str_replace('{WPSHOP_'.$element.'}', $value, $template_to_fill);
			}
		}
		if (WPSHOP_DISPLAY_AVAILABLE_KEYS_FOR_TEMPLATE) $template_to_fill = '<!-- Available keys : ' . implode(' / ', $available_key) . ' -->' . $template_to_fill;

		return $template_to_fill;
	}

	/**
	 * Check if template file exist in current theme directory. If not the case copy all template files into
	 *
	 * @param boolean $force_replacement Define if we overwrite in all case or just if it not exist
	 */
	public static function check_template_file( $force_replacement = false ) {
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

		/*	Add the product template	*/
		if(!is_file(get_stylesheet_directory() . '/single-wpshop_product.php') || ($force_replacement)){
			@copy(WPSHOP_TEMPLATES_DIR . 'single-wpshop_product.php', get_stylesheet_directory() . '/single-wpshop_product.php');
		}
	}


/**
 * Taxonomy display
 */
	/**
	 * Transform product taxonomy descrition field into a wysiwyg editor
	 */
	public static function wpshop_rich_text_tags() {
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
			$action = !empty( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
			$taxonomy = !empty( $_REQUEST['taxonomy'] ) ? sanitize_text_field( $_REQUEST['taxonomy'] ) : '';

			if ($pagenow == 'edit-tags.php' && isset($action) && $action == 'edit' && empty($taxonomy)) {
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
			$term = (array) $_POST[$v];
			wp_update_term($tag_ID,$v,$term);
		}
	}

	/**
	 * Definition for the wyswiwyg editor
	 *
	 * @param object $object The type of element currently edited
	 */
	public static function wpshop_add_form($object = '') {
		global $pagenow;

		$content = is_object($object) && isset($object->description) ? ( html_entity_decode( $object->description, ENT_COMPAT | ENT_HTML401, 'UTF-8' ) ) : '';

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
		$template_part = 'wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate';
		/*if ( !empty($_GET['action']) && ($_GET['action'] == 'edit') ) {
			$template_part = 'wpshop_transform_taxonomy_description_field_into_wysiwyg_for_js_duplicate';
		}*/
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

	public static function wps_hide_admin_bar_for_customers() {
		$wpshop_hide_admin_bar_option = get_option( 'wpshop_display_option' );
		$current_user = get_userdata( get_current_user_id() );
		if ( ! empty( $wpshop_hide_admin_bar_option ) && ! empty( $wpshop_hide_admin_bar_option['wpshop_hide_admin_bar'] ) && ( false !== $current_user && in_array( 'customer', $current_user->roles, true ) ) ) {
			show_admin_bar( false );
		}
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
	public static function products_page( $content = '' ) {
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
	public static function format_field_output( $output_type, $value ) {
		$formated_value = $value;

		if ( !empty($value) ) {
			switch ( $output_type ) {
				case 'wpshop_product_price':
					$formated_value = (is_numeric($value) ) ? number_format($value, 2, ',', '') : $value;
					$formated_value_content = explode(',', $formated_value);
					if ( !empty($formated_value_content) && !empty($formated_value_content[1]) && $formated_value_content[1] <= 0 ) {
						$formated_value = $formated_value_content[0];
					}
				break;
				case 'date':
					$formated_value = mysql2date('d/F/Y', $value, true);
				break;
			}
		}

		return $formated_value;
	}

}
