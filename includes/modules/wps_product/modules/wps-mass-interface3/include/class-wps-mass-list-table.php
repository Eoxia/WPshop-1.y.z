<?php
/**
 * File class of WPS_Mass_List_Table.
 *
 * @package wps-mass-interface3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	exit( 'class-wp-list-table.php not found.' );
}
/**
 *  Custom WP_List_Table to edit attributes from WPShop EAV.
 */
class WPS_Mass_List_Table extends WP_List_Table {
	/**
	 * Static variable to keep values options from getted attributes.
	 *
	 * @var array
	 */
	public static $wpsdb_values_options = array();
	/**
	 * Stock of getted items from request.
	 *
	 * @var array
	 */
	public $columns_items = array();
	/**
	 * Columns to show
	 *
	 * @var array
	 */
	public $show_columns = array();
	/**
	 * Current screen.
	 *
	 * @var WP_Screen
	 */
	public $screen;
	/**
	 * Product entity. See wpshop_entities::get_entity_identifier_from_code().
	 *
	 * @var int
	 */
	public $entity_id;
	/**
	 * Attributes codes exception in query.
	 *
	 * @var array
	 */
	public $exclude_attribute_codes = array();
	/**
	 * Current view. Attribute group.
	 *
	 * @var int
	 */
	public $current_view = null;
	/**
	 * Instance of each views count.
	 *
	 * @var array
	 */
	private $_views = null;
	/**
	 * Requested post types.
	 *
	 * @var array
	 */
	private $_post_types;
	/**
	 * Construct with args value like WP_List_Table. See WP_List_Table::__construct().
	 *
	 * @method __construct
	 * @param  array $args New key : exclude_attribute_codes.
	 */
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
		$this->entity_id = (int) wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
		$this->_post_types = array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION );
	}
	/**
	 * List columns.
	 *
	 * @method get_columns
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title' ),
			'thumbnail' => __( 'Thumbnail' ),
		);
		foreach ( $this->request_items_columns() as $column => $data_column ) {
			if ( ! empty( $column ) && ! empty( $data_column ) ) {
				$columns[ $column ] = $data_column['name'];
			}
		}
		$columns['date'] = __( 'Date' );
		return $columns;
	}
	/**
	 * List sortable columns.
	 *
	 * @method get_sortable_columns
	 * @return array
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'title'     => array( 'title', false ),
			'thumbnail' => array( 'thumbnail', false ),
			'date' => array( 'p.post_date', false ),
		);
		foreach ( $this->request_items_columns() as $column => $data_column ) {
			$sortable_columns[ $column ] = array( $data_column['code'], false );
		}
		return $sortable_columns;
	}
	/**
	 * Default display of content column.
	 *
	 * @method column_default
	 * @param  array  $item        Result of sql query.
	 * @param  string $column_name Current column.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( isset( $this->columns_items[ $column_name ] ) && is_callable( array( $this, "column_data_{$this->columns_items[ $column_name ]['type']}" ) ) ) {
			$callable_ext = str_replace( '-', '_', $this->columns_items[ $column_name ]['type'] );
			if ( ! method_exists( $this, "column_data_{$callable_ext}" ) ) {
				$callable_ext = 'text';
			}
			$callable = array( $this, "column_data_{$callable_ext}" );
			return call_user_func(
				$callable,
				(int) $this->columns_items[ $column_name ]['id'],
				$this->columns_items[ $column_name ]['code'],
				$this->columns_items[ $column_name ]['data'],
				$item
			);
		}
		return print_r( $item[ $column_name ], true );
	}
	/**
	 * Column content for checkbox.
	 *
	 * @method column_cb
	 * @param  array $item Result of sql query.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="cb[]" value="%d" />',
			$item['ID']
		);
	}
	/**
	 * Column content for thumbnail.
	 *
	 * @method column_thumbnail
	 * @param  array $item Result of sql query.
	 * @return string
	 */
	public function column_thumbnail( $item ) {
		$thumbnail_id = '';
		$link_content = get_the_post_thumbnail( $item['ID'], array( 25, 25 ) );
		if ( ! empty( $link_content ) ) {
			$link_content = "<span class=\"img\">{$link_content}</span>";
			$thumbnail_id = get_post_thumbnail_id( $item['ID'] );
		}
		$popup_title = __( 'Choose Image' );
		return sprintf(
			'<input type="hidden" name="row_%1$s[thumbnail]" value="%2$s"><a href="#thumbnail" data-media-title="%3$s">%4$s<span class="text">%3$s</span></a>',
			$item['ID'],
			$thumbnail_id,
			__( 'Choose Image' ),
			$link_content
		);
	}
	public function column_date( $item ) {
		global $mode;

		if ( '0000-00-00 00:00:00' === $item['pdate'] ) {
			$t_time = $h_time = __( 'Unpublished' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
			$m_time = $item['pdate'];
			$time = get_post_time( 'G', true, $item['ID'] );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
			}
		}

		if ( 'publish' === $item['status'] ) {
			_e( 'Published' );
		} elseif ( 'future' === $item['status'] ) {
			if ( $time_diff > 0 ) {
				echo '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
			} else {
				_e( 'Scheduled' );
			}
		} else {
			_e( 'Last Modified' );
		}
		echo '<br />';
		echo '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}
	/**
	 * Column content for title.
	 *
	 * @method column_title
	 * @param  array $item Result of sql query.
	 * @return string
	 */
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
				// translators: WordPress translate.
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
	/**
	 * Column default content for EAV data. (Not used)
	 *
	 * @method column_data_default
	 * @param  int    $attribute_id   Attribute ID.
	 * @param  string $attribute_code Attribute code.
	 * @param  string $attribute_data Attribute type ( ex:Varchar, Integer, Datetime ... ).
	 * @param  array  $item           Result of sql query.
	 * @return string
	 */
	public function column_data_default( $attribute_id, $attribute_code, $attribute_data, $item ) {
		return 'default';
	}
	/**
	 * Column content for EAV data displayed as text (replace column default).
	 *
	 * @method column_data_text
	 * @param  int    $attribute_id   Attribute ID.
	 * @param  string $attribute_code Attribute code.
	 * @param  string $attribute_data Attribute type ( ex:Varchar, Integer, Datetime ... ).
	 * @param  array  $item           Result of sql query.
	 * @return string
	 */
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
	/**
	 * Column content for EAV data displayed as select.
	 *
	 * @method column_data_select
	 * @param  int    $attribute_id   Attribute ID.
	 * @param  string $attribute_code Attribute code.
	 * @param  string $attribute_data Attribute type ( ex:Varchar, Integer, Datetime ... ).
	 * @param  array  $item           Result of sql query.
	 * @return string
	 */
	public function column_data_select( $attribute_id, $attribute_code, $attribute_data, $item ) {
		$unit = '';
		if ( is_array( $item[ $attribute_code ] ) && isset( $item[ $attribute_code ]['unit'] ) ) {
			$unit = ' ' . $item[ $attribute_code ]['unit'];
			$value = $item[ $attribute_code ]['value'];
		} else {
			$value = $item[ $attribute_code ];
		}
		$has_selected = false;
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
			$has_selected = empty( $selected ) ? $has_selected : true;
			$select_items[] = "<option value=\"{$option_item['id']}\"{$selected}>{$option_item['label']}</option>";
		}
		if ( ! $has_selected ) {
			array_unshift( $select_items, '<option selected disabled>' . __( 'None' ) . '</option>' );
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
	/**
	 * Column content for EAV data displayed as textarea.
	 *
	 * @method column_data_textarea
	 * @param  int    $attribute_id   Attribute ID.
	 * @param  string $attribute_code Attribute code.
	 * @param  string $attribute_data Attribute type ( ex:Varchar, Integer, Datetime ... ).
	 * @param  array  $item           Result of sql query.
	 * @return string
	 */
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
	/**
	 * Column content for EAV data displayed as multiple values.
	 *
	 * @method column_data_multiple_select
	 * @param  int    $attribute_id   Attribute ID.
	 * @param  string $attribute_code Attribute code.
	 * @param  string $attribute_data Attribute type ( ex:Varchar, Integer, Datetime ... ).
	 * @param  array  $item           Result of sql query.
	 * @return string
	 */
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
	/**
	 * Query function.
	 *
	 * @method request
	 * @param  Mixed $id_post Null = all / Int = single.
	 * @return array Return all values with ARRAY_A.
	 */
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
		$post_types = implode( "','", $this->_post_types );
		$orderby = isset( $_REQUEST['orderby'] ) ? esc_sql( $_REQUEST['orderby'] ) : 'p.post_date'; // WPCS: CSRF ok.
		$order = isset( $_REQUEST['order'] ) ? esc_sql( strtoupper( $_REQUEST['order'] ) ) : 'DESC'; // WPCS: CSRF ok.
		$cast = isset( $_REQUEST['cast'] ) ? esc_sql( $_REQUEST['cast'] ) : ''; // WPCS: CSRF ok.
		$cast = strtoupper( $cast );
		$s = isset( $_REQUEST['s'] ) ? esc_sql( $_REQUEST['s'] ) : ''; // WPCS: CSRF ok.
		$exclude_attribute_codes = implode( "','", $this->exclude_attribute_codes );
		$items_count = $wpdb->prepare( "SELECT FOUND_ROWS() FROM {$wpdb->posts} WHERE 1 = %d", 1 );
		$true = true;
		if ( $true ) { // FOUND_ROWS incompatibilities ?
			$items_count = $wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} AS PM ON PM.post_id = p.ID AND PM.meta_key = '_wpshop_product_attribute_set_id' AND PM.meta_value LIKE %s
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
		if ( ! in_array( $orderby, apply_filters( 'wps_mass_list_custom_orderby', array( 'title', 'ID', 'thumbnail', 'p.post_date' ) ), true ) ) {
			$extra_select = "SELECT GROUP_CONCAT( IFNULL( val_dec1.value,
				IFNULL( val_dat1.value,
					IFNULL( val_tex1.value,
						IFNULL( val_var1.value,
							IFNULL( val_opt1.value,
								val_int1.value
							)
						)
					)
				)
			) SEPARATOR ' ' )
			FROM {$wpdb->posts} p1
			LEFT JOIN {$wpsdb_attribute} attr1 ON attr1.status = 'valid' AND attr1.code = '{$orderby}'
			LEFT JOIN {$wpsdb_values_decimal} val_dec1 ON val_dec1.attribute_id = attr1.id AND val_dec1.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_datetime} val_dat1 ON val_dat1.attribute_id = attr1.id AND val_dat1.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_integer} val_int1 ON val_int1.attribute_id = attr1.id AND val_int1.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_text} val_tex1 ON val_tex1.attribute_id = attr1.id AND val_tex1.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_varchar} val_var1 ON val_var1.attribute_id = attr1.id AND val_var1.entity_id = p1.ID
			LEFT JOIN {$wpsdb_values_options} val_opt1 ON val_opt1.attribute_id = attr1.id AND val_opt1.id = val_int1.value
			WHERE p1.ID = p.ID";
			$extra_select = "( {$extra_select} )";
			if ( ! empty( $cast ) ) {
				$extra_select = "CAST( {$extra_select} AS {$cast} )";
			}
			$extra_select = ",
			{$extra_select} AS {$orderby}";
		}
		if ( 'thumbnail' === $orderby ) {
			$ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT CAST( pm.post_id AS SIGNED INTEGER ) as col
				FROM {$wpdb->postmeta} pm
				JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_type IN ( '{$post_types}' )
				WHERE pm.meta_key = %s
				AND pm.meta_value != %d
				ORDER BY pm.meta_value {$order}",
				'_thumbnail_id',
				0
			) );
			$ids = implode( ', ', $ids );
			$orderby = "FIELD( p.ID, {$ids} )";
		}
		$orderby = apply_filters( 'wps_mass_list_custom_orderby_query', $orderby );
		$extra = "GROUP BY p.ID
		ORDER BY {$orderby} {$order}
		LIMIT %d, %d";
		if ( ! is_null( $id_post ) ) {
			$id_post = intval( $id_post );
			$extra = "AND p.ID = {$id_post}";
			$s = '';
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
				p.post_date as pdate,
				GROUP_CONCAT(
					CONCAT(
						attr.id, '&amp;',
						attr.code, '&amp;',
						attr.frontend_label, '&amp;',
						CONCAT(
							IFNULL( val_dec.value, '' ),
							IFNULL( val_dat.value, '' ),
							IFNULL( val_int.value, '' ),
							IFNULL( val_tex.value, '' ),
							IFNULL( val_var.value, '' )
						), '&amp;',
						attr.is_requiring_unit, '&amp;',
						IFNULL( unit.unit, '' ), '&amp;',
						attr.backend_input, '&amp;',
						attr.data_type
					) SEPARATOR '&data;'
				) as data{$extra_select}
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} postmeta ON postmeta.post_id = p.ID AND postmeta.meta_key = %s AND postmeta.meta_value = %d
				LEFT JOIN {$wpsdb_attribute_set} attr_set ON attr_set.status = 'valid' AND attr_set.entity_type_id = %d AND attr_set.attribute_set_id = %d
				LEFT JOIN {$wpsdb_attribute} attr ON attr.status = 'valid' AND attr.entity_id = %d AND attr.code NOT IN ( '{$exclude_attribute_codes}' ) AND attr.id = attr_set.attribute_id
				LEFT JOIN {$wpsdb_values_decimal} val_dec ON val_dec.attribute_id = attr.id AND val_dec.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_datetime} val_dat ON val_dat.attribute_id = attr.id AND val_dat.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_integer} val_int ON val_int.attribute_id = attr.id AND val_int.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_text} val_tex ON val_tex.attribute_id = attr.id AND val_tex.entity_id = p.ID
				LEFT JOIN {$wpsdb_values_varchar} val_var ON val_var.attribute_id = attr.id AND val_var.entity_id = p.ID
				LEFT JOIN {$wpsdb_unit} unit ON (
					unit.id = val_dec.unit_id
					OR unit.id = val_dat.unit_id
					OR unit.id = val_int.unit_id
					OR unit.id = val_tex.unit_id
					OR unit.id = val_var.unit_id
				)
				WHERE p.post_status IN ( '{$include_states}' )
				AND p.post_type IN ( '{$post_types}' )
				AND p.post_title LIKE %s
				{$extra}",
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
	/**
	 * Main function to call before display WP_List_Table. See parent class.
	 *
	 * @method prepare_items
	 * @return void Same results as request() but id as key.
	 */
	public function prepare_items() {
		foreach ( $this->request() as $item ) {
			$this->items[ $item['ID'] ] = $item;
		}
	}
	/**
	 * Set cast sort as case.
	 *
	 * @method cast_column
	 * @param  string $column_key Current column.
	 * @return string
	 */
	public function cast_column( $column_key ) {
		$columns_items = $this->request_items_columns();
		if ( isset( $columns_items[ $column_key ] ) ) {
			$cast = $columns_items[ $column_key ]['data'];
			if ( in_array( $cast, array( 'varchar', 'text' ), true ) ) {
				$cast = 'char';
			}
			if ( 'tx_tva' === $column_key ) {
				return 'decimal';
			}
			if ( 'integer' === $cast && 'select' === $columns_items[ $column_key ]['type'] ) {
				return null;
			}
			if ( 'integer' === $cast && 'multiple-select' === $columns_items[ $column_key ]['type'] ) {
				return null;
			}
			return $cast;
		}
		return null;
	}
	/**
	 * Reorganize item for compatibility.
	 *
	 * @method data_reorganize
	 * @param  array $item Result of sql query.
	 * @return array Item reorganized.
	 */
	public function data_reorganize( $item ) {
		$values = explode( '&data;', $item['data'] );
		foreach ( $values as $value ) {
			$value = explode( '&amp;', $value );
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
					} elseif ( $item[ $value[1] ] !== $value[3] ) {
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
	/**
	 * Get values for select display.
	 *
	 * @method get_select_items_option
	 * @param  int $attribute_id Attribute ID.
	 * @return array
	 */
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
	/**
	 * Get all views available.
	 *
	 * @method request_views
	 * @return array
	 */
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
			$post_types = implode( "','", $this->_post_types );
			$this->_views = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT s.id, name, slug, default_set, COUNT(p.ID) AS count
					FROM {$wpsdb_sets} s
					JOIN {$wpdb->postmeta} pm ON meta_key = %s AND id = meta_value
					JOIN {$wpdb->posts} p ON p.ID = post_id AND post_status IN ( '{$include_states}' ) AND post_type IN ( '{$post_types}' )
					WHERE entity_id = %d
					AND status = %s
					GROUP BY id",
					WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY,
					$this->entity_id,
					'valid'
				),
				ARRAY_A
			);
		}
		return $this->_views;
	}
	/**
	 * Get current view. If empty, try recover default_set.
	 *
	 * @method request_current_view
	 * @return [type]               [description]
	 */
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
	/**
	 * Request attributes for current view.
	 *
	 * @method request_items_columns
	 * @return array See :754 order columns.
	 */
	public function request_items_columns() {
		if ( empty( $this->columns_items ) ) {
			global $wpdb;
			$wpsdb_attribute = WPSHOP_DBT_ATTRIBUTE;
			$wpsdb_attribute_set = WPSHOP_DBT_ATTRIBUTE_DETAILS;
			$exclude_attribute_codes = implode( "','", $this->exclude_attribute_codes );
			foreach ( $wpdb->get_results(
				$wpdb->prepare(
					"SELECT attr.id, attr.code, attr.frontend_label AS name, attr.backend_input AS type, attr.data_type AS data
					FROM {$wpsdb_attribute} attr
					LEFT JOIN {$wpsdb_attribute_set} attr_set ON attr_set.status = 'valid' AND attr_set.entity_type_id = %d AND attr_set.attribute_set_id = %d
					WHERE attr.status = 'valid'
					AND attr.entity_id = %d
					AND attr.code NOT IN ( '{$exclude_attribute_codes}' )
					AND attr.id = attr_set.attribute_id",
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
	/**
	 * Content display views.
	 *
	 * @method get_views
	 * @return array
	 */
	public function get_views() {
		$result = array();
		foreach ( $this->request_views() as $view ) {
			$class = '';
			if ( (int) $view['id'] === (int) $this->request_current_view() ) {
				$class = ' class="current"';
			}
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			$link = add_query_arg(
				array(
					'page' =>
					str_replace(
						"{$this->screen->post_type}_page_",
						'',
						substr( $this->screen->id, 0, strpos( $this->screen->id, '_att_set_' ) ) . '_att_set_' . $view['id']
					),
				),
				$current_url
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
	/**
	 * Bulk actions display.
	 *
	 * @method bulk_actions
	 * @param  string $which Can be top or bottom.
	 * @return void Direct display.
	 */
	public function bulk_actions( $which = '' ) {
		submit_button( __( 'Save changes', 'wpshop' ), 'bulk-save', 'bulk-save', false, array(
			'data-nonce' => wp_create_nonce( 'bulk-save-mass-edit-interface-3' ),
		) );
		?><span class="spinner"></span><?php
	}
	/**
	 * Recursive function for display hierarchy. This function group & search also childs & parent.
	 * When parent is always traited, it add childs after. Or get parent before traited (on list or not) and add childs after.
	 *
	 * @method _display_row
	 * @param  int   $lvl     Represent current level of child.
	 * @param  int   $item_id Current ID row.
	 * @param  array $item    Data row.
	 * @param  array $rows    List of rows always traited.
	 * @return void
	 */
	private function _display_row( &$lvl, $item_id, $item, &$rows ) {
		if ( array_key_exists( $item_id, $rows ) ) {
			return;
		}
		if ( ! array_key_exists( $item['parent'], $this->items ) && 0 !== (int) $item['parent'] ) {
			$parent_item = $this->request( $item['parent'] );
			if ( isset( $parent_item[0] ) ) {
				$this->items[ $item['parent'] ] = $parent_item[0];
			} else {
				$this->items[ $item['parent'] ] = null;
			}
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
	/**
	 * See WP_List_Table.
	 *
	 * @method display_rows
	 * @return void Direct display.
	 */
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
	/**
	 * See WP_List_Table.
	 *
	 * @method views
	 * @return void Direct display.
	 */
	public function views() {
		parent::views();
		$current_view = $this->request_current_view();
	}
	/**
	 * Add cast parameter for sort. Copy of print_column_headers function in wp_list_table class.
	 *
	 * @method print_column_headers
	 * @param  boolean $with_id See wp_list_table::print_column_headers.
	 * @return void Direct display.
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) { // WPCS: CSRF ok.
			$current_orderby = $_GET['orderby']; // WPCS: CSRF ok.
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) { // WPCS: CSRF ok.
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

			echo "<$tag $scope $id $class>$column_display_name</$tag>"; // WPCS: XSS ok.
		}// End foreach().
	}
}
