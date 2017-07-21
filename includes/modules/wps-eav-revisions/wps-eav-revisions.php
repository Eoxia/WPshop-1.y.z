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
	private function product_attributes( $post_id ) {
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
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT attr.id,
			attr.code,
			attr.frontend_label,
			attr.data_type,
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
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpsdb_attribute} attr ON attr.status = 'valid'
			LEFT JOIN {$wpsdb_values_decimal} val_dec ON val_dec.attribute_id = attr.id AND val_dec.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_datetime} val_dat ON val_dat.attribute_id = attr.id AND val_dat.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_integer} val_int ON val_int.attribute_id = attr.id AND val_int.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_text} val_tex ON val_tex.attribute_id = attr.id AND val_tex.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_varchar} val_var ON val_var.attribute_id = attr.id AND val_var.entity_id = p.ID
			LEFT JOIN {$wpsdb_values_options} val_opt ON val_opt.attribute_id = attr.id AND val_opt.id = val_int.value
			WHERE p.ID = %d
			GROUP BY attr.code",
			$post_id
		), ARRAY_A );
	}
	public function wp_restore_post_revision( $post_id, $revision_id ) {
		$product_class = new wpshop_products();
		$attrs = array();
		foreach ( $this->product_attributes( $post_id ) as $attr ) {
			$attrs[ $attr['data_type'] ][ $attr['code'] ] = $this->wp_post_revision_field( '', $attr['id'], get_post( $revision_id ), '', false );
		}
		$product_class->save_product_custom_informations(
			$post_id,
			array(
				'post_ID' => $post_id,
				'product_id' => $post_id,
				'user_ID' => get_current_user_id(),
				'action' => 'editpost',
				'wpshop_product_attribute' => $attrs,
			)
		);
	}
	public function wp_post_revision_fields( $fields, $post ) {
		foreach ( $this->product_attributes( $post['ID'] ) as $data ) {
			$fields[ $data['id'] ] = $data['frontend_label'];
			add_filter( "_wp_post_revision_field_{$data['id']}", array( $this, 'wp_post_revision_field' ), 10, 4 );
		}
		return $fields;
	}
	public function wp_post_revision_field( $value, $field, $revision, $fromto, $option_label = true ) {
		global $wpdb;
		$wpsdb_histo = WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO;
		$wpsdb_values_options = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
		$result = '0';
		if ( true === $option_label ) {
			$select = 'IFNULL( val_opt.label,
				histo.value
			)';
		} else {
			$select = 'histo.value';
		}
		$result = $wpdb->get_var( $wpdb->prepare(
			"SELECT {$select}
			FROM {$wpsdb_histo} histo
			LEFT JOIN {$wpsdb_values_options} val_opt ON val_opt.attribute_id = histo.attribute_id AND val_opt.id = histo.value
			LEFT JOIN (
				SELECT *
				FROM {$wpdb->posts} rev
				WHERE rev.post_type = 'revision'
 				AND rev.post_parent = %d
				AND rev.post_date > %s
				ORDER BY rev.ID ASC LIMIT 1
			) rev ON 1 = 1
			WHERE histo.creation_date >= %s
			AND ( rev.post_date IS NULL OR histo.creation_date < rev.post_date )
			AND histo.attribute_id = %d
			AND histo.entity_id = %d
			ORDER BY histo.value_id DESC LIMIT 1",
			$revision->post_parent,
			$revision->post_date,
			$revision->post_date,
			$field,
			$revision->post_parent
		) );
		$result = ( '0' === $result ) ? null : $result;
		return $result;
	}
}
new WPS_EAV_Revisions();
