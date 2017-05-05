<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
}
new mass_interface3();
class mass_interface3 {
	private $post_type_object;
	private $wp_list_table;
	public $show_columns = array(
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
		add_action( 'init', array( $this, 'mass_init' ) );
		add_action( 'wp_ajax_wps_mass_3_new', array( $this, 'ajax_new' ) );
	}
	public function mass_init() {
		$hook = add_submenu_page( 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, __( 'Mass product edit 3', 'wpshop' ), __( 'Mass product edit 3', 'wpshop' ), 'manage_options', 'mass_edit_interface3', array( $this, 'mass_interface' ) );
		add_action( "load-{$hook}", array( $this, 'mass_interface_screen_option' ) );
		add_action( "admin_print_scripts-{$hook}", array( $this, 'ajax_scripts' ) );
	}
	public function mass_interface() {
		$this->wp_list_table->prepare_items( $this->exclude_attribute_codes );
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
		$this->wp_list_table->screen->render_screen_meta();
		?>
		<div class="wrap">
		<h1 class="wp-heading-inline"><?php
		echo esc_html( $this->post_type_object->labels->name );
		?></h1>

		<?php
		if ( current_user_can( $this->post_type_object->cap->create_posts ) ) {
			echo ' <a href="' . esc_url( admin_url( $post_new_file ) ) . '" class="page-title-action" onclick="addPost(event, this)">' . esc_html( $this->post_type_object->labels->add_new ) . '</a>';
		}
		?>
		<form id="posts-filter" method="get">
		<?php $this->wp_list_table->display(); ?>
		<table style="display:none;">
			<tbody id="posts-add">
				<tr id="inline-edit" class="inline-edit-row inline-edit-row-<?php echo "post inline-edit-{$this->post_type_object->name} quick-edit-row quick-edit-row-post inline-edit-{$this->post_type_object->name}"; ?>" style="display: none">
					<td colspan="<?php echo $this->wp_list_table->get_column_count(); ?>" class="colspanchange">
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
		</form>
		</div>
		<?php
	}
	public function mass_interface_screen_option() {
		$this->post_type_object = get_post_type_object( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$this->wp_list_table = new WPS_Mass_List_Table( array(
			'screen' => $this->post_type_object->name,
		) );
		$this->wp_list_table->show_columns = $this->show_columns;
		$this->wp_list_table->screen->set_screen_reader_content( array(
			'heading_views'      => $this->post_type_object->labels->filter_items_list,
			'heading_pagination' => $this->post_type_object->labels->items_list_navigation,
			'heading_list'       => $this->post_type_object->labels->items_list,
		) );
		$this->wp_list_table->screen->add_option( 'per_page', array( 'default' => 20, 'option' => "edit_{$this->post_type_object->name}_per_page" ) );
	}
	public function hidden_columns( $hidden, $screen ) {
		if( $screen == $this->wp_list_table->screen ) {
			$hidden = array_diff( array_flip( $this->wp_list_table->get_columns() ), $this->show_columns );
		}
		return $hidden;
	}
	public function ajax_scripts() {
		wp_enqueue_script(
			'mass_interface3-ajax', plugin_dir_url( __FILE__ ).'interface3.js',
			array( 'jquery' ),
			true
		);
	}
	public function ajax_new() {
		$new_product_id = wp_insert_post( array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'post_status' => 'publish',
				'post_title' => $_POST['title'],
		) );
		if ( !empty( $new_product_id ) ) {
			$product_attribute_set_id = ( !empty( $_POST['attributes_set'] ) ) ? intval( $_POST['attributes_set'] ) : 1;
			update_post_meta( $new_product_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id', $product_attribute_set_id );
		} else {
			wp_die( 1 );
		}
		$this->post_type_object = get_post_type_object( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$this->wp_list_table = new WPS_Mass_List_Table( array(
			'screen' => $this->post_type_object->name,
		) );
		$this->wp_list_table->show_columns = $this->show_columns;
		$data = $this->wp_list_table->request( $this->exclude_attribute_codes, $new_product_id );
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
		ob_start();
		$this->wp_list_table->single_row( $data[0] );
		wp_send_json_success( array( 'row' => ob_get_clean() ) );
	}
}
class WPS_Mass_List_Table extends WP_List_Table {
	public static $wpsdb_values_options = array();
	public $columns_items = array();
	public $show_columns = array();
	public $screen;
	public function __construct( $args ) {
		parent::__construct( array(
			'plural'	=> 'posts',
			'ajax'		=> true,
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'title'    => __( 'Title' ),
		);
		foreach ( $this->columns_items as $column => $data_column ) {
			$columns[ $column ] = $data_column['name'];
		}
		return $columns;
	}
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'title'    => array( 'title', false ),
		);
		foreach ( $this->columns_items as $column => $data_column ) {
			$sortable_columns[ $column ] = array( $data_column['code'], false );
		}
		return $sortable_columns;
	}
	public function column_default( $item, $column_name ) {
		if ( isset( $this->columns_items[ $column_name ] ) && is_callable( array( $this, "column_data_{$this->columns_items[ $column_name ]['type']}" ) ) ) {
			return call_user_func( array( $this, "column_data_{$this->columns_items[ $column_name ]['type']}" ),
				$this->columns_items[ $column_name ]['id'],
				$this->columns_items[ $column_name ]['code'],
				$item
			);
		}
		return print_r( $item[ $column_name ], true );
	}
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['ID']
		);
	}
	public function column_title( $item ) {
		return sprintf(
			'<a class="row-title" href="%s" aria-label="%s">%s</a>',
			get_edit_post_link( $item['ID'] ),
			esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $item['title'] ) ),
			$item['title']
		);
	}
	public function column_data_text( $attribute_id, $attribute_code, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		return sprintf(
			'<input type="text" name="%1$s[%2$s]" value="%3$s">%4$s',
			$attribute_code,
			$item['ID'],
			$value,
			$unit
		);
	}
	public function column_data_select( $attribute_id, $attribute_code, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		$select_items = array();
		foreach ( $this->get_select_items_option( $attribute_id ) as $item ) {
			$select_items[] = "<option value=\"{$item['id']}\"" . selected( $value, $item['id'], false ) . ">{$item['label']}</option>";
		}
		$select_items = implode( '', $select_items );
		return "<select name='{$attribute_code}[{$item['ID']}]'>{$select_items}</select>{$unit}";
	}
	public function request( $exclude_attribute_codes, $id_post = null ) {
		global $wpdb;
		$per_page = $this->get_items_per_page( 'edit_' . $this->screen->post_type . '_per_page' );
		$exclude_states = get_post_stati( array(
			'show_in_admin_all_list' => false,
		) );
		$exclude_states = implode( "','", $exclude_states );
		$orderby = isset( $_REQUEST['orderby'] ) ? esc_sql( $_REQUEST['orderby'] ) : 'p.post_date';
		$order = isset( $_REQUEST['order'] )  ? esc_sql( $_REQUEST['order'] ) : 'DESC';
		$exclude_attribute_codes = implode( "','", $exclude_attribute_codes );
		$entity_id = wpshop_entities::get_entity_identifier_from_code( $this->screen->post_type );
		$extra = '';
		if( !is_null( $id_post ) ) {
			$id_post = intval( $id_post );
			$extra = "AND p.ID = {$id_post}";
		}
		$wpsdb_attribute = WPSHOP_DBT_ATTRIBUTE;
		$wpsdb_unit = WPSHOP_DBT_ATTRIBUTE_UNIT;
		$wpsdb_values_decimal = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
		$wpsdb_values_datetime = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
		$wpsdb_values_integer = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
		$wpsdb_values_varchar = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
		$wpsdb_values_text = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
		$datas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS
				p.ID,
				p.post_title as title,
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
						{$wpsdb_attribute}.frontend_input
					) SEPARATOR ';'
				) as data
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpsdb_attribute} ON {$wpsdb_attribute}.status = 'valid' AND {$wpsdb_attribute}.entity_id = %d AND {$wpsdb_attribute}.code NOT IN ( '{$exclude_attribute_codes}' )
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
				WHERE p.post_status NOT IN ( '{$exclude_states}' )
				AND p.post_type = %s
				{$extra}
				GROUP BY p.ID
				ORDER BY {$orderby} {$order}
				LIMIT %d, %d",
				$entity_id,
				$this->screen->post_type,
				( $this->get_pagenum() -1 ) * $per_page,
				$per_page
			),
			ARRAY_A
		);
		return array_map( array( $this, 'data_reorganize' ), $datas );
	}
	public function prepare_items( $exclude_attribute_codes, $id_post = null ) {
		global $wpdb;
		$per_page = $this->get_items_per_page( 'edit_' . $this->screen->post_type . '_per_page' );
		$this->items = $this->request( $exclude_attribute_codes );
		$total_items = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, array_diff( array_flip( $this->get_columns() ), $this->show_columns ), $sortable );
		$this->set_pagination_args( array(
			'total_items'	=> $total_items,
			'per_page'		=> $per_page,
			'total_pages'	=> ceil( $total_items / $per_page ),
			'orderby'		=> $orderby,
			'order'			=> $order,
		) );
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
				);
			}
			if ( 'yes' === $value[4] ) {
				$item[ $value[1] ] = array(
					'value' => $value[3],
					'unit' => $value[5],
				);
			} else {
				$item[ $value[1] ] = $value[3];
			}
		}
		unset( $item['data'] );
		return $item;
	}
	public function get_select_items_option( $attribute_id ) {
		if ( ! isset( self::$wpsdb_values_options[ $attribute_id ] ) ) {
			global $wpdb;
			$wpsdb_values_options = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
			self::$wpsdb_values_options[ $attribute_id ] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpsdb_values_options} WHERE attribute_id = %d ORDER BY position", $attribute_id ), ARRAY_A );
		}
		return self::$wpsdb_values_options[ $attribute_id ];
	}
}
?>
