<?php
class WPS_EAV_Revisions {
	public static $created_revisions = array();
	public function __construct() {
		add_action( 'save_post', array( $this, 'save_post' ), 100, 2 );
		add_action( 'wp_restore_post_revision', array( $this, 'wp_restore_post_revision' ), 10, 2 );
		add_filter( '_wp_post_revision_fields', array( $this, 'wp_post_revision_fields' ), 10, 2 );
	}
	public function save_post( $post_id, $post ) {
		if ( false === wp_is_post_revision( $post_id ) ) {
			if ( ( ! isset( self::$created_revisions[ $post_id ] ) || true !== self::$created_revisions[ $post_id ] ) && post_type_supports( $post->post_type, 'revisions' ) ) {
				self::$created_revisions[ wp_is_post_revision( $post_id ) ] = true;
				add_filter( 'wp_save_post_revision_post_has_changed', array( $this, 'wp_save_post_revision_post_has_changed' ), 10, 3 );
				wp_save_post_revision( $post_id );
			}
		} else {
			self::$created_revisions[ wp_is_post_revision( $post_id ) ] = true;
		}
	}
	public function wp_save_post_revision_post_has_changed( $post_has_changed, $last_revision, $post ) {
		return true;
	}
	public function wp_restore_post_revision( $post_id, $revision_id ) {
		/*
		$post     = get_post( $post_id );
		$revision = get_post( $revision_id );
		$meta = get_metadata( 'post', $revision->ID, 'foo', true );
		if ( false === $meta ) {
			delete_post_meta( $post_id, 'foo' );
		} else {
			update_post_meta( $post_id, 'foo', $meta );
		}*/
	}
	public function wp_post_revision_fields( $fields, $post ) {
		global $wpdb;
		$wpsdb_attribute = WPSHOP_DBT_ATTRIBUTE;
		$wpsdb_attribute_set = WPSHOP_DBT_ATTRIBUTE_DETAILS;
		$wpsdb_unit = WPSHOP_DBT_ATTRIBUTE_UNIT;
		$wpsdb_values_decimal = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
		$wpsdb_values_datetime = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
		$wpsdb_values_integer = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
		$wpsdb_values_varchar = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
		$wpsdb_values_text = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
		$wpsdb_values_options = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
		$datas = $wpdb->get_results( $wpdb->prepare(
			"SELECT attr.id,
			attr.code,
			attr.frontend_label,
			GROUP_CONCAT(
				IFNULL( val_dec.value,
					IFNULL( val_dat.value,
						IFNULL( val_tex.value,
							IFNULL( val_var.value,
								IFNULL( val_opt.value,
									val_int.value
								)
							)
						)
					)
				)
				SEPARATOR ', '
			) as data
			FROM wp_posts p
			LEFT JOIN {$wpsdb_attribute} attr ON attr.status = 'valid'
			LEFT JOIN {$wpsdb_values_decimal} val_dec ON val_dec.attribute_id = attr.id AND val_dec.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_datetime} val_dat ON val_dat.attribute_id = attr.id AND val_dat.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_integer} val_int ON val_int.attribute_id = attr.id AND val_int.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_text} val_tex ON val_tex.attribute_id = attr.id AND val_tex.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_varchar} val_var ON val_var.attribute_id = attr.id AND val_var.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_options} val_opt ON val_opt.attribute_id = attr.id AND val_opt.id = val_int.value
			WHERE p.ID = %d
			GROUP BY attr.code",
			$post['ID']
		), ARRAY_A );
		foreach ( $datas as $data ) {
			$fields[ $data['id'] ] = $data['frontend_label'];
			add_filter( "_wp_post_revision_field_{$data['id']}", array( $this, 'wp_post_revision_field' ), 10, 4 );
		}
		return $fields;
	}
	public function wp_post_revision_field( $value, $field, $revision, $fromto ) {
		global $wpdb;
		$wpsdb_histo = WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO;
		$wpsdb_values_options = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
		$result = '0';
		$fromto_compare = '';
		if ( ! empty( $fromto ) ) {
			$result = $wpdb->get_var( $wpdb->prepare(
				"SELECT IFNULL( val_opt.label,
					histo.value
				),
				rev.post_date
				FROM {$wpsdb_histo} histo
				LEFT JOIN {$wpsdb_values_options} val_opt ON val_opt.attribute_id = histo.attribute_id AND val_opt.id = histo.value
				LEFT JOIN (
					SELECT *
					FROM {$wpdb->posts} rev
					WHERE rev.post_type = 'revision'
     				AND rev.post_parent = %d
					AND rev.post_date > %s
					LIMIT 1
				) rev ON 1 = 1
				WHERE histo.creation_date >= %s
				AND ( rev.post_date IS NULL OR histo.creation_date < rev.post_date )
				AND histo.attribute_id = %d
				AND histo.entity_id = %d
				ORDER BY histo.creation_date DESC LIMIT 1",
				$revision->post_parent,
				$revision->post_date,
				$revision->post_date,
				$field,
				$revision->post_parent
			) );
		}
		$result = ( '0' === $result ) ? null : $result;
		return $result;
	}
}
new WPS_EAV_Revisions();
/*
OLD Version
add_meta_box('wpshop_histo_attrs', __( 'Historic attributes', 'wpshop' ), function( $post ) {
	global $wpdb;
	$limit = 40;
	$count_rows = $wpdb->prepare( 'SELECT COUNT(value_id) FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO . ' WHERE entity_id = %d', $post->ID );
	$max_page = ceil( $wpdb->get_var( $count_rows ) / $limit );
	$current_page = absint( isset( $_GET['paged_historic'] ) ? $_GET['paged_historic'] : 1 );
	$query = $wpdb->prepare('SELECT *, histo.value as brut_value FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO . ' AS histo
	LEFT JOIN ' . WPSHOP_DBT_ATTRIBUTE . ' AS attr ON histo.attribute_id = attr.id
	LEFT JOIN wp_wpshop__attribute_value_options AS opt ON histo.attribute_id = opt.attribute_id AND histo.value = opt.id
	WHERE histo.entity_id = %d ORDER BY histo.creation_date DESC LIMIT %d OFFSET %d', $post->ID, $limit, ( ( $current_page - 1 ) * $limit ) );
	$histo = $wpdb->get_results( $query );
	$histo_array = array();
	foreach ( $histo as $row ) {
		$histo_array[ $row->creation_date_value ][] = $row;
	}
	foreach ( $histo_array as $date => $values ) {
		?>
		<fieldset style="border:1px solid #eee; margin-bottom: 20px">
			<legend style="margin-left: 10px; font-weight: bold"><?php printf( __( '%s ago' ), human_time_diff( strtotime( $date ), current_time( 'timestamp' ) ) ); ?> :</legend>
			<?php foreach ( $values as $value ) { ?>
				<div style="margin-left: 10px; margin-bottom: 8px"><?php echo $value->frontend_label; ?>: <br><input type="text" value="<?php echo isset( $value->value ) ? $value->value : $value->brut_value; ?>" disabled></div>
			<?php } ?>
		</fieldset>
		<?php
	}
	echo paginate_links( array(
		'base' => '%_%',
		'format' => '?paged_historic=%#%',
		'current' => $current_page,
		'total' => $max_page,
	) );
}, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'default');*/
