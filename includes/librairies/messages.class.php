<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


class wpshop_messages {

	/**
	 *	Call wordpress function that declare a new term type in coupon to define the product as wordpress term (taxonomy)
	 */
	function create_message_type() {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, array(
			'labels' => array(
				'name' 					=> __('Message', 'wpshop'),
				'singular_name' 		=> __('message', 'wpshop'),
				'add_new' 				=> __('Add message', 'wpshop'),
				'add_new_item' 			=> __('Add New message', 'wpshop'),
				'edit' 					=> __('Edit', 'wpshop'),
				'edit_item' 			=> __('Edit message', 'wpshop'),
				'new_item' 				=> __('New message', 'wpshop'),
				'view' 					=> __('View message', 'wpshop'),
				'view_item' 			=> __('View message', 'wpshop'),
				'search_items' 			=> __('Search messages', 'wpshop'),
				'not_found' 			=> __('No message found', 'wpshop'),
				'not_found_in_trash' 	=> __('No message found in trash', 'wpshop'),
				'parent-item-colon' 	=> ''
			),
			'description' 				=> __('This is where store messages are stored.', 'wpshop'),
			'public' 					=> true,
			'show_ui' 					=> true,
			'capability_type' 			=> 'post',
			'publicly_queryable' 		=> false,
			'exclude_from_search' 		=> true,
			'show_in_menu' 				=> false,
			'hierarchical' 				=> false,
			'show_in_nav_menus' 		=> false,
			'rewrite' 					=> false,
			'query_var' 				=> true,
			'supports' 					=> array('title','editor'),
			'has_archive' 				=> false
		));
	}

	function getMessageListOption($current=0) {
		$posts = query_posts(array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE
		));
		$options='';
		if (!empty($posts)) {
			foreach ($posts as $p) {
				$selected = $p->ID==$current ? ' selected="selected"': '';
				$options .= '<option value="'.$p->ID.'"'.$selected.'>'.$p->post_title.'</option>';
			}
		}
		wp_reset_query();
		return $options;
	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		// Ajout de la box info
		add_meta_box(
			'wpshop_message_histo',
			__('Message historic', 'wpshop'),
			array('wpshop_messages', 'message_histo_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, 'normal', 'high'
		);

		// Ajout de la box info
		add_meta_box(
			'wpshop_message_info',
			__('Informations', 'wpshop'),
			array('wpshop_messages', 'message_info_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, 'side', 'low'
		);
	}

	/* Prints the box content */
	function message_histo_box($post, $params) {
		global $wpdb;

		$query = 'SELECT meta_key FROM '.$wpdb->postmeta.' WHERE meta_key LIKE "%wpshop_messages_histo%" AND post_id='.$post->ID;
		$list = $wpdb->get_results($query);

		if (!empty($list)) {

			$string_date = $string_content = $select_date = '';

			foreach ($list as $l) {

				$historic = get_post_meta($post->ID, $l->meta_key, true);

				$date = substr($l->meta_key,22);

				$select_date .= '<option value="'.$date.'">'.$date.'</option>';

				foreach ($historic as $k => $a) {
					$string_content .= '<div class="message">';
					$string_content .= '<b>'.__('Email','wpshop').'</b>: '.$a['mess_user_email'].'<br />';
					$string_content .= '<b>'.__('Title','wpshop').'</b>: '.$a['mess_title'].'<br />';
					$string_content .= '<b>'.__('Message','wpshop').'</b>: '.$a['mess_message'].'<br />';
					$string_content .= '<b>'.__('Number of dispatch','wpshop').'</b>: '.count($a['mess_dispatch_date']).' <input type="hidden" name="messageid" value="'.$post->ID.'-'.$date.'-'.$k.'" /><input type="button" name="resendMessage" value="'.__('Resend message','').'" />';
					$string_content .= '</div><hr />';
				}
			}
			$string_date = substr($string_date,0,-3);
			echo '<select name="date" class="chosen_select">';
			echo $select_date;
			echo '</select><br /><br />';

			echo $string_content;

		}
		else {
			echo '<p>'.__('There is no historic for this message','').'</p>';
		}
	}

	function message_info_box($post, $params) {
		// USERS
		$users = wpshop_customer::getUserList();
		$select_users = '';
		foreach($users as $user) {
			if ($user->ID != 1) {
				$select_users .= '<option value="'.$user->ID.'">'.$user->user_login.'</option>';
			}
		}

		echo '<label>'.__('Recipient','wpshop').'</label><br />';
		echo wpshop_customer::custom_user_list(array('name'=>'recipient', 'id'=>'recipient'), "", false, false);
		/* echo '<select name="recipient" class="chosen_select">';
		echo $select_users;
		echo '</select>'; */

		echo '<input type="hidden" name="wpshop_postid" value="'.$post->ID.'" />';
		echo '<br /><br /><input type="button" class="button-primary alignright" value="'.__('Send the message','wpshop').'" id="sendMessage" /><br /><br />';
	}

	/**
	 * Transfert des messages des tables crées vers la table de wordpress
	 */
	function importMessageFromLastVersion() {
		global $wpdb;
		$tab_objet = $tab_message = array();

		$i=0;
		$messages_code = array('WPSHOP_SIGNUP_MESSAGE', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', 'WPSHOP_ORDER_UPDATE_MESSAGE', 'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE');
		foreach ($messages_code as $code) {

			$object = get_option($code.'_OBJECT', null);
			$object = empty($object) ? constant($code.'_OBJECT') : $object;

			$message = get_option($code, null);
			$message = empty($message) ? constant($code) : $message;

			// Create post object
			$my_post = array(
					'post_title' => __($object, 'wpshop'),
					'post_content' => __($message, 'wpshop'),
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE
			);

			// Insert the post into the database
			$id = wp_insert_post( $my_post );

			update_option($code, $id);

			$tab_objet_en[$id] = $object;
			$tab_message_en[$id] = $message;

			$tab_objet[$id] = __($object, 'wpshop');
			$tab_message[$id] = __($message, 'wpshop');
			$i++;

		}

		$postmeta = array();
		$query = $wpdb->prepare("SELECT *, MESS_HISTO.hist_datetime FROM ".WPSHOP_DBT_MESSAGES." AS MESS INNER JOIN ".WPSHOP_DBT_HISTORIC." AS MESS_HISTO ON (MESS_HISTO.hist_message_id = MESS.mess_id)", '');
		$histo_message = $wpdb->get_results($query);
		$stored_message = array();
		foreach ( $histo_message as $message ) {
			$stored_message[$message->mess_title][] = $message;
		}

		foreach ( $stored_message as $message_subject => $messages ) {
			foreach ( $messages as $message ) {
				if ( in_array($message_subject, $tab_objet) ){
					$id_obj =  array_search($message_subject, $tab_objet);
				}
				elseif ( in_array($message_subject, $tab_objet_en) ){
					$id_obj =  array_search($message_subject, $tab_objet_en);
				}

				if( !empty($id_obj) ) {
					self::add_message($message->mess_user_id,$message->mess_user_email, $message->mess_title, $message->mess_message, $id_obj, array('object_type'=>$message->mess_object_type, 'object_id'=>$message->mess_object_id), $message->hist_datetime);
				}
			}
		}

		$messages_code = array('WPSHOP_SIGNUP_MESSAGE', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', 'WPSHOP_ORDER_UPDATE_MESSAGE', 'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE');
		foreach ($messages_code as $code) {
			$object=constant($code.'_OBJECT');
			$object_components = explode('[', $object);
			if( (count($object_components) > 1) && !empty($object_components[1]) ) {
				$number_of_character = strlen($object_components[0]);
				$query = $wpdb->prepare("SELECT *, MESS_HISTO.hist_datetime FROM ".WPSHOP_DBT_MESSAGES." AS MESS INNER JOIN ".WPSHOP_DBT_HISTORIC." AS MESS_HISTO ON (MESS_HISTO.hist_message_id = MESS.mess_id) WHERE SUBSTRING(mess_title, 1, ".$number_of_character.") = '".$object_components[0]."' OR  SUBSTRING(mess_title, 1, ".$number_of_character.") = '".__($object_components[0], 'wpshop')."'", '');
				$histo_message = $wpdb->get_results($query);
				$stored_message = array();
				foreach ( $histo_message as $message ) {
					$stored_message[$message->mess_title][] = $message;
				}
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE SUBSTRING(post_title, 1, ".$number_of_character.") = '".$object_components[0]."' OR  SUBSTRING(post_title, 1, ".$number_of_character.") = '".__($object_components[0], 'wpshop')."'", '');
				$post_id = $wpdb->get_var($query);
				foreach ( $stored_message as $message_subject => $messages ) {
					foreach ( $messages as $message ) {
						wpshop_messages::add_message($message->mess_user_id,$message->mess_user_email, $message->mess_title, $message->mess_message, $post_id, array('object_type'=>$message->mess_object_type, 'object_id'=>$message->mess_object_id), $message->hist_datetime);
					}
				}
			}
		}

	}

	/** Set the custom colums
	 * @return array
	*/
	function messages_edit_columns($columns) {
	  $columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Name', 'wpshop'),
			'extract' =>__('Extract from the message','wpshop'),
			'date' => __('Creation date','wpshop'),
			'last_dispatch_date' => __('Last dispatch date','wpshop')
	  );

	  return $columns;
	}

	/** Give the content by column
	 * @return array
	*/
	function messages_custom_columns($column) {
		global $post;

		$metadata = get_post_custom();

		switch($column){
			case "extract":
				echo wp_trim_words($post->post_content, 55);
			break;
			case "last_dispatch_date":
				if(!empty($metadata['wpshop_message_last_dispatch_date'][0]))
					echo mysql2date('d F Y, H:i:s',$metadata['wpshop_message_last_dispatch_date'][0], true);
				else
					echo '-';
			break;
		}
	}

	/**
	*
	*/
	function save_message_custom_informations() {
		if(!empty($_REQUEST['post_ID']))
		{
			//$message = get_post_meta($_REQUEST['post_ID'], 'wpshop_message_'.date('my'), true);
			//$message = !empty($message) ? $message : array();

			//$date = current_time('mysql', 0);
			/*$message = array_merge($message, array(
				'recipient' => $_REQUEST['recipient'],
				'email_address' => $_REQUEST['email_address'],
				'creation_date' => $_REQUEST['creation_date'],
				'last_dispatch_date' => $date
			));

			update_post_meta($_REQUEST['post_ID'], 'wpshop_message_'.date('my'), $message);*/
			//update_post_meta($_REQUEST['post_ID'], 'wpshop_message_last_dispatch_date', $date);
		}
	}

	/** Store a new message
	* @return boolean
	*/
	function add_message($recipient_id=0, $email, $title, $message, $model_id, $object, $date = null) {
		$date = empty($date) ? current_time('mysql', 0) : $date;
		$object_empty = array('object_type'=>'','object_id'=>0);
		$object = array_merge($object_empty, $object);

		$historic = get_post_meta($model_id, 'wpshop_messages_histo_'.substr($date, 0, 7), true);

		$historic[] = array(
			'mess_user_id' => $recipient_id,
			'mess_user_email' => $email,
			'mess_object_type' => $object['object_type'],
			'mess_object_id' => $object['object_id'],
			'mess_title' => $title,
			'mess_message' => $message,
			'mess_dispatch_date' => array($date)
		);

		update_post_meta($model_id, 'wpshop_messages_histo_'.substr($date, 0, 7), $historic);

	}

	/**
	 * Add custom message to existing message list, for custom message output when saving new "custom post"
	 *
	 * @param array $messages Default message list
	 * @return array The new message list
	 */
	function update_wp_message_list( $messages) {
		$messages['post'][34070] = __('You have to fill all field marked with a red star');

		return $messages;
	}

}

?>