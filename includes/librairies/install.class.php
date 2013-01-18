<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Plugin installation file.
*
*	This file contains the different methods called when plugin is actived and removed
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	Class defining the different method used when plugin is activated
* @package wpshop
* @subpackage librairies
*/
class wpshop_install{

	/**
	*	Define the action launch when plugin is activate
	*
	* @return void
	*/
	function install_on_activation(){
		/*	Create the different option needed for the plugin work properly	*/
		add_option('wpshop_db_options', array('db_version' => 0));
		add_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);
		add_option('wpshop_shop_default_currency', WPSHOP_SHOP_DEFAULT_CURRENCY);
		add_option('wpshop_emails', array('noreply_mail' => get_bloginfo('admin_email'), 'contact' =>  get_bloginfo('admin_email')));
		add_option('wpshop_catalog_product_option', array('wpshop_catalog_product_slug' => WPSHOP_CATALOG_PRODUCT_SLUG));
		add_option('wpshop_catalog_categories_option', array('wpshop_catalog_categories_slug' => WPSHOP_CATALOG_CATEGORIES_SLUG));
		add_option('wpshop_display_option', array('wpshop_display_list_type' => 'grid', 'wpshop_display_grid_element_number' => '3', 'wpshop_display_cat_sheet_output' => array('category_description', 'category_subcategory', 'category_subproduct')));

		/*	Create the different pages	*/
		self::wpshop_insert_default_pages(WPSHOP_DEFAULT_SHOP_TYPE);
	}

	/**
	*	Create the default pages
	*/
	function wpshop_insert_default_pages($pages_type = ''){
		global $wpdb,$wp_rewrite;

		$default_pages = unserialize(WPSHOP_DEFAULT_PAGES);
		$pages_to_create = array();
		if(!empty($pages_type) && !empty($default_pages[$pages_type]) && is_array($default_pages[$pages_type])){
			$pages_to_create = $default_pages[$pages_type];
		}
		else{
			foreach($default_pages as $page_shop_type => $pages){
				foreach($pages as $page_definition){
					$pages_to_create[] = $page_definition;
				}
			}
		}

		/*	if we will create any new pages we need to flush page cache */
		$page_creation = false;

		/* Default data array for add page */
		$default_add_post_array = array(
			'post_type' 	=>	'page',
			'comment_status'=>	'closed',
			'ping_status' 	=>	'closed',
			'post_status' 	=>	'publish',
			'post_author' 	=>	1
		);

		/*	Rename the basket page into cart page if 	*/
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_basket]%', 'revision');
		$cart_page_id = $wpdb->get_var($query);
		if(!empty($cart_page_id)){
			$query = $wpdb->update($wpdb->posts, array(
				'post_content' => '[wpshop_cart]'
			), array(
				'ID' => $cart_page_id
			));

			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_cart_page_id', $cart_page_id);
			wp_cache_flush();
			wp_cache_delete('all_page_ids', 'pages');
		}

		/*	Check if pages exists. If page does not exist so we create the page	*/
		foreach($pages_to_create as $page_definition){
			$query = $wpdb->prepare("SELECT ID FROM ". $wpdb->posts . " WHERE post_content LIKE %s AND post_type != %s", '%' . $page_definition['post_content'] . '%', 'revision');
			$page = $wpdb->get_var($query);
			if(empty($page)){
				/*	Create the default page for product in front	*/
				$page_id = wp_insert_post(array_merge(array(
					 'post_title' 		=>	__($page_definition['post_title'], 'wpshop'),
					 'post_name'		=>	$page_definition['post_name'],
					 'post_content' 	=>	$page_definition['post_content'],
					 'menu_order' 		=>	$page_definition['menu_order']
				),$default_add_post_array));

				/* On enregistre l'ID de la page dans les options */
				add_option($page_definition['page_code'], $page_id);

				$page_creation = true;
			}
			else{
				/* On enregistre l'ID de la page dans les options */
				add_option($page_definition['page_code'], $page);

				$page_creation = true;
			}
		}

		wp_cache_flush();

		/* If new page => empty cache */
		if($page_creation) {
			wp_cache_delete('all_page_ids', 'pages');
			$wp_rewrite->flush_rules();
		}
	}

	/**
	 * Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
	 */
	function update_wpshop_dev(){
		global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		self::execute_operation_on_db_for_update('dev');
	}
	/**
	 * Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
	 */
	function update_wpshop(){
		global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update;
		$do_changes = false;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$current_db_version = get_option('wpshop_db_options', 0);
		$current_db_version = $current_db_version['db_version'];

		$current_def_max_version = max(array_keys($wpshop_update_way));
		$new_version = $current_def_max_version + 1;
		$version_nb_delta = $current_def_max_version - $current_db_version;

		/*	Check if there are modification to do	*/
		if($current_def_max_version >= $current_db_version){
			/*	Check the lowest version of db to execute	*/
			$lowest_version_to_execute = $current_def_max_version - $version_nb_delta;

			for($i = $lowest_version_to_execute; $i <= $current_def_max_version; $i++){
				$do_changes = self::execute_operation_on_db_for_update($i);
			}
		}

		/*	Update the db version option value	*/
		if($do_changes){
			$db_version = get_option('wpshop_db_options', 0);
			$db_version['db_version'] = $new_version;
			update_option('wpshop_db_options', $db_version);
		}
	}

	/**
	 * Do changes on database for wpshop plugin for a given version
	 *
	 * @param integer $i The wpshop db version to execute operation for
	 *
	 * @return boolean
	 */
	function execute_operation_on_db_for_update($i){
		global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update, $wpshop_db_request, $wpshop_db_delete;
		$do_changes = false;

		/*	Check if there are modification to do	*/
		if(isset($wpshop_update_way[$i])){
			/*	Check if there are modification to make on table	*/
			if(isset($wpshop_db_table_list[$i])){
				foreach($wpshop_db_table_list[$i] as $table_name){
					dbDelta($wpshop_db_table[$table_name]);
				}
				$do_changes = true;
			}

			/********************/
			/*		Insert data		*/
			/********************/
			/*	Options content	*/
			if(isset($wpshop_db_options_add[$i]) && is_array($wpshop_db_options_add) && is_array($wpshop_db_options_add[$i]) && (count($wpshop_db_options_add[$i]) > 0)){
				foreach($wpshop_db_options_add[$i] as $option_name => $option_content){
					add_option($option_name, $option_content, '', 'yes');
				}
				$do_changes = true;
			}
			if(isset($wpshop_db_options_update[$i]) && is_array($wpshop_db_options_update) && is_array($wpshop_db_options_update[$i]) && (count($wpshop_db_options_update[$i]) > 0)){
				foreach($wpshop_db_options_update[$i] as $option_name => $option_content){
					$option_current_content = get_option($option_name);
					foreach($option_content as $option_key => $option_value){
						$option_current_content[$option_key] = $option_value;
					}
					update_option($option_name, $option_current_content);
				}
				$do_changes = true;
			}

			/*	Eav content	*/
			if(isset($wpshop_eav_content[$i]) && is_array($wpshop_eav_content) && is_array($wpshop_eav_content[$i]) && (count($wpshop_eav_content[$i]) > 0)){
				/*	Create entities if entites are set to be created for the current version	*/
				if(isset($wpshop_eav_content[$i]['entities']) && is_array($wpshop_eav_content[$i]['entities']) && is_array($wpshop_eav_content[$i]['entities']) && (count($wpshop_eav_content[$i]['entities']) > 0)){
					foreach($wpshop_eav_content[$i]['entities'] as $entity){
						/*	Creation de l'entité produit dans la table des posts	*/
						wp_insert_post( $entity );
					}
				}

				/*	Create attributes for a given entity if attributes are set to be created for current version	*/
				if(!empty($wpshop_eav_content[$i]['attributes']) && is_array($wpshop_eav_content[$i]['attributes']) && is_array($wpshop_eav_content[$i]['attributes']) && (count($wpshop_eav_content[$i]['attributes']) > 0)){
					foreach($wpshop_eav_content[$i]['attributes'] as $entity_code => $attribute_definition){
						foreach($attribute_definition as $attribute_def){
							$option_list_for_attribute = '';
							if(isset($attribute_def['backend_input_values'])){
								$option_list_for_attribute = $attribute_def['backend_input_values'];
								unset($attribute_def['backend_input_values']);
							}

							/*	Get entity identifier from code	*/
							$attribute_def['entity_id'] = wpshop_entities::get_entity_identifier_from_code($entity_code);
							$attribute_def['status'] = 'valid';
							if(!empty($attribute_def['attribute_status'])){
								$attribute_def['status'] = $attribute_def['attribute_status'];
								unset($attribute_def['attribute_status']);
							}
							$attribute_def['creation_date'] = current_time('mysql', 0);
							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE, $attribute_def);
							$new_attribute_id = $wpdb->insert_id;

							/*	Insert option values if there are some to add for the current attribute	*/
							if(($option_list_for_attribute != '') && (is_array($option_list_for_attribute))){
								foreach($option_list_for_attribute as $option_code => $option_value){
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $new_attribute_id, 'label' => ((substr($option_code, 0, 2) != '__') ? $option_value : __(substr($option_code, 2), 'wpshop')), 'value' => $option_value));
									if($option_code == $attribute_def['default_value']){
										$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $wpdb->insert_id), array('id' => $new_attribute_id, 'default_value' => $option_code));
									}
								}
							}
						}
					}
				}

				/*	Create attribute groups for a given entity if attributes groups are set to be created for current version	*/
				if(isset($wpshop_eav_content[$i]['attribute_groups']) && is_array($wpshop_eav_content[$i]['attribute_groups']) && (count($wpshop_eav_content[$i]['attribute_groups']) > 0)){
					foreach($wpshop_eav_content[$i]['attribute_groups'] as $entity_code => $attribute_set){
						$entity_id = wpshop_entities::get_entity_identifier_from_code($entity_code);

						if($entity_id > 0){
							foreach($attribute_set as $set_name => $set_groups){
								$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = LOWER(%s)", $entity_id, wpshop_tools::slugify($set_name, array('noAccent', 'noSpaces', 'lowerCase')));
								$attribute_set_id = $wpdb->get_var($query);
								if($attribute_set_id <= 0){
									$attribute_set_content = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $entity_id, 'name' => $set_name);
									if($set_name == 'default'){
										$attribute_set_content['default_set'] = 'yes';
									}
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_SET, $attribute_set_content);
									$attribute_set_id = $wpdb->insert_id;
								}

								if($attribute_set_id > 0){
									foreach($set_groups as $set_group_infos){
										$set_group_infos_details = $set_group_infos['details'];
										unset($set_group_infos['details']);
										/*	Change an attribute set status if definition specify this param 	*/
										if(isset($set_group_infos['status'])){
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('last_update_date' => current_time('mysql', 0), 'status' => $set_group_infos['status']), array('id' => $attribute_set_id));
										}
										$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, $set_group_infos['code']);
										$attribute_set_section_id = $wpdb->get_var($query);
										if($attribute_set_section_id <= 0){
											$new_set_section_infos = $set_group_infos;
											$new_set_section_infos['status'] = (isset($new_set_section_infos['status']) ? $new_set_section_infos['status'] : 'valid');
											$new_set_section_infos['creation_date'] = current_time('mysql', 0);
											$new_set_section_infos['attribute_set_id'] = $attribute_set_id;
											$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos);
											$attribute_set_section_id = $wpdb->insert_id;
										}

										if(($attribute_set_section_id > 0) && (isset($set_group_infos_details) && is_array($set_group_infos_details) && (count($set_group_infos_details) > 0))){
											$query = $wpdb->prepare("SELECT MAX(position) AS position FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d", $entity_id, $attribute_set_id, $attribute_set_section_id);
											$last_position = $wpdb->get_var($query);
											$position = (int)$last_position + 1;
											foreach($set_group_infos_details as $attribute_code){
												$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_code, $entity_id);
												$attribute_id = $wpdb->get_var($query);
												if($attribute_id > 0){
													$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $attribute_id, 'position' => $position));
													$position++;
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$do_changes = true;
			}
			/*	Eav content update	*/
			if(isset($wpshop_eav_content_update[$i]) && is_array($wpshop_eav_content_update) && is_array($wpshop_eav_content_update[$i]) && (count($wpshop_eav_content_update[$i]) > 0)){
				/*	Update attributes fo a given entity if attributes are set to be updated for current version	*/
				if(isset($wpshop_eav_content_update[$i]['attributes']) && is_array($wpshop_eav_content_update[$i]['attributes']) && (count($wpshop_eav_content_update[$i]['attributes']) > 0)){
					foreach($wpshop_eav_content_update[$i]['attributes'] as $entity_code => $attribute_definition){
						foreach($attribute_definition as $attribute_def){
							$option_list_for_attribute = '';
							if(isset($attribute_def['backend_input_values'])){
								$option_list_for_attribute = $attribute_def['backend_input_values'];
								unset($attribute_def['backend_input_values']);
							}

							/*	Get entity identifier from code	*/
							$attribute_def['entity_id'] = wpshop_entities::get_entity_identifier_from_code($entity_code);
							$attribute_def['status'] = $attribute_def['attribute_status'];
							unset($attribute_def['attribute_status']);
							$attribute_def['last_update_date'] = current_time('mysql', 0);
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE, $attribute_def, array('code' => $attribute_def['code']));
							$attribute_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_def['code']));

							/*	Insert option values if there are some to add for the current attribute	*/
							if(($option_list_for_attribute != '') && (is_array($option_list_for_attribute))){
								foreach($option_list_for_attribute as $option_code => $option_value){
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $attribute_id, 'label' => ((substr($option_code, 0, 2) != '__') ? $option_value : __(substr($option_code, 2), 'wpshop')), 'value' => $option_value));
									if($option_code == $attribute_def['default_value']){
										$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $wpdb->insert_id), array('id' => $attribute_id, 'default_value' => $option_code));
									}
								}
							}
						}
					}
					$do_changes = true;
				}

				/*	Update attribute groups fo a given entity if attributes groups are set to be updated for current version	*/
				if(is_array($wpshop_eav_content_update[$i]['attribute_groups']) && is_array($wpshop_eav_content_update[$i]['attribute_groups']) && (count($wpshop_eav_content_update[$i]['attribute_groups']) > 0)){
					foreach($wpshop_eav_content_update[$i]['attribute_groups'] as $entity_code => $attribute_set){
						$entity_id = wpshop_entities::get_entity_identifier_from_code($entity_code);

						if($entity_id > 0){
							foreach($attribute_set as $set_name => $set_groups){
								$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = LOWER(%s)", $entity_id, wpshop_tools::slugify($set_name, array('noAccent', 'noSpaces', 'lowerCase')));
								$attribute_set_id = $wpdb->get_var($query);
								if($attribute_set_id <= 0){
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_SET, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $entity_id, 'name' => $set_name));
									$attribute_set_id = $wpdb->insert_id;
								}

								if($attribute_set_id > 0){
									foreach($set_groups as $set_group_infos){
										$set_group_infos_details = $set_group_infos['details'];
										unset($set_group_infos['details']);
										/*	Change an attribute set status if definition specify this param 	*/
										if(isset($set_group_infos['status'])){
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('last_update_date' => current_time('mysql', 0), 'status' => $set_group_infos['status']), array('id' => $attribute_set_id));
										}
										$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, $set_group_infos['code']);
										$attribute_set_section_id = $wpdb->get_var($query);
										if($attribute_set_section_id <= 0){
											$new_set_section_infos = $set_group_infos;
											$new_set_section_infos['status'] = (isset($new_set_section_infos['status']) ? $new_set_section_infos['status'] : 'valid');
											$new_set_section_infos['creation_date'] = current_time('mysql', 0);
											$new_set_section_infos['attribute_set_id'] = $attribute_set_id;
											$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos);
											$attribute_set_section_id = $wpdb->insert_id;
										}
										else{
											$new_set_section_infos = $set_group_infos;
											$new_set_section_infos['last_update_date'] = current_time('mysql', 0);
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos, array('id' => $attribute_set_section_id));
										}

										if(($attribute_set_section_id > 0) && (isset($set_group_infos_details) && is_array($set_group_infos_details))){
											if(count($set_group_infos_details) <= 0){
												$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), array('entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id));
											}
											else{
												$query = $wpdb->prepare("SELECT MAX(position) AS position FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d", $entity_id, $attribute_set_id, $attribute_set_section_id);
												$last_position = $wpdb->get_var($query);
												$position = (int)$last_position + 1;
												foreach($set_group_infos_details as $attribute_code){
													$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_code, $entity_id);
													$attribute_id = $wpdb->get_var($query);
													if($attribute_id > 0){
														$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $attribute_id, 'position' => $position));
														$position++;
													}
												}
											}
										}
									}
								}
							}
						}
					}
					$do_changes = true;
				}
			}

			/*	Add datas	*/
			if(isset($wpshop_db_content_add[$i]) && is_array($wpshop_db_content_add) && is_array($wpshop_db_content_add[$i]) && (count($wpshop_db_content_add[$i]) > 0)){
				foreach($wpshop_db_content_add[$i] as $table_name => $def){
					foreach($def as $information_index => $table_information){
						$wpdb->insert($table_name, $table_information, '%s');
						$do_changes = true;
					}
				}
			}

			/*	Request maker	*/
			if(isset($wpshop_db_request[$i]) && is_array($wpshop_db_request) && is_array($wpshop_db_request[$i]) && (count($wpshop_db_request[$i]) > 0)){
				foreach($wpshop_db_request[$i] as $request){
					$query = $wpdb->prepare($request, '');
					$wpdb->query($query);
					$do_changes = true;
				}
			}

			/*	Update datas	*/
			if(isset($wpshop_db_content_update[$i]) && is_array($wpshop_db_content_update) && is_array($wpshop_db_content_update[$i]) && (count($wpshop_db_content_update[$i]) > 0)){
				foreach($wpshop_db_content_update[$i] as $table_name => $def){
					foreach($def as $information_index => $table_information){
						$wpdb->update($table_name, $table_information['datas'], $table_information['where'], '%s', '%s');
						$do_changes = true;
					}
				}
			}

			/*	Delete datas	*/
			if(isset($wpshop_db_delete[$i]) && is_array($wpshop_db_delete) && is_array($wpshop_db_delete[$i]) && (count($wpshop_db_delete[$i]) > 0)){
				foreach($wpshop_db_delete[$i] as $request){
					$wpdb->query($request);
				}
			}
		}

		$do_changes = self::make_specific_operation_on_update($i);

		return $do_changes;
	}

	/**
	 * Manage special operation on wpshop plugin update
	 */
	function make_specific_operation_on_update($version){
		global $wpdb,$wp_rewrite;
		$wpshop_shop_type = get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);

		switch($version){
			case 3:
				self::wpshop_insert_default_pages($wpshop_shop_type);
				wp_cache_flush();
				return true;
			break;
			case 6:
				self::wpshop_insert_default_pages($wpshop_shop_type);
				wp_cache_flush();
				return true;
			break;
			case 8:
				/*	Update the product prices into database	*/
				$query = $wpdb->prepare("
SELECT
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS product_price,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS price_ht,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS tx_tva,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS tva", 'product_price', 'price_ht', 'tx_tva', 'tva');
				$product_prices = $wpdb->get_row($query);
				$tax_id = $wpdb->get_var($wpdb->prepare("SELECT ATT_OPT.id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATT_OPT WHERE attribute_id = %d AND value = '19.6'", $product_prices->tx_tva));
				$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " WHERE attribute_id = %d", $product_prices->product_price);
				$price_list = $wpdb->get_results($query);
				foreach($price_list as $existing_ttc_price){
					$tax_rate = 1.196;
					$price_ht = $existing_ttc_price->value / $tax_rate;
					$tax_amount = $existing_ttc_price->value - $price_ht;

					$wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->price_ht, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $price_ht, 'creation_date_value' => current_time('mysql', 0)));
					$wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->tx_tva, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $tax_id, 'creation_date_value' => current_time('mysql', 0)));
					$wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->tva, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $tax_amount, 'creation_date_value' => current_time('mysql', 0)));
				}

				/*	Update orders structure into database	*/
				$orders_id = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'"');
				foreach($orders_id as $o){
					$myorder = get_post_meta($o->ID, '_order_postmeta', true);
					$neworder = array();
					$items = array();

					if(!isset($myorder['order_tva'])){
						$order_total_ht = 0;
						$order_total_ttc = 0;
						$order_tva = array('19.6'=>0);

						foreach($myorder['order_items'] as $item){
							/* item */
							$pu_ht = $item['cost']/1.196;
							$pu_tva = $item['cost']-$pu_ht;
							$total_ht = $pu_ht*$item['qty'];
							$tva_total_amount = $pu_tva*$item['qty'];
							$total_ttc = $item['cost']*$item['qty'];
							/* item */
							$order_total_ht += $total_ht;
							$order_total_ttc += $total_ttc;
							$order_tva['19.6'] += $tva_total_amount;

							$items[] = array(
								'item_id' => $item['id'],
								'item_ref' => 'Nc',
								'item_name' => $item['name'],
								'item_qty' => $item['qty'],

								'item_pu_ht' => number_format($pu_ht, 5, '.', ''),
								'item_pu_ttc' => number_format($item['cost'], 5, '.', ''),

								'item_ecotaxe_ht' => number_format(0, 5, '.', ''),
								'item_ecotaxe_tva' => 19.6,
								'item_ecotaxe_ttc' => number_format(0, 5, '.', ''),

								'item_discount_type' => 0,
								'item_discount_value' => 0,
								'item_discount_amount' => number_format(0, 5, '.', ''),

								'item_tva_rate' => 19.6,
								'item_tva_amount' => number_format($pu_tva, 5, '.', ''),

								'item_total_ht' => number_format($total_ht, 5, '.', ''),
								'item_tva_total_amount' => number_format($tva_total_amount, 5, '.', ''),
								'item_total_ttc' => number_format($total_ttc, 5, '.', '')
								/*'item_total_ttc_with_ecotaxe' => number_format($total_ttc, 5, '.', '')*/
							);
						}

						$neworder = array(
							'order_key' => $myorder['order_key'],
							'customer_id' => $myorder['customer_id'],
							'order_status' => $myorder['order_status'],
							'order_date' => $myorder['order_date'],
							'order_payment_date' => $myorder['order_payment_date'],
							'order_shipping_date' => $myorder['order_shipping_date'],
							'payment_method' => $myorder['payment_method'],
							'order_invoice_ref' => '',
							'order_currency' => $myorder['order_currency'],
							'order_total_ht' => $order_total_ht,
							'order_total_ttc' => $order_total_ttc,
							'order_grand_total' => $order_total_ttc,
							'order_shipping_cost' => number_format(0, 5, '.', ''),
							'order_tva' => array_map('number_format_hack', $order_tva),
							'order_items' => $items
						);
						/* Update the order postmeta */
						update_post_meta($o->ID, '_order_postmeta', $neworder);
					}
				}

				self::wpshop_insert_default_pages($wpshop_shop_type);
				wp_cache_flush();
				return true;
			break;
			case 12:
				$query = $wpdb->prepare("SELECT ID FROM $wpdb->users", '');
				$user_list = $wpdb->get_results($query);
				foreach($user_list as $user){
					$user_first_name = get_user_meta($user->ID, 'first_name', true);
					$user_last_name = get_user_meta($user->ID, 'last_name', true);
					$shipping_info = get_user_meta($user->ID, 'shipping_info', true);

					if(($user_first_name == '') && !empty($shipping_info['first_name'])){
						update_user_meta($user->ID, 'first_name', $shipping_info['first_name']);
					}

					if(($user_last_name == '') && !empty($shipping_info['last_name'])){
						update_user_meta($user->ID, 'last_name', $shipping_info['last_name']);
					}
				}

				/*	Update orders structure into database	*/
				$orders_id = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'"');
				foreach($orders_id as $o){
					$myorder = get_post_meta($o->ID, '_order_postmeta', true);
					if(!empty($myorder)){
						$new_items = array();
						foreach($myorder['order_items'] as $item){
							$new_items = $item;
							$new_items['item_discount_type'] = !empty($item['item_discount_rate'])?$item['item_discount_rate']:'amount';
							// unset($new_items['item_discount_rate']);
							$new_items['item_discount_value'] = 0;
						}
						$myorder['order_items'] = $new_items;

						/* Update the order postmeta */
						update_post_meta($o->ID, '_order_postmeta', $myorder);
					}
				}

				/*	Delete useless database table	*/
				$query = $wpdb->prepare("DROP TABLE " . WPSHOP_DBT_CART, '');
				$wpdb->query($query);
				$query = $wpdb->prepare("DROP TABLE " . WPSHOP_DBT_CART_CONTENTS, '');
				$wpdb->query($query);
				return true;
			break;
			case 13:
				$attribute_used_for_sort_by = wpshop_attributes::getElement('yes', "'valid', 'moderated', 'notused'", 'is_used_for_sort_by', true);
				foreach($attribute_used_for_sort_by as $attribute){
					$data = query_posts(array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					foreach($data as $post){
						$postmeta = get_post_meta($post->ID, '_wpshop_product_metadata', true);
						if(!empty($postmeta[$attribute->code])) {
							update_post_meta($post->ID, '_'.$attribute->code, $postmeta[$attribute->code]);
						}
					}
					wp_reset_query();
				}
				return true;
			break;
			case 17:
				$products = query_posts(array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)
				);
				$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE default_set = %s", 'yes');
				$default_attribute_set = $wpdb->get_var($query);
				foreach($products as $product){
					$p_att_set_id = get_post_meta($product->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
					if(empty($p_att_set_id)){
						/*	Update the attribute set id for the current product	*/
						update_post_meta($product->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $default_attribute_set);
					}
					wp_reset_query();
				}
				self::wpshop_insert_default_pages($wpshop_shop_type);
				wp_cache_flush();
				return true;
			break;
			case 18:
				self::wpshop_insert_default_pages($wpshop_shop_type);
				wp_cache_flush();
				return true;
			break;
			case 19:
				$wp_rewrite->flush_rules();
				return true;
			break;

			case 21:
				/**
				 * Correction des valeurs pour l'attributs "gestion du stock" qui n'�taient pas cr�es automatiquement
				 */
				$query = $wpdb->prepare("SELECT ATTR_OPT.id, ATTR_OPT.value, ATTR_OPT.label, ATTR_OPT.position, ATTR_OPT.attribute_id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATTR_OPT INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR ON (ATTR.id = ATTR_OPT.attribute_id) WHERE ATTR_OPT.status=%s AND ATTR.code=%s", 'valid', 'manage_stock');
				$manage_stock_option = $wpdb->get_results($query);
				if( !empty( $manage_stock_option ) ){
					$no_is_present = false;
					$attribute_id = $manage_stock_option[0]->attribute_id;
					foreach ($manage_stock_option as $manage_definition) {
						if($manage_definition->value == 'no'){
							$no_is_present = true;
						}
					}
					if ( !$no_is_present ) {
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'valid', 'creation_date'=>current_time('mysql',0), 'last_update_date'=>current_time('mysql',0),'attribute_id'=>$attribute_id, 'value'=>'no', 'label'=>__('No', 'wpshop')));
					}
				}

				/**
				 * Transfert des messages de la base ajout�e vers la base de wordpress en vue de la suppression de la base ajout�e
				 */
				wpshop_messages::importMessageFromLastVersion();

				/**
				 * Change price attribute set section order for default set
				 */
				$price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
				unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);
				$query = $wpdb->prepare("SELECT GROUP_CONCAT(id) FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code IN ('" . implode("','", $price_tab) . "')", "");
				$attribute_ids = $wpdb->get_var($query);

				$query = $wpdb->prepare("
SELECT ATTR_DET.attribute_group_id
FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTR_DET
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTR_GROUP ON ((ATTR_GROUP.id = ATTR_DET.attribute_group_id) AND (ATTR_GROUP.code = %s))
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTR_SET ON ((ATTR_SET.id = ATTR_GROUP.attribute_set_id) AND (ATTR_SET.name = %s))
WHERE ATTR_DET.attribute_id IN (" . $attribute_ids . ")"
						, 'prices', __('default', 'wpshop'));
				$list = $wpdb->get_results($query);
				if(!empty($list)){
					$change_order = true;
					$old_value = $list[0]->attribute_group_id;
					unset($list[0]);
					if(!empty($list)){
						foreach ($list as $data) {
							if ( $old_value !=  $data->attribute_group_id) {
								$change_order = false;
							}
						}
						if ($change_order) {
							foreach($price_tab as $price_code){
								$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $price_code);
								$attribute_id = $wpdb->get_var($query);
								switch($price_code){
									case WPSHOP_PRODUCT_PRICE_HT:
										$position = ( WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ) ? 1 : 3;
										break;
									case WPSHOP_PRODUCT_PRICE_TAX:
										$position = 2;
										break;
									case WPSHOP_PRODUCT_PRICE_TTC:
										$position = ( WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ) ? 3 : 1;
										break;
									case WPSHOP_PRODUCT_PRICE_TAX_AMOUNT:
										$position = 4;
										break;
								}
								$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status'=>'valid', 'last_update_date'=>current_time('mysql', 0), 'position'=>$position), array('attribute_group_id'=>$old_value, 'attribute_id'=>$attribute_id));
							}
						}
					}
				}
				return true;
			break;
			case 22:
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
				$product_entity_id = $wpdb->get_var($query);
				if(empty($product_entityd_id) || ($product_entity_id <= 0) || !$product_entity_id){
					/*	Create the product entity into post table	*/
					$product_entity = array(
						'post_title' => __('Products', 'wpshop'),
						'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
						'post_content' => __('Define the entity allowing to manage product on your store. If you delete this entity you won\'t be able to manage your store', 'wpshop'),
						'post_status' => 'publish',
						'post_author' => 1,
						'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES
					);
					$product_entity_id = wp_insert_post( $product_entity );
				}

				/*	Update eav table with the new entity id for product	*/
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('entity_id'=>$product_entity_id), array('entity_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('entity_id'=>$product_entity_id), array('entity_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, array('entity_type_id'=>$product_entity_id), array('entity_type_id'=>1));

				/*	Create an element of customer entity for each existing user	*/
				$user_list = get_users();
				foreach ($user_list as $user) {
					wpshop_entities::create_entity_customer_when_user_is_created($user->ID);
				}

				return true;
			break;
			case 23:
				/*	Delete duplicate entities	*/
				$query = ("SELECT ID FROM " . $wpdb->posts . " WHERE post_name LIKE '%".WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT."%' ");
				$product_entity_list = $wpdb->get_results($query);
				if ( count($product_entity_list) > 1 ) {
					$i = 0;
					foreach($product_entity_list as $product_entity){
						if ($i>0) {
							wp_delete_post($product_entity->ID);
						}
					}
				}
				return true;
			break;
			case 24:
				/*	Update the link status for disabled attribute set	*/
				$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE status = %s", 'deleted');
				$deleted_attribute_group = $wpdb->get_results($query);
				if(!empty($deleted_attribute_group)) {
					foreach($deleted_attribute_group as $group){
						$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'deleted', 'last_update_date' => current_time('mysql', 0)), array('attribute_group_id' => $group->id));
					}
				}

				/*	Update entities meta management	*/
				$entities = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES));
				if(!empty($entities)){
					foreach($entities as $entity){
						$support = get_post_meta($entity->ID, '_wpshop_entity_support', true);
						$rewrite = get_post_meta($entity->ID, '_wpshop_entity_rewrite', true);
						update_post_meta($entity->ID, '_wpshop_entity_params', array('support' => $support, 'rewrite' => array('slug' => $rewrite)));
					}
				}
				wp_reset_query();
				return true;
			break;
			case 25:
				/*	Get the first entities of product and customer	*/
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name=%s AND post_type=%s ORDER BY ID ASC LIMIT 1", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES);
				$product_entity_id = $wpdb->get_var($query);

				/*	Update attributes that are not linked with entities	*/
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('entity_id'=>$product_entity_id), array('entity_id'=>0));

				/*	Get entities that have been created a lot of time and delete them	*/
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE (post_name LIKE '%%" . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "-%%' OR post_name LIKE '%%" . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . "-%%') AND post_type=%s", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES);
				$entities_to_delete = $wpdb->get_results($query);
				if ( !empty($entities_to_delete) && is_array($entities_to_delete) ) {
					foreach ($entities_to_delete as $entity) {
						wp_delete_post($entity->ID, true);
					}
				}

				/*	Get post list that are children of entities created a lot of time */
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . "-%");
				$entities_to_update = $wpdb->get_results($query);
				if ( !empty($entities_to_update) && is_array($entities_to_update) ) {
					foreach ($entities_to_update as $entity) {
						wp_update_post(array('ID' => $entity->ID, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
					}
				}
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "-%");
				$entities_to_update = $wpdb->get_results($query);
				if ( !empty($entities_to_update) && is_array($entities_to_update) ) {
					foreach ($entities_to_update as $entity) {
						wp_update_post(array('ID' => $entity->ID, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					}
				}

				/*	Change addons managament	*/
				$wpshop_addons_options = get_option('wpshop_addons_state', array());
				if ( !empty($wpshop_addons_options) ) {
					foreach ($wpshop_addons_options as $addon_name => $addon_state ) {
						$options_args = array();
						$options_args[$addon_name]['activate'] = $addon_state;
						$options_args[$addon_name]['activation_date'] = current_time('mysql', 0);
						if (! $addon_state ) $options_args[$addon_name]['deactivation_date'] = current_time('mysql', 0);
						add_option(WPSHOP_ADDONS_OPTION_NAME, $options_args);
					}
					delete_option('wpshop_addons_state');
				}

				/*	Update the different entities id into attribute set details table	*/
				$query = $wpdb->prepare("UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATT_DET INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DET.attribute_id) SET ATT_DET.entity_type_id = ATT.entity_id", "");
				$wpdb->query($query);

				return true;
			break;
			case 26:
				$query = $wpdb->prepare("SELECT post_id, meta_value FROM " .$wpdb->postmeta. " WHERE meta_key = '_order_postmeta' ", '');
				$results = $wpdb->get_results($query);
				foreach ($results as $result) {
					$order_info = unserialize($result->meta_value);
					update_post_meta($result->post_id, '_wpshop_order_customer_id', $order_info['customer_id']);
					update_post_meta($result->post_id, '_wpshop_order_shipping_date', $order_info['order_shipping_date']);
					update_post_meta($result->post_id, '_wpshop_order_status', $order_info['order_status']);
					update_post_meta($result->post_id, '_wpshop_order_payment_date', $order_info['order_payment_date']);
					update_post_meta($result->post_id, '_wpshop_payment_method', $order_info['payment_method']);
				}

				/*	Update the different entities id into attribute set details table	*/
				$query = $wpdb->prepare("UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATT_DET INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DET.attribute_id) SET ATT_DET.entity_type_id = ATT.entity_id", "");
				$wpdb->query($query);

				return true;
			break;

			case 29:
					$address_entity = array(
							'post_title' => __('Address', 'wpshop'),
							'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
							'post_content' => __('Includes the addresses of a customer', 'wpshop'),
							'post_status' => 'publish',
							'post_author' => 1,
							'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES
						);
					$address_entity_id = wp_insert_post( $address_entity );

					/* Billing Address */
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Address title', 'wpshop'), 'frontend_label' => __('Address title', 'wpshop'), 'frontend_input' => 'text', 'code' => 'address_title') );
					$titre_adresse_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'integer', 'backend_input' => 'select', 'backend_label' => __('Civility', 'wpshop'), 'frontend_label' => __('Civility', 'wpshop'), 'frontend_input' => 'select', 'code' => 'civility') );
					$civility_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Last name', 'wpshop'), 'frontend_label' => __('Last name', 'wpshop'), 'frontend_input' => 'text', 'code' => 'address_last_name') );
					$last_name_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('First name', 'wpshop'), 'frontend_label' => __('First name', 'wpshop'), 'frontend_input' => 'text', 'code' => 'address_first_name') );
					$first_name_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'no', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' =>  __('Company', 'wpshop'), 'frontend_label' =>  __('Company', 'wpshop'), 'frontend_input' => 'text', 'code' => 'company') );
					$company_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'no', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('TVA Intracommunautaire', 'wpshop'), 'frontend_label' => __('TVA Intracommunautaire', 'wpshop'), 'frontend_input' => 'text', 'code' => 'tva_intra') );
					$tva_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Email Address', 'wpshop'), 'frontend_label' => __('Email Address', 'wpshop'), 'frontend_input' => 'text', 'code' => 'address_user_email', '_need_verification' => 'yes', 'frontend_verification' => 'email') );
					$email_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Address', 'wpshop'), 'frontend_label' => __('Address', 'wpshop'), 'frontend_input' => 'text', 'code' => 'address') );
					$address_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('City', 'wpshop'), 'frontend_label' => __('City', 'wpshop'), 'frontend_input' => 'text', 'code' => 'city') );
					$city_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Postcode', 'wpshop'), 'frontend_label' =>__('Postcode', 'wpshop'), 'frontend_input' => 'text', 'code' => 'postcode', 'frontend_verification' => 'postcode') );
					$postcode_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Country', 'wpshop'), 'frontend_label' =>__('Country', 'wpshop'), 'frontend_input' => 'text', 'code' => 'country', 'frontend_verification' => 'country') );
					$country_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'no', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('State/County', 'wpshop'), 'frontend_label' =>__('State/County', 'wpshop'), 'frontend_input' => 'text', 'code' => 'state', 'frontend_verification' => 'state') );
					$state_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Phone', 'wpshop'), 'frontend_label' =>__('Phone', 'wpshop'), 'frontend_input' => 'text', 'code' => 'phone', 'frontend_verification' => 'phone') );
					$phone_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'no', 'data_type' => 'varchar', 'backend_input' => 'hidden', 'backend_label' => __('Longitude', 'wpshop'), 'frontend_label' =>__('Longitude', 'wpshop'), 'frontend_input' => 'hidden', 'code' => 'longitude') );
					$longitude_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'no', 'data_type' => 'varchar', 'backend_input' => 'hidden', 'backend_label' => __('Latitude', 'wpshop'), 'frontend_label' =>__('Latitude', 'wpshop'), 'frontend_input' => 'hidden', 'code' => 'latitude') );
					$latitude_id = $wpdb->insert_id;
					/* Attribute Set */
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_SET, array( 'status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'name' => __('Billing address', 'wpshop') ) );
					$attribute_set_id_billing = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_SET, array( 'status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $address_entity_id, 'name' => __('Shipping address', 'wpshop') ) );
					$attribute_set_id_shipping = $wpdb->insert_id;

					/* Attribute Set Section*/
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_GROUP, array( 'status' => 'valid', 'attribute_set_id' => $attribute_set_id_billing, 'creation_date' => current_time('mysql', 0), 'display_on_frontend' => 'no', 'code' => 'billing_address', 'name' => __('Billing address', 'wpshop') ) );
					$id_attribute_set_section_billing = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_GROUP, array( 'status' => 'valid', 'attribute_set_id' => $attribute_set_id_shipping, 'creation_date' => current_time('mysql', 0), 'display_on_frontend' => 'no','code' => 'shipping_address', 'name' => __('Shipping address', 'wpshop') ) );
					$id_attribute_set_section_shipping = $wpdb->insert_id;

					/* Attribute set section details */
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $titre_adresse_id, 'position' => 1));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $civility_id, 'position' => 2));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $last_name_id, 'position' => 3));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $first_name_id, 'position' => 4));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $company_id, 'position' => 5));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $address_id, 'position' => 6));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $postcode_id, 'position' => 7));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $city_id, 'position' => 8));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $state_id, 'position' => 9));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $country_id, 'position' => 10));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $tva_id, 'position' => 11));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $phone_id, 'position' => 12));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $email_id, 'position' => 13));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $longitude_id, 'position' => 14));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_billing, 'attribute_group_id' => $id_attribute_set_section_billing, 'attribute_id' => $latitude_id, 'position' => 15));

					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $titre_adresse_id, 'position' => 1));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $civility_id, 'position' => 2));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $last_name_id, 'position' => 3));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $first_name_id, 'position' => 4));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $company_id, 'position' => 5));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $address_id, 'position' => 6));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $postcode_id, 'position' => 7));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $city_id, 'position' => 8));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $state_id, 'position' => 9));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $country_id, 'position' => 10));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $longitude_id, 'position' => 11));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $address_entity_id, 'attribute_set_id' => $attribute_set_id_shipping, 'attribute_group_id' => $id_attribute_set_section_shipping, 'attribute_id' => $latitude_id, 'position' => 12));

					$customer_entity_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $customer_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Username', 'wpshop'), 'frontend_label' => __('User name', 'wpshop'), 'frontend_input' => 'text', 'code' => 'user_login', 'frontend_verification' => 'username', 'is_unique' => 'yes') );
					$user_name_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $customer_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'password', 'backend_label' => __('Password', 'wpshop'), 'frontend_label' => __('Password', 'wpshop'), 'frontend_input' => 'password', 'code' => 'user_pass', '_need_verification' => 'yes') );
					$password_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $customer_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Email Address', 'wpshop'), 'frontend_label' => __('Email Address', 'wpshop'), 'frontend_input' => 'text', 'code' => 'user_email', '_need_verification' => 'yes', 'frontend_verification' => 'email', 'is_unique' => 'yes') );
					$email_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $customer_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('Last name', 'wpshop'), 'frontend_label' => __('Last name', 'wpshop'), 'frontend_input' => 'text', 'code' => 'last_name') );
					$last_name_id = $wpdb->insert_id;
					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $customer_entity_id, 'is_visible_in_front' => 'no', 'is_required' => 'yes', 'data_type' => 'varchar', 'backend_input' => 'text', 'backend_label' => __('First name', 'wpshop'), 'frontend_label' => __('First name', 'wpshop'), 'frontend_input' => 'text', 'code' => 'first_name') );
					$first_name_id = $wpdb->insert_id;

					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_SET, array( 'status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $customer_entity_id, 'name' => __('Account', 'wpshop'), 'default_set' => 'yes' ) );
					$attribute_set_id_account = $wpdb->insert_id;

					$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_GROUP, array( 'status' => 'valid', 'attribute_set_id' => $attribute_set_id_account, 'creation_date' => current_time('mysql', 0), 'display_on_frontend' => 'no', 'code' => 'account', 'name' => __('Account', 'wpshop') ) );
					$id_attribute_set_section_account = $wpdb->insert_id;

					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $customer_entity_id, 'attribute_set_id' => $attribute_set_id_account, 'attribute_group_id' => $id_attribute_set_section_account, 'attribute_id' => $last_name_id, 'position' => 1));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $customer_entity_id, 'attribute_set_id' => $attribute_set_id_account, 'attribute_group_id' => $id_attribute_set_section_account, 'attribute_id' => $first_name_id, 'position' => 2));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $customer_entity_id, 'attribute_set_id' => $attribute_set_id_account, 'attribute_group_id' => $id_attribute_set_section_account, 'attribute_id' => $user_name_id , 'position' => 3));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $customer_entity_id, 'attribute_set_id' => $attribute_set_id_account, 'attribute_group_id' => $id_attribute_set_section_account, 'attribute_id' => $email_id, 'position' => 4));
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $customer_entity_id, 'attribute_set_id' => $attribute_set_id_account, 'attribute_group_id' => $id_attribute_set_section_account, 'attribute_id' => $password_id, 'position' => 5));

					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $civility_id, 'position' => 1, 'value' => __('Mister', 'wpshop'), 'label' => __('Mister', 'wpshop')));
					$mister_id = $wpdb->insert_id;
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $civility_id, 'position' => 2, 'value' => __('Madam', 'wpshop'), 'label' => __('Madam', 'wpshop')));
					$madam_id = $wpdb->insert_id;
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $civility_id, 'position' => 3, 'value' => __('Miss', 'wpshop'), 'label' => __('Miss', 'wpshop')));
					$miss_id = $wpdb->insert_id;
					//Add Address & Google Map API KEY options
					add_option( 'wpshop_billing_address', array('choice' => $attribute_set_id_billing), '', 'yes' );
					add_option( 'wpshop_shipping_address_choice', array('activate'=>'on', 'choice'=> $attribute_set_id_shipping), '', 'yes' );
					add_option( 'wpshop_google_map_api_key', '', '', 'yes' );

					//UPDATE USERS ADDRESSES
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->users. '', '');
					$results = $wpdb->get_results($query);
					$billing_address_set_id_query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE name = "' .__('Billing address', 'wpshop'). '"', '');
					$shipping_address_set_id_query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE name = "' .__('Shipping address', 'wpshop'). '"', '');
					$billing_address_set_id = $wpdb->get_var($billing_address_set_id_query);
					$shipping_address_set_id = $wpdb->get_var($shipping_address_set_id_query);

					foreach ($results as $result) {
						$billing_infos = get_user_meta( $result->ID, 'billing_info', true );
						$shipping_infos = get_user_meta( $result->ID, 'shipping_info', true );
						if ( !empty($billing_infos) ) {
							//Save Billing Infos
							$billing_address = array();
							switch ( $billing_infos['civility'] ) {
								case 1:
									$civility = $mister_id ;
								break;
								case 2:
									$civility = $madam_id;
								break;
								case 3:
									$civility = $miss_id;
								break;
							}
							$billing_title = __('Billing address', 'wpshop');
							$billing_address = array('address_title' => $billing_title,
									'address_last_name' => $billing_infos['last_name'],
									'address_first_name' => $billing_infos['first_name'],
									'company' => $billing_infos['company'],
									'address' => $billing_infos['address'],
									'postcode' => $billing_infos['postcode'],
									'city' => $billing_infos['city'],
									'state' => $billing_infos['state'],
									'country' => $billing_infos['country'],
									'address_user_email' => $billing_infos['email'],
									'phone' => $billing_infos['phone'],
									'tva_intra' => $billing_infos['company_tva_intra'],
									'civility' => $civility
							);
							//Create the post and post_meta for the billing address
							$post_address = array('post_author' => $result->ID,
									'post_title' => $billing_title,
									'post_status' => 'publish',
									'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
									'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
									'post_parent' => $result->ID
							);
							$post_address_id = wp_insert_post($post_address);

							//Create the post_meta with the address infos

							update_post_meta($post_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', $billing_address);
							update_post_meta($post_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_attribute_set_id', $billing_address_set_id);
						}
						if ( !empty($shipping_infos) ) {
							//Save Shipping Infos
							if ( !empty($shipping_infos['civility']) ) {
								switch ( $shipping_infos['civility'] ) {
									case 1:
										$civility = $mister_id ;
										break;
									case 2:
										$civility = $madam_id;
										break;
									case 3:
										$civility = $miss_id;
										break;
								}
							}
							else {
								$civility = $mister_id ;
							}
							$shipping_address = array();
							$shipping_title = __('Shipping address', 'wpshop');
							$shipping_address = array('address_title' => $shipping_title,
									'address_last_name' => $shipping_infos['last_name'],
									'address_first_name' => $shipping_infos['first_name'],
									'company' => $shipping_infos['company'],
									'address' => $shipping_infos['address'],
									'postcode' => $shipping_infos['postcode'],
									'city' => $shipping_infos['city'],
									'state' => $shipping_infos['state'],
									'country' => $shipping_infos['country'],
									'civility' => $civility
							);
							//Create the post and post_meta for the billing address
							$post_address = array('post_author' => $result->ID,
									'post_title' => $shipping_title,
									'post_status' => 'publish',
									'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
									'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
									'post_parent' => $result->ID
							);
							$post_address_id = wp_insert_post($post_address);
							//Create the post_meta with the address infos
							update_post_meta($post_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', $shipping_address);
							update_post_meta($post_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_attribute_set_id', $shipping_address_set_id);
						}
					}
					// FORMATE THE ORDER ADDRESSES INFOS
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ORDER. '"', '');
					$results = $wpdb->get_results($query);
					$results = query_posts( array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'post_per_page' => -1) );
					foreach ( $results as $result ) {
						$address = get_post_meta($result->ID, '_order_info', true);
						if ( !empty($address['billing']['civility']) ) {
							switch ( $address['billing']['civility'] ) {
								case 1:
									$civility = $mister_id ;
									break;
								case 2:
									$civility = $madam_id;
									break;
								case 3:
									$civility = $miss_id;
									break;
								default:
									$civility = $mister_id ;
									break;
							}
						}
						else {
							$civility = $mister_id ;
						}
						$billing_address = array('address_title' => $billing_title,
								'address_last_name' => $address['billing']['last_name'],
								'address_first_name' => $address['billing']['first_name'],
								'company' => $address['billing']['company'],
								'address' => $address['billing']['address'],
								'postcode' => $address['billing']['postcode'],
								'city' => $address['billing']['city'],
								'state' => $address['billing']['state'],
								'country' => $address['billing']['country'],
								'address_user_email' => $address['billing']['email'],
								'phone' => $address['billing']['phone'],
								'tva_intra' => $address['billing']['company_tva_intra'],
								'civility' => $civility
						);
						switch ( $address['shipping']['civility'] ) {
							case 1:
								$civility = $mister_id ;
								break;
							case 2:
								$civility = $madam_id;
								break;
							case 3:
								$civility = $miss_id;
								break;
						}
						$shipping_address = array('address_title' => $shipping_title,
								'address_last_name' => $address['shipping']['last_name'],
								'address_first_name' => $address['shipping']['first_name'],
								'company' => $address['shipping']['company'],
								'address' => $address['shipping']['address'],
								'postcode' => $address['shipping']['postcode'],
								'city' => $address['shipping']['city'],
								'state' =>$address['shipping']['state'],
								'country' => $address['shipping']['country'],
								'civility' => $civility
						);


						$billing_array_content = array('id'=>$billing_address_set_id, 'address'=>$billing_address);
						$shipping_array_content = array('id'=>$shipping_address_set_id, 'address'=>$shipping_address);
						$array_new_format = array('billing' => $billing_array_content, 'shipping' => $shipping_array_content);

						//Update the post meta
						update_post_meta($result->ID, '_order_info', $array_new_format);
					}
					/*	Update entities meta management	*/
					$entities = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES));
					if(!empty($entities)){
						foreach($entities as $entity){
							$params = get_post_meta($entity->ID, '_wpshop_entity_params', true);
							$support = ( !empty($params['support']) ) ? $params['support'] : '';
							$rewrite = ( !empty($params['rewrite']) ) ? $params['rewrite'] : '';
							if( $entity->ID == $address_entity_id ) {
								$display_admin_menu = '';
							}
							else {
								$display_admin_menu = 'on';
							}
							update_post_meta($entity->ID, '_wpshop_entity_params', array('support' => $support, 'rewrite' => $rewrite, 'display_admin_menu' => $display_admin_menu));
						}
					}
					wp_reset_query();
					// Default Weight unity and Currency Options
					add_option( 'wpshop_shop_weight_group', 3, '', 'yes' );
					add_option( 'wpshop_shop_default_weight_unity', 6, '', 'yes' );
					add_option( 'wpshop_shop_currency_group', 4, '', 'yes' );


					$default_currency = get_option('wpshop_shop_default_currency');
					foreach ( unserialize(WPSHOP_SHOP_CURRENCIES) as $k=>$v ) {
						if ( $default_currency == $k ) {
							$symbol = $v;
						}
					}

					$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE unit = "'.html_entity_decode($symbol, ENT_QUOTES, 'UTF-8').'"', '');
					$currency = $wpdb->get_row($query);
					if ( !empty($currency) ) {
						update_option('wpshop_shop_default_currency', $currency->id);
						// Update the change rate of the default currency
						$wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('change_rate' => 1), array('id' => $currency->id) );
					}
					// Update the field for variation and user definition field
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_for_variation' => 'yes'), array('is_user_defined' => 'yes') );
					// Update field type for frontend output selection
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'text'), array());
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'textarea'), array('backend_input' => 'textarea') );
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'select'), array('backend_input' => 'multiple-select') );
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'select'), array('backend_input' => 'select') );

					add_option('wpshop_cart_option', array( 'product_added_to_cart' => array('dialog_msg'), 'product_added_to_quotation' => array('cart_page')) );

					/*	Regenerate the size for wpshop_thumbnail image size	*/
					$attachments = get_posts(array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null));
					if ( is_array( $attachments ) && ( count( $attachments ) > 0)  ) {
						foreach ( $attachments as $attachment ) {
							if ( 'image/' == substr( $attachment->post_mime_type, 0, 6 ) ) {
								$fullsizepath = get_attached_file( $attachment->ID );
								if ( false === $fullsizepath || ! file_exists( $fullsizepath ) ) {

								}
								else {
									@set_time_limit( 900 );

									$metadata = wp_generate_attachment_metadata( $attachment->ID, $fullsizepath );

									if ( is_wp_error( $metadata ) )
										$this->die_json_error_msg( $attachment->ID, $metadata->get_error_message() );
									if ( empty( $metadata ) )
										$this->die_json_error_msg( $attachment->ID, __( 'Unknown failure reason.', 'wpshop' ) );

									// If this fails, then it just means that nothing was changed (old value == new value)
									wp_update_attachment_metadata( $attachment->ID, $metadata );
								}
							}
						}
					}
				return true;
			break;

			case '30':
				/**	Update the current price piloting field for using it into variation specific attributes	*/
				$price_piloting_attribute = constant('WPSHOP_PRODUCT_PRICE_' . WPSHOP_PRODUCT_PRICE_PILOT);
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => $price_piloting_attribute));

				/**	Update the product reference field for using it into variation specific attributes	*/
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'product_reference'));

				/**	Update the product stock field for using it into variation specific attributes	*/
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'product_stock'));
				return true;
			break;

			/*	Always add specific case before this bloc	*/
			case 'dev':
				wp_cache_flush();
				$wp_rewrite->flush_rules();
				return true;
			break;

			default:
				return true;
			break;
		}
	}

	/**
	 * Method called when deactivating the plugin
	 * @see register_deactivation_hook()
	 */
	function uninstall_wpshop(){
		global $wpdb;

		if(WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_MODE_ALLOWED_IP))){
			$query = $wpdb->query("DROP TABLE `wp_wpshop__attribute`, `wp_wpshop__attributes_unit`, `wp_wpshop__attributes_unit_groups`, `wp_wpshop__attribute_set`, `wp_wpshop__attribute_set_section`, `wp_wpshop__attribute_set_section_details`, `wp_wpshop__attribute_value_datetime`, `wp_wpshop__attribute_value_decimal`, `wp_wpshop__attribute_value_integer`, `wp_wpshop__attribute_value_text`, `wp_wpshop__attribute_value_varchar`, `wp_wpshop__attribute_value__histo`, `wp_wpshop__cart`, `wp_wpshop__cart_contents`, `wp_wpshop__documentation`, `wp_wpshop__entity`, `wp_wpshop__historique`, `wp_wpshop__message`, `wp_wpshop__attribute_value_options`;");
			$query = $wpdb->query("DELETE FROM `wp_options` WHERE `option_name` LIKE '%wpshop%';");

			$wpshop_products_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE 'wpshop_%';");
			$list = '  ';
			foreach($wpshop_products_posts as $post){
				$list .= "'" . $post->ID . "', ";
			}
			$list = substr($list, 0, -2);

			$wpshop_products_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_parent IN (" . $list . ");");
			$list_attachment = '  ';
			foreach($wpshop_products_posts as $post){
				$list_attachment .= "'" . $post->ID . "', ";
			}
			$list_attachment = substr($list_attachment, 0, -2);

			$query = $wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE post_id IN (" . $list . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE post_id IN (" . $list_attachment . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE ID IN (" . $list . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE ID IN (" . $list_attachment . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE post_content LIKE '%wpshop%';");
		}

		/*	Unset administrator permission	*/
		$adminRole = get_role('administrator');
		foreach($adminRole->capabilities as $capabilityName => $capability){
			if(substr($capabilityName, 0, 7) == 'wpshop_'){
				if($adminRole->has_cap($capabilityName)){
					$adminRole->remove_cap($capabilityName);
				}
			}
		}
	}

}