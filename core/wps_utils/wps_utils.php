<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main controller file for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wpshop_tools {

	public static $currency_cache = null;

	/**
	 * INTERNAL LIB - Check and get the template file path to use for a given display part
	 *
	 * @uses locate_template()
	 * @uses get_template_part()
	 *
	 * @param string $plugin_dir_name The main directory name containing the plugin
	 * @param string $main_template_dir THe main directory containing the templates used for display
	 * @param string $side The website part were the template will be displayed. Backend or frontend
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 *
	 * @return string The template file path to use
	 */
	public static function get_template_part( $plugin_dir_name, $main_template_dir, $side, $slug, $name=null, $debug = null ) {
		$path = '';

		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		$templates[] = "{$side}/{$slug}.php";

		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $plugin_dir_name . "/" . $template;
			$path = locate_template( $check_theme_template, false );
			if( !empty($path) ) {
				break;
			}
		}

		/**	Allow debugging	*/
		if ( !empty( $debug ) ) {
			echo '--- Debug mode ON - Start ---<br/>';
			echo __FILE__ . '<br/>';
			echo 'Debug for display method<br/>';
			echo 'Asked path ' . $path . '<br/>';
		}

		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;

				if ( !empty( $debug ) ) {
					echo __LINE__ . ' - ' . $main_template_dir . $template_name . '<hr/>';
				}

				$file_exists = file_exists( $main_template_dir . $template_name );
				if ( $file_exists ) {
					$path = $main_template_dir . $template_name;
					break;
				}

				if ( !empty( $debug ) ) {
					echo __LINE__ . ' - ' . (bool)$file_exists . '<hr/>';
				}
			}
		}

		/**	Allow debugging	*/
		if ( !empty( $debug ) ) {
			echo '--- Debug mode ON - END ---<br/><br/>';
		}

		return $path;
	}

	/**
	 *	Define the tools main page
	 */
	public static function main_page() {
		echo wpshop_display::display_template_element('wpshop_admin_tools_main_page', array(), array(), 'admin');
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
	public static function varSanitizer($varToSanitize, $varDefaultValue = '', $varType = '') {
		$sanitizedVar = is_string( $varToSanitize ) && (trim(strip_tags(stripslashes($varToSanitize))) != '') ? trim(strip_tags(stripslashes(($varToSanitize)))) : $varDefaultValue ;

		return $sanitizedVar;
	}

	/**
	 * Permit to force download a file
	 * @param string $Fichier_a_telecharger
	 * @param boolean $delete_after_download
	 */
	public static function forceDownload($Fichier_a_telecharger, $delete_after_download = false) {

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
		ob_end_clean();
		readfile($Fichier_a_telecharger);
		if ( $delete_after_download ) {
			unlink( $Fichier_a_telecharger );
		}
		exit;
	}

	/**
	 * Check if Send SMS is actived
	 * @return boolean
	 */
	public static function is_sendsms_actived() {
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

	/**
	 * Search all variations possibilities
	 * @param unknown_type $input
	 * @return Ambigous <multitype:, multitype:multitype:unknown  >
	 */
	public static function search_all_possibilities( $input ) {
		$result = array();

		while (list($key, $values) = each($input)) {
			if (empty($values)) {
				continue;
			}

			if (empty($result)) {
				foreach($values as $value) {
					$result[] = array($key => $value);
				}
			}
			else {
				$append = array();
				foreach($result as &$product) {
					$product[$key] = array_shift($values);
					$copy = $product;

					foreach($values as $item) {
						$copy[$key] = $item;
						$append[] = $copy;
					}

					array_unshift($values, $product[$key]);
				}

				$result = array_merge($result, $append);
			}
		}

		return $result;
	}

	/**
	 * Return Default currency
	 * @param boolean $code : false return sigle, true return code (€ or EUR)
	 * @return string currency code or sigle
	 */
	public static function wpshop_get_currency($code=false) {
		// Currency
		if( is_null( self::$currency_cache ) ) {
			global $wpdb;
			$current_currency = get_option('wpshop_shop_default_currency');
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id =%d ', $current_currency );
			self::$currency_cache = $wpdb->get_row( $query );
		}
		if ( !empty(self::$currency_cache) ) {
			$code = ($code) ?  self::$currency_cache->name : self::$currency_cache->unit;
			return $code;
		}
		else {
			return '';
		}
	}

	/**
	 * Return unit sigle
	 * @param unknown_type $code
	 * @param unknown_type $column_to_return
	 */
	public static function wpshop_get_sigle($code, $column_to_return = "unit") {
		$tmp_code = (int)$code;
		$key_to_get = 'name';
		if ( is_int($tmp_code) && !empty($tmp_code) ) {
			$key_to_get = 'id';
		}
		$old_way_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		if ( array_key_exists( $code, $old_way_currencies)) {
			$code = $old_way_currencies[$code];
			$key_to_get = 'name';
		}

		$current_currency = wpshop_attributes_unit::getElement($code, "'valid'", $key_to_get);

		return $current_currency->$column_to_return;
	}

	/**
	 * Clean variable
	 * @param string $var : variable to clean
	 * @return string
	 */
	public static function wpshop_clean( $var ) {
		return trim(strip_tags(stripslashes($var)));
	}

	/**
	 * Check if string have phone number structure
	 * @param   string	phone number
	 * @return  boolean
	 */
	public static function is_phone( $phone ) {
		return preg_match( '/(?=.*[0-9])([ 0-9\-\+\(\)]+)/', $phone );
	}

	/**
	 * Check if string have postcode valid structure
	 * @param   string	postcode
	 * @return  boolean
	 */
	public static function is_postcode( $postcode ) {
		return preg_match( '/(?=.*[0-9A-Za-z])([ \-A-Za-z0-9]+)/', $postcode );
	}

	/**
	 *	Return a form field type from a database field type
	 *
	 *	@param string $dataFieldType The database field type we want to get the form field type for
	 *
	 *	@return string $type The form input type to use for the given field
	 */
	public static function defineFieldType($dataFieldType, $input_type, $frontend_verification){
		$type = 'text';

		if ( $dataFieldType == 'datetime' ) {
			$type = 'text';
		}
		else {
			switch ( $frontend_verification ) {
				case 'phone':
					$type = 'tel';
					break;
				case 'email':
					$type = 'email';
					break;
				default:
					$type = $input_type;
					break;
			}
		}
		return $type;
	}

	/**
	 * Get the method through which the data are transferred (POST OR GET)
	 *
	 * @return array The different element send by request method
	 */
	public static function getMethode(){
		$request_method = null;
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$request_method = (array)$_GET;
		}
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$request_method = (array)$_POST;
		}

		if ( null === $request_method ) {
			die ('Invalid REQUEST_METHOD (not GET, not POST).');
		}
		else {
			return $request_method;
		}
	}

	/**
	 *	Transform a given text with a specific pattern, send by the second parameter
	 *
	 *	@param string $toSlugify The string we want to "clean" for future use
	 *	@param array|string $slugifyType The type of cleaning we are going to do on the input text
	 *
	 *	@return string $slugified The input string that was slugified with the selected method
	 */
	public static function slugify($toSlugify, $slugifyType){
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
	public static function trunk($string, $maxlength) {
		if(strlen($string)>$maxlength+3)
			return substr($string,0,$maxlength).'...';
		else return $string;
	}

	/**
	 * Run a safe redirect in javascript
	 */
	public static function wpshop_safe_redirect($url='') {
		$url = empty($url) ? admin_url('admin.php?page='.WPSHOP_URL_SLUG_DASHBOARD) : $url;
		echo '<script type="text/javascript">window.top.location.href = "'.$url.'"</script>';
		exit;
	}

	/**
	 * Create a custom hook action
	 * @param string $hook_name
	 * @param array $args : Hook arguments
	 * @return string
	 */
	public static function create_custom_hook ($hook_name, $args = '') {
		ob_start();
		if ( !empty($args) ) {
			do_action($hook_name, $args);
		}
		else {
			do_action($hook_name);
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Return a plug-in activation code
	 * @param string $plugin_name
	 * @param string $encrypt_base_attribute
	 * @return string
	 */
	public static function get_plugin_validation_code($plugin_name, $encrypt_base_attribute) {
		$code = '';
		if ( !function_exists( 'get_plugin_data') )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plug = get_plugin_data( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/wpshop.php' );
		$code_part = array();
		$code_part[] = substr(hash ( "sha256" , $plugin_name ), WPSHOP_ADDONS_KEY_IS, 5);
		$code_part[] = substr(hash ( "sha256" , $plug['Name'] ), WPSHOP_ADDONS_KEY_IS, 5);
		$code_part[] = substr(hash ( "sha256" , 'addons' ), WPSHOP_ADDONS_KEY_IS, 5);
		$code = $code_part[1] . '-' . $code_part[2] . '-' . $code_part[0];
		$att = $encrypt_base_attribute;
		$code .= '-' . substr(hash ( "sha256" , $att ),  WPSHOP_ADDONS_KEY_IS, 5);

		return $code;
	}

	/**
	 * Check the WPShop Add-ons encrypt code validity
	 * @param string $plugin_name
	 * @param string $encrypt_base_attribute
	 * @return boolean
	 */
	public static function check_plugin_activation_code( $plugin_name, $encrypt_base_attribute, $from = 'file') {
		$is_valid = false;
		$valid_activation_code = self::get_plugin_validation_code($plugin_name, $encrypt_base_attribute);
		$activation_code_filename = WP_PLUGIN_DIR .'/'. $plugin_name.'/encoder.txt';
		if ( is_file($activation_code_filename) ) {
			$activation_code_file = fopen($activation_code_filename, 'r' );
			if ( $activation_code_file !== false) {
				$activation_code = fread( $activation_code_file, filesize($activation_code_filename));
				if ( $activation_code == $valid_activation_code ) {
					$is_valid = true;
				}
			}
		}
		return $is_valid;
	}

	/**
	 * Formate number, Add span on cents on hide cents if equals zero
	 * @param unknown_type $number
	 * @return string
	 */
	public static function formate_number( $number ) {
		$number = number_format( $number, 2, '.', '' );
		$exploded_number = explode( '.', $number );
		$number = $exploded_number[0];
		if( $exploded_number[1] != '00' ) {
			$number .= '<span class="wps_number_cents">,' . $exploded_number[1]. '</span>';
		}
		return $number;
	}

	/**
	 * Return the translated element id of a page
	 * @param int $page_id
	 * @return int
	 */
	public static function get_page_id( $page_id ) {
		if( !empty($page_id) ) {
			if ( function_exists( 'icl_object_id' ) && defined('ICL_LANGUAGE_CODE') ) {
				$element_post_type = get_post_type( $page_id );
				$translated_element_id = icl_object_id( $page_id, $element_post_type, true, ICL_LANGUAGE_CODE );
				if( !empty($translated_element_id) ) {
					$page_id = $translated_element_id;
				}
			}
		}
		return $page_id;
	}

	public static function minutes_to_time( $minutes, $format = '%hh %imin' ) {
		$dtF = new \DateTime( '@0' );
		$dtT = new \DateTime( '@' . ( $minutes * 60 ) );
		return $dtF->diff($dtT)->format( $format );
	}

	public static function number_format_hack($n) {
		return number_format($n, 5, '.', '');
	}
	public static function is_serialized( $data, $strict = true ) {
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = substr( $data, -1 );
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace = strpos( $data, '}' );
			if ( false === $semicolon && false === $brace ) {
				return false;
			}
			if ( false !== $semicolon && $semicolon < 3 ) {
				return false;
			}
			if ( false !== $brace && $brace < 4 ) {
				return false;
			}
		}
		$token = $data[0];
		switch ( $token ) {
			case 's' :
				if ( $strict ) {
					if ( '"' !== substr( $data, -2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}
		return false;
	}
}
