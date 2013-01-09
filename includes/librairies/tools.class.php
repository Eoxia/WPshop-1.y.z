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

	/** Custom search shortcode */
	function wpshop_custom_search_shortcode() {
		global $post;

		$products_list = $others = '';

		while ( have_posts() ) : the_post();
			if($post->post_type=="wpshop_product") {
				ob_start();
				echo wpshop_products::product_mini_output($post->ID, 0, 'list');
				$products_list .= ob_get_contents();
				ob_end_clean();
			}
			else {
				ob_start();
				get_template_part( 'content', get_post_format() );
				$others .= ob_get_contents();
				ob_end_clean();
			}
		endwhile;

		if(!empty($products_list)) {
			echo '<ul class="products_listing list_3 list_mode clearfix">'.$products_list.'</ul>';
		}
		echo $others;
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

	/** Advanced search shortcode */
	function wpshop_advanced_search_shortcode() {
		global $wpdb;

		if(!empty($_POST['search'])) {

			if(!empty($_POST['advanced_search_attribute'])) {

				$att_type = array(
					'datetime'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME,
					'decimal'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL,
					'integer'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER,
					'text'		=>	WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT,
					'varchar'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR
				);

				$table_to_use = $data_to_use = array();
				// Foreach the post data
				foreach($_POST['advanced_search_attribute'] as $type => $array) {
					foreach($array as $att_code => $att_value) {
						if(!empty($att_value)) {

							// If data type is decimal, we trait the number format
							if($type=='decimal') {
								$att_value = str_replace(',', '.', $att_value);
								$number_figures=5;
								$att_value = number_format((float)$att_value, $number_figures, '.', '');
							}

							$data_to_use[$type][$att_code] = $att_value;

							if(!in_array($type, $table_to_use)) {
								$table_to_use[] = $type;
							}
						}
					}
				}
				$left_join=$where='';
				foreach($table_to_use as $t) {

					$left_join .= ' LEFT JOIN '.$att_type[$t].' AS att_'.$t.' ON att_'.$t.'.entity_id=post.ID';

					foreach($data_to_use[$t] as $code => $value) {
						$attr = wpshop_attributes::getElement($code,"'valid'",'code');
						$where .= 'att_'.$t.'.attribute_id="'.$attr->id.'" AND att_'.$t.'.value="'.$value.'" AND ';
					}
				}
				if(!empty($where))$where='WHERE '.substr($where,0,-4);

				$results='';

				if( (!empty($table_to_use) && !empty($data_to_use) && !empty($where) && !empty($left_join)) OR !empty($_POST['product_name']))
				{
					if(!empty($_POST['product_name'])) {
						if(!empty($where))$where.='AND post.post_title LIKE "%'.$wpdb->escape($_POST['product_name']).'%"';
						else $where.='WHERE post.post_title LIKE "%'.$wpdb->escape($_POST['product_name']).'%"';
					}

					$query = 'SELECT post.ID FROM '.$wpdb->posts.' AS post '.$left_join.' '.$where.' GROUP BY post.ID';
					$data = $wpdb->get_results($query);

					if(!empty($data)) {
						foreach($data as $d) {
							$results .= wpshop_products::product_mini_output($d->ID, 0, 'list');
						}
					}
				}
			}
		}

		$inputs = wpshop_attributes::getAttributeForAdvancedSearch();

		echo '
			<form method="post">
				'.__('Product name','wpshop').' : <input type="text" name="product_name" /><br />
				'.$inputs.'
				<input type="submit" name="search" value="'.__('Search','wpshop').'" />
			</form>
		';

		if(!empty($_POST['search'])) {
			if(!empty($results)) {
				echo '<ul class="products_listing list_3 list_mode clearfix">'.$results.'</ul>';
			} else echo '<p>'.__('Empty list','wpshop').'</p>';
		}
	}

	/** Return the shop currency */
	function wpshop_get_currency($code=false) {
		// Currency
		$wpshop_shop_currency = get_option('wpshop_shop_default_currency', WPSHOP_SHOP_DEFAULT_CURRENCY);
		$wpshop_shop_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		return $code ? $wpshop_shop_currency : $wpshop_shop_currencies[$wpshop_shop_currency];
	}

	/** Return the shop currency */
	function wpshop_get_sigle($code) {
		// Currencies
		$wpshop_shop_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		return $wpshop_shop_currencies[$code];
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
		if(($dataFieldType == 'char') || ($dataFieldType == 'varchar') || ($dataFieldType == 'int')){
			$type = 'text';
			if($input_type == 'password'){
				$type = 'password';
			}
		}
		elseif($dataFieldType == 'text'){
			$type = 'textarea';
		}
		elseif($dataFieldType == 'enum'){
			$type = 'select';
		}

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

	/** Run a safe redirect in javascript */
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
	function price($price) {
		return $price;
	}

}

/* Others tools functions */
function number_format_hack($n) {
	return number_format($n, 5, '.', '');
}