<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_opinion_model {
	
	var $id; 
	var $opinion_post_ID;
	var $author_IP;
	var $author;
	var $author_email;
	var $opinion_content;
	var $opinion_date;
	var $author_id;
	var $opinion_approved;
	var $opinion_rate;
	
	function __construct( $data= array() ) {
		if( !empty($data) ) {
			$this->id = ( !empty($data['id']) ) ? $data['id'] : ''; 
			$this->opinion_post_ID = ( !empty($data['opinion_post_ID']) ) ? $data['opinion_post_ID'] : '';
			$this->author_IP = ( !empty($data['author_IP']) ) ? $data['author_IP'] : '';
			$this->author = ( !empty($data['author']) ) ? $data['author'] : '';
			$this->author_name = ( !empty($data['author_name']) ) ? $data['author_name'] : '';
			$this->author_email = ( !empty($data['author_email']) ) ? $data['author_email'] : '';
			$this->opinion_content = ( !empty($data['opinion_content']) ) ? $data['opinion_content'] : '';
			$this->opinion_date = ( !empty($data['opinion_date']) ) ? $data['opinion_date'] : '';
			$this->author_id = ( !empty($data['author_id']) ) ? $data['author_id'] : '';
			$this->opinion_approved = ( !empty($data['opinion_approved']) ) ? $data['opinion_approved'] : '';
			$this->opinion_rate = ( !empty($data['opinion_rate']) ) ? $data['opinion_rate'] : '';
		}
	}
	
	/**
	 * Create opinion model
	 * @param array $data
	 */
	function Create( $data ) {
		$this->id = ( !empty($data['id']) ) ? $data['id'] : '';
		$this->opinion_post_ID = ( !empty($data['opinion_post_ID']) ) ? $data['opinion_post_ID'] : '';
		$this->author_IP = ( !empty($data['author_IP']) ) ? $data['author_IP'] : '';
		$this->author = ( !empty($data['author']) ) ? $data['author'] : '';
		$this->author_email = ( !empty($data['author_email']) ) ? $data['author_email'] : '';
		$this->opinion_content = ( !empty($data['opinion_content']) ) ? $data['opinion_content'] : '';
		$this->opinion_date = ( !empty($data['opinion_date']) ) ? $data['opinion_date'] : '';
		$this->author_id = ( !empty($data['author_id']) ) ? $data['author_id'] : '';
		$this->opinion_approved = ( !empty($data['opinion_approved']) ) ? $data['opinion_approved'] : 0;
		$this->opinion_rate = ( !empty($data['opinion_rate']) ) ? $data['opinion_rate'] : 0;
	}

	/**
	 * Save opinion
	 * @return bool $status
	 */
	function Save() {
		if( !empty($this->opinion_post_ID) && !empty($this->author_id) && !empty($this->opinion_content) ) {
			$post_type = get_post_type( $this->opinion_post_ID );
			// If element is a product or a product variation, we accept to save opinion
			if( $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT || $post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				/** Insert comment **/
				$data = array(
						'comment_post_ID' => $this->opinion_post_ID,
						'comment_author_IP' => $this->author_IP,
						'comment_author' => $this->author,
						'comment_author_email' => $this->author_email,
						'comment_content' => $this->opinion_content,
						'comment_date' => $this->opinion_date,
						'user_id' => $this->author_id,
						'comment_approved' => $this->opinion_approved, 
						'comment_type' => WPS_OPINION_ID
				);
				$this->id = wp_insert_comment($data);
				
				if( !empty($this->id) ) {
					$status = update_comment_meta( $this->id, '_wps_customer_rate', $this->opinion_rate );
				}
			}
		}
		return $status;
	}
	
	/**
	 * Check if an opinion was posted for an product
	 * @param integer $pid
	 * @param integer $user_id
	 * @return array
	 */
	function check_opinion_exists( $pid, $user_id ) {
		$opinions = array();
		if( !empty($pid) && !empty($user_id) ) {
			$opinions = get_comments( array( 'post_id' => $pid, 'user_id' => $user_id) );
		}
		return $opinions;
	}
	
	/**
	 * Get ordered products List
	 * @param integer $customer_id
	 * @param bool $send_opinion
	 * @return array
	 */
	function get_ordered_products( $customer_id, $send_opinion = false ) {
		global $wpdb;
		$products = array();
		if( !empty( $customer_id ) ) {
			$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, $customer_id );
			$orders = $wpdb->get_results( $query );
			if( !empty($orders) ) {
				foreach( $orders as $order ) {
					$order_metadata = get_post_meta( $order->ID, '_order_postmeta', true );
					if( !empty($order_metadata) && !empty($order_metadata['order_items']) ) {
						foreach( $order_metadata['order_items'] as $item_id => $item ) {
							// Check if product is a variation 
							$item_id = ( !empty($item['item_id']) ) ? $item['item_id'] : $item_id;
							if( get_post_type($item_id) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
								$post_item = get_post( $item_id );
								if( !empty($post_item) ) {
									$item_id = $post_item->post_parent;
								}
							}
							// Check if opinion exists
							if( get_post_type($item_id) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) {
								$check_opinion = $this->check_opinion_exists( $item_id, $customer_id );
								$opinion_exists = ( !empty( $check_opinion ) ) ? true : false;
								if( (!$opinion_exists && !$send_opinion) || ($opinion_exists && $send_opinion) ) {
									$products[ $item_id ] = $item_id;
								}
							}
						}
					}
				}
			}
		}
		return $products;
	}
	
	/**
	 * Get customer posted opinions
	 * @param integer $customer_id
	 * @return array
	 */
	function get_customer_opinions( $customer_id ) {
		$opinions = array();
		if( !empty($customer_id) ) {
			$opinions = get_comments( array('user_id' => $customer_id, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) );
		}
		return $opinions;
	}

	/**
	 * Get opinions for an product
	 * @param integer $product_id
	 * @return array
	 */
	function get_opinions_for_product( $product_id, $approved = '' ) {
		$returned_opinions = array();
		if( !empty($product_id) ) {
			$opinions = get_comments( array('post_id' => $product_id, 'status' => $approved ) );
			if( !empty($opinions) ) {
				foreach( $opinions as $opinion ) {
					$comment_def = array(
							'id' => $opinion->comment_ID,
							'opinion_post_ID' => $opinion->comment_post_ID,
							'author_IP' => $opinion->comment_author_IP, 
							'author' => $opinion->comment_author,
							'author_email' => $opinion->comment_author_email,
							'opinion_content' => $opinion->comment_content,
							'opinion_date' => $opinion->comment_date,
							'author_id' => $opinion->user_id,
							'opinion_approved' => $opinion->comment_approved,
							'opinion_rate' => get_comment_meta( $opinion->comment_ID, '_wps_customer_rate', true ),
					);
					$o = new wps_opinion_model(); 
					$o->Create($comment_def);
					$returned_opinions[] = $o;
				}
			}	
		}
		return $returned_opinions;
	}

}