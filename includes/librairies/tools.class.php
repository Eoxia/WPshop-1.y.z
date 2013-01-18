<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Define the different tools for the entire plugin
*
*	Define the different tools for the entire plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different tools for the entire plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_tools
{
	/**
	*	Define the tools main page
	*/
	function main_page(){
		echo wpshop_display::displayPageHeader(__('Outils du logiciel WP-Shop', 'wpshop'), '', __('Outils du logiciel', 'wpshop'), __('Outils du logiciel', 'wpshop'), false, '', '');
?>
<div id="wpshop_configurations_container" class="clear" >
	<div id="tools_tabs" class="wpshop_tabs wpshop_full_page_tabs wpshop_tools_tabs" >
		<ul>
			<li><a href="<?php echo WPSHOP_AJAX_FILE_URL; ?>?post=true&amp;elementCode=tools&amp;action=db_manager" title="wpshop_tools_tab_container" ><?php _e('V&eacute;rification de la base de donn&eacute;es', 'wpshop'); ?></a></li>
		</ul>
		<div id="wpshop_tools_tab_container" >&nbsp;</div>
	</div>
</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_tools_tab_container").html(jQuery("#round_loading_img").html());
		jQuery("#tools_tabs").tabs({
			select: function(event, ui){
				jQuery("#wpshop_tools_tab_container").html(jQuery("#round_loading_img").html());
				var url = jQuery.data(ui.tab, "load.tabs");
				jQuery("#wpshop_tools_tab_container").load(url);
				jQuery("#tools_tabs ul li").each(function(){
					jQuery(this).removeClass("ui-tabs-selected ui-state-active");
				});
				jQuery("#tools_tabs ul li:eq(" + ui.index + ")").addClass("ui-tabs-selected ui-state-active");

				return false;
			}
		});
	});
</script>
<?php
		echo wpshop_display::displayPageFooter();
	}

	/**
	*	Return a variable with some basic treatment
	*
	*	@param mixed $varToSanitize The variable we want to treat for future use
	*	@param mixed $varDefaultValue The default value to set to the variable if the different test are not successfull
	*	@param string $varType optionnal The type of the var for better verification
	*
	*	@return mixed $sanitizedVar The var after treatment
	*/
	function varSanitizer($varToSanitize, $varDefaultValue = '', $varType = '')
	{
		$sanitizedVar = (trim(strip_tags(stripslashes($varToSanitize))) != '') ? trim(strip_tags(stripslashes(($varToSanitize)))) : $varDefaultValue ;

		return $sanitizedVar;
	}

	function forceDownload($Fichier_a_telecharger) {

		$nom_fichier = basename($Fichier_a_telecharger);
		switch(strrchr($nom_fichier, ".")) {
			case ".gz": $type = "application/x-gzip"; break;
			case ".tgz": $type = "application/x-gzip"; break;
			case ".zip": $type = "application/zip"; break;
			case ".pdf": $type = "application/pdf"; break;
			case ".png": $type = "image/png"; break;
			case ".gif": $type = "image/gif"; break;
			case ".jpg": $type = "image/jpeg"; break;
			case ".txt": $type = "text/plain"; break;
			case ".htm": $type = "text/html"; break;
			case ".html": $type = "text/html"; break;
			default: $type = "application/octet-stream"; break;
		}

		header("Content-disposition: attachment; filename=$nom_fichier");
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: $type\n"); // Surtout ne pas enlever le \n
		header("Content-Length: ".filesize($Fichier_a_telecharger));
		header("Pragma: no-cache");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
		header("Expires: 0");
		readfile($Fichier_a_telecharger);
		exit;
	}

	function is_sendsms_actived() {
		if(is_plugin_active('wordpress-send-sms/Send-SMS.php')) {
			$configOption = get_option('sendsms_config', '');
			$ligne = unserialize($configOption);
			$nicOVH = $ligne['nicOVH'];
			$passOVH = $ligne['passOVH'];
			$compteSMS = $ligne['compteSMS'];
			$tel_admin = $ligne['tel_admin'];
			return !empty($nicOVH) && !empty($passOVH) && !empty($compteSMS) && !empty($tel_admin);
		}
		return false;
	}

	function search_all_possibilities($input) {
		$result = array();

		while (list($key, $values) = each($input)) {
			if (empty($values)) {
				continue;
			}

			// Special case: seeding the product array with the values from the first sub-array
			if (empty($result)) {
				foreach($values as $value) {
					$result[] = array($key => $value);
				}
			}
			else {
				// Second and subsequent input sub-arrays work like this:
				//   1. In each existing array inside $product, add an item with
				//      key == $key and value == first item in input sub-array
				//   2. Then, for each remaining item in current input sub-array,
				//      add a copy of each existing array inside $product with
				//      key == $key and value == first item in current input sub-array

				// Store all items to be added to $product here; adding them on the spot
				// inside the foreach will result in an infinite loop
				$append = array();
				foreach($result as &$product) {
					// Do step 1 above. array_shift is not the most efficient, but it
					// allows us to iterate over the rest of the items with a simple
					// foreach, making the code short and familiar.
					$product[$key] = array_shift($values);

					// $product is by reference (that's why the key we added above
					// will appear in the end result), so make a copy of it here
					$copy = $product;

					// Do step 2 above.
					foreach($values as $item) {
						$copy[$key] = $item;
						$append[] = $copy;
					}

					// Undo the side effecst of array_shift
					array_unshift($values, $product[$key]);
				}

				// Out of the foreach, we can add to $results now
				$result = array_merge($result, $append);
			}
		}

		return $result;
	}

	/** Return the shop currency */
	function wpshop_get_currency($code=false) {
		// Currency
		global $wpdb;
		$current_currency = get_option('wpshop_shop_default_currency');
		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id =%d ', $current_currency );
		$currency_infos = $wpdb->get_row( $query );
		if ( !empty($currency_infos) ) {
			$code = ($code) ?  $currency_infos->name : $currency_infos->unit;
			return $code;
		}
		else {
			return '';
		}
	}

	/** Return the shop currency */
	function wpshop_get_sigle($code, $column_to_return = "unit") {
		$tmp_code = (int)$code;
		$key_to_get = 'name';
		if ( is_int($tmp_code) && !empty($tmp_code) ) {
			$key_to_get = 'id';
		}

		$current_currency = wpshop_attributes_unit::getElement($code, "'valid'", $key_to_get);

		return $current_currency->$column_to_return;
	}

	/**
	* Clean variables
	**/
	function wpshop_clean( $var ) {
		return trim(strip_tags(stripslashes($var)));
	}

	/**
	 * Validates a phone number using a regular expression
	 *
	 * @param   string	phone number
	 * @return  boolean
	 */
	function is_phone( $phone ) {
		if (strlen(trim(preg_replace('/[\s\#0-9_\-\+\(\)]/', '', $phone)))>0) return false;
		else return true;
	}

	/**
	 * Checks for a valid postcode
	 *
	 * @param   string	postcode
	 * @return  boolean
	 */
	function is_postcode($postcode) {
		if (strlen(trim(preg_replace('/[\s\-A-Za-z0-9]/', '', $postcode)))>0) return false;
		else return true;
	}

	/**
	*	Return a form field type from a database field type
	*
	*	@param string $dataFieldType The database field type we want to get the form field type for
	*
	*	@return string $type The form input type to use for the given field
	*/
	function defineFieldType($dataFieldType, $input_type){
		$type = 'text';

		if ( $dataFieldType == 'datetime' ) {
			$type = 'text';
		}
		else {
			$type = $input_type;
		}
// 		if( ($dataFieldType == 'char') || ($dataFieldType == 'varchar') || ($dataFieldType == 'int') ){
// 			$type = 'text';
// 			if($input_type == 'password'){
// 				$type = 'password';
// 			}
// 			elseif($input_type == 'hidden') {
// 				$type = 'hidden';
// 			}
// 			elseif( $input_type == 'country' ){
// 				$type = 'country';
// 			}
// 		}
// 		elseif($dataFieldType == 'text'){
// 			$type = 'textarea';
// 		}
// 		elseif($dataFieldType == 'enum'){
// 				$type = 'select';
// 		}

		return $type;
	}

	/** Create un cutom message with $data array */
	function customMessage($string, $data) {
		$avant = array();
		$apres = array();
		foreach($data as $key => $value) {
			$avant[] = '['.$key.']';
			$apres[] = $value;
		}
		$string = str_replace($avant, $apres, $string);
		$string = preg_replace("/\[(.*)\]/Usi", '', $string);
		return $string;
	}

	/** Envoie un email personnalis� */
	function wpshop_prepared_email($email, $model_name, $data=array(), $object=array()) {

	/*
		$title = get_option($code_message.'_OBJECT', null);
		$title = empty($title) ? constant($code_message.'_OBJECT') : $title;
		$title = self::customMessage($title, $data);
		$message = get_option($code_message, null);
		$message = empty($message) ? constant($code_message) : $message;
		$message = self::customMessage($message, $data);
	*/

		$model_id = get_option($model_name, 0);
		$post = get_post($model_id);
		if (!empty($post)) {
			$title = self::customMessage($post->post_title, $data);
			$message = self::customMessage($post->post_content, $data);
			/* On envoie le mail */
			self::wpshop_email($email, $title, $message, $save=true, $model_id, $object);
		}
	}

	/** Envoie un mail */
	function wpshop_email($email, $title, $message, $save=true, $model_id, $object=array()) {
		global $wpdb;

		// Sauvegarde
		if($save) {
			$user = $wpdb->get_row('SELECT ID FROM '.$wpdb->users.' WHERE user_email="'.$email.'";');
			$user_id = $user ? $user->ID : 0;
			wpshop_messages::add_message($user_id, $email, $title, nl2br($message), $model_id, $object);
		}

		$emails = get_option('wpshop_emails', array());
		$noreply_email = $emails['noreply_email'];
		// Split the email to get the name
		$vers_nom = substr($email, 0, strpos($email,'@'));

		// Headers du mail
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";
		$headers .= "To: $vers_nom <$email>\r\n";
		$headers .= 'From: '.get_bloginfo('name').' <'.$noreply_email.'>' . "\r\n";
		// Mail en HTML
// 		return @mail($email, $title, nl2br($message), $headers);
		return @wp_mail($email, $title, nl2br($message), $headers);
	}

	/**
	*	Transform a given text with a specific pattern, send by the second parameter
	*
	*	@param string $toSlugify The string we want to "clean" for future use
	*	@param array|string $slugifyType The type of cleaning we are going to do on the input text
	*
	*	@return string $slugified The input string that was slugified with the selected method
	*/
	function slugify($toSlugify, $slugifyType)
	{
		$slugified = '';

		if($toSlugify != '')
		{
			$slugified = $toSlugify;
			foreach($slugifyType as $type)
			{
				if($type == 'noAccent')
				{
					$pattern = array("/&eacute;/", "/&egrave;/", "/&ecirc;/", "/&ccedil;/", "/&agrave;/", "/&acirc;/", "/&icirc;/", "/&iuml;/", "/&ucirc;/", "/&ocirc;/", "/&Egrave;/", "/&Eacute;/", "/&Ecirc;/", "/&Euml;/", "/&Igrave;/", "/&Iacute;/", "/&Icirc;/", "/&Iuml;/", "/&Ouml;/", "/&Ugrave;/", "/&Ucirc;/", "/&Uuml;/","/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/");
					$rep_pat = array("e", "e", "e", "c", "a", "a", "i", "i", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U","e", "e", "e", "c", "a", "a", "i", "i", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U");
				}
				elseif($type == 'noSpaces')
				{
					$pattern = array('/\s/');
					$rep_pat = array('_');
					$slugified = trim($slugified);
				}
				elseif($type == 'lowerCase')
				{
					$slugified = strtolower($slugified);
				}
				elseif($type == 'noPunctuation')
				{
					$pattern = array("/#/", "/\{/", "/\[/", "/\(/", "/\)/", "/\]/", "/\}/", "/&/", "/~/", "/�/", "/`/", "/\^/", "/@/", "/=/", "/�/", "/�/", "/%/", "/�/", "/!/", "/�/", "/:/", "/\$/", "/;/", "/\./", "/,/", "/\?/", "/\\\/", "/\//");
					$rep_pat = array("_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_");
				}

				if(is_array($pattern) && is_array($rep_pat))
				{
					$slugified = preg_replace($pattern, $rep_pat, utf8_decode($slugified));
				}
			}
	  }

	  return $slugified;
	}

	/**
	*	Trunk a string too long
	*
	*	@param string $string The string we want to "trunk"
	*	@param int $maxlength The max length of the result string
	*
	*	@return string $string The output string that was trunk if necessary
	*/
	function trunk($string, $maxlength) {
		if(strlen($string)>$maxlength+3)
			return substr($string,0,$maxlength).'...';
		else return $string;
	}

	/**
	 * Run a safe redirect in javascript
	 */
	function wpshop_safe_redirect($url='') {
		$url = empty($url) ? admin_url('admin.php?page='.WPSHOP_URL_SLUG_DASHBOARD) : $url;
		echo '<script type="text/javascript">window.top.location.href = "'.$url.'"</script>';
		exit;
	}

	/**
	 * Format a number before displaying it
	 * @deprecated
	 *
	 */
	function price( $price ) {
		return $price;
	}

}

/* Others tools functions */
function number_format_hack($n) {
	return number_format($n, 5, '.', '');
}