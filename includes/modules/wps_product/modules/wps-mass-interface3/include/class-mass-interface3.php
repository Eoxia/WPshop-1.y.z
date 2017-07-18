<?php
/**
 * Main class for display WPShop Mass interface.
 *
 * @package wps-mass-interface3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Mass Interface 3 is kind of controller.
 * Construct menu page & use WPS Mass List Table.
 */
class Mass_Interface3 {
	/**
	 * Menu page identifier.
	 *
	 * @var string
	 */
	public $hook;
	/**
	 * Post type object.
	 *
	 * @var stdClass
	 */
	private $post_type_object;
	/**
	 * Instance of WPS Mass List Table
	 *
	 * @var WPS_Mass_List_Table
	 */
	private $wp_list_table;
	/**
	 * Default configuration of displayed columns.
	 *
	 * @var array
	 */
	public $default_show_columns = array(
		'cb',
		'title',
		'product_price',
		'price_ht',
		'product_stock',
		'product_reference',
		'tx_tva',
		'manage_stock',
		'product_weight',
	);
	/**
	 * Attributes code to exclude.
	 *
	 * @var array
	 */
	public $exclude_attribute_codes = array(
		'product_attribute_set_id',
		'price_behaviour',
	);
	/**
	 * Constructor for menu & screen configuration & ajax actions.
	 *
	 * @method __construct
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'mass_init' ), 350 );
		add_action( 'wp_ajax_wps_mass_3_new', array( $this, 'ajax_new' ) );
		add_action( 'wp_ajax_wps_mass_3_save', array( $this, 'ajax_save' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}
	/**
	 * Page Initialisation
	 *
	 * @method mass_init
	 * @return void
	 */
	public function mass_init() {
		$page = ( isset( $_GET['page'] ) && strpos( $_GET['page'] , 'mass_edit_interface3_att_set_' ) !== false ) ? $_GET['page'] : 'mass_edit_interface3_att_set_1';
		$this->hook = add_submenu_page(
			'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
			__( 'Mass product edit', 'wpshop' ),
			__( 'Mass product edit', 'wpshop' ),
			'manage_options',
			$page,
			array( $this, 'mass_interface' )
		);
		add_action( "load-{$this->hook}", array( $this, 'mass_interface_screen_option' ) );
		add_action( "admin_print_scripts-{$this->hook}", array( $this, 'scripts' ) );
		add_action( "admin_print_styles-{$this->hook}", array( $this, 'styles' ) );
	}
	/**
	 * Page content.
	 *
	 * @method mass_interface
	 * @return void Direct display.
	 */
	public function mass_interface() {
		$wp_list_table = $this->wp_list_table( $this->hook );
		$wp_list_table->prepare_items(); ?>
		<div class="wrap">
		<h1 class="wp-heading-inline"><?php
		echo esc_html( $this->post_type_object->labels->name );
		?></h1>
		<?php
		if ( current_user_can( $this->post_type_object->cap->create_posts ) ) {
			echo ' <a href="#addPost" class="page-title-action" data-nonce="' . esc_attr( wp_create_nonce( 'add_post-' . sanitize_title( get_class() ) ) ) . '">';
			echo esc_html( $this->post_type_object->labels->add_new ) . '</a>';
		}
		?>
		<hr class="wp-header-end">
		<?php echo '<input type="hidden" id="hook" value="' . esc_attr( $this->hook ) . '">'; ?>
		<form id="posts-filter" method="get">
		<?php $wp_list_table->views(); ?>
		<?php $wp_list_table->search_box( $this->post_type_object->labels->search_items, 'post' ); ?>
		<input type="hidden" name="page" value="<?php
		echo esc_attr( str_replace(
			"{$wp_list_table->screen->post_type}_page_",
			'',
			$wp_list_table->screen->id
		) ); ?>">
		<input type="hidden" name="post_type" value="<?php echo esc_attr( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ); ?>">
		</form>
		<?php $wp_list_table->display(); ?>
		<table style="display:none;">
		 <tbody id="posts-add">
		  <tr id="inline-edit" class="inline-edit-row inline-edit-row-post <?php echo esc_attr( "inline-edit-{$this->post_type_object->name} quick-edit-row quick-edit-row-post inline-edit-{$this->post_type_object->name}" ); ?>" style="display: none">
		   <td colspan="<?php echo esc_attr( $wp_list_table->get_column_count() ); ?>" class="colspanchange">
			<fieldset class="inline-edit-col">
			 <legend class="inline-edit-legend"><?php echo esc_html( $this->post_type_object->labels->add_new ) ?></legend>
			 <div class="inline-edit-col">
		   <label>
			<span class="title"><?php esc_html_e( 'Title' ); ?></span>
			<span class="input-text-wrap"><input type="text" name="post_title" class="ptitle" value="" /></span>
		   </label>
			 </div>
			</fieldset>
			<p class="submit inline-edit-save">
			 <button type="button" class="button cancel alignleft"><?php esc_html_e( 'Cancel' ); ?></button>
			 <button type="button" class="button button-primary save alignright"><?php echo esc_html( $this->post_type_object->labels->add_new ); ?></button>
			 <span class="spinner"></span>
			 <span class="error" style="display:none"></span>
			 <br class="clear" />
			</p>
		   </td>
		  </tr>
		 </tbody>
		</table>
		</div>
		<?php
	}
	/**
	 * Notices hook (not used) & hidden_columns on page.
	 *
	 * @method mass_interface_screen_option
	 * @return void
	 */
	public function mass_interface_screen_option() {
		add_action( 'admin_notices', array( $this, 'ajax_admin_notice' ) );
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
		$this->wp_list_table( $this->hook );
	}
	/**
	 * Instance WPS_Mass_List_Table.
	 *
	 * @method wp_list_table
	 * @param  string $screen Current screen.
	 * @return WPS_Mass_List_Table Table class.
	 */
	public function wp_list_table( $screen ) {
		if ( is_null( $this->wp_list_table ) ) {
			$this->post_type_object = get_post_type_object( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
			include_once( WPS_PDCT_MASS_INCLUDE_PATH . 'class-wps-mass-list-table.php' );
			$this->wp_list_table = new WPS_Mass_List_Table(
				array(
					'screen' => $screen,
					'exclude_attribute_codes' => $this->exclude_attribute_codes,
				)
			);
			$this->wp_list_table->screen->set_screen_reader_content(
				array(
					'heading_views'      => $this->post_type_object->labels->filter_items_list,
					'heading_pagination' => $this->post_type_object->labels->items_list_navigation,
					'heading_list'       => $this->post_type_object->labels->items_list,
				)
			);
			$class = sanitize_title( get_class() );
			$this->wp_list_table->screen->add_option(
				'per_page', array(
					'default' => 20,
					'option' => "{$class}_per_page",
				)
			);
		}
		return $this->wp_list_table;
	}
	/**
	 * Filter hidden columns, only without user preferences.
	 *
	 * @method hidden_columns
	 * @param  array     $hidden Given by hook, see "default_hidden_columns".
	 * @param  WP_Screen $screen Current screen.
	 * @return array Return of filter.
	 */
	public function hidden_columns( $hidden, $screen ) {
		$wp_list_table = $this->wp_list_table( $this->hook );
		if ( $screen === $wp_list_table->screen ) {
			$hidden = array_diff( array_flip( $wp_list_table->get_columns() ), $this->default_show_columns );
			$hidden[] = 'thumbnail';
		}
		return $hidden;
	}
	/**
	 * Hook for save per_page option.
	 *
	 * @method set_screen_option
	 * @param  string $string Given by WordPress.
	 * @param  string $option Actual option to save.
	 * @param  mixed  $value  Actual value to save.
	 */
	public function set_screen_option( $string, $option, $value ) {
		$class = sanitize_title( get_class() );
		if ( "{$class}_per_page" === $option ) {
			$value = (int) $value;
			if ( $value < 1 || $value > 999 ) {
				$string = false;
			}
			return $value;
		}
		return $string;
	}
	/**
	 * Notice template, duplicated by JS for show notices.
	 *
	 * @method ajax_admin_notice
	 * @return void Direct display.
	 */
	public function ajax_admin_notice() {
		printf( '<div class="%1$s"><p></p></div>', esc_attr( 'hidden is-dismissible notice' ) );
	}
	/**
	 * Enqueue scripts.
	 *
	 * @method scripts
	 * @return void
	 */
	public function scripts() {
		wp_deregister_script( 'wpes_chosen_js' );
		wp_enqueue_script(
			'jquery_chosen_js',
			WPS_PDCT_MASS_CHOSEN_JS . 'chosen.jquery.min.js',
			array( 'jquery' ),
			true
		);
		wp_enqueue_script(
			'mass_interface3-ajax',
			WPS_PDCT_MASS_JS . 'wps-mass-interface3.js',
			array( 'jquery', 'jquery-form' ),
			true
		);
		wp_enqueue_media();
	}
	/**
	 * Enqueue styles.
	 *
	 * @method styles
	 * @return void
	 */
	public function styles() {
		wp_register_style( 'jquery_chosen_css', WPS_PDCT_MASS_CHOSEN_CSS . 'chosen.min.css' );
		wp_register_style( 'mass_interface3_css', WPS_PDCT_MASS_CSS . 'wps-mass-interface3.css' );
		wp_enqueue_style( 'jquery_chosen_css' );
		wp_enqueue_style( 'mass_interface3_css' );
		wp_dequeue_style( 'wpshop_main_css' );
	}
	/**
	 * Change default url for set_url_scheme(). See pagination & get_views. Impossible to re-use set_url_sheme inner.
	 *
	 * @method set_current_url
	 * @param  string        $url    Given url.
	 * @param  string | null $scheme Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', 'relative', 'rest', 'rpc', or null.
	 */
	public function set_current_url( $url, $scheme ) {
		/*
		Remove_filter( 'set_url_scheme', array( $this, 'set_current_url' ), 10 );
		$url = set_url_sheme( esc_url( $_POST['current_url'] ) );
		add_filter( 'set_url_scheme', array( $this, 'set_current_url' ), 10, 2 );
		*/
		return $_POST['current_url'];
	}
	/**
	 * Ajax callback for new element.
	 *
	 * @method ajax_new
	 * @return void JSON with all elements to update.
	 */
	public function ajax_new() {
		check_ajax_referer( 'add_post-' . sanitize_title( get_class() ) );
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
		add_filter( 'set_url_scheme', array( $this, 'set_current_url' ), 10, 2 );
		$wp_list_table = $this->wp_list_table( sanitize_title( $_POST['hook'] ) );
		$wpshop_product_attribute = array();
		foreach ( $wp_list_table->request_items_columns() as $key_var => $var ) {
			$wpshop_product_attribute[ $var['data'] ][ $key_var ] = null;
		}
		$new_product_id = wp_insert_post(
			array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'post_status' => 'publish',
				'post_title' => sanitize_text_field( $_POST['title'] ),
			)
		);
		if ( ! empty( $new_product_id ) ) {
			update_post_meta( $new_product_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id', $wp_list_table->current_view );
			$product_class = new wpshop_products();
			$product_class->save_product_custom_informations(
				$new_product_id, array(
					'post_ID' => $new_product_id,
					'product_id' => $new_product_id,
					'wpshop_product_attribute' => $wpshop_product_attribute,
					'user_ID' => get_current_user_id(),
					'action' => 'editpost',
				)
			);
		} else {
			wp_die( 1 );
		}
		$data = $wp_list_table->request( $new_product_id );
		$per_page = $wp_list_table->screen->get_option( 'per_page', 'option' );
		$wp_list_table->column_headers();
		$wp_list_table->items = true;
		ob_start();
		$wp_list_table->views();
		$subsubsub = ob_get_clean();
		ob_start();
		$wp_list_table->display_tablenav( 'top' );
		$tablenav_top = ob_get_clean();
		ob_start();
		$wp_list_table->display_tablenav( 'bottom' );
		$tablenav_bottom = ob_get_clean();
		ob_start();
		$wp_list_table->single_row( $data[0] );
		wp_send_json_success( array(
			'row' => ob_get_clean(),
			'per_page' => $per_page,
			'tablenav_top' => $tablenav_top,
			'tablenav_bottom' => $tablenav_bottom,
			'subsubsub' => $subsubsub,
		) );
	}
	/**
	 * Ajax callback for save selected elements.
	 *
	 * @method ajax_save
	 * @return void JSON with number of saved elements (not used).
	 */
	public function ajax_save() {
		check_ajax_referer( 'bulk-save-mass-edit-interface-3' );
		$i = 0;
		$product_class = new wpshop_products();
		if ( ! empty( $_REQUEST['cb'] ) ) {
			foreach ( $_REQUEST['cb'] as $id ) {
				$id = intval( $id );
				if ( isset( $_REQUEST[ 'row_' . $id ]['thumbnail'] ) ) {
					intval( $_REQUEST[ 'row_' . $id ]['thumbnail'] );
					update_post_meta( $id, '_thumbnail_id', intval( $_REQUEST[ 'row_' . $id ]['thumbnail'] ) );
					unset( $_REQUEST[ 'row_' . $id ]['thumbnail'] );
				}
				if ( ! empty( $_REQUEST[ 'row_' . $id ] ) ) {
					$product_class->save_product_custom_informations(
						$id,
						array_merge(
							$_REQUEST[ 'row_' . $id ],
							array(
								'post_ID' => $id,
								'product_id' => $id,
								'user_ID' => get_current_user_id(),
								'action' => 'editpost',
							)
						)
					);
					$i++;
				}
			}
		}
		wp_send_json_success(
			array(
				'notice' => "{$i} rows has been updated",
			)
		);
	}
}
