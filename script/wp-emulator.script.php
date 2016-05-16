<?php
	define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DIRECTORY_SEPARATOR);
	define('WP_PLUGIN_DIR', dirname(dirname(dirname(__FILE__))) );
	define('WP_LANG_DIR', 'FR');
	define('WP_CONTENT_DIR', 'wp-content');

	function __( $str )
	{
		$translate = "Result Testing text";
		echo "[+] Requesting __ -> " . $str . PHP_EOL;
		return $translate;
	}

	function add_action( $init, $object )
	{
		$length = 0;
		$hooked = "";
		$oType = gettype($object);
		if($oType == "object" || $oType == "array")
		{
			$hooked = "[";
			foreach($object as $hook)
			{
				$length++;
				$type = gettype($hook);

				switch($type)
				{
					case "integer":
					case "boolean":
					case "double":
					case "string":
						$hooked .= $hook . ",";
						break;
					default:
						$hooked .= $type . ",";
						break;
				}
			}
			if($length > 0) $hooked = rtrim($hooked, ",");
			$hooked .= "]";
		}
		else $hooked .= $object;
		echo "[+] Adding action -> " . $init . " in " . $hooked . PHP_EOL;
	}

	function register_activation_hook( $file , $object )
	{
		$length = 0;
		$hooked = "";
		$oType = gettype($object);
		if($oType == "object" || $oType == "array")
		{
			$hooked = "[";
			foreach($object as $hook)
			{
				$length++;
				$type = gettype($hook);

				switch($type)
				{
					case "integer":
					case "boolean":
					case "double":
					case "string":
						$hooked .= $hook . ",";
						break;
					default:
						$hooked .= $type . ",";
						break;
				}
			}
			if($length > 0) $hooked = rtrim($hooked, ",");
			$hooked .= "]";
		}
		else $hooked .= $object;
		echo "[+] Registering actiovation hook -> " . $file . " in " . $hooked . PHP_EOL;
	}

	function register_deactivation_hook( $file , $object )
	{
		$length = 0;
		$hooked = "";
		$oType = gettype($object);
		if($oType == "object" || $oType == "array")
		{
			$hooked = "[";
			foreach($object as $hook)
			{
				$length++;
				$type = gettype($hook);

				switch($type)
				{
					case "integer":
					case "boolean":
					case "double":
					case "string":
						$hooked .= $hook . ",";
						break;
					default:
						$hooked .= $type . ",";
						break;
				}
			}
			if($length > 0) $hooked = rtrim($hooked, ",");
			$hooked .= "]";
		}
		else $hooked .= $object;
		echo "[+] Registering actiovation hook -> " . $file . " in " . $hooked . PHP_EOL;
	}

	function add_filter( $name, $fn, $nbr = "")
	{
		echo "[+] Adding filter -> " . $name . " X" . $nbr . PHP_EOL;
	}

	function add_shortcode( $name )
	{
		echo "[+] Adding shortcode -> " . $name . PHP_EOL;
	}

	function add_option( $name, $conf)
	{
		echo "[+] Adding option -> " . $name . " conf:" . $conf . PHP_EOL;
	}

	function update_option( $name, $conf)
	{
		echo "[+] Updating option -> " . $name . " conf:" . $conf . PHP_EOL;
	}

	function sanitize_text_field($str)
	{
		echo "[+] Sanitizing -> " . $str . PHP_EOL;
	}

	function get_posts()
	{
		echo "[+] Requesting posts " . PHP_EOL;
	}

	function get_role()
	{
		echo "[+] Requesting role " . PHP_EOL;
	}

	function load_textdomain()
	{
		echo "[+] Loading text domain" . PHP_EOL;
	}

	function plugin_dir_path($path)
	{
		echo "[+] Plugin path -> " . $path . PHP_EOL;
		return trailingslashit( dirname( $path ) );
	}

	function trailingslashit( $string )
	{
	    return untrailingslashit( $string ) . '/';
	}

	function untrailingslashit( $string )
	{
	        return rtrim( $string, '/\\' );
	}

	function site_url()
	{
		$url = "http://testunitaire.dom/";
		echo "[+] Requesting site_url -> " . $url . PHP_EOL;
		return $url;
	}

	function plugins_url()
	{
		$url = "http://testunitaire.dom/wp-content/plugins/wpshop/";
		echo "[+] Requesting plugins_url -> " . $url . PHP_EOL;
		return $url;
	}

	function is_multisite()
	{
		$result = true;
		echo "[+] Requesting is_multisite -> " . $result . PHP_EOL;
		return $result;
	}

	function is_admin()
	{
		$result = true;
		echo "[+] Requesting is admin -> " . $result . PHP_EOL;
		return $result;
	}

	function admin_url()
	{
		$url = "http://testunitaire.dom/wp-admin/";
		echo "[+] Requesting admin_url -> " . $url . PHP_EOL;
		return $url;
	}

	function plugin_basename()
	{
		$basename = dirname(__FILE__);
		echo "[+] Requesting plugin_basename -> " . $basename . PHP_EOL;
		return $basename;
	}

	function wp_upload_dir()
	{
		$dir = dirname(__FILE__);
		echo "[+] Requesting wp_upload_dir -> " . $dir . PHP_EOL;
		return $dir;
	}

	function add_theme_support( $theme )
	{
		echo "[+] Adding theme support -> " . $theme . PHP_EOL;
	}

	function add_image_size( $image, $x, $y, $bool )
	{
		echo "[+] Adding image size -> " . $image . " x:" . $x . " y:" . $y . " bool:" . $bool . PHP_EOL;
	}

	function get_locale()
	{
		$lang = "fr-FR";
		echo "[+] Requesting get_local -> " . $lang . PHP_EOL;
		return $lang;
	}

	function load_plugin_textdomain( $name, $bool, $dir)
	{
		echo "[+] Loading plugin textdomain -> " . $name . " bool:" . $bool . " dir:" . $dir . PHP_EOL;
	}

	function get_option( $name, $int = 0 )
	{
		echo "[+] Getting option -> " . $name . " int:" . $int . PHP_EOL;
		return "";
	}

	function get_site_option( $name, $int = 0 )
	{
		echo "[+] Getting site option -> " . $name . " int:" . $int . PHP_EOL;
		return "";
	}

	function current_time( $str )
	{
		$time = " 2005-08-05 10:41:13";
		echo "[+] Requesting current_time -> " . $str . PHP_EOL;
		return $time;
	}

	class WPDB
	{
		function prepare( $request = "" )
		{
			echo "[+] Preparing -> " . $request . PHP_EOL;
		}

		public static function get_var( $request = "" )
		{
			echo "[+] Requesting get var -> " . $request . PHP_EOL;
		}

		public static function get_charset_collate( $request = "" )
		{
			echo "[+] Requesting get charset collate -> " . $request . PHP_EOL;
		}

		public $usermeta = "usermeta";
		public $users = "user";
		public $blogs = "blog";
		public $prefix = "prefix";
		public $posts = "post";
		public $post = "postmeta";
		public $terms = "term";
		public $termmeta = "termmeta";
		public $options = "option";
		public $links = "link";
		public $comments = "comment";
		public $commentmeta = "commentmeta";
		public $term_relationships = "term_relationship";
		public $term_taxonomy = "term_taxonomy";

    }

	class WP_Widget
	{
		function __construct()
		{

		}
	}

	class Walker
	{
		function __construct()
		{

		}
	}

	global $wpdb;
	$wpdb = new WPDB();
