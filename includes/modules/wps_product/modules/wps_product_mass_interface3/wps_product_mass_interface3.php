<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
}
new mass_interface3();
class mass_interface3 {

	public $hook;
	private $post_type_object;
	private $wp_list_table;
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
	public $exclude_attribute_codes = array(
		'product_attribute_set_id',
		'price_behaviour',
	);
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'mass_init' ), 350 );
		add_action( 'wp_ajax_wps_mass_3_new', array( $this, 'ajax_new' ) );
		add_action( 'wp_ajax_wps_mass_3_save', array( $this, 'ajax_save' ) );
	}
	public function mass_init() {
		$this->hook = add_submenu_page( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, __( 'Mass product edit 3', 'wpshop' ), __( 'Mass product edit 3', 'wpshop' ), 'manage_options', ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'mass_edit_interface3_att_set_' ) !== false ) ? $_GET['page'] : 'mass_edit_interface3_att_set_1', array( $this, 'mass_interface' ) );
		add_action( "load-{$this->hook}", array( $this, 'mass_interface_screen_option' ) );
		add_action( "admin_print_scripts-{$this->hook}", array( $this, 'scripts' ) );
		add_action( "admin_print_styles-{$this->hook}", array( $this, 'styles' ) );
	}
	public function mass_interface() {
		$wp_list_table = $this->wp_list_table( $this->hook );
		$wp_list_table->prepare_items(); ?>
		<div class="wrap">
		<h1 class="wp-heading-inline"><?php
		echo esc_html( $this->post_type_object->labels->name );
		?></h1>
		<?php
		if ( current_user_can( $this->post_type_object->cap->create_posts ) ) {
			echo ' <a href="#" class="page-title-action" onclick="addPost(event, this)">' . esc_html( $this->post_type_object->labels->add_new ) . '</a>';
		}
		?>
		<hr class="wp-header-end">
		<form id="posts-filter" method="get">
		<?php $wp_list_table->views(); ?>
		<?php $wp_list_table->search_box( $this->post_type_object->labels->search_items, 'post' ); ?>
		<input type="hidden" name="page" value="<?php
		 echo str_replace(
			 "{$wp_list_table->screen->post_type}_page_",
			 '',
			 $wp_list_table->screen->id
		 ); ?>">
		<input type="hidden" name="post_type" value="<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>">
		</form>
		<?php $wp_list_table->display(); ?>
		<table style="display:none;">
		 <tbody id="posts-add">
		  <tr id="inline-edit" class="inline-edit-row inline-edit-row-<?php echo "post inline-edit-{$this->post_type_object->name} quick-edit-row quick-edit-row-post inline-edit-{$this->post_type_object->name}"; ?>" style="display: none">
		   <td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="colspanchange">
			<fieldset class="inline-edit-col">
			 <legend class="inline-edit-legend"><?php echo esc_html( $this->post_type_object->labels->add_new ) ?></legend>
			 <div class="inline-edit-col">
		   <label>
			<span class="title"><?php _e( 'Title' ); ?></span>
			<span class="input-text-wrap"><input type="text" name="post_title" class="ptitle" value="" /></span>
		   </label>
			 </div>
			</fieldset>
			<p class="submit inline-edit-save">
			 <button type="button" class="button cancel alignleft"><?php _e( 'Cancel' ); ?></button>
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
	public function mass_interface_screen_option() {
		add_action( 'admin_notices', array( $this, 'ajax_admin_notice' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
		$this->wp_list_table( $this->hook );
	}
	public function wp_list_table( $screen ) {
		$this->post_type_object = get_post_type_object( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
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
		$class = get_class();
		$this->wp_list_table->screen->add_option(
			'per_page', array(
				'default' => 20,
				'option' => "{$class}_per_page",
			)
		);
		return $this->wp_list_table;
	}
	public function hidden_columns( $hidden, $screen ) {
		$wp_list_table = $this->wp_list_table( $this->hook );
		if ( $screen === $wp_list_table->screen ) {
			$hidden = array_diff( array_flip( $wp_list_table->get_columns() ), $this->default_show_columns );
		}
		return $hidden;
	}
	public function set_screen_option( $string, $option, $value ) {
		$class = get_class();
		if ( "{$class}_per_page" === $option ) {
			$value = (int) $value;
			if ( $value < 1 || $value > 999 ) {
				$string = false;
			}
			return $value;
		}
		return $string;
	}
	public function ajax_admin_notice() {
		printf( '<div class="%1$s"><p></p></div>', esc_attr( 'hidden is-dismissible notice' ) );
	}
	public function scripts() {
		wp_enqueue_script(
			'jquery_chosen_js',
			plugin_dir_url( __FILE__ ) . 'chosen.jquery.min.js',
			array( 'jquery' ),
			true
		);
		wp_enqueue_script(
			'mass_interface3-ajax',
			plugin_dir_url( __FILE__ ) . 'interface3.js',
			array( 'jquery', 'jquery-form' ),
			true
		);
	}
	public function styles() {
		wp_register_style( 'jquery_chosen_css', plugin_dir_url( __FILE__ ) . 'chosen.min.css' );
		wp_register_style( 'mass_interface3_css', plugin_dir_url( __FILE__ ) . 'interface3.css' );
		wp_enqueue_style( 'jquery_chosen_css' );
		wp_enqueue_style( 'mass_interface3_css' );
		wp_deregister_style( 'wpshop_main_css' );
	}
	public function ajax_new() {
		$wp_list_table = $this->wp_list_table( $_POST['screen'] );
		$wpshop_product_attribute = array();
		foreach ( $wp_list_table->request_items_columns() as $key_var => $var ) {
			$wpshop_product_attribute[ $var['data'] ][ $key_var ] = null;
		}
		$new_product_id = wp_insert_post(
			array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'post_status' => 'publish',
				'post_title' => $_POST['title'],
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
		$class = get_class();
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
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
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
	public function ajax_save() {
		$i = 0;
		$product_class = new wpshop_products();
		if ( ! empty( $_REQUEST['cb'] ) ) {
			foreach ( $_REQUEST['cb'] as $id ) {
				$id = intval( $id );
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
class WPS_Mass_List_Table extends WP_List_Table {

	public static $wpsdb_values_options = array();
	public $columns_items = array();
	public $show_columns = array();
	public $screen;
	public $entity_id;
	public $exclude_attribute_codes = array();
	public $current_view = null;
	private $_views = null;
	public function __construct( $args ) {
		if ( isset( $args['exclude_attribute_codes'] ) ) {
			$this->exclude_attribute_codes = $args['exclude_attribute_codes'];
		}
		parent::__construct(
			array(
				'plural' => 'posts',
				'ajax' => true,
				'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
			)
		);
		$this->current_view = (int) substr( $this->screen->id, strpos( $this->screen->id, '_att_set_' ) + 9 );
		$this->entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
	}
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'title'    => __( 'Title' ),
		);
		foreach ( $this->request_items_columns() as $column => $data_column ) {
			if ( ! empty( $column ) && ! empty( $data_column ) ) {
				$columns[ $column ] = $data_column['name'];
			}
		}
		return $columns;
	}
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'title'    => array( 'title', false ),
		);
		foreach ( $this->request_items_columns() as $column => $data_column ) {
			$sortable_columns[ $column ] = array( $data_column['code'], false );
		}
		return $sortable_columns;
	}
	public function column_default( $item, $column_name ) {
		if ( isset( $this->columns_items[ $column_name ] ) && is_callable( array( $this, "column_data_{$this->columns_items[ $column_name ]['type']}" ) ) ) {
			$callable_ext = str_replace( '-', '_', $this->columns_items[ $column_name ]['type'] );
			$callable = array( $this, "column_data_{$callable_ext}" );
			if ( ! is_callable( $callable ) ) {
				$callable = array( $this, 'column_data_text' );
			}
			return call_user_func(
				$callable,
				$this->columns_items[ $column_name ]['id'],
				$this->columns_items[ $column_name ]['code'],
				$this->columns_items[ $column_name ]['data'],
				$item
			);
		}
		return print_r( $item[ $column_name ], true );
	}
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="cb[]" value="%d" />',
			$item['ID']
		);
	}
	public function column_title( $item ) {
		if ( 'private' === $item['status'] ) {
			$post_states['private'] = __( 'Private' );
		}
		if ( 'draft' === $item['status'] ) {
			$post_states['draft'] = __( 'Draft' );
		}
		if ( 'pending' === $item['status'] ) {
			$post_states['pending'] = _x( 'Pending', 'post status' );
		}
		if ( 'future' === $item['status'] ) {
			$post_states['scheduled'] = __( 'Scheduled' );
		}
		if ( current_user_can( 'edit_post', $item['ID'] ) && 'trash' !== $item['status'] ) {
			$result = sprintf(
				'<a class="row-title" href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $item['ID'] ),
				esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $item['title'] ) ),
				$item['title']
			);
		} else {
			$result = $item['title'];
		}
		if ( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION === $item['type'] ) {
			$attr_def = get_post_meta( $item['ID'], '_wpshop_variations_attribute_def', true );
			$columns_items = $this->request_items_columns();
			$first_variation = true;
			$parent = $this->items[ $item['parent'] ];
			foreach ( $attr_def as $key => $value ) {
				foreach ( $this->get_select_items_option( $columns_items[ $key ]['id'] ) as $all_value ) {
					if ( $all_value['id'] === $value ) {
						if ( $first_variation ) {
							$result = $parent['title'] . ' : ';
							$first_variation = false;
						} else {
							$result .= ' / ';
						}
						$result .= $all_value['label'];
						break;
					}
				}
			}
		}
		if ( ! empty( $post_states ) ) {
			$state_count = count( $post_states );
			$i = 0;
			$result .= ' &mdash; ';
			foreach ( $post_states as $state ) {
				++$i;
				( $i === $state_count ) ? $sep = '' : $sep = ', ';
				$result .= "<span class='post-state'>$state$sep</span>";
			}
		}
		return sprintf(
			'<strong>%s</strong>',
			isset( $item['lvl'] ) ? $item['lvl'] . $result : $result
		);
	}
	public function column_data_default( $attribute_id, $attribute_code, $attribute_data, $item ) {
		return 'default';
	}
	public function column_data_text( $attribute_id, $attribute_code, $attribute_data, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		return sprintf(
			'<input type="text" name="row_%2$s[wpshop_product_attribute][%3$s][%1$s]" value="%4$s">',
			$attribute_code,
			$item['ID'],
			$attribute_data,
			$value,
			$unit
		);
	}
	public function column_data_select( $attribute_id, $attribute_code, $attribute_data, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) && isset( $item[ $attribute_code ]['unit'] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		$select_items = array();
		foreach ( $this->get_select_items_option( $attribute_id ) as $option_item ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $val ) {
					$selected = selected( $val, $option_item['id'], false );
					if ( ! empty( $selected ) ) {
						break;
					}
				}
			} else {
				$selected = selected( $value, $option_item['id'], false );
			}
			$select_items[] = "<option value=\"{$option_item['id']}\"{$selected}>{$option_item['label']}</option>";
		}
		$select_items = implode( '', $select_items );
		return sprintf(
			'<select name="row_%2$s[wpshop_product_attribute][%3$s][%1$s]">%4$s</select>',
			$attribute_code,
			$item['ID'],
			$attribute_data,
			$select_items,
			$unit
		);
	}
	public function column_data_textarea( $attribute_id, $attribute_code, $attribute_data, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		return sprintf(
			'<textarea name="row_%2$s[wpshop_product_attribute][%3$s][%1$s]">%4$s</textarea>',
			$attribute_code,
			$item['ID'],
			$attribute_data,
			$value,
			$unit
		);
	}
	public function column_data_multiple_select( $attribute_id, $attribute_code, $attribute_data, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) && isset( $item[ $attribute_code ]['unit'] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		$select_items = array();
		foreach ( $this->get_select_items_option( $attribute_id ) as $option_item ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $val ) {
					$selected = selected( $val, $option_item['id'], false );
					if ( ! empty( $selected ) ) {
						break;
					}
				}
			} else {
				$selected = selected( $value, $option_item['id'], false );
			}
			$select_items[] = "<option value=\"{$option_item['id']}\"{$selected}>{$option_item['label']}</option>";
		}
		$select_items = implode( '', $select_items );
		return sprintf(
			'<select class="chosen-select" multiple data-placeholder="%6$s" name="row_%2$s[wpshop_product_attribute][%3$s][%1$s][]">%4$s</select>',
			$attribute_code,
			$item['ID'],
			$attribute_data,
			$select_items,
			$unit,
			'Select some options'
		);
	}
	public function request( $id_post = null ) {
		global $wpdb;
		$per_page = $this->get_items_per_page( $this->screen->get_option( 'per_page', 'option' ) );
		$include_states = array(
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'trash',
			'scheduled',
		);
		$include_states = implode( "','", $include_states );
		$post_types = array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION );
		$post_types = implode( "','", $post_types );
		$orderby = isset( $_REQUEST['orderby'] ) ? esc_sql( $_REQUEST['orderby'] ) : 'p.post_date';
		$order = isset( $_REQUEST['order'] ) ? esc_sql( $_REQUEST['order'] ) : 'DESC';
		$cast = isset( $_REQUEST['cast'] ) ? esc_sql( $_REQUEST['cast'] ) : '';
		$cast = strtoupper( $cast );
		$s = isset( $_REQUEST['s'] ) ? esc_sql( $_REQUEST['s'] ) : '';
		$exclude_attribute_codes = implode( "','", $this->exclude_attribute_codes );
		$extra = '';
		$items_count = $wpdb->prepare( "SELECT FOUND_ROWS() FROM {$wpdb->posts} WHERE 1 = %d", 1 );
		if ( ! is_null( $id_post ) ) {
			$id_post = intval( $id_post );
			$extra = "
			AND p.ID = {$id_post}";
			$s = '';
		}
		if ( true ) { // FOUND_ROWS incompatibilities ?
			$items_count = $wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p
				INNER JOIN wp_postmeta ON wp_postmeta.post_id = p.ID AND wp_postmeta.meta_key = '_wpshop_product_attribute_set_id' AND wp_postmeta.meta_value LIKE %s
				WHERE p.post_status IN ( '{$include_states}' )
				AND p.post_type IN ( '{$post_types}' )
				AND p.post_title LIKE %s",
				$this->request_current_view(),
				"%{$s}%"
			);
		}
		$wpsdb_attribute = WPSHOP_DBT_ATTRIBUTE;
		$wpsdb_attribute_set = WPSHOP_DBT_ATTRIBUTE_DETAILS;
		$wpsdb_unit = WPSHOP_DBT_ATTRIBUTE_UNIT;
		$wpsdb_values_decimal = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
		$wpsdb_values_datetime = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
		$wpsdb_values_integer = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
		$wpsdb_values_varchar = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
		$wpsdb_values_text = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
		$wpsdb_values_options = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
		$extra_select = '';
		if ( ! in_array( $orderby, array( 'p.post_date', 'title', 'ID' ), true ) ) {
			$extra_select = "( SELECT IFNULL( {$wpsdb_values_decimal}.value,
				IFNULL( {$wpsdb_values_datetime}.value,
					IFNULL( {$wpsdb_values_text}.value,
						IFNULL( {$wpsdb_values_varchar}.value,
							IFNULL( {$wpsdb_values_options}.value,
								{$wpsdb_values_integer}.value
							)
						)
					)
				)
			)
			FROM wp_posts p1
			LEFT JOIN {$wpsdb_attribute} ON {$wpsdb_attribute}.status = 'valid' AND {$wpsdb_attribute}.code = '{$orderby}'
			LEFT JOIN {$wpsdb_values_decimal} ON {$wpsdb_values_decimal}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_decimal}.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_datetime} ON {$wpsdb_values_datetime}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_datetime}.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_integer} ON {$wpsdb_values_integer}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_integer}.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_text} ON {$wpsdb_values_text}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_text}.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_varchar} ON {$wpsdb_values_varchar}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_varchar}.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_options} ON {$wpsdb_values_options}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_options}.id = {$wpsdb_values_integer}.value
			WHERE p1.ID = p.ID )";
			if ( ! empty( $cast ) ) {
				$extra_select = "CAST( {$extra_select} AS {$cast} )";
			}
			$extra_select = ",
			{$extra_select} AS {$orderby}";
		}
		$wpdb->query(
			$wpdb->prepare( 'SET SESSION group_concat_max_len = %d', 1000000 )
		);
		$datas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS
				p.ID,
				p.post_title as title,
				p.post_parent as parent,
				p.post_status as status,
				p.post_type as type,
				GROUP_CONCAT(
					CONCAT(
						{$wpsdb_attribute}.id, ':',
						{$wpsdb_attribute}.code, ':',
						{$wpsdb_attribute}.frontend_label, ':',
						CONCAT(
							IFNULL( {$wpsdb_values_decimal}.value, '' ),
							IFNULL( {$wpsdb_values_datetime}.value, '' ),
							IFNULL( {$wpsdb_values_integer}.value, '' ),
							IFNULL( {$wpsdb_values_text}.value, '' ),
							IFNULL( {$wpsdb_values_varchar}.value, '' )
						), ':',
						{$wpsdb_attribute}.is_requiring_unit, ':',
						IFNULL( {$wpsdb_unit}.unit, '' ), ':',
						{$wpsdb_attribute}.backend_input, ':',
						{$wpsdb_attribute}.data_type
					) SEPARATOR ';'
				) as data{$extra_select}
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} ON {$wpdb->postmeta}.post_id = p.ID AND {$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value = %d
				LEFT JOIN {$wpsdb_attribute_set} ON {$wpsdb_attribute_set}.status = 'valid' AND {$wpsdb_attribute_set}.entity_type_id = %d AND {$wpsdb_attribute_set}.attribute_set_id = %d
				LEFT JOIN {$wpsdb_attribute} ON {$wpsdb_attribute}.status = 'valid' AND {$wpsdb_attribute}.entity_id = %d AND {$wpsdb_attribute}.code NOT IN ( '{$exclude_attribute_codes}' ) AND {$wpsdb_attribute}.id = {$wpsdb_attribute_set}.attribute_id
				LEFT JOIN {$wpsdb_values_decimal} ON {$wpsdb_values_decimal}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_decimal}.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_datetime} ON {$wpsdb_values_datetime}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_datetime}.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_integer} ON {$wpsdb_values_integer}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_integer}.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_text} ON {$wpsdb_values_text}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_text}.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_varchar} ON {$wpsdb_values_varchar}.attribute_id = {$wpsdb_attribute}.id AND {$wpsdb_values_varchar}.entity_id = p.ID
				LEFT JOIN {$wpsdb_unit} ON (
					{$wpsdb_unit}.id = {$wpsdb_values_decimal}.unit_id
					OR {$wpsdb_unit}.id = {$wpsdb_values_datetime}.unit_id
					OR {$wpsdb_unit}.id = {$wpsdb_values_integer}.unit_id
					OR {$wpsdb_unit}.id = {$wpsdb_values_text}.unit_id
					OR {$wpsdb_unit}.id = {$wpsdb_values_varchar}.unit_id
				)
				WHERE p.post_status IN ( '{$include_states}' )
				AND p.post_type IN ( '{$post_types}' )
				AND p.post_title LIKE %s{$extra}
				GROUP BY p.ID
				ORDER BY {$orderby} {$order}
				LIMIT %d, %d",
				WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY,
				$this->request_current_view(),
				$this->entity_id,
				$this->request_current_view(),
				$this->entity_id,
				'%' . $s . '%',
				( $this->get_pagenum() -1 ) * $per_page,
				$per_page
			),
			ARRAY_A
		);
		if ( ! is_array( $datas ) ) {
			$datas = array();
		}
		if ( ! isset( $this->_pagination_args['total_items'] ) && ! isset( $this->_pagination_args['per_page'] ) ) {
			$this->set_pagination_args(
				array(
					'total_items' => (int) $wpdb->get_var( $items_count ),
					'per_page' => $this->get_items_per_page( $this->screen->get_option( 'per_page', 'option' ) ),
				)
			);
		}
		return array_map( array( $this, 'data_reorganize' ), $datas );
	}
	public function prepare_items() {
		foreach ( $this->request() as $item ) {
			$this->items[ $item['ID'] ] = $item;
		}
	}
	public function cast_column( $column_key ) {
		$columns_items = $this->request_items_columns();
		if ( isset( $columns_items[ $column_key ] ) ) {
			$cast = $columns_items[ $column_key ]['data'];
			if ( 'tx_tva' === $column_key ) {
				return 'decimal';
			}
			if ( 'integer' === $cast && 'select' === $columns_items[ $column_key ]['type'] ) {
				return null;
			}
			return $cast;
		}
		return null;
	}
	public function data_reorganize( $item ) {
		$values = explode( ';', $item['data'] );
		foreach ( $values as $value ) {
			$value = explode( ':', $value );
			if ( ! isset( $this->columns_items[ $value[1] ] ) ) {
				$this->columns_items[ $value[1] ] = array(
					'id' => $value[0],
					'code' => $value[1],
					'name' => $value[2],
					'type' => $value[6],
					'data' => $value[7],
				);
			}
			if ( 'yes' === $value[4] ) {
				if ( isset( $item[ $value[1] ]['value'] ) ) {
					if ( is_array( $item[ $value[1] ]['value'] ) ) {
						$item[ $value[1] ]['value'][] = $value[3];
					} else {
						$item[ $value[1] ]['value'] = array( $item[ $value[1] ]['value'], $value[3] );
					}
				} else {
					$item[ $value[1] ] = array(
						'value' => $value[3],
						'unit' => $value[5],
					);
				}
			} else {
				if ( isset( $item[ $value[1] ] ) ) {
					if ( is_array( $item[ $value[1] ] ) ) {
						$item[ $value[1] ][] = $value[3];
					} else {
						$item[ $value[1] ] = array( $item[ $value[1] ], $value[3] );
					}
				} else {
					$item[ $value[1] ] = $value[3];
				}
			}
		}// End foreach().
		unset( $item['data'] );
		return $item;
	}
	public function get_select_items_option( $attribute_id ) {
		if ( ! isset( self::$wpsdb_values_options[ $attribute_id ] ) ) {
			global $wpdb;
			$wpsdb_values_options = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
			self::$wpsdb_values_options[ $attribute_id ] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT *
					FROM {$wpsdb_values_options}
					WHERE attribute_id = %d
					ORDER BY position",
					$attribute_id
				),
				ARRAY_A
			);
		}
		return self::$wpsdb_values_options[ $attribute_id ];
	}
	public function request_views() {
		global $wpdb;
		if ( is_null( $this->_views ) ) {
			$wpsdb_sets = WPSHOP_DBT_ATTRIBUTE_SET;
			$include_states = array(
				'publish',
				'future',
				'draft',
				'pending',
				'private',
				'trash',
				'scheduled',
			);
			$include_states = implode( "','", $include_states );
			$this->_views = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT s.id, name, slug, default_set, COUNT(p.ID) AS count
					FROM {$wpsdb_sets} s
					JOIN wp_postmeta pm ON meta_key = %s AND id = meta_value
					JOIN wp_posts p ON p.ID = post_id AND post_status IN ( '{$include_states}' ) AND post_type = %s
					WHERE entity_id = %d
					AND status = %s
					GROUP BY id",
					WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY,
					WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
					$this->entity_id,
					'valid'
				),
				ARRAY_A
			);
		}
		return $this->_views;
	}
	public function request_current_view() {
		if ( is_null( $this->current_view ) ) {
			foreach ( $this->request_views() as $view ) {
				if ( filter_var( $view['default_set'], FILTER_VALIDATE_BOOLEAN ) ) {
					$this->current_view = $view['id'];
				}
			}
		}
		return $this->current_view;
	}
	public function request_items_columns() {
		if ( empty( $this->columns_items ) ) {
			global $wpdb;
			$wpsdb_attribute = WPSHOP_DBT_ATTRIBUTE;
			$wpsdb_attribute_set = WPSHOP_DBT_ATTRIBUTE_DETAILS;
			$exclude_attribute_codes = implode( "','", $this->exclude_attribute_codes );
			foreach ( $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$wpsdb_attribute}.id, {$wpsdb_attribute}.code, {$wpsdb_attribute}.frontend_label AS name, {$wpsdb_attribute}.backend_input AS type, {$wpsdb_attribute}.data_type AS data
					FROM {$wpsdb_attribute}
					LEFT JOIN {$wpsdb_attribute_set} ON {$wpsdb_attribute_set}.status = 'valid' AND {$wpsdb_attribute_set}.entity_type_id = %d AND {$wpsdb_attribute_set}.attribute_set_id = %d
					WHERE {$wpsdb_attribute}.status = 'valid'
					AND {$wpsdb_attribute}.entity_id = %d
					AND {$wpsdb_attribute}.code NOT IN ( '{$exclude_attribute_codes}' )
					AND {$wpsdb_attribute}.id = {$wpsdb_attribute_set}.attribute_id",
					$this->entity_id,
					$this->request_current_view(),
					$this->entity_id
				),
				ARRAY_A
			) as $column ) {
				$this->columns_items[ $column['code'] ] = $column;
			}
		}
		ksort( $this->columns_items );
		return $this->columns_items;
	}
	public function get_views() {
		$result = array();
		foreach ( $this->request_views() as $view ) {
			$class = '';
			if ( (int) $view['id'] === (int) $this->request_current_view() ) {
				$class = ' class="current"';
			}
			$link = add_query_arg(
				array(
					'page' =>
				str_replace(
					"{$this->screen->post_type}_page_",
					'',
					substr( $this->screen->id, 0, strpos( $this->screen->id, '_att_set_' ) ) . '_att_set_' . $view['id']
				),
				)
			);
			$link = remove_query_arg( 'paged', $link );
			$result[ $view['id'] ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $link ),
				$class,
				$view['name'],
				number_format_i18n( $view['count'] )
			);
		}
		return $result;
	}
	public function bulk_actions( $which = '' ) {
		submit_button( __( 'Save changes', 'wpshop' ), 'bulk-save', 'bulk-save', false );
			?><span class="spinner"></span><?php
	}
	private function _display_row( &$lvl, $item_id, $item, &$rows ) {
		if ( array_key_exists( $item_id, $rows ) ) {
			return;
		}
		if ( ! array_key_exists( $item['parent'], $this->items ) && 0 !== (int) $item['parent'] ) {
			$parent_item = $this->request( $item['parent'] );
			$this->items[ $item['parent'] ] = $parent_item[0];
		}
		if ( array_key_exists( $item['parent'], $rows ) ) {
			$offset = array_search( $item['parent'], array_keys( $rows ), true );
			$rows_a = array_slice( $rows, $offset, null, true );
			$rows_a[ $item_id ] = $item;
			$rows_b = array_slice( $rows, 0, $offset, true );
			$rows = array_replace( $rows_a, $rows_b );
			// $rows = $rows_a + $rows_b; FASTER ?
			$lvl++;
		} elseif ( 0 !== (int) $item['parent'] ) {
			$this->_display_row( $lvl, $item['parent'], $this->items[ $item['parent'] ], $rows );
			$lvl++;
		}
		if ( ! empty( $item ) ) {
			$item['lvl'] = str_repeat( '&#8212; ', $lvl );
		}
		$rows[ $item_id ] = $item;
	}
	public function display_rows() {
		$rows = array();
		foreach ( $this->items as $item_id => $item ) {
			$lvl = 0;
			$this->_display_row( $lvl, $item_id, $item, $rows );
		}
		foreach ( $rows as $item ) {
			if ( ! empty( $item ) ) {
				$this->single_row( $item );
			}
		}
	}
	public function single_row( $item ) {
		parent::single_row( $item );
	}
	public function views() {
		parent::views();
		$current_view = $this->request_current_view();
		// echo "<input type=\"hidden\" name=\"attribute_set\" value=\"{$current_view}\">";
	}
	// Duplicate of wp_list_table function
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
			 . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden, true ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
				$class[] = 'num';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby === $orderby ) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$cast = $this->cast_column( $column_key );

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order', 'cast' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'";
			}

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}// End foreach().
	}
}
