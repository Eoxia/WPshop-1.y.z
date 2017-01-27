<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fichier du controleur des metaboxes pour l'administration des clients dans wpshop / Controller file for managing metaboxes into customer administration interface
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Classe du controleur des fournisseurs dans wpshop / Controller class for wpshop provider
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 */
class wps_provider_ctr {
	// WORDPRESS
	public function __construct() {
		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
		add_action( 'add_meta_boxes_' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, array( $this, 'add_meta_box' ) );
	}
	public function add_meta_box( $third ) {
		/*$is_provider = $this->read( $third->ID );
		if( isset( $is_provider ) ) {
			add_meta_box( 'provider_products', __( 'Provider\'s products' ), array( $this, 'provider_products_box' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low', $third );
		}*/
	}
	public function save_post( $post_id, $post, $update ) {
		if( wp_is_post_revision( $post_id ) || $post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS )
			return;

		$wps_provider_product = !empty( $_REQUEST['wps_provider_product'] ) ? (array)$_REQUEST['wps_provider_product'] : array();
		$is_provider = $this->read( $post_id );
		if( isset( $is_provider ) && !empty( $wps_provider_product ) ) {
			foreach( $wps_provider_product as $product_id => $product ) {
				switch( $product['special_provider'] ) {
					case 'update':
						$product['ID'] = $product_id;
						$this->update_product( $product );
						break;
					case 'delete':
						$this->delete_product( $product_id );
						break;
				}
			}
		}
	}
	public function provider_products_box( $third ) {
		global $wpdb;
		foreach( $this->read_product( $third->ID ) as $post ) {
			$attributes_display = '';
			$product_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
			$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE . ' WHERE entity_id = %d AND is_used_in_quick_add_form = %s AND status = %s', $product_entity_id, 'yes', 'valid' );
			$attributes = $wpdb->get_results( $query, ARRAY_A );
			if( !empty($attributes) ) {
				foreach( $attributes as $attribute_id => $att_def ) {
					$current_value = wpshop_attributes::getAttributeValueForEntityInSet( $att_def['data_type'], $att_def['id'], $product_entity_id, $post->ID );
					$output_specs =  array(
						'page_code' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
						'element_identifier' => $post->ID,
						'field_id' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'_'.$post->ID. '_',
						'current_value' => ( !empty($current_value->value) ? $current_value->value : '' )
					);
					$att = wpshop_attributes::display_attribute( $att_def['code'], 'admin', $output_specs );
					ob_start();
					require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/', 'backend', 'provider_products/product_provider_attribute' ) );
					$attributes_display .= ob_get_contents();
					ob_end_clean();
				}
			}
			require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/', 'backend', 'provider_products/product_provider' ) );
		}
	}
	// PROVIDER
	private $id_attr = null;
	private $value_attr = null;
	private $entity_attr = null;
	private function get_entity_attr() {
		$this->entity_attr = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
	}
	private function get_id_attr() {
		if( is_null( $this->id_attr ) ) {
			global $wpdb;
			$query = $wpdb->prepare( 'SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE code = %s AND entity_id = %d', 'is_provider', $this->get_entity_attr() );
			$this->id_attr = $wpdb->get_var( $query );
		}
		return $this->id_attr;
	}
	private function get_value_attr() {
		if( is_null( $this->value_attr ) ) {
			global $wpdb;
			$query = $wpdb->prepare( 'SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE attribute_id = %d AND LOWER( value ) = %s', $this->get_id_attr(), strtolower( __( 'yes', 'wpshop' ) ) );
			$this->value_attr = $wpdb->get_var( $query );
		}
		return $this->value_attr;
	}
	public function read( $args = array() ) {
		global $wpdb;
		if( is_array( $args ) ) {
			$args['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
			if( isset( $args['posts_per_page'] ) ) {
				$args['posts_per_page'] = -1;
			}
			$query = $wpdb->prepare( 'SELECT entity_id FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . ' WHERE attribute_id = %d AND value = %d', $this->get_id_attr(), $this->get_value_attr() );
			$args['post__in'] = $wpdb->get_col( $query );
			$query = new WP_Query( $args );
			$return = $query->get_posts();
		} elseif( is_numeric( $args ) ) {
			$query = $wpdb->prepare( 'SELECT value_id FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . ' WHERE entity_id = %d AND attribute_id = %d AND value = %d', $args, $this->get_id_attr(), $this->get_value_attr() );
			$id = $wpdb->get_var( $query );
			$exist = (bool) isset( $id );
			if( $exist ) {
				$return = get_post( $args );
			} else {
				$return = null;
			}
		}
		return $return;
	}
	// CRUD PROVIDER'S PRODUCTS
	public function create_product( $id_provider = 0, $product ) {
		if( is_array( $product ) || is_object( $product ) ) {
			if( is_array( $product ) ) {
				$data_to_save = $product[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute'];
				unset( $product[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute'] );
			} elseif( is_object( $product ) ) {
				$var = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute';
				$data_to_save = $product->$var;
				unset( $product->$var );
			}
			$product_class = new wpshop_products();
			$data_to_save = apply_filters( 'create_provider_product_data_to_save', array( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute' => $data_to_save, 'action' => 'autosave' ) );
			$product['post_status'] = 'pending';
			$product['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
			if( !isset( $product['post_title'] ) ) {
				$product['post_title'] = __( 'New product', 'wpshop' );
			}
			$product_id = wp_insert_post( $product );
			if( $id_provider != 0 ) {
				update_post_meta( $product_id, WPSHOP_PRODUCT_PROVIDER, array( (string) $id_provider ) );
			}
			$data_to_save['post_ID'] = $product_id;
			$product_class->save_product_custom_informations( $product_id, $data_to_save );
			return $product_id;
		}
	}
	public function read_product( $id_provider = 0, $args = array() ) {
		$args['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
		$args['post_status'] = array( 'pending', 'publish', 'draft', 'private' );
		$args['meta_key'] = WPSHOP_PRODUCT_PROVIDER;
		if( !isset( $args['posts_per_page'] ) ) {
			$args['posts_per_page'] = -1;
		}
		if( is_numeric( $id_provider ) && $id_provider != 0 ) {
			$args['meta_value'] = serialize( (string) $id_provider );
			$args['meta_compare'] = 'LIKE';
		}
		$query = new WP_Query( $args );
		return $query->get_posts();
	}
	public function update_product( $product ) {
		if( is_array( $product ) || is_object( $product ) ) {
			if( is_array( $product ) ) {
				$product_id = $product['ID'];
				$data_to_save = $product[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute'];
				unset( $product[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute'] );
			} elseif( is_object( $product ) ) {
				$product_id = $product->ID;
				$var = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute';
				$data_to_save = $product->$var;
				unset( $product->$var );
			}
			$product_class = new wpshop_products();
			$data_to_save = apply_filters( 'update_provider_product_data_to_save', array( 'post_ID' => $product_id, 'action' => 'autosave', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute' => $data_to_save ) );
			$product_class->save_product_custom_informations( $product_id, $data_to_save );
			wp_update_post( $product );
		}
	}
	public function delete_product( $args ) {
		if( is_numeric( $args ) || is_object( $args ) ) {
			wp_trash_post( $args );
		}
	}
}
