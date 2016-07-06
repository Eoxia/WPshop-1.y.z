<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Define the different tools for the entire plugin
*
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different tools for the entire plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_form_management {

	var $errors = array(); // Stores store errors
	var $messages = array(); // Stores store messages
	var $filters = array(); // Stores store filters

	/**
	* Add an error
	*/
	function add_error( $error ) {
		$this->errors[] = $error;
	}

	/**
	* Add a message
	*/
	function add_message( $message ) {
		$this->messages[] = $message;
	}

		/**
		* Add a filter
		*/
		function add_filter( $attribute_frontend_verification, $function ) {
			$this->filters[$attribute_frontend_verification] = $function;
		}

	/**
	* Get error count
	*/
	function error_count() {
		return sizeof($this->errors);
	}

	/**
	* Get message count
	*/
	function message_count() {
		return sizeof($this->messages);
	}

	/**
	* Output the errors and messages
	*/
	function show_messages() {
		if (!empty($this->errors) && $this->error_count()>0) :
			$message = '<div class="error_bloc">'.__('Errors were detected', 'wpshop').' :<ul>';
			foreach($this->errors as $e) {
				$message .= '<li>'.$e.'</li>';
			}
			$message .= '</ul></div>';
			return $message;
		else :
			return null;
		endif;
	}

	/** Valide les champs d'un formlaire
	* @param array $array : Champs a lire
	* @return boolean
	*/
	function validateForm($array, $values = array(), $from = '', $partial = false, $user = 0) {


		$user_id = empty( $user ) ? get_current_user_id() : $user;
		foreach($array as $attribute_id => $attribute_definition):
			$values_array = !empty($values) ? $values : (array) $_POST['attribute'];
			$value = ( !empty($values_array[$attribute_definition['data_type']][$attribute_definition['name']]) ) ? $values_array[$attribute_definition['data_type']][$attribute_definition['name']] : '';

			// Si le champ est obligatoire
			if ( empty($value) && ($attribute_definition['required'] == 'yes') ) {
				$this->add_error(sprintf(__('The field "%s" is required','wpshop'),__( $attribute_definition['label'], 'wpshop' ) ));
			}
			if( $partial == false && $attribute_definition['_need_verification'] == 'yes'  ) {
				$value2 = $values_array[$attribute_definition['data_type']][$attribute_definition['name'].'2'];
				if ( $value != $value2) {
					$this->add_error(sprintf(__('The  "%s" confirmation is incorrect','wpshop'),__($attribute_definition['label'], 'wpshop') ));
				}
			}
			if(!empty($value) && !empty($attribute_definition['type'])) {
				switch($attribute_definition['frontend_verification']) {
					case 'email':
						$email_exist = email_exists($value);
						if(!is_email($value)) {
							$this->add_error(sprintf(__('The field "%s" is incorrect','wpshop'),$attribute_definition['label']));
						}
						elseif ( empty($from) && (($user_id > 0 && !empty($email_exist) && $email_exist !== $user_id) || (!empty($email_exist) && $user_id <= 0)) ) {
							$this->add_error(__('An account is already registered with your email address. Please login.', 'wpshop'));
						}
					break;

					case 'postcode':
						if(!wpshop_tools::is_postcode($value)) {
							$this->add_error(sprintf(__('The field "%s" is incorrect','wpshop'),__( $attribute_definition['label'], 'wpshop' ) ));
						}
					break;

					case 'phone':
						if(!wpshop_tools::is_phone($value)) {
							$this->add_error(sprintf(__('The field "%s" is incorrect','wpshop'), __( $attribute_definition['label'], 'wpshop' ) ));
						}
					break;

					case 'username':
						$username_exists = username_exists($value);
						// On s'assure que le nom d'utilisateur est libre
						if (!validate_username($value)) :
							$this->add_error( __('Invalid email/username.', 'wpshop') );
						elseif ( ($user_id > 0) && !empty($username_exists) && ($username_exists !== $user_id) || !empty($username_exists) && ($user_id <= 0) ) :
							$this->add_error( __('An account is already registered with that username. Please choose another.', 'wpshop') );
						endif;
					break;
				}
				if (array_key_exists($attribute_definition['name'], $this->filters) && is_callable($this->filters[$attribute_definition['name']])) {
					$validation = call_user_func($this->filters[$attribute_definition['name']], $value);
					if( !filter_var($validation, FILTER_VALIDATE_BOOLEAN) ) {
						$this->add_error( $validation );
					}
				}
			}
		endforeach;

		return ($this->error_count()==0);
	}

}
