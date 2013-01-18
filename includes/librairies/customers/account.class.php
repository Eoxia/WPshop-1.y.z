<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


/* Instantiate the class from the shortcode */
function wpshop_account_display_form() {
	global $wpdb, $wpshop, $wpshop_account, $civility;

	$wpshop_account->managePost();

	$user_id = get_current_user_id();
	if (!$user_id) {
		echo $wpshop_account->display_login_form();
	}
	else {
		// Order status possibilities
		$order_status = unserialize(WPSHOP_ORDER_STATUS);
		// Payment method possibilities
		$payment_method = array('paypal' => 'Paypal', 'check' => __('Check','wpshop'), 'cic' => __('Credit card','wpshop'));

		if (!empty($_GET['action'])) {

			// --------------------------
			// Edition infos personnelles
			// --------------------------
			if ($_GET['action']=='editinfo') {
				$shipping_info = get_user_meta($user_id, 'shipping_info', true);
				$billing_info = get_user_meta($user_id, 'billing_info', true);
				$user_preferences = get_user_meta($user_id, 'user_preferences', true);

				// Si il y a des infos � afficher
				if(!empty($shipping_info) && !empty($billing_info)) {
					// On ajoute le pr�fixe qu'il faut pour que tout soit fonctionnel
					foreach($shipping_info as $k => $v):
						$shipping_info['shipping_'.$k] = $shipping_info[$k];
						unset($shipping_info[$k]);
					endforeach;
					foreach($billing_info as $k => $v):
						$billing_info['billing_'.$k] = $billing_info[$k];
						unset($billing_info[$k]);
					endforeach;
				}
				else {
					$shipping_info = $billing_info = array('first_name'=>null,'last_name'=>null,'address'=>null,'postcode'=>null,'city'=>null,'country'=>null);
				}

				if(empty($_GET['return'])) :

				elseif($_GET['return'] == 'checkout'):
					// Display the address infos Dashboard

				endif;

				echo '<form method="post" name="billingAndShippingForm">';
					echo $wpshop_account->display_addresses_dashboard();
					$wpshop_account ->display_commercial_newsletter_form();
					echo '<input type="submit" name="submitbillingAndShippingInfo" value="'.__('Save','wpshop').'" />';
				echo '</form>';
			}
			// Edit your account infos.
			elseif ($_GET['action']=='editinfo_account' ) {
				echo '<div id="reponseBox"></div>';
				echo '<form  method="post" id="register_form" action="' . admin_url('admin-ajax.php') . '">';
					echo '<input type="hidden" name="wpshop_ajax_nonce" value="' . wp_create_nonce('wpshop_customer_register') . '" />';
					echo '<input type="hidden" name="action" value="wpshop_save_customer_account" />';
					// Bloc REGISTER
					echo '<div class="col1 wpshopShow" id="register_form_classic">';
					$wpshop_account->display_account_form();
					echo '<input type="submit" name="submitOrderInfos" value="'.__('Save my account informations','wpshop').'" />';
					echo '</div>';
				echo '</form>';
			}

			// Edit an address
			elseif ( $_GET['action']=='editAddress' ) {
				if ( isset($_GET['id']) && !empty($_GET['id']) ) {
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE ID = ' .$_GET['id']. ' AND post_parent = ' .get_current_user_id(). '', '');
					$post = $wpdb->get_row($query);
					$attribute_set_id = get_post_meta($post->ID, '_wpshop_address_attribute_set_id', true);

					if ( !empty($post)) {
						echo $wpshop_account -> display_form_fields($attribute_set_id, $_GET['id']);
					}
					else {
						wpshop_tools::wpshop_safe_redirect( $_SERVER['HTTP_REFERER'] );
					}

				}
			}
			// Choose the address type
			elseif( $_GET['action'] == 'choose_address' ) {
				$shipping_options = get_option('wpshop_shipping_address_choice');
				if ( !empty($shipping_options['activate']) ) {
					echo  '<h1>'.__('Address Type','wpshop').'</h1><p>';
					echo '<form id="selectNewAddress" method="post" action="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=add_address">';
					echo '<div class="create-account">';
					echo __('Select the address type you want to create','wpshop').'</p>';
					$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
					$entity_id = $wpdb->get_var($query);

					$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = ' .$entity_id. '', '');
					$content = $wpdb->get_results($query);

					$input_def['name'] = 'address_type';
					$input_def['id'] = 'address_type';
					$input_def['possible_value'] = $content;
					$input_def['type'] = 'select';
					echo wpshop_form::check_input_type($input_def);
					echo '</div>';
					echo '<input type="hidden" name="referer" value="'.$_SERVER['HTTP_REFERER'].'" />';
					echo '<input type="submit" name="chooseAddressType" value="'.__('Choose','wpshop').'" />';
					echo '</form>';
				}
				else {
					wpshop_tools::wpshop_safe_redirect( get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=add_address' );
				}
			}
			//Add a new address
			elseif ($_GET['action'] == 'add_address') {
				//Test if it's the first address of the user
				if ( isset($_GET['first']) ) {
					$billing_address_option = get_option('wpshop_billing_address');
					$shipping_address_option = get_option('wpshop_shipping_address_choice');
					echo '<form method="post" name="billingAndShippingForm">';
					echo $wpshop_account -> display_form_fields($billing_address_option['choice'], '', 'first');
					if ( $shipping_address_option['activate'] ) {
						echo '<p class="formField alignleft"><label><input type="checkbox" name="shiptobilling" checked="checked" /> '.__('Use as shipping information','wpshop').'</label></p>';
						$display = 'display:none;';
						echo '<div id="shipping_infos_bloc" style="'.$display.'">';
						echo $wpshop_account -> display_form_fields($shipping_address_option['choice'], '', 'first');
						echo '</div><br/>';
					}
					echo '<p class="formField alignleft"><input type="submit" name="submitbillingAndShippingInfo" value="'.__('Save','wpshop').'" /></p>';
					echo '</form>';
				}
				else {
					// Check if an address type was send for generate the form
					if ( !empty($_POST['address_type']) ) {
						$address_type = strip_tags( $_POST['address_type'] );
					}
					elseif ( !empty($_POST['type_of_form']) ) {
						$address_type = strip_tags($_POST['type_of_form']);
					}
					else {
						$address_type = get_option('wpshop_billing_address');
					}
					echo $wpshop_account->display_form_fields( $address_type['choice'], '', '', $_POST['referer'] );
				}
			}

			// --------------------------
			// Infos commande
			// --------------------------
			elseif ($_GET['action']=='order' && !empty($_GET['oid']) && is_numeric($_GET['oid'])) {
				$order_info = get_post_meta($_GET['oid'], '_order_postmeta', true);

				if(!empty($order_info) && $order_info['customer_id']==$user_id) {

					echo '<h2>'.__('Order details','wpshop').'</h2>';
					// Display the order's address infos
					$order_info = get_post_meta($_GET['oid'], '_order_info', true);

 					foreach ( $order_info as $key=>$address ) {
						if( !empty($address['address']) ) {
							echo '<div class="half">';
							echo '<h2>'.($key =='billing' ? __('Billing address', 'wpshop') : __('Shipping address', 'wpshop')).'</h2>';
							echo '<ul>';

							foreach ( $address['address'] as $attribute_code => $attribute_def) {
								$info = $attribute_def;
								if ($attribute_code == 'civility') {
									$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id=' . $attribute_def . '', '');
									$info = $wpdb->get_var($query);
								}

								if( !empty($info) ) {
									echo wpshop_display::display_template_element('customer_address_display', array('CUSTOMER_ADDRESS_ELEMENT' => $info, 'CUSTOMER_ADDRESS_ELEMENT_KEY' => $attribute_code));
								}
							}

						echo '</ul>';
						echo '</div>';
						}
					}


					// Donn�es commande
					$order = get_post_meta($_GET['oid'], '_order_postmeta', true);
					$currency = wpshop_tools::wpshop_get_sigle($order['order_currency']);

					if(!empty($order)) {
						echo '<div class="order"><div>';
						echo __('Order number','wpshop').' : <strong>'.$order['order_key'].'</strong><br />';
						echo __('Date','wpshop').' : <strong>'.$order['order_date'].'</strong><br />';
						echo __('Total','wpshop').' : <strong>'.number_format($order['order_total_ttc'], 2, '.', '').' '.$currency.'</strong><br />';
						echo __('Payment method','wpshop').' : <strong>'.$payment_method[$order['payment_method']].'</strong><br />';
						if($order['payment_method']=='paypal') {
							$order_paypal_txn_id = get_post_meta($_GET['oid'], '_order_paypal_txn_id', true);
							echo __('Paypal transaction id', 'wpshop').' : <strong>'.(empty($order_paypal_txn_id)?'Unassigned':$order_paypal_txn_id).'</strong><br />';
						}
						echo __('Status','wpshop').' : <strong><span class="status '.$order['order_status'].'">'.$order_status[$order['order_status']].'</span></strong><br />';
						echo __('Tracking number','wpshop').' : '.(empty($order['order_trackingNumber'])?__('none','wpshop'):'<strong>'.$order['order_trackingNumber'].'</strong>').'<br /><br />';
						echo '<strong>'.__('Order content','wpshop').'</strong><br />';
						if(!empty($order['order_items'])){

							// Codes de t�l�chargement
							if(in_array($order['order_status'], array('completed', 'shipped'))) {
								$download_codes = get_user_meta($user_id, '_order_download_codes_'.$_GET['oid'], true);
							}

							foreach($order['order_items'] as $o) {
								// If the order is >= completed, so give the download link to the user
								if(!empty($download_codes[$o['item_id']])) {
									$link = '<a href="'.WPSHOP_URL.'/download_file.php?oid='.$_GET['oid'].'&amp;download='.$download_codes[$o['item_id']]['download_code'].'">'.__('Download','wpshop').'</a>';
								} else $link='';

								echo '<span class="right">'.number_format($o['item_total_ttc'], 2, '.', '').' '.$currency.'</span>'.$o['item_qty'].' x '.$o['item_name'].' '.$link.'<br />';
							}
							echo '<hr />';
							echo '<span class="right">'.number_format($order['order_total_ht'], 2, '.', '').' '.$currency.'</span>'.__('Total ET','wpshop').'<br />';
							echo '<span class="right">'.number_format(array_sum($order['order_tva']), 2, '.', '').' '.$currency.'</span>'.__('Taxes','wpshop').'<br />';
							echo '<span class="right">'.(empty($order['order_shipping_cost'])?'<strong>'.__('Free','wpshop').'</strong>':number_format($order['order_shipping_cost'], 2, '.', '').' '.$currency).'</span>'.__('Shipping fee','wpshop').'<br />';

							if(!empty($order['order_grand_total_before_discount']) && $order['order_grand_total_before_discount'] != $order['order_grand_total']){
								echo '
									'.__('Total ATI before discount','wpshop').'<span class="total_ttc right">'.number_format($order['order_grand_total_before_discount'],2).' '.$currency.'</span>
									<br />'.__('Discount','wpshop').'<span class="total_ttc right">- '.number_format($order['order_discount_amount_total_cart'],2).' '.$currency.'</span><br />
								';
							}

							echo '<span class="right"><strong>'.number_format($order['order_grand_total'], 2, '.', '').' '.$currency.'</strong></span>'.__('Total ATI','wpshop');
						}
						else{
							echo __('No product for this order', 'wpshop');
						}
						echo '</div></div>';

						/* If the payment is completed */
						if(in_array($order['order_status'], array('completed', 'shipped'))) {
							echo '<a href="' . get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=order&oid='.$_GET['oid'].'&download_invoice='.$_GET['oid'].'">'.__('Download the invoice','wpshop').'</a>';
						}
						else {

							$available_payement_method = wpshop_payment::display_payment_methods_choice_form($_GET['oid']);
							echo '<h2>'.__('Complete the order','wpshop').'</h2>' . $available_payement_method[0];
						}
					}
					else echo __('No order', 'wpshop');
			    }
				else echo __('You don\'t have the right to access this order.', 'wpshop');
			}
		}
		// --------------------------
		// DASHBOARD
		// --------------------------
		else {
			// Display the address infos Dashboard
			echo $wpshop_account->display_addresses_dashboard();

			echo '<h2>'.__('Your orders','wpshop').'</h2>';

			$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_status = "publish" ORDER BY post_date DESC', '');
			$orders_id = $wpdb->get_results($query);

			if ( !empty($orders_id) ) {
				$order = array();
				foreach ($orders_id as $o) {

					$order_id = $o->ID;
					$o = get_post_meta($order_id, '_order_postmeta', true);
					$currency = wpshop_tools::wpshop_get_sigle($o['order_currency']);

					if ( !empty($o['order_items']) && ( $user_id == $o['customer_id'] ) ) {
						echo '<div class="order"><div>';
						echo __('Order number','wpshop').' : <strong>'.$o['order_key'].'</strong><br />';
						echo __('Date','wpshop').' : <strong>'.$o['order_date'].'</strong><br />';
						echo __('Total ATI','wpshop').' : <strong>'.number_format($o['order_grand_total'], 2, '.', '').' '.$currency.'</strong><br />';
						echo __('Status','wpshop').' : <strong><span class="status '.$o['order_status'].'">'.$order_status[$o['order_status']].'</span></strong><br />';
						echo '<a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=order&oid='.$order_id.'" title="'.__('More info about this order...', 'wpshop').'">'.__('More info about this order...', 'wpshop').'</a>';
						echo '</div></div>';
					}
				}
			}
			else echo __('No order', 'wpshop');
		}
	}
}

/* Class wpshop_account */
class wpshop_account {

	var $login_fields = array();
	var $personal_info_fields = array();
	var $billing_fields = array();
	var $shipping_fields = array();

	/** Constructor of the class */
	function __construct() {
		$user = wp_get_current_user();
		$current_item_edited = isset($user->ID) ? $user->ID : null;
		$attributes_set = wpshop_attributes_set::getElement('yes', "'valid'", 'is_default', '', wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
		$address = array();
		/*	Get the attribute set details in order to build the product interface	*/
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
		if(!empty($productAttributeSetDetails)){
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							if( !empty($_POST['submitOrderInfos']) ) {
								$value = $_POST['attribute'][$attribute->data_type][$attribute->code];
							}
							else {
								$value = '';
								if ( $attribute->code != 'user_pass') {
									$code = $attribute->code;
									$value = $user->$code;
								}
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$this->personal_info_fields[$attribute->code] = $attribute_output_def;

						}
					}
				}
			}
		}
	}

	/** Traite les donnees reçus en POST
	 * @return void
	*/
	function managePost() {

		global $wpshop, $wpshop_account;

		if( isset($_POST['submitbillingAndShippingInfo'])) {
			if (isset($_POST['shiptobilling']) && $_POST['shiptobilling'] == "on") {
				$wpshop_account->same_billing_and_shipping_address( $_POST['billing_address'], $_POST['shipping_address']);
			}
			foreach ( $_POST['attribute'] as $id_group => $attribute_group ) {
				$group = wpshop_address::get_addresss_form_fields_by_type ($id_group);
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$id_group], 'address_edition');
					}
				}
			}
			if( $validate ) {
				if ( !empty($_POST['billing_address']) ) {
					$wpshop_account->treat_forms_infos( $_POST['billing_address'] );
				}
				if( !empty($_POST['shipping_address']) ) {
					$wpshop_account->treat_forms_infos( $_POST['shipping_address'] );
				}

			 	if(!empty($_GET['return']) && $_GET['return']=='checkout') {
			 		wpshop_tools::wpshop_safe_redirect($_POST['referer']);
			 	}
			 	else {
			 		wpshop_tools::wpshop_safe_redirect( $_POST['referer'] );
			 	}
			}
		}
		elseif(!empty($_GET['download_invoice'])) {
			$pdf = new wpshop_export_pdf();
			$pdf->invoice_export($_GET['download_invoice']);
		}
		// Test the infos if the account form was posted
		if ( isset($_POST['submitAccountInfo']) ) {
			if ( $wpshop->validateForm($this->personal_info_fields) ) {
				self::save_account_form(get_current_user_id());
				wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
			}
		}

		// if there is errors
		if($wpshop->error_count()>0) {
			echo $wpshop->show_messages();
			return false;
		}
		else {
			return true;
		}
	}

	/** Display the login form
	 * @return void
	*/
	function display_login_form() {
		return wp_login_form();
	}

	/** Display the account form
	 * @return void
	 */
	function display_account_form( $first = '' ) {
		global $wpdb;

		$tpl_component = array();
		$tpl_component['ACCOUNT_FORM_FIELD'] = '';
		foreach ($this->personal_info_fields as $key => $field) :
			$template = 'wpshop_account_form_input';
			if ( $field['type'] == 'hidden' ) {
				$template = 'wpshop_account_form_hidden_input';
			}

			if ( $field['frontend_verification'] == 'country' ) {
				$field['type'] = 'select';
				$field['possible_value'] = unserialize(WPSHOP_COUNTRY_LIST);
				$field['valueToPut'] = 'index';
			}

			$attributeInputDomain = 'attribute[' . $field['data_type'] . ']';
			$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
			$input_tpl_component = array();
			$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
			$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = $field['label'] . ($field['required'] == 'yes' ? ' <span class="required">*</span>' : '');
			$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . (( $field['data_type'] == 'datetime' ) ? $field['options'] : '');
			$tpl_component['ACCOUNT_FORM_FIELD'] .= wpshop_display::display_template_element($template, $input_tpl_component);
			unset($input_tpl_component);

			if ( $field['_need_verification'] == 'yes') {
				$field['name'] = $field['name'] . '2';
				$field['id'] = $field['id'] . '2';
				$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
				$input_tpl_component = array();
				$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
				$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf(__('Confirm %s', 'wpshop'), $field['label']) . ($field['required'] == 'yes' ? ' <span class="required">*</span>' : '');
				$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . (( $field['data_type'] == 'datetime' ) ? $field['options'] : '');
				$tpl_component['ACCOUNT_FORM_FIELD'] .= wpshop_display::display_template_element($template, $input_tpl_component);
				unset($input_tpl_component);
			}

			$wpshop_billing_address = get_option('wpshop_billing_address');
			if ( !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') && !empty($wpshop_billing_address['integrate_into_register_form_after_field']) && ($wpshop_billing_address['integrate_into_register_form_after_field'] == $key ) ) {
				$current_connected_user = null;
				if ( get_current_user_id() > 0 ) {
					$query = $wpdb->prepare ("SELECT *
						FROM " . $wpdb->posts . "
						WHERE post_type = '" .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. "'
						AND post_parent = %d
						ORDER BY ID
						LIMIT 1", get_current_user_id() );
					$current_connected_user = $wpdb->get_var($query);
				}
				$tpl_component['ACCOUNT_FORM_FIELD'] .= $this->display_form_fields( $wpshop_billing_address['choice'], $current_connected_user, 'first', '', array(), array('title' => false, 'address_title' => false, 'field_to_hide' => $wpshop_billing_address['integrate_into_register_form_matching_field']) );
			}
		endforeach;

		echo wpshop_display::display_template_element('wpshop_account_form', $tpl_component);
		self::display_commercial_newsletter_form();
	}

	/** Display the commercial & newsletter form
	 * @return void
	 */
	function display_commercial_newsletter_form() {
		$user_preferences = get_user_meta(get_current_user_id(), 'user_preferences', true );
		echo '<h2>Mes newsletters et informations commerciales</h2>';
		echo '<input type="checkbox" name="newsletters_site" id="newsletters_site" '.((!empty($user_preferences['newsletters_site']) && $user_preferences['newsletters_site']==1 OR !empty($_POST['newsletters_site']))?'checked="checked"':null).' /><label for="newsletters_site">'.__('I want to receive promotional information from the site','wpshop').'</label><br />';
		echo '<input type="checkbox" name="newsletters_site_partners" id="newsletters_site_partners" '.((!empty($user_preferences['newsletters_site_partners']) && $user_preferences['newsletters_site_partners']==1 OR !empty($_POST['newsletters_site_partners']))?'checked="checked"':null).' /><label for="newsletters_site_partners">'.__('I want to receive promotional information from partner companies','wpshop').'</label><br /><br />';

	}
	/** Display the address Dashboard
	 * @return void
	 */
	function display_addresses_dashboard() {
		global $wpdb;

		$addresses_list = '';

		$addresses_list .= '<a href="'.wp_logout_url(get_permalink(get_option('wpshop_product_page_id'))).'" title="'.__('Logout','wpshop').'" class="right">'.__('Logout','wpshop').'</a>';
		$addresses_list .= '<a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=editinfo_account" title="'.__('Edit my account infos', 'wpshop').'">'.__('Edit my account infos', 'wpshop').'</a>';

		$query = $wpdb->prepare ('SELECT *
					FROM ' .$wpdb->posts. '
					WHERE post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '"
					AND post_parent = ' .get_current_user_id(). '', '');

		$addresses = $wpdb->get_results($query);

		if( count($addresses) > 0 ) {
			$shipping_options = get_option('wpshop_shipping_address_choice');
			if ( !empty($shipping_options['activate']) ) {
				$addresses_list .= ' | <a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=choose_address" title="'.__('Add a new address', 'wpshop').'">'.__('Add a new address', 'wpshop').'</a>';
			}
			else {
				$addresses_list .= ' | <a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=add_address" title="'.__('Add a new address', 'wpshop').'">'.__('Add a new address', 'wpshop').'</a>';
			}
			$addresses_list .= '<h2>'.__('My addresses', 'wpshop').'</h2>';
			foreach ( $addresses as $address ) {
				// Display the addresses
				$addresses_list .= '<div class="half">';
				$address_infos = get_post_meta($address->ID, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				$address_set = get_post_meta($address->ID, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_attribute_set_id', true);
				$set_infos = wpshop_attributes_set::getElement($address_set, "'valid'");
				$add = wpshop_address::get_addresss_form_fields_by_type($set_infos->id);
				if ( !empty($address_infos) ) {
					$addresses_list .= '<ul>';
					foreach( $add as $id_group => $group_fields) {
						foreach ($group_fields as $key => $fields) {
							foreach ( $fields['content'] as $attribute_code => $attribute_def) {
								$info = !empty($address_infos[$attribute_def['name']]) ? $address_infos[$attribute_def['name']] : '';
								if (($attribute_def['name'] == 'civility')) {
									if ( !empty($address_infos[$attribute_def['name']]) ) {
										$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id=' . $address_infos[$attribute_def['name']] . '', '');
										$info = $wpdb->get_var($query);
									}
								}

								if ($attribute_def['name'] == 'country') {
									if ( !empty($info) ) {

										foreach (unserialize(WPSHOP_COUNTRY_LIST) as $key=>$value) {
											if ( $info == $key) {
												$info = $value;
											}
										}
									}
								}

								if(!empty($address_infos[$attribute_def['name']])){
									$addresses_list .= wpshop_display::display_template_element('customer_address_display', array('CUSTOMER_ADDRESS_ELEMENT' => $info, 'CUSTOMER_ADDRESS_ELEMENT_KEY' => $attribute_def['name']));
								}
							}
						}
					}
					$addresses_list .= '</ul>';
				}
				else {
					$addresses_list .= '<span style="color:red;">'.__('No data','wpshop').'</span>';
				}
				$addresses_list .= '<a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=editAddress&amp;id='.$address->ID.'" title="">'.__('Edit', 'wpshop').'</a>';
				if( is_page(get_option('wpshop_checkout_page_id')) ) {
					$attribute_set_id = get_post_meta($address->ID, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY);
					$attribute_set_id = $attribute_set_id[0];
					$query = $wpdb->prepare('SELECT name FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE id = ' .$attribute_set_id. '', '');
					$attribute_set_name = $wpdb->get_var($query);
					// Test the address type
					$shipping =  __('Shipping address', 'wpshop');
					$billing =  __('Billing address', 'wpshop');
					// Display the checkboxes to select shipping & billing addresses.
					if( $attribute_set_name == $billing) {
						$addresses_list .= '<br/><input type="radio" id="billing_address" name="billing_address" value="' .$address->ID. '" checked="checked" /> Choose as billing address';
					}
					elseif ($attribute_set_name == $shipping) {
						$addresses_list .= '<br/><input type="radio" id="shipping_address" name="shipping_address" value="' .$address->ID. '" checked="checked"/> Choose as shipping address';
					}
					else {
						$addresses_list .= '<br/><input type="radio" name="billing_address" value="' .$address->ID. '" /> Choose as billing address';
						$addresses_list .= '<br/><input type="radio" name="shipping_address" value="' .$address->ID. '"/> Choose as shipping address';
					}

				}
				$addresses_list .= '</div>';
			}
		}
		else {
			$addresses_list .= '<div class="infos_bloc wpshopShow" id="infos_register"><a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=add_address&amp;first" title="'.__('Add a new address', 'wpshop').'">'.__('You must create an address', 'wpshop').'</a></div>';
		}

		return $addresses_list;
	}

	/**
	 * Display the differents forms fields
	 * @param string $type : Type of address
	 * @param string $first : Customer first address ?
	 * @param string $referer : Referer website page
	 * @param string $admin : Display this form in admin panel
	 */
	function display_form_fields($type, $id = '', $first = '', $referer = '', $special_values = array(), $options = array() ) {
		global $wpshop, $wpshop_form, $wpdb;
		$choosen_address = get_option('wpshop_billing_address');
		$output_form_fields = '';

		if ( empty($type) ) {
			$type = $choosen_address['choice'];
		}
		$result = wpshop_address::get_addresss_form_fields_by_type($type, $id);

		$form = $result[$type];
		// Take the post id to make the link with the post meta of  address
		$values = array();
		// take the address informations
		$current_item_edited = !empty($id) ? (int)wpshop_tools::varSanitizer($id) : null;
		$output_form_fields = '';
		//if ( empty($options) || (!empty($options) && ($options['title']))) $output_form_fields = '<h1>' .$form['name']. '</h1>';
		if ( !is_admin() && empty($first) ) $output_form_fields .= '<form method="post" name="billingAndShippingForm">';

		foreach ( $form as $group_id => $group_fields) {
			if ( empty($options) || (!empty($options) && ($options['title']))) $output_form_fields .= '<h2>'.$group_fields['name'].'</h2>';
			foreach ( $group_fields['content'] as $key => $field) {
				if ( empty($options['field_to_hide']) || !is_array($options['field_to_hide']) || !in_array( $key, $options['field_to_hide'] ) ) {
					$attributeInputDomain = 'attribute[' . $type . '][' . $field['data_type'] . ']';
					// Test if there is POST var or if user have already fill his address infos and fill the fields with these infos
					if( !empty($_POST) ) {
						$referer = !empty($_POST['referer']) ? $_POST['referer'] : '';
						if ( !empty($form['id']) && !empty($field['name']) && isset($_POST[$form['id']."_".$field['name']]) ) {
							$value = $_POST[$form['id']."_".$field['name']];
						}
					}
					if (empty($referer)) {
						$referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
					}
					if( $field['name'] == 'address_title' && !empty($first) && $type == __('Billing address', 'wpshop') ) {
						$value = __('Billing address', 'wpshop');
					}
					elseif( $field['name'] == 'address_title' && !empty($first) && $type == __('Shipping address', 'wpshop') ) {
						$value = __('Shipping address', 'wpshop');
					}

					if ( !empty($special_values[$field['name']]) ) {
						$field['value'] = $special_values[$field['name']];
					}

					$template = 'wpshop_account_form_input';
					if ( $field['type'] == 'hidden' ) {
						$template = 'wpshop_account_form_hidden_input';
					}

					if ( $field['frontend_verification'] == 'country' ) {
						$field['type'] = 'select';
						$field['possible_value'] = array_merge(array('' => __('Choose a country')), unserialize(WPSHOP_COUNTRY_LIST));
						$field['valueToPut'] = 'index';
					}

					$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
					$input_tpl_component = array();
					$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = $field['label'] . ( ($field['required'] == 'yes') && !is_admin() ? ' <span class="required">*</span>' : '');
					$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain);
					$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
					unset($input_tpl_component);

					if ( $field['_need_verification'] == 'yes' && !is_admin() ) {
						$field['name'] = $field['name'] . '2';
						$field['id'] = $field['id'] . '2';
						$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
						$input_tpl_component = array();
						$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf( __('Confirm %s', 'wpshop'), $field['label'] ). ( ($field['required'] == 'yes') && !is_admin() ? ' <span class="required">*</span>' : '');
						$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . $field['options'];
						$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
						unset($input_tpl_component);
					}
				}
			}
		}

		if ( $type ==  $choosen_address['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="billing_address" value="'.$choosen_address['choice'].'" />';
		}
		$shipping_address_options = get_option('wpshop_shipping_address_choice');
		if ( $type ==  $shipping_address_options['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="shipping_address" value="' .$shipping_address_options['choice']. '" />';
		}
		$output_form_fields .= '<input type="hidden" name="edit_other_thing" value="'.false.'" />';
		$output_form_fields .= '<input type="hidden" name="referer" value="'.$referer.'" />';
		$output_form_fields .= '<input type="hidden" name="type_of_form" value="' .$type. '" /><input type="hidden" name="item_id" value="' .$current_item_edited. '" />';
		if ( !is_admin() && empty($first) ) $output_form_fields .= '<input type="submit" name="submitbillingAndShippingInfo" value="'.__('Save','wpshop').'" />';
		if ( !is_admin() && empty($first) )$output_form_fields .= '</form>';

		return $output_form_fields;
	}

	/** Fill the shipping informations with the billing informations if user check there are same addresses
	 * @param int $billing_address_id
	 * @param int $shipping_address_id
	 * @return void
	 */
	function same_billing_and_shipping_address ($billing_address_id, $shipping_address_id) {
		if ( !empty($_POST) ) {
			$tableauGeneral =  $_POST;
		}
		else {
			$tableauGeneral = $_REQUEST;
		}


		// Create an array with the shipping address fields definition
		$shipping_fields = array();
		foreach ($tableauGeneral['attribute'][$shipping_address_id] as $key=>$attribute_group ) {
			foreach( $attribute_group as $field_name=>$value ) {
				$shipping_fields[] =  $field_name;
			}
		}
		// Test if the billing address field exist in shipping form
		foreach ($tableauGeneral['attribute'][$billing_address_id] as $key=>$attribute_group ) {
			foreach( $attribute_group as $field_name=>$value ) {
				if ( in_array($field_name, $shipping_fields) ) {
					if ($field_name == 'address_title') {
						$tableauGeneral['attribute'][$shipping_address_id][$key][$field_name] = __('Shipping address', 'wpshop');
					}
					else {
						$tableauGeneral['attribute'][$shipping_address_id][$key][$field_name] = $tableauGeneral['attribute'][$billing_address_id][$key][$field_name];
					}
				}
			}
		}

		foreach ( $tableauGeneral as $key=>$value ) {
			if ( !empty($_POST) ) {
				$_POST[$key] = $value;
			}
			else {
				$_REQUEST[$key] = $value;
			}
		}
	}

	/** Treat the differents fields of form and classified them by form
	 * @return boolean
	 */
	function treat_forms_infos( $attribute_set_id ) {
		global $wpdb;
		$current_item_edited = !empty($_POST['item_id']) ? (int)wpshop_tools::varSanitizer($_POST['item_id']) : null;
		// Create or update the post address
		$post_parent = '';
		$post_author = get_current_user_id();
		if ( !empty($_POST['user']['customer_id']) ) {
			$post_parent = $_POST['user']['customer_id'];
			$post_author = $_POST['user']['customer_id'];
		}
		elseif ( !empty($_POST['post_ID']) ) {
			$post_parent = $_POST['post_ID'];
		}
		else {
			$post_parent = get_current_user_id();
		}
		$post_address = array(
			'post_author' => $post_author,
			'post_title' => $_POST['attribute'][$attribute_set_id]['varchar']['address_title'],
			'post_status' => 'publish',
			'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
			'post_parent'=>	$post_parent
		);
		$_POST['edit_other_thing'] = true;

		if ( empty($current_item_edited) ) {
			$current_item_edited = wp_insert_post( $post_address );
			//$_POST['item_id'] = $current_item_edited;
		}
		else {
			$post_address['ID'] = $current_item_edited;
			wp_update_post( $post_address );
		}
		//Update the post_meta of address
		update_post_meta($current_item_edited, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, $attribute_set_id);

		foreach ( $_POST['attribute'][ $attribute_set_id ] as $type => $type_content) {
			$attribute_not_to_do = array();
			foreach ( $type_content as $code => $value) {
				$attribute_def = wpshop_attributes::getElement($code, "'valid'", 'code');
				if ( !empty($attribute_def->_need_verification) && $attribute_def->_need_verification == 'yes' ) {
					$code_verif = $code.'2';
					$attribute_not_to_do[] = $code_verif;
					if ( !empty($attributes[$code_verif] )) {
						unset($attributes[$code_verif]);
					}
				}
				if( !in_array($code, $attribute_not_to_do)) $attributes[$code] = $value;
			}
		}

		//GPS coord
		$address = $attributes['address']. ' ' .$attributes['postcode']. ' ' .$attributes['city'];
		$gps_coord = wpshop_address::get_coord_from_address($address);
		$attributes['longitude'] = $gps_coord['longitude'];
		$attributes['latitude'] = $gps_coord['latitude'];

		$result = wpshop_attributes::setAttributesValuesForItem($current_item_edited, $attributes, false, '');
		$result['current_id'] = $current_item_edited;

		return $result;
	}


	/** Save the account informations
	 * @return void
	 */
	function save_account_form($user_id = null) {

		global $wpdb, $wpshop, $wpshop_account;

		$account_creation = false;
		if ( empty($user_id) ) {
			/** Create customer account */
			$reg_errors = new WP_Error();
			do_action('register_post', $_POST['attribute']['varchar']['user_email'], $_POST['attribute']['varchar']['user_email'], $reg_errors);
			$errors = apply_filters('registration_errors', $reg_errors, $_POST['attribute']['varchar']['user_email'], $_POST['attribute']['varchar']['user_email']);

			// if there are no errors, let's create the user account
			if ( !$reg_errors->get_error_code() ) {
				$account_creation = true;

				$user_name = !empty($_POST['attribute']['varchar']['user_login']) ? $_POST['attribute']['varchar']['user_login'] : $_POST['attribute']['varchar']['user_email'];
				$user_pass = !empty($_POST['attribute']['varchar']['user_pass']) ? $_POST['attribute']['varchar']['user_pass'] : wp_generate_password( 12, false );
				$user_id = wp_create_user($user_name, $user_pass, $_POST['attribute']['varchar']['user_email']);
				if ( !is_int($user_id) ) {
					$wpshop->add_error(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'wpshop'), get_option('admin_email')));
					return false;
				}
				// Change role
				wp_update_user( array( 'ID' => $user_id, 'role' => 'customer' ) );
			}
			else {
				$wpshop->add_error($reg_errors->get_error_message());
				return false;
			}
		}

		if ( $user_id > 0 ) {
			$user_database_fields = wpshop_database::get_field_list($wpdb->users);
			foreach ( $user_database_fields as $user_database_field ) {
				$fields[] = $user_database_field->Field;
			}
			foreach ($this->personal_info_fields as $key => $field) :
				$this->posted[$key] = isset($_POST['attribute'][$field['data_type']][$key]) ? wpshop_tools::wpshop_clean($_POST['attribute'][$field['data_type']][$key]) : null;
				if ( !in_array($key, $fields) ) {
					update_user_meta( $user_id, $key, $this->posted[$key]  );
				}
				else {
					wp_update_user( array('ID' => $user_id, $key => $this->posted[$key]) );
				}
			endforeach;

			$_POST['user']['customer_id'] = $user_id;

			$wpshop_billing_address = get_option('wpshop_billing_address');
			if ( !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') ) {
				$wpshop_account->treat_forms_infos( $wpshop_billing_address['choice'] );
			}

			if ($account_creation) {
				// Set the WP login cookie
				$secure_cookie = is_ssl() ? true : false;
				wp_set_auth_cookie($user_id, true, $secure_cookie);

				// Envoi du mail d'inscription
				wpshop_tools::wpshop_prepared_email($_POST['attribute']['varchar']['user_email'], 'WPSHOP_SIGNUP_MESSAGE', array(
					'customer_first_name' => $_POST['attribute']['varchar']['first_name'],
					'customer_last_name' => $_POST['attribute']['varchar']['last_name']
				));
			}
			$user_preferences = array(
					'newsletters_site' => !empty($_POST['newsletters_site']) && $_POST['newsletters_site']=='on',
					'newsletters_site_partners' => !empty($_POST['newsletters_site_partners']) && $_POST['newsletters_site_partners']=='on'
			);
			update_user_meta($user_id, 'user_preferences', $user_preferences);
		}

		return true;
	}

	/** Return true if the login info is ok and not if not
	 * @return boolean
	*/
	function isRegistered($email_or_username, $password, $login=false) {

		global $wpshop;

		if(!empty($email_or_username)) {
			$user_data = get_user_by('email', $email_or_username);
			// Test connexion par identifiant et par email
			if(user_pass_ok($email_or_username, $password) OR user_pass_ok($user_data->user_login, $password)) {
				if($login) {
					$user_data = empty($user_data) ? get_user_by('login', $email_or_username) : $user_data;
					$user_id = $user_data->ID;
					$secure_cookie = is_ssl() ? true : false;
					// On connecte l'utilisateur
					wp_set_auth_cookie($user_id, true, $secure_cookie);
				}
				return true;
			} else $wpshop->add_error(__('Incorrect login infos', 'wpshop'));
		} else $wpshop->add_error(__('Incorrect login infos', 'wpshop'));
		return false;
	}

	/**
	*	Return output for customer adress
	*
	*	@param array $address_type The customer address stored into an array
	*
	*	@return string $user_address_output The html output for the customer address
	*/
	function display_customer_address($address_type = 'billing', $address_infos){
		global $civility;
		$user_address_output = '';

		$user_address_output .=  '<div class="half"><span >'.__(ucfirst(strtolower($address_type)),'wpshop').'</span><br /><br />';
		$user_address_output .=  (!empty($address_infos['civility']) ? __($civility[$address_infos['civility']], 'wpshop') : null).' <strong>'.$address_infos['first_name'].' '.$address_infos['last_name'].'</strong>';
		$user_address_output .=  empty($address_infos['company'])?'<br />':'<br/><i>' . __('Company', 'wpshop') . '</i>: '.$address_infos['company'].'<br />';
		$user_address_output .=  '<i>' . __('Email address', 'wpshop') . '</i>: '.(!empty($address_infos['email']) ? $address_infos['email'] : ' - ').'<br />';
		$user_address_output .=  '<i>' . __('Phone', 'wpshop') . '</i>: '.(!empty($address_infos['phone']) ? $address_infos['phone'] : ' - ').'<br />';
		$user_address_output .=  $address_infos['address'].'<br />';
		$user_address_output .=  $address_infos['postcode'].' '.$address_infos['city'].', '.$address_infos['country'];
		$user_address_output .=  '</div>';

		return $user_address_output;
	}

	/**
	*	Return output for customer adress
	*
	*	@param array $address_type The customer address stored into an array
	*
	*	@return string $user_address_output The html output for the customer address
	*/
	function edit_customer_address($address_type = 'Billing', $address_infos, $customer_id, $order_state = ''){
		global $civility, $customer_adress_information_field;
		$user_address_output = '';

		$user_info = null;
		if ( !empty( $customer_id ) ) {
			$user_info = get_userdata($customer_id);

			if(empty($address_infos['first_name'])){
				if(!empty($user_info->user_firstname)){
					$address_infos['first_name'] = $user_info->user_firstname;
				}else{
					$address_infos['first_name'] = $user_info->user_login;
				}
			}
			if(empty($address_infos['last_name'])){
				if(!empty($user_info->user_lastname)){
					$address_infos['last_name'] = $user_info->user_lastname;
				}
			}
			if(empty($address_infos['email'])){
				if(!empty($user_info->user_email )){
					$address_infos['email'] = $user_info->user_email ;
				}
			}
		}

		// R�cup�ration de la liste des champs concernant l'adresse des utilisateurs
		foreach ( $customer_adress_information_field as $input_identifier => $input_label ) {

			switch ( $input_identifier ) {
				case 'civility':
					$user_address_output .= '';
					if ( in_array( $order_state, array('awaiting_payment', '') ) ) {
						$user_address_output .= '
<p class="wpshop_customer_adress_edition_input_container wpshop_customer_adress_edition_input_container_' . $input_identifier . '">
	<label class="wpshop_customer_adress_edition_input_label" for="wpshop_customer_adress_edition_input_' . strtolower($address_type) . '_' . $input_identifier . '">' . __( $input_label, 'wpshop') . '</label>
	<select name="user[' . strtolower($address_type) . '_info][' . $input_identifier . ']" id="wpshop_customer_adress_edition_input_' . strtolower($address_type) . '_' . $input_identifier . '" class="wpshop_customer_adress_edition_input wpshop_customer_adress_edition_input_' . $input_identifier . '" >
		<option value="">' . __('Choose...', 'wpshop') . '</option>';
						if ( !empty( $civility ) ) {
							foreach ( $civility as $key => $civil ) {
								$selected = (!empty($address_infos['civility']) && ($address_infos['civility'] == $key) ? ' selected="selected" ' : '');
								$user_address_output .= '<option value="' . $key . '"' . $selected . '>' . __($civil, 'wpshop') . '</option>';
							}
						}
						$user_address_output .= '
	</select>
</p>';
					}
					else {
						$input_value = '';
						if ( !empty( $address_infos[$input_identifier] ) )
							$input_value = __($civility[$address_infos['civility']], 'wpshop');

						ob_start();
						include(WPSHOP_TEMPLATES_DIR.'admin/customer_adress_input_read_only.tpl.php');
						$user_address_output .= ob_get_contents();
						ob_end_clean();
					}
				break;
				default:
					$input_options = '';
					$input_value = '';
					if ( !empty( $address_infos[$input_identifier] ) )
						$input_value = $address_infos[$input_identifier];

					ob_start();
					if ( in_array( $order_state, array('awaiting_payment', '') ) ) {
						include(WPSHOP_TEMPLATES_DIR.'admin/customer_adress_input.tpl.php');
					}
					else {
						include(WPSHOP_TEMPLATES_DIR.'admin/customer_adress_input_read_only.tpl.php');
					}
					$user_address_output .= ob_get_contents();
					ob_end_clean();
				break;
			}

		}

		return $user_address_output;
	}

}

?>