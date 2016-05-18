<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Main controller file for bubble module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for bubble module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wpsBubble_ctr {
	private $array_bubble = array();
	private $post_type = "wps_bubble";
	private $post_metakey = "_wpeo_bubble_position";
	public static $name_i18n = "wps_guided_tour";

	/**
	 * Add action init, admin_init, save_post, admin_enqueue_scripts and AJAX
	 */
	function __construct() {
		add_action('init', array(&$this, 'register_post_type'));
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('save_post', array(&$this, 'save_custom'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'), 100);

		/** Ajax */
		add_action('wp_ajax_reset-bubble-all-user', array(&$this, 'reset_bubble_all_user'));
		add_action('wp_ajax_dismiss-my-pointer', array(&$this, 'dismiss_my_pointer'));
	}

	/**
	* Register post type (Bubbles)
	*/
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Bubbles', 'post type general name', self::$name_i18n ),
			'singular_name'      => _x( 'Bubble', 'post type singular name', self::$name_i18n ),
			'menu_name'          => _x( 'Bubbles', 'admin menu', self::$name_i18n ),
			'name_admin_bar'     => _x( 'Bubble', 'add new on admin bar', self::$name_i18n ),
			'add_new'            => _x( 'Add New', 'bubble', self::$name_i18n ),
			'add_new_item'       => __( 'Add New Bubble', self::$name_i18n ),
			'new_item'           => __( 'New Bubble', self::$name_i18n ),
			'edit_item'          => __( 'Edit Bubble', self::$name_i18n ),
			'view_item'          => __( 'View Bubble', self::$name_i18n ),
			'all_items'          => __( 'All Bubbles', self::$name_i18n ),
			'search_items'       => __( 'Search Bubbles', self::$name_i18n ),
			'parent_item_colon'  => __( 'Parent Bubbles:', self::$name_i18n ),
			'not_found'          => __( 'No bubble found.', self::$name_i18n ),
			'not_found_in_trash' => __( 'No bubble found in Trash.', self::$name_i18n )
		);

		$args = array(
			'labels'             	=> $labels,
			'public'             	=> false,
			'publicly_queryable' 	=> true,
			'show_ui'            	=> false,
			'show_in_menu'       	=> false,
			'query_var'         	=> true,
			'rewrite'            	=> array( 'slug' => $this->post_type ),
			'capability_type'    	=> 'post',
			'exclude_from_search'	=> true,
			'has_archive'        	=> true,
			'hierarchical'       	=> false,
			'menu_position'      	=> null,
			'menu_icon'				=> 'dashicons-format-status',
			'supports'          	=> array( 'title', 'editor' )

		);

		register_post_type( $this->post_type, $args );
	}

	/**
	* For add_meta_box (position, actions, conditions, url, advanced)
	*/
	public function admin_init() {
		add_meta_box("wpeo-bubble-metabox-position", __("Position", self::$name_i18n), array(&$this, "metabox_position"), $this->post_type, "normal", "low");
		add_meta_box("wpeo-bubble-metabox-actions", __("Actions", self::$name_i18n), array(&$this, "metabox_actions"), $this->post_type, "normal", "low");
		add_meta_box("wpeo-bubble-metabox-conditions", __("Conditions", self::$name_i18n), array(&$this, "metabox_conditions"), $this->post_type, "advanced", "low");
		add_meta_box("wpeo-bubble-metabox-url", __("Url", self::$name_i18n), array(&$this, "metabox_url"), $this->post_type, "advanced", "low");
		add_meta_box("wpeo-bubble-metabox-advanced", __("Advanced", self::$name_i18n), array(&$this, "metabox_advanced"), $this->post_type, "side", "low");
	}

	/**
	* Update post meta of post ID, rework the meta URL and save the meta
	*
	* @param int $post_id The id of the post being saved
	*/
	public function save_custom($post_id) {
		/** Eviter l'auto save pour ne pas vider les champs personnalisés */
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		$meta = !empty( $_POST['meta'] ) ? (array) $_POST['meta'] : false;
		if(!$meta)
			return;

		// Rework the URL meta
		$tmp_array_urls = array();
		if(!empty($meta['urls'])) {
			for($i = 0; $i < count($meta['urls']['paramater']); $i++) {
				$tmp_array_urls[$i]['paramater'] = 	sanitize_text_field( $meta['urls']['paramater'][$i] );
				$tmp_array_urls[$i]['value'] = 			(!empty($meta['urls']['value']) && !empty($meta['urls']['value'][$i])) ? sanitize_text_field( $meta['urls']['value'][$i] ) : "";
			}
			$meta['urls'] = array();
			$meta['urls'] = $tmp_array_urls;
		}

		// Update post meta
		update_post_meta($post_id, $this->post_metakey, $meta);
	}

	/**
	* Get all pages of the backend, get current meta for this post, and call the template for display the metabox position.
	*
	* @param WP_Post $post The object for the current post/page.
	*/
	public function metabox_position($post) {
		global $menu, $_parent_pages;
		$tmp_menu = array();
		$tmp_menu["all"] = __("All pages", self::$name_i18n);

		foreach($menu as $array_menu) {
			if(!empty($array_menu[0]))
				$tmp_menu[$array_menu[2]] = $array_menu[0];
		}

		$array_pages = array_merge($tmp_menu, $_parent_pages);

		foreach($array_pages as $key => $pages) {
			if(empty($pages))
				$array_pages[$key] = $key;
		}

		$meta = get_post_meta($post->ID, $this->post_metakey, true);
		require_once( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox', 'position'));
	}

	/**
	* Get current meta for this post, declare the array type next, get all bubble posts and display the template metabox actions.
	*
	* @param WP_Post $post The object for the current post/page.
	*/
	public function metabox_actions($post) {
		$meta = get_post_meta($post->ID, $this->post_metakey, true);
		$array_type_next = array("link", "bubble");
		$array_bubbles = get_posts(array(
			'posts_per_page' 	=> -1,
			'orderby' 				=> 'title',
			'post_type'				=> $this->post_type,
		));
		require_once( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox', 'actions'));
	}

	/**
	* Get current meta for this $post and display the template metabox conditions
	*
	* @param WP_Post $post The object for the current post/page.
	*/
	public function metabox_conditions($post) {
		$meta = get_post_meta($post->ID, $this->post_metakey, true);
		require_once( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox', 'conditions'));
	}

	/**
	* Get current meta for this $post and display template metabox url.
	*
	* @param WP_Post $post The object for the current post/page.
	*/
	public function metabox_url($post) {
		$meta = get_post_meta($post->ID, $this->post_metakey, true);
		require_once( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox', 'url'));
	}

	/**
	* Get current meta for this $post and display template metabox advanced.
	*
	* @param WP_Post $post The object for the current post/page.
	*/
	public function metabox_advanced($post) {
		$meta = get_post_meta($post->ID, $this->post_metakey, true);
		require_once( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'metabox', 'advanced'));
	}

	/**
	* Register my css and my js bubble, add wp-pointer and add action admin_print_footer_scripts.
	*/
	public function admin_enqueue_scripts() {
		/** Css */
		wp_register_style( 'wpeo-bubble-css', WPS_GUIDED_URL . '/assets/css/backend.css', '', WPS_GUIDED_VERSION );
		wp_enqueue_style( 'wpeo-bubble-css' );

		/** For use pointer */
	    wp_enqueue_script( 'wp-pointer' );
	    wp_enqueue_style( 'wp-pointer' );

		/** My js */
		wp_enqueue_script( 'wpeo-bubble-js', WPS_GUIDED_URL . '/assets/js/backend.js', array("jquery"), WPS_GUIDED_VERSION );
		/** Bottom of page */
		add_action( 'admin_print_footer_scripts', array(&$this, 'custom_admin_pointers_footer'), 50);
	}

	/**
	* Get all bubble, rework the array bubble for check if the bubble is dismiss, in this page and respects the conditions (url and database)
	* and display it.
	*/
	public function custom_admin_pointers_footer() {
		$this->array_bubble = get_posts(
			array(
				"post_type" => $this->post_type,
				"posts_per_page" => -1,
				"order" => "ASC",
			)
		);

		$dismiss_pointer = explode(',', get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true));

		foreach($this->array_bubble as $key => $bubble) {
			$meta = get_post_meta($bubble->ID, $this->post_metakey, true);
			$this->array_bubble[$key]->post_meta = $meta;

			/** On vérifie si la bubble à déjà était fermer ou pas */
			$slug = "wpeo_bubble_" . $bubble->post_name;
			if(in_array($slug, $dismiss_pointer))
				unset($this->array_bubble[$key]);

			/** On vérifie si on est sur la bonne page de la bubble */
			if(!$this->check_page_bubble((!empty($meta) && !empty($meta['position'])) ? $meta['position'] : array()))
				unset($this->array_bubble[$key]);

			/** On vérifie si y'a pas des conditions */
			if(!$this->check_condition_bubble((!empty($meta) && !empty($meta['conditions'])) ? $meta['conditions'] : array()))
				unset($this->array_bubble[$key]);

			/** On vérifie les $_GET */
			if(!$this->check_url_bubble((!empty($meta) && !empty($meta['urls'])) ? $meta['urls'] : array()))
				unset($this->array_bubble[$key]);
		}
		sort($this->array_bubble);

		require_once( wpsBubbleTemplate_ctr::get_template_part( WPS_GUIDED_DIR, WPS_GUIDED_TEMPLATES_MAIN_DIR, 'backend', 'pointer'));
	}

	/**
	* Check the current page for the bubble
	*
	* @param array $position (page, anchor_id)
	* @return boolean true is the bubble is in page or false if not.
	*/
	public function check_page_bubble($position) {
		if(empty($position['page']))
			return true;

		if("all" === $position['page'])
			return true;

		if(get_current_screen()->parent_file === $position['page'])
			return true;

		return false;
	}

	/**
	* Check condition in database for the bubble
	*
	* @param Array $conditions The array (option_name, data_name, option_value)
	* @return boolean true is the bubble respect the condition or false if not.
	*/
	public function check_condition_bubble($conditions) {
		if(empty($conditions) || empty($conditions['option_name']))
			return true;

		$option = get_option($conditions['option_name']);

		if(empty($option) || $option == "")
			return true;

		if(!is_array($option) && $option == $conditions['option_value']) {
			return true;
		}
		else if(is_array($option) && $conditions['option_value'] == $option[$conditions['data_name']]) {
			return true;
		}
		return false;
	}

	/**
	* Check $_GET in url for the bubble
	*
	* @param array $url The array (paramater, value).
	* @return boolean true is the bubble is checked in URL or false if not.
	*/
	public function check_url_bubble($urls) {
		// Il faut que ça respecte au moins 1 $_GET
		if ( !empty( $urls ) ) {
			foreach ($urls as $url ) {
				$url = !empty( $_GET[$url['paramater']] ) ? sanitize_text_field( $_GET[$url['paramater']] ) : '';
				if ( !empty( $url ) && $url == $url['value']) {
					return true;
				}
				else if(empty($url['paramater']) && empty($url['value'])) {
					return true;
				}
			}
		}
		else
			return true;

		return false;
	}

	/**
	* Reset the bubble id for all user
	*
	* @param int post_ID - The bubble ID
	*/
	public function reset_bubble_all_user() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'reset_bubble_all_user' ) )
			wp_die();

		$post = get_post((int)$_POST['post_ID']);
		$post_name = $post->post_name;

		$array_users = get_users();
		if(!empty($array_users)) {
			foreach($array_users as $user) {
				// User id $user->data->ID;
				$meta = get_user_meta($user->data->ID, 'dismissed_wp_pointers', true);
				$meta = explode(',', $meta);
				$pos = array_search('wpeo_bubble_' . $post_name, $meta);
				unset($meta[$pos]);
				$meta = implode(',', $meta);
				update_user_meta($user->data->ID, 'dismissed_wp_pointers', $meta);
			}
		}

		wp_die();
	}

	/**
	*	Dismiss the pointer if not already dismissed
	*
	* @param string $_POST['pointer'] The sanitize name of the pointer
	*/
	public function dismiss_my_pointer() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'dismiss_my_pointer' ) )
			wp_die();

		$pointer = sanitize_key( $_POST['pointer'] );
		if ( $pointer != sanitize_key( $pointer ) )
			wp_die(0);

		$dismissed = array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) );

		if(in_array($pointer, $dismissed))
			wp_die(0);

		$dismissed[] = $pointer;
		$dismissed = implode( ',', $dismissed );

		update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );
		wp_die(1);
	}

	/**
	* Replace quote, simple quote, backline
	*
	* @param string $string The string
	*/
	private function format_string_php_to_js($string) {
		$string = str_replace(CHR(13).CHR(10),"<br />",$string);
		$string = str_replace("'", "&#39;", $string);
		$string = nl2br($string);
		$string = preg_replace( "/\r|\n/", "", $string );
		return trim($string);
	}

	/**
	 * Convertis un xml_object en array
	 * @param xml_object $xml_object
	 * @param array $out
	 * @return array
	 */
	static public function xml_2_array($xml_object, $out = array()) {
		foreach((array) $xml_object as $index => $node) {
			$out[$index] = (is_object($node)) ? xml_2_array($node) : $node;
		}

		return $out;
	}

	/**
	* For import the base XML when install or update wpshop
	*/
	static public function import_xml() {
		global $wpdb, $wp_rewrite;

		/** Default data array for add product */
		$product_default_args = array(
			'comment_status' 	=>	'closed',
			'ping_status' 		=>	'closed',
			'post_status' 		=>	'publish',
			'post_author' 		=>	get_current_user_id(),
		);

		/**	Get the default datas for installation - sample products	*/
		$sample_datas = file_get_contents( WPS_GUIDED_PATH . '/assets/data/default-data-guided-tour.xml' );
		$defined_sample_datas = new SimpleXMLElement( $sample_datas, LIBXML_NOCDATA );

		$namespaces = $defined_sample_datas->getDocNamespaces();
		if ( ! isset( $namespaces['wp'] ) )
			$namespaces['wp'] = 'http://wordpress.org/export/1.1/';
		if ( ! isset( $namespaces['excerpt'] ) )
			$namespaces['excerpt'] = 'http://wordpress.org/export/1.1/excerpt/';

		foreach ( $defined_sample_datas->xpath( '//item' ) as $product ) {
			$dc = $product->children( 'http://purl.org/dc/elements/1.1/' );
			$content = $product->children( 'http://purl.org/rss/1.0/modules/content/' );
			$excerpt = $product->children( $namespaces['excerpt'] );
			$wp = $product->children( $namespaces['wp'] );

			$product_args  = wp_parse_args( array(
				'post_title' => __((string)$product->title, self::$name_i18n),
				'post_name' => (string) $wp->post_name,
				'post_content' => __((string) $content->encoded, self::$name_i18n),
				'post_excerpt' => (string) $excerpt->encoded,
				'post_type' => (string) $wp->post_type,
			), $product_default_args );

			$product_id = wp_insert_post( $product_args );
			foreach ( $wp->postmeta as $meta ) {
				$m = self::xml_2_array($meta->meta_value);
				$m = maybe_unserialize($m[0]);
				update_post_meta( $product_id, (string)$meta->meta_key, $m);
			}
		}

	}
}

?>
