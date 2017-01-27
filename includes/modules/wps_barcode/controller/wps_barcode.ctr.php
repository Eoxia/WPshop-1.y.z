<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_barcode {
	public function __construct() {
		global $post_ID;

		$this->install();
		add_action( 'save_post', array($this, 'insert_barcode' ), 10, 3 );
	}

	/**
	 * Install default configuration in database if not present
	 */
	public function install() {
		global $table_prefix, $wpdb;

		/*Create config informations*/
		$conf = get_option('wps_barcode');

		if ( empty($conf) ) {
			$conf['generate_barcode'] = false;
			$conf['type'] = 'internal';
			$conf['internal_client'] = '040';
			$conf['internal_provider'] = '041';
			$conf['internal_invoice_client'] = '042';
			$conf['internal_do_client'] = '043';
			$conf['internal_product'] = '044';
			$conf['internal_assets_client'] = '050';
			$conf['internal_coupons'] = '051';
			$conf['internal_invoice_provider'] = '045';
			$conf['internal_do_provider'] = '046';

			$conf['normal_country_code'] = '320';
			$conf['normal_enterprise_code'] = '0001';

			add_option('wps_barcode', $conf);

			/*Adding barcode for existing products*/
			$posts = get_posts( array('posts_per_page' => -1 ,'post_type' => 'wpshop_product'));
			foreach ( $posts as $post ) {
				$meta = get_post_meta($post->ID);

				$attr = wpshop_attributes_set::getAttributeSetDetails($meta['_wpshop_product_attribute_set_id']);
				$output_order = array();

				if ( count($attr) > 0 ) {
					if (!empty($attr) ) {
						foreach ( $attr as $product_attr_group_id =>
								$product_attr_group_detail) {
							foreach ( $product_attr_group_detail['attribut']
									as $position => $attribute_def) {
								if ( !empty($attribute_def->code)
										AND $attribute_def->code === 'barcode') {

									$types_with_barcode = array(
											WPSHOP_IDENTIFIER_PRODUCT,
											WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
											WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION,
											WPSHOP_NEWTYPE_IDENTIFIER_COUPON,
											WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
									);

									if ( !empty( $post->post_type ) &&
											in_array( $post->post_type,
											$types_with_barcode ) ) {

										$ref = '';

										if ( strlen($post->ID) < 5 ) {
											$length = 5-strlen($post->ID);
											for ($i = 0; $i < $length; $i++) {
												$ref .= '0';
											}
										}

										$ref .= strval($post->ID);

										if ( $conf['type'] === 'normal' ) {
											$array['normal'] = array('country' =>
													$conf['normal_country_code'],
													'enterprise' =>
													$conf['normal_enterprise_code'],'ID' => $ref);
										}
										else if ( $conf['type'] === 'internal' ) {
											$pDate = new DateTime($post->post_date);
											$date = $pDate->format('my');

											if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ||
													$post->post_type === WPSHOP_IDENTIFIER_PRODUCT) {
												$type = $conf['internal_product'];
											}
											else if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_ORDER) {
												$type = $conf['internal_invoice_client'];
											}
											else if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_COUPON) {
												$type = $conf['internal_coupons'];
											}
											else {
												$type = '000';
											}

											$array['internal'] = array('type' => $type,
													'date' => $date,
													'ID' => $ref);
										}

										$barcode = $this->wps_generate_barcode($array);
										$data = array('entity_type_id' => 4,
												'attribute_id' => '12',
												'entity_id' => $post->ID,
												'user_id' => $post->post_author,
												'value' => $barcode,
												'creation_date_value' => date( 'Y-m-d H:i:s' ));


										$query = $wpdb->prepare( "
											SELECT *
											FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . "
											WHERE entity_id = %d
												AND attribute_id = (
													SELECT id
													FROM " . WPSHOP_DBT_ATTRIBUTE . "
													WHERE code = %s
												)" , $post->ID, 'barcode' );
											$result = $wpdb->get_results( $query, ARRAY_A );

										if ( empty($result) ) {
											$wpdb->insert($table_prefix.
													'wpshop__attribute_value_varchar', $data);
										} elseif( empty( $result[0]['value'] ) ) {
											$wpdb->update($table_prefix.
													'wpshop__attribute_value_varchar', $data, array( 'value_id' => $result[0]['value_id'] ) );
										}
									}
								}
							}
						}
					}
				}
			}

			/*Adding barcode for existing coupon*/
			$posts = get_posts( array('posts_per_page' => -1 ,'post_type' => 'wpshop_shop_coupon') );
			foreach ( $posts as $post ) {
				$meta = get_post_meta($post->ID);
				if ( empty($meta['wpshop_coupon_barcode']) ) {
					if ( $conf['type'] === 'normal' ) {
						$array['normal'] = array('country' =>
								$conf['normal_country_code'],
								'enterprise' =>
								$conf['normal_enterprise_code'],'ID' => $ref);
					}
					else if ( $conf['type'] === 'internal' ) {
						$pDate = new DateTime($post->post_date);
						$date = $pDate->format('my');

						if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_COUPON) {
							$type = $conf['internal_coupons'];
						}
						else {
							$type = '000';
						}

						$ref = '';
						if ( strlen($post->ID) < 5 ) {
							$length = 5-strlen($post->ID);
							for ($i = 0; $i < $length; $i++) {
								$ref .= '0';
							}
						}

						$ref .= strval($post->ID);
						$array['internal'] = array('type' => $type,
								'date' => $date,
								'ID' => $ref);
					}
					$barcode = $this->wps_generate_barcode($array);
					update_post_meta($post->ID, 'wpshop_coupon_barcode', $barcode);
				}
			}
		}
	}

	/**
	 * Verifty post identifier and run a barcode generator
	 * @param string $post_ID Ident of post
	 */
	public function insert_barcode( $post_ID, $post, $update ) {
		if ( wp_is_post_revision( $post_ID ) )
			return;

		global $wpdb;

		$types_with_barcode = array(
			WPSHOP_IDENTIFIER_PRODUCT,
			WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
			WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION,
			WPSHOP_NEWTYPE_IDENTIFIER_COUPON,
			WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
		);

		/** Si c'est un type qui est dans le tableau $types_with_barcode */
		if ( !empty( $post->post_type ) && in_array( $post->post_type,
				$types_with_barcode ) ) {

			$conf = get_option('wps_barcode');
			$ref = '';

			if ( strlen($post_ID) < 5 ) {
				$length = 5-strlen($post_ID);
				for ($i = 0; $i < $length; $i++) {
					$ref .= '0';
				}
			}

			$ref .= strval($post_ID);

			if ( $conf['type'] === 'normal' ) {
				$array['normal'] = array(
					'country' 		=> $conf['normal_country_code'],
					'enterprise' 	=> $conf['normal_enterprise_code'],
					'ID' 			=> $ref,
				);
			}
			else if ( $conf['type'] === 'internal' ) {
				$pDate = new DateTime($post->post_date);
				$date = $pDate->format('my');

				if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT || $post->post_type === WPSHOP_IDENTIFIER_PRODUCT) {
					$type = $conf['internal_product'];
				}
				else if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_ORDER) {
					$type = $conf['internal_invoice_client'];
				}
				else if ($post->post_type === WPSHOP_NEWTYPE_IDENTIFIER_COUPON) {
					$type = $conf['internal_coupons'];
				}
				else {
					$type = '000';
				}

				$array['internal'] = array(
					'type' => $type,
					'date' => $date,
					'ID' => $ref
				);
			}

			//$barcode = $this->wps_generate_barcode($array);
			/** For add a product */
			$_REQUEST['wpshop_product_attribute']['varchar']['barcode'] = !empty( $_REQUEST['wpshop_product_attribute']['varchar']['barcode'] ) ? sanitize_text_field( $_REQUEST['wpshop_product_attribute']['varchar']['barcode'] ) : null;
			if( !isset($_REQUEST['wpshop_product_attribute']['varchar']['barcode']) ) {
				wpeologs_ctr::log_datas_in_files( 'wps_barcode',
				array(
					'object_id' => $post_ID,
					'message' => sprintf( __('Adding barcode: %s for %s object ID', 'wps_barcode'), '<b>'.$_REQUEST['wpshop_product_attribute']['varchar']['barcode'].'</b>', '<b>'.$post_ID.'</b>') ),
				0);
				$_REQUEST['wpshop_product_attribute']['varchar']['barcode'] = $this->wps_generate_barcode($array);
			}
			/*if (  isset($barcode) ) {
				if ($barcode !== '') {
					wpeologs_ctr::log_datas_in_files( 'wps_barcode',
					array(
					'object_id' => $post_ID,
					'message' => sprintf( __('Change barcode: %s replacing %s for %s object ID', 'wps_barcode'), '<b>'.sanitize_text_field( $_REQUEST['wpshop_product_attribute']['varchar']['barcode'] ).'</b>', '<b>'.$barcode.'</b>', '<b>'.$post_ID.'</b>') ), 0);

					$barcode = sanitize_text_field( $_REQUEST['wpshop_product_attribute']['varchar']['barcode'] );
				}
				else {
					wpeologs_ctr::log_datas_in_files( 'wps_barcode',
					array(
						'object_id' => $post_ID,
						'message' => sprintf( __('Adding barcode: %s for %s object ID', 'wps_barcode'), '<b>'.$barcode.'</b>', '<b>'.$post_ID.'</b>') ),
					0);

					// $_REQUEST['wpshop_product_attribute']['varchar']['barcode'] = $barcode;
				}
			}
			else {
				/** On met à jour l'attribut barcode *//*
				$products = new wps_product_ctr();
				$products->update_the_attribute_for_product($post_ID, 'varchar', 'barcode', $barcode);
			}*/
		}
	}

	/**
	 * Save post meta of the post
	 * @param string $post_type Constant identifier of post
	 * @param string $post_date Date of creation post
	 * @param string $post_ID Ident of post
	 * @return string Number code of barcode
	 */
	//private function wps_generate_barcode($post_type, $post_date, $post_ID) {
	private function wps_generate_barcode($array) {
		include_once('wps_barcodegen.ctr.php');

		$code = '';
		$ref = '';
		$barcode = new wps_barcodegen;

		$conf = get_option('wps_barcode');

		if ( array_key_exists('normal', $array) ) {
			$code .= $array['normal']['country'];
			$code .= $array['normal']['enterprise'];
			$code .= $array['normal']['ID'];

			$id = $array['normal']['ID'];

		}
		else if ( array_key_exists('internal', $array) ) {
			$code.= $array['internal']['type'];
			$code .= $array['internal']['date'];
			$code .= $array['internal']['ID'];

			$id = $array['internal']['ID'];
		}

		$gencode = $barcode->checksum($code);

		$barcode->writeLog( sprintf( __("Checksum generate: %s from %s <br />",
		'wps_barcode'), '<b>'.$gencode.'</b>',
		'<b>'.$code.'</b>') );

		$message = sprintf( __("Log generated by wps_barcodegen for add/update product: <br /> %s",
				'wps_barcode'), $barcode->getLog() );

		if ( class_exists('wpeologs_ctr') ) {
			wpeologs_ctr::log_datas_in_files( 'wps_barcode', array('object_id' => $id, 'message' => $message), 0);
		}

		return $gencode;
	}
}
