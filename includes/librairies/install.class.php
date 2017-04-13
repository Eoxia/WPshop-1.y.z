<?php if (!defined('ABSPATH')) {
    exit;
}

/*    Check if file is include. No direct access possible with file url    */
if (!defined('WPSHOP_VERSION')) {
    die(__('Access is not allowed by this way', 'wpshop'));
}

/**
 * Plugin installation file.
 *
 * This file contains the different methods called when plugin is actived and removed
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */

/**
 * Class defining the different method used when plugin is activated
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_install
{

    /**
     *    Define the action launch when plugin is activate
     *
     * @return void
     */
    public static function install_on_activation()
    {
        /*    Create the different option needed for the plugin work properly    */
        add_option('wpshop_db_options', array('db_version' => 0));
        add_option('wpshop_shop_default_currency', WPSHOP_SHOP_DEFAULT_CURRENCY);
        add_option('wpshop_emails', array('noreply_email' => get_bloginfo('admin_email'), 'contact_email' => get_bloginfo('admin_email')));
        add_option('wpshop_catalog_product_option', array('wpshop_catalog_product_slug' => WPSHOP_CATALOG_PRODUCT_SLUG));
        add_option('wpshop_catalog_categories_option', array('wpshop_catalog_categories_slug' => WPSHOP_CATALOG_CATEGORIES_SLUG));
        add_option('wpshop_display_option', array('wpshop_display_list_type' => 'grid', 'wpshop_display_grid_element_number' => '3', 'wpshop_display_cat_sheet_output' => array('category_description', 'category_subcategory', 'category_subproduct')));
    }

    /**
     *    Create the default pages
     */
    public static function wpshop_insert_default_pages($pages_type = '')
    {
        global $wpdb, $wp_rewrite;

        /**    if we will create any new pages we need to flush page cache */
        $page_creation = false;
        $created_pages = array();

        /** Default data array for add page */
        $page_default_args = array(
            'post_type' => 'page',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        );

        /**    Get defined shop type    */
        $wpshop_shop_type = !empty($pages_type) ? $pages_type : get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);

        /**    Get the default datas for installation - Pages    */
        $xml_default_pages = file_get_contents(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/default_pages.xml');
        $defined_default_pages = new SimpleXMLElement($xml_default_pages);
        foreach ($defined_default_pages->xpath('//pages/page') as $page) {
            if (($wpshop_shop_type == $page->attributes()->shop_type) || ('sale' == $wpshop_shop_type)) {
                $page_id = null;

                /**    Do a specific check for cart page, for old wpshop installation    */
                if ('wpshop_cart_page_id' == (string) $page->attributes()->code) {
                    $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_basket]%', 'revision');
                    $page_id = $wpdb->get_var($query);

                    wp_update_post(array(
                        'ID' => $page_id,
                        'post_content' => (string) $page->content,
                    ));
                }

                /**    Check if a page exists with the current content readed form xml file    */
                if (empty($page_id)) {
                    $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_type != %s", '%' . (string) $page->content . '%', 'revision');
                    $page_id = $wpdb->get_var($query);
                }

                /**    If the page does not exists create it    */
                if (empty($page_id)) {
                    $default_page_args = wp_parse_args(array(
                        'post_title' => __((string) $page->title, 'wpshop'),
                        'post_name' => __((string) $page->slug, 'wpshop'),
                        'post_content' => __((string) $page->content, 'wpshop'),
                        'menu_order' => (string) $page->attributes()->position,
                    ), $page_default_args);

                    $page_id = wp_insert_post($default_page_args);
                    $created_pages[] = (string) $page->attributes()->code;
                }

                /**    If the page is created or already exists associated the page to the good service    */
                if (!empty($page_id)) {
                    add_option((string) $page->attributes()->code, $page_id);

                    $page_creation = true;
                }

            }
        }

        /**    Check if page have been created in order to do specific action    */
        if (!empty($created_pages)) {
            /**    If cart page and checkout page have just been created, change cart page id into checkout page id    */
            if (in_array('wpshop_cart_page_id', $created_pages) && in_array('wpshop_checkout_page_id', $created_pages)) {
                update_option('wpshop_cart_page_id', get_option('wpshop_checkout_page_id'));
            }

        }

        wp_cache_flush();
        /** If new page => empty cache */
        if ($page_creation) {
            wp_cache_delete('all_page_ids', 'pages');
            //    $wp_rewrite->flush_rules();
        }
    }

    /**
     * Insert sample datas when installing wpshop the first time if the admin choose
     */
    public static function import_sample_datas()
    {
        global $wpdb, $wp_rewrite;

        /** Default data array for add product */
        $product_default_args = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        );

        /**    Get the default datas for installation - sample products    */
        $sample_datas = file_get_contents(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/sample_datas.xml');
        $defined_sample_datas = new SimpleXMLElement($sample_datas, LIBXML_NOCDATA);

        $namespaces = $defined_sample_datas->getDocNamespaces();
        if (!isset($namespaces['wp'])) {
            $namespaces['wp'] = 'http://wordpress.org/export/1.1/';
        }

        if (!isset($namespaces['excerpt'])) {
            $namespaces['excerpt'] = 'http://wordpress.org/export/1.1/excerpt/';
        }

        foreach ($defined_sample_datas->xpath('//wpshop_products/wpshop_product') as $product) {
            $dc = $product->children('http://purl.org/dc/elements/1.1/');
            $content = $product->children('http://purl.org/rss/1.0/modules/content/');
            $excerpt = $product->children($namespaces['excerpt']);
            $wp = $product->children($namespaces['wp']);

            $product_args = wp_parse_args(array(
                'post_title' => (string) $product->title,
                'post_name' => (string) $wp->post_name,
                'post_content' => (string) $content->encoded,
                'post_excerpt' => (string) $excerpt->encoded,
                'post_type' => (string) $wp->post_type,
            ), $product_default_args);

            $product_id = wp_insert_post($product_args);

            foreach ($wp->postmeta as $meta) {
                update_post_meta($product_id, (string) $meta->meta_key, (string) $meta->meta_value);
            }

            foreach ($defined_sample_datas->xpath('//wps_pdt_variations/wps_pdt_variation/wp:post_parent[. ="' . $wp->post_id . '"]/parent::*') as $product_variation) {
                $wps_pdt_var_dc = $product_variation->children('http://purl.org/dc/elements/1.1/');
                $wps_pdt_var_content = $product_variation->children('http://purl.org/rss/1.0/modules/content/');
                $wps_pdt_var_excerpt = $product_variation->children($namespaces['excerpt']);
                $wps_pdt_var_wp = $product_variation->children($namespaces['wp']);

                $product_args = wp_parse_args(array(
                    'post_title' => (string) $product_variation->title,
                    'post_name' => (string) $wps_pdt_var_wp->post_name,
                    'post_content' => (string) $wps_pdt_var_content->encoded,
                    'post_excerpt' => (string) $wps_pdt_var_excerpt->encoded,
                    'post_type' => (string) $wps_pdt_var_wp->post_type,
                    'post_parent' => $product_id,
                ), $product_default_args);

                $product_variation_id = wp_insert_post($product_args);

                foreach ($wps_pdt_var_wp->postmeta as $meta) {
                    update_post_meta($product_variation_id, (string) $meta->meta_key, (string) $meta->meta_value);
                }
            }
        }
    }

    /**
     * Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
     */
    public static function update_wpshop_dev()
    {
        global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        self::execute_operation_on_db_for_update('dev');
    }

    /**
     * Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
     */
    public static function update_wpshop()
    {
        global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update;
        $do_changes = false;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $current_db_version = get_option('wpshop_db_options', 0);
        $current_db_version = $current_db_version['db_version'];

        $current_def_max_version = max(array_keys($wpshop_update_way));
        $new_version = $current_def_max_version + 1;
        $version_nb_delta = $current_def_max_version - $current_db_version;

        /*    Check if there are modification to do    */
        if ($current_def_max_version >= $current_db_version) {
            /*    Check the lowest version of db to execute    */
            $lowest_version_to_execute = $current_def_max_version - $version_nb_delta;

            for ($i = $lowest_version_to_execute; $i <= $current_def_max_version; $i++) {
                $do_changes = self::execute_operation_on_db_for_update($i);
            }
        }

        /*    Update the db version option value    */
        if ($do_changes) {
            $db_version = get_option('wpshop_db_options', 0);
            $db_version['db_version'] = $new_version;
            update_option('wpshop_db_options', $db_version);
        }
    }

    /**
     * Update db structure on each plugin update
     *
     * @param integer $i The current plugin db version
     * @return boolean If the changes are done correctly or not
     */
    public static function alter_db_structure_on_update($i)
    {
        $do_changes = false;
        global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_request, $wpshop_db_delete;

        /*    Check if there are modification to do    */
        if (isset($wpshop_update_way[$i])) {
            /*    Check if there are modification to make on table    */
            if (isset($wpshop_db_table_list[$i])) {
                foreach ($wpshop_db_table_list[$i] as $table_name) {
                    dbDelta($wpshop_db_table[$table_name]);
                }
                $do_changes = true;
            }

            /*    Request maker    */
            if (isset($wpshop_db_request[$i]) && is_array($wpshop_db_request) && is_array($wpshop_db_request[$i]) && (count($wpshop_db_request[$i]) > 0)) {
                foreach ($wpshop_db_request[$i] as $request) {
                    $wpdb->query($request);
                    $do_changes = true;
                }
            }

            /*    Delete datas    */
            if (isset($wpshop_db_delete[$i]) && is_array($wpshop_db_delete) && is_array($wpshop_db_delete[$i]) && (count($wpshop_db_delete[$i]) > 0)) {
                foreach ($wpshop_db_delete[$i] as $request) {
                    $wpdb->query($request);
                }
            }
        }

        return $do_changes;
    }

    /**
     * Do changes on database for wpshop plugin for a given version
     *
     * @param integer $i The wpshop db version to execute operation for
     *
     * @return boolean
     */
    public static function execute_operation_on_db_for_update($i)
    {
        global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update, $wpshop_db_request, $wpshop_db_delete;
        $do_changes = false;

        /*    Check if there are modification to do    */
        if (isset($wpshop_update_way[$i])) {
            $do_changes = self::alter_db_structure_on_update($i);

            /********************/
            /*        Insert data        */
            /********************/
            /*    Options content    */
            if (isset($wpshop_db_options_add[$i]) && is_array($wpshop_db_options_add) && is_array($wpshop_db_options_add[$i]) && (count($wpshop_db_options_add[$i]) > 0)) {
                foreach ($wpshop_db_options_add[$i] as $option_name => $option_content) {
                    add_option($option_name, $option_content, '', 'yes');
                }
                $do_changes = true;
            }
            if (isset($wpshop_db_options_update[$i]) && is_array($wpshop_db_options_update) && is_array($wpshop_db_options_update[$i]) && (count($wpshop_db_options_update[$i]) > 0)) {
                foreach ($wpshop_db_options_update[$i] as $option_name => $option_content) {
                    $option_current_content = get_option($option_name);
                    foreach ($option_content as $option_key => $option_value) {
                        $option_current_content[$option_key] = $option_value;
                    }
                    update_option($option_name, $option_current_content);
                }
                $do_changes = true;
            }

            /*    Eav content    */
            if (isset($wpshop_eav_content[$i]) && is_array($wpshop_eav_content) && is_array($wpshop_eav_content[$i]) && (count($wpshop_eav_content[$i]) > 0)) {
                $do_changes = self::add_content_to_eav($wpshop_eav_content[$i], $do_changes);
            }
            /*    Eav content update    */
            if (isset($wpshop_eav_content_update[$i]) && is_array($wpshop_eav_content_update) && is_array($wpshop_eav_content_update[$i]) && (count($wpshop_eav_content_update[$i]) > 0)) {
                $do_changes = self::add_content_to_eav($wpshop_eav_content_update[$i], $do_changes);
            }

            /*    Add datas    */
            if (isset($wpshop_db_content_add[$i]) && is_array($wpshop_db_content_add) && is_array($wpshop_db_content_add[$i]) && (count($wpshop_db_content_add[$i]) > 0)) {
                foreach ($wpshop_db_content_add[$i] as $table_name => $def) {
                    foreach ($def as $information_index => $table_information) {
                        $wpdb->insert($table_name, $table_information, '%s');
                        $do_changes = true;
                    }
                }
            }

            /*    Update datas    */
            if (isset($wpshop_db_content_update[$i]) && is_array($wpshop_db_content_update) && is_array($wpshop_db_content_update[$i]) && (count($wpshop_db_content_update[$i]) > 0)) {
                foreach ($wpshop_db_content_update[$i] as $table_name => $def) {
                    foreach ($def as $information_index => $table_information) {
                        $wpdb->update($table_name, $table_information['datas'], $table_information['where'], '%s', '%s');
                        $do_changes = true;
                    }
                }
            }
        }

        $do_changes = self::make_specific_operation_on_update($i);

        return $do_changes;
    }

    /**
     * Create specific data in eav db model
     *
     * @param array $eav_content The complete array with all element to create into database
     * @param boolean $do_changes The current state of changes to do
     *
     * @return boolean If there are changes to do or not
     */
    public static function add_content_to_eav($eav_content, $do_changes)
    {
        global $wpdb;
        /*    Create entities if entites are set to be created for the current version    */
        if (isset($eav_content['entities']) && is_array($eav_content['entities']) && is_array($eav_content['entities']) && (count($eav_content['entities']) > 0)) {
            foreach ($eav_content['entities'] as $entity) {
                /*    Creation de l'entité produit dans la table des posts    */
                wpshop_entities::create_cpt_from_csv_file($entity);
            }
            $do_changes = true;
        }

        /*    Create attributes for a given entity if attributes are set to be created for current version    */
        if (!empty($eav_content['attributes']) && is_array($eav_content['attributes']) && is_array($eav_content['attributes']) && (count($eav_content['attributes']) > 0)) {
            foreach ($eav_content['attributes'] as $entity_code) {
                wpshop_entities::create_cpt_attributes_from_csv_file($entity_code);
            }
            $do_changes = true;
        }

        /*    Create attribute groups for a given entity if attributes groups are set to be created for current version    */
        if (isset($eav_content['attribute_groups']) && is_array($eav_content['attribute_groups']) && (count($eav_content['attribute_groups']) > 0)) {
            foreach ($eav_content['attribute_groups'] as $entity_code => $attribute_set) {
                $entity_id = wpshop_entities::get_entity_identifier_from_code($entity_code);

                if ($entity_id > 0) {
                    foreach ($attribute_set as $set_name => $set_groups) {
                        $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = LOWER(%s)", $entity_id, wpshop_tools::slugify($set_name, array('noAccent', 'noSpaces', 'lowerCase')));
                        $attribute_set_id = $wpdb->get_var($query);
                        if ($attribute_set_id <= 0) {
                            $attribute_set_content = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $entity_id, 'name' => $set_name);
                            if ($set_name == 'default') {
                                $attribute_set_content['default_set'] = 'yes';
                            }
                            $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_SET, $attribute_set_content);
                            $attribute_set_id = $wpdb->insert_id;
                        }

                        if ($attribute_set_id > 0) {
                            foreach ($set_groups as $set_group_infos) {
                                $set_group_infos_details = $set_group_infos['details'];
                                unset($set_group_infos['details']);
                                /*    Change an attribute set status if definition specify this param     */
                                if (isset($set_group_infos['status'])) {
                                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('last_update_date' => current_time('mysql', 0), 'status' => $set_group_infos['status']), array('id' => $attribute_set_id));
                                }
                                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, $set_group_infos['code']);
                                $attribute_set_section_id = $wpdb->get_var($query);
                                if ($attribute_set_section_id <= 0) {
                                    $new_set_section_infos = $set_group_infos;
                                    $new_set_section_infos['status'] = (isset($new_set_section_infos['status']) ? $new_set_section_infos['status'] : 'valid');
                                    $new_set_section_infos['creation_date'] = current_time('mysql', 0);
                                    $new_set_section_infos['attribute_set_id'] = $attribute_set_id;
                                    $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos);
                                    $attribute_set_section_id = $wpdb->insert_id;
                                }

                                if (($attribute_set_section_id > 0) && (isset($set_group_infos_details) && is_array($set_group_infos_details) && (count($set_group_infos_details) > 0))) {
                                    $query = $wpdb->prepare("SELECT MAX(position) AS position FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d", $entity_id, $attribute_set_id, $attribute_set_section_id);
                                    $last_position = $wpdb->get_var($query);
                                    $position = (int) $last_position + 1;
                                    foreach ($set_group_infos_details as $attribute_code) {
                                        $query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_code, $entity_id);
                                        $attribute_id = $wpdb->get_row($query);

                                        if ($attribute_id->id > 0) {
                                            $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $attribute_id->id, 'position' => $position));
                                            $position++;
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

        return $do_changes;
    }

    /**
     * Update specific data in eav db model
     *
     * @param array $eav_content The complete array with all element to create into database
     * @param boolean $do_changes The current state of changes to do
     *
     * @return boolean If there are changes to do or not
     */
    public static function update_eav_content($eav_content, $do_changes)
    {
        /*    Update attributes fo a given entity if attributes are set to be updated for current version    */
        if (isset($eav_content['attributes']) && is_array($eav_content['attributes']) && (count($eav_content['attributes']) > 0)) {
            foreach ($eav_content['attributes'] as $entity_code => $attribute_definition) {
                foreach ($attribute_definition as $attribute_def) {
                    $option_list_for_attribute = '';
                    if (isset($attribute_def['backend_input_values'])) {
                        $option_list_for_attribute = $attribute_def['backend_input_values'];
                        unset($attribute_def['backend_input_values']);
                    }

                    /*    Get entity identifier from code    */
                    $attribute_def['entity_id'] = wpshop_entities::get_entity_identifier_from_code($entity_code);
                    $attribute_def['status'] = $attribute_def['attribute_status'];
                    unset($attribute_def['attribute_status']);
                    $attribute_def['last_update_date'] = current_time('mysql', 0);
                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE, $attribute_def, array('code' => $attribute_def['code']));
                    $attribute_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_def['code']));

                    /*    Insert option values if there are some to add for the current attribute    */
                    if (($option_list_for_attribute != '') && (is_array($option_list_for_attribute))) {
                        foreach ($option_list_for_attribute as $option_code => $option_value) {
                            $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $attribute_id, 'label' => ((substr($option_code, 0, 2) != '__') ? $option_value : __(substr($option_code, 2), 'wpshop')), 'value' => $option_value));
                            if ($option_code == $attribute_def['default_value']) {
                                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $wpdb->insert_id), array('id' => $attribute_id, 'default_value' => $option_code));
                            }
                        }
                    }
                }
            }
            $do_changes = true;
        }

        /*    Update attribute groups fo a given entity if attributes groups are set to be updated for current version    */
        if (is_array($eav_content['attribute_groups']) && is_array($eav_content['attribute_groups']) && (count($eav_content['attribute_groups']) > 0)) {
            foreach ($eav_content['attribute_groups'] as $entity_code => $attribute_set) {
                $entity_id = wpshop_entities::get_entity_identifier_from_code($entity_code);

                if ($entity_id > 0) {
                    foreach ($attribute_set as $set_name => $set_groups) {
                        $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = LOWER(%s)", $entity_id, wpshop_tools::slugify($set_name, array('noAccent', 'noSpaces', 'lowerCase')));
                        $attribute_set_id = $wpdb->get_var($query);
                        if ($attribute_set_id <= 0) {
                            $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_SET, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $entity_id, 'name' => $set_name));
                            $attribute_set_id = $wpdb->insert_id;
                        }

                        if ($attribute_set_id > 0) {
                            foreach ($set_groups as $set_group_infos) {
                                $set_group_infos_details = $set_group_infos['details'];
                                unset($set_group_infos['details']);
                                /*    Change an attribute set status if definition specify this param     */
                                if (isset($set_group_infos['status'])) {
                                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('last_update_date' => current_time('mysql', 0), 'status' => $set_group_infos['status']), array('id' => $attribute_set_id));
                                }
                                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, $set_group_infos['code']);
                                $attribute_set_section_id = $wpdb->get_var($query);
                                if ($attribute_set_section_id <= 0) {
                                    $new_set_section_infos = $set_group_infos;
                                    $new_set_section_infos['status'] = (isset($new_set_section_infos['status']) ? $new_set_section_infos['status'] : 'valid');
                                    $new_set_section_infos['creation_date'] = current_time('mysql', 0);
                                    $new_set_section_infos['attribute_set_id'] = $attribute_set_id;
                                    $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos);
                                    $attribute_set_section_id = $wpdb->insert_id;
                                } else {
                                    $new_set_section_infos = $set_group_infos;
                                    $new_set_section_infos['last_update_date'] = current_time('mysql', 0);
                                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos, array('id' => $attribute_set_section_id));
                                }

                                if (($attribute_set_section_id > 0) && (isset($set_group_infos_details) && is_array($set_group_infos_details))) {
                                    if (count($set_group_infos_details) <= 0) {
                                        $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), array('entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id));
                                    } else {
                                        $query = $wpdb->prepare("SELECT MAX(position) AS position FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d", $entity_id, $attribute_set_id, $attribute_set_section_id);
                                        $last_position = $wpdb->get_var($query);
                                        $position = (int) $last_position + 1;
                                        foreach ($set_group_infos_details as $attribute_code) {
                                            $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_code, $entity_id);
                                            $attribute_id = $wpdb->get_var($query);
                                            if ($attribute_id > 0) {
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

        return $do_changes;
    }

    /**
     * Manage special operation on wpshop plugin update
     */
    public static function make_specific_operation_on_update($version)
    {
        global $wpdb, $wp_rewrite;
        $wpshop_shop_type = get_option('wpshop_shop_type', WPSHOP_DEFAULT_SHOP_TYPE);

        switch ($version) {
            case 3:
            case 6:
                self::wpshop_insert_default_pages($wpshop_shop_type);
                wp_cache_flush();
                return true;
                break;
            case 8:
                /**    Change metaboxes order for product in case it already exists    */
                $query = $wpdb->prepare("SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s", 'meta-box-order_wpshop_product');
                $customer_metaboxes_order = $wpdb->get_results($query);
                if (!empty($customer_metaboxes_order)) {
                    foreach ($customer_metaboxes_order as $customer_metabox_order) {
                        $do_changes = false;
                        $current_order = unserialize($customer_metabox_order->meta_value);
                        if (array_key_exists('normal', $current_order) && (false !== strpos('wpshop_product_important_datas', $current_order['normal']))) {
                            str_replace('wpshop_product_important_datas,', '', $current_order['normal']);
                            $do_changes = true;
                        }

                        if (array_key_exists('side', $current_order)) {
                            str_replace('wpshop_product_important_datas,', '', $current_order['side']);
                            str_replace('submitdiv,', 'submitdiv,wpshop_product_important_datas,', $current_order['side']);
                            $do_changes = true;
                        }

                        if (true === $do_changes) {
                            $wpdb->update($wpdb->usermeta, array('meta_value' => serialize($current_order)), array('umeta_id' => $customer_metabox_order->umeta_id));
                        }
                    }
                } else {
                    $users = get_users(array('role' => 'administrator'));
                    if (!empty($users)) {
                        foreach ($users as $user) {
                            $user_meta = array(
                                'side' => 'submitdiv,formatdiv,wpshop_product_important_datas,wpshop_product_categorydiv,pageparentdiv,wps_barcode_product,wpshop_product_actions,wpshop_product_options,postimagediv',
                                'normal' => 'wpshop_product_fixed_tab,postexcerpt,trackbacksdiv,postcustom,commentstatusdiv,slugdiv,authordiv,wpshop_wpshop_variations,wps_media_manager,wpshop_product_order_historic',
                                'advanced' => '',
                            );
                            update_user_meta($user->ID, 'meta-box-order_wpshop_product', $user_meta);
                        }
                    }
                }

                /*    Update the product prices into database    */
                $query = $wpdb->prepare("
SELECT
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS product_price,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS price_ht,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS tx_tva,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS tva", 'product_price', 'price_ht', 'tx_tva', 'tva');
                $product_prices = $wpdb->get_row($query);
                $tax_id = $wpdb->get_var($wpdb->prepare("SELECT ATT_OPT.id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATT_OPT WHERE attribute_id = %d AND value = '20'", $product_prices->tx_tva));
                $query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " WHERE attribute_id = %d", $product_prices->product_price);
                $price_list = $wpdb->get_results($query);
                foreach ($price_list as $existing_ttc_price) {
                    $tax_rate = 1.20;
                    $price_ht = $existing_ttc_price->value / $tax_rate;
                    $tax_amount = $existing_ttc_price->value - $price_ht;

                    $wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->price_ht, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $price_ht, 'creation_date_value' => current_time('mysql', 0)));
                    $wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->tx_tva, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $tax_id, 'creation_date_value' => current_time('mysql', 0)));
                    $wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->tva, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $tax_amount, 'creation_date_value' => current_time('mysql', 0)));
                }

                /*    Update orders structure into database    */
                $orders_id = $wpdb->get_results('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '"');
                foreach ($orders_id as $o) {
                    $myorder = get_post_meta($o->ID, '_order_postmeta', true);
                    $neworder = array();
                    $items = array();

                    if (!isset($myorder['order_tva'])) {
                        $order_total_ht = 0;
                        $order_total_ttc = 0;
                        $order_tva = array('20' => 0);

                        foreach ($myorder['order_items'] as $item) {
                            /* item */
                            $pu_ht = $item['cost'] / 1.20;
                            $pu_tva = $item['cost'] - $pu_ht;
                            $total_ht = $pu_ht * $item['qty'];
                            $tva_total_amount = $pu_tva * $item['qty'];
                            $total_ttc = $item['cost'] * $item['qty'];
                            /* item */
                            $order_total_ht += $total_ht;
                            $order_total_ttc += $total_ttc;
                            $order_tva['20'] += $tva_total_amount;

                            $items[] = array(
                                'item_id' => $item['id'],
                                'item_ref' => 'Nc',
                                'item_name' => $item['name'],
                                'item_qty' => $item['qty'],

                                'item_pu_ht' => number_format($pu_ht, 2, '.', ''),
                                'item_pu_ttc' => number_format($item['cost'], 2, '.', ''),

                                'item_ecotaxe_ht' => number_format(0, 2, '.', ''),
                                'item_ecotaxe_tva' => 20,
                                'item_ecotaxe_ttc' => number_format(0, 2, '.', ''),

                                'item_discount_type' => 0,
                                'item_discount_value' => 0,
                                'item_discount_amount' => number_format(0, 2, '.', ''),

                                'item_tva_rate' => 20,
                                'item_tva_amount' => number_format($pu_tva, 2, '.', ''),

                                'item_total_ht' => number_format($total_ht, 2, '.', ''),
                                'item_tva_total_amount' => number_format($tva_total_amount, 2, '.', ''),
                                'item_total_ttc' => number_format($total_ttc, 2, '.', ''),
                                /*'item_total_ttc_with_ecotaxe' => number_format($total_ttc, 2, '.', '')*/
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
                            'order_shipping_cost' => number_format(0, 2, '.', ''),
                            'order_tva' => array_map(array('wpshop_tools', 'number_format_hack'), $order_tva),
                            'order_items' => $items,
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
                $query = "SELECT ID FROM " . $wpdb->users;
                $user_list = $wpdb->get_results($query);
                foreach ($user_list as $user) {
                    $user_first_name = get_user_meta($user->ID, 'first_name', true);
                    $user_last_name = get_user_meta($user->ID, 'last_name', true);
                    $shipping_info = get_user_meta($user->ID, 'shipping_info', true);

                    if (($user_first_name == '') && !empty($shipping_info['first_name'])) {
                        update_user_meta($user->ID, 'first_name', $shipping_info['first_name']);
                    }

                    if (($user_last_name == '') && !empty($shipping_info['last_name'])) {
                        update_user_meta($user->ID, 'last_name', $shipping_info['last_name']);
                    }
                }

                /*    Update orders structure into database    */
                $orders_id = $wpdb->get_results('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '"');
                foreach ($orders_id as $o) {
                    $myorder = get_post_meta($o->ID, '_order_postmeta', true);
                    if (!empty($myorder)) {
                        $new_items = array();
                        foreach ($myorder['order_items'] as $item) {
                            $new_items = $item;
                            $new_items['item_discount_type'] = !empty($item['item_discount_rate']) ? $item['item_discount_rate'] : 'amount';
                            // unset($new_items['item_discount_rate']);
                            $new_items['item_discount_value'] = 0;
                        }
                        $myorder['order_items'] = $new_items;

                        /* Update the order postmeta */
                        update_post_meta($o->ID, '_order_postmeta', $myorder);
                    }
                }

                /*    Delete useless database table    */
                $query = "DROP TABLE " . WPSHOP_DBT_CART;
                $wpdb->query($query);
                $query = "DROP TABLE " . WPSHOP_DBT_CART_CONTENTS;
                $wpdb->query($query);
                return true;
                break;
            case 13:
                $attribute_used_for_sort_by = wpshop_attributes::getElement('yes', "'valid', 'moderated', 'notused'", 'is_used_for_sort_by', true);
                foreach ($attribute_used_for_sort_by as $attribute) {
                    $data = query_posts(array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
                    foreach ($data as $post) {
                        $postmeta = get_post_meta($post->ID, '_wpshop_product_metadata', true);
                        if (!empty($postmeta[$attribute->code])) {
                            update_post_meta($post->ID, '_' . $attribute->code, $postmeta[$attribute->code]);
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
                foreach ($products as $product) {
                    $p_att_set_id = get_post_meta($product->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
                    if (empty($p_att_set_id)) {
                        /*    Update the attribute set id for the current product    */
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
                if (!empty($manage_stock_option)) {
                    $no_is_present = false;
                    $attribute_id = $manage_stock_option[0]->attribute_id;
                    foreach ($manage_stock_option as $manage_definition) {
                        if (strtolower(__($manage_definition->value, 'wpshop')) == strtolower(__('no', 'wpshop'))) {
                            $no_is_present = true;
                        }
                    }
                    if (!$no_is_present) {
                        $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'last_update_date' => current_time('mysql', 0), 'attribute_id' => $attribute_id, 'value' => 'no', 'label' => __('No', 'wpshop')));
                    }
                }

                /** Change price attribute set section order for default set */
                $price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
                unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);
                $query = "SELECT GROUP_CONCAT(id) FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code IN ('" . implode("','", $price_tab) . "')";
                $attribute_ids = $wpdb->get_var($query);

                $query = $wpdb->prepare("
SELECT ATTR_DET.attribute_group_id
FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTR_DET
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTR_GROUP ON ((ATTR_GROUP.id = ATTR_DET.attribute_group_id) AND (ATTR_GROUP.code = %s))
	INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTR_SET ON ((ATTR_SET.id = ATTR_GROUP.attribute_set_id) AND (ATTR_SET.name = %s))
WHERE ATTR_DET.attribute_id IN (" . $attribute_ids . ")"
                    , 'prices', __('default', 'wpshop'));
                $list = $wpdb->get_results($query);
                if (!empty($list)) {
                    $change_order = true;
                    $old_value = $list[0]->attribute_group_id;
                    unset($list[0]);
                    if (!empty($list)) {
                        foreach ($list as $data) {
                            if ($old_value != $data->attribute_group_id) {
                                $change_order = false;
                            }
                        }
                        if ($change_order) {
                            foreach ($price_tab as $price_code) {
                                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $price_code);
                                $attribute_id = $wpdb->get_var($query);
                                switch ($price_code) {
                                    case WPSHOP_PRODUCT_PRICE_HT:
                                        $position = (WPSHOP_PRODUCT_PRICE_PILOT == 'HT') ? 1 : 3;
                                        break;
                                    case WPSHOP_PRODUCT_PRICE_TAX:
                                        $position = 2;
                                        break;
                                    case WPSHOP_PRODUCT_PRICE_TTC:
                                        $position = (WPSHOP_PRODUCT_PRICE_PILOT == 'HT') ? 3 : 1;
                                        break;
                                    case WPSHOP_PRODUCT_PRICE_TAX_AMOUNT:
                                        $position = 4;
                                        break;
                                }
                                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'last_update_date' => current_time('mysql', 0), 'position' => $position), array('attribute_group_id' => $old_value, 'attribute_id' => $attribute_id));
                            }
                        }
                    }
                }
                return true;
                break;
            case 22:
                $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
                $product_entity_id = $wpdb->get_var($query);
                if (empty($product_entityd_id) || ($product_entity_id <= 0) || !$product_entity_id) {
                    /*    Create the product entity into post table    */
                    $product_entity = array(
                        'post_title' => __('Products', 'wpshop'),
                        'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
                        'post_content' => __('Define the entity allowing to manage product on your store. If you delete this entity you won\'t be able to manage your store', 'wpshop'),
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
                    );
                    $product_entity_id = wp_insert_post($product_entity);
                }

                /*    Update eav table with the new entity id for product    */
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('entity_id' => $product_entity_id), array('entity_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('entity_id' => $product_entity_id), array('entity_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, array('entity_type_id' => $product_entity_id), array('entity_type_id' => 1));

                /*    Create an element of customer entity for each existing user    */
                $user_list = get_users();
                foreach ($user_list as $user) {
                    wps_customer_ctr::create_entity_customer_when_user_is_created($user->ID);
                }

                return true;
                break;
            case 23:
                /*    Delete duplicate entities    */
                $query = ("SELECT ID FROM " . $wpdb->posts . " WHERE post_name LIKE '%" . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "%' ");
                $product_entity_list = $wpdb->get_results($query);
                if (count($product_entity_list) > 1) {
                    $i = 0;
                    foreach ($product_entity_list as $product_entity) {
                        if ($i > 0) {
                            wp_delete_post($product_entity->ID);
                        }
                    }
                }
                return true;
                break;
            case 24:
                /*    Update the link status for disabled attribute set    */
                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE status = %s", 'deleted');
                $deleted_attribute_group = $wpdb->get_results($query);
                if (!empty($deleted_attribute_group)) {
                    foreach ($deleted_attribute_group as $group) {
                        $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'deleted', 'last_update_date' => current_time('mysql', 0)), array('attribute_group_id' => $group->id));
                    }
                }

                /*    Update entities meta management    */
                $entities = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES));
                if (!empty($entities)) {
                    foreach ($entities as $entity) {
                        $support = get_post_meta($entity->ID, '_wpshop_entity_support', true);
                        $rewrite = get_post_meta($entity->ID, '_wpshop_entity_rewrite', true);
                        update_post_meta($entity->ID, '_wpshop_entity_params', array('support' => $support, 'rewrite' => array('slug' => $rewrite)));
                    }
                }
                wp_reset_query();
                return true;
                break;
            case 25:
                /*    Get the first entities of product and customer    */
                $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name=%s AND post_type=%s ORDER BY ID ASC LIMIT 1", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES);
                $product_entity_id = $wpdb->get_var($query);

                /*    Update attributes that are not linked with entities    */
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('entity_id' => $product_entity_id), array('entity_id' => 0));

                /*    Get entities that have been created a lot of time and delete them    */
                $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE (post_name LIKE '%%" . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "-%%' OR post_name LIKE '%%" . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . "-%%') AND post_type=%s", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES);
                $entities_to_delete = $wpdb->get_results($query);
                if (!empty($entities_to_delete) && is_array($entities_to_delete)) {
                    foreach ($entities_to_delete as $entity) {
                        wp_delete_post($entity->ID, true);
                    }
                }

                /*    Get post list that are children of entities created a lot of time */
                $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . "-%");
                $entities_to_update = $wpdb->get_results($query);
                if (!empty($entities_to_update) && is_array($entities_to_update)) {
                    foreach ($entities_to_update as $entity) {
                        wp_update_post(array('ID' => $entity->ID, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
                    }
                }
                $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE %s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "-%");
                $entities_to_update = $wpdb->get_results($query);
                if (!empty($entities_to_update) && is_array($entities_to_update)) {
                    foreach ($entities_to_update as $entity) {
                        wp_update_post(array('ID' => $entity->ID, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
                    }
                }

                /*    Change addons managament    */
                $wpshop_addons_options = get_option('wpshop_addons_state', array());
                if (!empty($wpshop_addons_options)) {
                    foreach ($wpshop_addons_options as $addon_name => $addon_state) {
                        $options_args = array();
                        $options_args[$addon_name]['activate'] = $addon_state;
                        $options_args[$addon_name]['activation_date'] = current_time('mysql', 0);
                        if (!$addon_state) {
                            $options_args[$addon_name]['deactivation_date'] = current_time('mysql', 0);
                        }

                        add_option(WPSHOP_ADDONS_OPTION_NAME, $options_args);
                    }
                    delete_option('wpshop_addons_state');
                }

                /*    Update the different entities id into attribute set details table    */
                $query = "UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATT_DET INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DET.attribute_id) SET ATT_DET.entity_type_id = ATT.entity_id";
                $wpdb->query($query);

                return true;
                break;
            case 26:
                $query = "SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = '_order_postmeta' ";
                $results = $wpdb->get_results($query);
                foreach ($results as $result) {
                    $order_info = unserialize($result->meta_value);
                    update_post_meta($result->post_id, '_wpshop_order_customer_id', $order_info['customer_id']);
                    update_post_meta($result->post_id, '_wpshop_order_shipping_date', $order_info['order_shipping_date']);
                    update_post_meta($result->post_id, '_wpshop_order_status', $order_info['order_status']);
                }

                /*    Update the different entities id into attribute set details table    */
                $query = "UPDATE " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATT_DET INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DET.attribute_id) SET ATT_DET.entity_type_id = ATT.entity_id";
                $wpdb->query($query);

                return true;
                break;

            case 29:
                $billing_title = __('Billing address', 'wpshop');
                $shipping_title = __('Shipping address', 'wpshop');

                //UPDATE USERS ADDRESSES
                $billing_address_set_id_query = 'SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE name = "' . $billing_title . '"';
                $shipping_address_set_id_query = 'SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE name = "' . $shipping_title . '"';

                $billing_address_set_id = $wpdb->get_var($billing_address_set_id_query);
                $shipping_address_set_id = $wpdb->get_var($shipping_address_set_id_query);

                //Add Address & Google Map API KEY options
                add_option('wpshop_billing_address', array('choice' => $billing_address_set_id), '', 'yes');
                add_option('wpshop_shipping_address_choice', array('activate' => 'on', 'choice' => $shipping_address_set_id), '', 'yes');
                add_option('wpshop_google_map_api_key', '', '', 'yes');

                $query = 'SELECT * FROM ' . $wpdb->users . '';
                $results = $wpdb->get_results($query);
                foreach ($results as $result) {
                    $billing_infos = get_user_meta($result->ID, 'billing_info', true);
                    $shipping_infos = get_user_meta($result->ID, 'shipping_info', true);
                    if (!empty($billing_infos)) {
                        //Save Billing Infos
                        $billing_address = array();
                        if (!empty($billing_infos['civility'])) {
                            switch ($billing_infos['civility']) {
                                case 1:
                                    $civility = $mister_id;
                                    break;
                                case 2:
                                    $civility = $madam_id;
                                    break;
                                case 3:
                                    $civility = $miss_id;
                                    break;
                            }
                        } else {
                            $civility = $mister_id;
                        }
                        $billing_address = array('address_title' => $billing_title,
                            'address_last_name' => !empty($billing_infos['last_name']) ? $billing_infos['last_name'] : '',
                            'address_first_name' => !empty($billing_infos['first_name']) ? $billing_infos['first_name'] : '',
                            'company' => !empty($billing_infos['company']) ? $billing_infos['company'] : '',
                            'address' => !empty($billing_infos['address']) ? $billing_infos['address'] : '',
                            'postcode' => !empty($billing_infos['postcode']) ? $billing_infos['postcode'] : '',
                            'city' => !empty($billing_infos['city']) ? $billing_infos['city'] : '',
                            'state' => !empty($billing_infos['state']) ? $billing_infos['state'] : '',
                            'country' => !empty($billing_infos['country']) ? $billing_infos['country'] : '',
                            'address_user_email' => !empty($billing_infos['email']) ? $billing_infos['email'] : '',
                            'phone' => !empty($billing_infos['phone']) ? $billing_infos['phone'] : '',
                            'tva_intra' => !empty($billing_infos['company_tva_intra']) ? $billing_infos['company_tva_intra'] : '',
                            'civility' => $civility,
                        );
                        //Create the post and post_meta for the billing address
                        $post_address = array(
                            'post_author' => $result->ID,
                            'post_title' => $billing_title,
                            'post_status' => 'publish',
                            'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
                            'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
                            'post_parent' => $result->ID,
                        );
                        $post_address_id = wp_insert_post($post_address);

                        //Create the post_meta with the address infos
                        update_post_meta($post_address_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . '_metadata', $billing_address);
                        update_post_meta($post_address_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . '_attribute_set_id', $billing_address_set_id);
                    }

                    if (!empty($shipping_infos)) {
                        //Save Shipping Infos
                        if (!empty($shipping_infos['civility'])) {
                            switch ($shipping_infos['civility']) {
                                case 1:
                                    $civility = $mister_id;
                                    break;
                                case 2:
                                    $civility = $madam_id;
                                    break;
                                case 3:
                                    $civility = $miss_id;
                                    break;
                            }
                        } else {
                            $civility = $mister_id;
                        }
                        $shipping_address = array();
                        $shipping_address = array('address_title' => $shipping_title,
                            'address_last_name' => !empty($shipping_infos['last_name']) ? $shipping_infos['last_name'] : '',
                            'address_first_name' => !empty($shipping_infos['first_name']) ? $shipping_infos['first_name'] : '',
                            'company' => !empty($shipping_infos['company']) ? $shipping_infos['company'] : '',
                            'address' => !empty($shipping_infos['address']) ? $shipping_infos['address'] : '',
                            'postcode' => !empty($shipping_infos['postcode']) ? $shipping_infos['postcode'] : '',
                            'city' => !empty($shipping_infos['city']) ? $shipping_infos['city'] : '',
                            'state' => !empty($shipping_infos['state']) ? $shipping_infos['state'] : '',
                            'country' => !empty($shipping_infos['country']) ? $shipping_infos['country'] : '',
                            'civility' => $civility,
                        );
                        //Create the post and post_meta for the billing address
                        $post_address = array(
                            'post_author' => $result->ID,
                            'post_title' => $shipping_title,
                            'post_status' => 'publish',
                            'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
                            'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
                            'post_parent' => $result->ID,
                        );
                        $post_address_id = wp_insert_post($post_address);
                        //Create the post_meta with the address infos
                        update_post_meta($post_address_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . '_metadata', $shipping_address);
                        update_post_meta($post_address_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS . '_attribute_set_id', $shipping_address_set_id);
                    }
                }

                // FORMATE THE ORDER ADDRESSES INFOS
                $results = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'posts_per_page' => -1));
                foreach ($results as $result) {
                    $address = get_post_meta($result->ID, '_order_info', true);

                    $billing_address = array();
                    if (!empty($address['billing'])) {
                        if (!empty($address['billing']['civility'])) {
                            switch ($address['billing']['civility']) {
                                case 1:
                                    $civility = $mister_id;
                                    break;
                                case 2:
                                    $civility = $madam_id;
                                    break;
                                case 3:
                                    $civility = $miss_id;
                                    break;
                                default:
                                    $civility = $mister_id;
                                    break;
                            }
                        } else {
                            $civility = $mister_id;
                        }
                        $billing_address = array('address_title' => $billing_title,
                            'address_last_name' => !empty($address['billing']['last_name']) ? $address['billing']['last_name'] : '',
                            'address_first_name' => !empty($address['billing']['first_name']) ? $address['billing']['first_name'] : '',
                            'company' => !empty($address['billing']['company']) ? $address['billing']['company'] : '',
                            'address' => !empty($address['billing']['address']) ? $address['billing']['address'] : '',
                            'postcode' => !empty($address['billing']['postcode']) ? $address['billing']['postcode'] : '',
                            'city' => !empty($address['billing']['city']) ? $address['billing']['city'] : '',
                            'state' => !empty($address['billing']['state']) ? $address['billing']['state'] : '',
                            'country' => !empty($address['billing']['country']) ? $address['billing']['country'] : '',
                            'address_user_email' => !empty($address['billing']['email']) ? $address['billing']['email'] : '',
                            'phone' => !empty($address['billing']['phone']) ? $address['billing']['phone'] : '',
                            'tva_intra' => !empty($address['billing']['company_tva_intra']) ? $address['billing']['company_tva_intra'] : '',
                            'civility' => $civility,
                        );
                    }

                    $shipping_address = array();
                    if (!empty($address['shipping'])) {
                        if (!empty($address['shipping']['civility'])) {
                            switch ($address['shipping']['civility']) {
                                case 1:
                                    $civility = $mister_id;
                                    break;
                                case 2:
                                    $civility = $madam_id;
                                    break;
                                case 3:
                                    $civility = $miss_id;
                                    break;
                            }
                        } else {
                            $civility = $mister_id;
                        }
                        $shipping_address = array('address_title' => $shipping_title,
                            'address_last_name' => !empty($address['shipping']['last_name']) ? $address['shipping']['last_name'] : '',
                            'address_first_name' => !empty($address['shipping']['first_name']) ? $address['shipping']['first_name'] : '',
                            'company' => !empty($address['shipping']['company']) ? $address['shipping']['company'] : '',
                            'address' => !empty($address['shipping']['address']) ? $address['shipping']['address'] : '',
                            'postcode' => !empty($address['shipping']['postcode']) ? $address['shipping']['postcode'] : '',
                            'city' => !empty($address['shipping']['city']) ? $address['shipping']['city'] : '',
                            'state' => !empty($address['shipping']['state']) ? $address['shipping']['state'] : '',
                            'country' => !empty($address['shipping']['country']) ? $address['shipping']['country'] : '',
                            'civility' => $civility,
                        );
                    }

                    $billing_array_content = array('id' => $billing_address_set_id, 'address' => $billing_address);
                    $shipping_array_content = array('id' => $shipping_address_set_id, 'address' => $shipping_address);
                    $array_new_format = array('billing' => $billing_array_content, 'shipping' => $shipping_array_content);

                    //Update the post meta
                    update_post_meta($result->ID, '_order_info', $array_new_format);
                }

                /*    Update entities meta management    */
                $entities = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'posts_per_page' => -1));
                if (!empty($entities)) {
                    foreach ($entities as $entity) {
                        $params = get_post_meta($entity->ID, '_wpshop_entity_params', true);
                        $support = (!empty($params['support'])) ? $params['support'] : '';
                        $rewrite = (!empty($params['rewrite'])) ? $params['rewrite'] : '';

                        $display_admin_menu = 'on';

                        update_post_meta($entity->ID, '_wpshop_entity_params', array('support' => $support, 'rewrite' => $rewrite, 'display_admin_menu' => $display_admin_menu));
                    }
                }
                wp_reset_query();

                // Default Weight unity and Currency Options
                add_option('wpshop_shop_weight_group', 3, '', 'yes');
                add_option('wpshop_shop_default_weight_unity', 6, '', 'yes');
                add_option('wpshop_shop_currency_group', 4, '', 'yes');

                $default_currency = get_option('wpshop_shop_default_currency');
                foreach (unserialize(WPSHOP_SHOP_CURRENCIES) as $k => $v) {
                    if ($default_currency == $k) {
                        $symbol = $v;
                    }
                }
                if (!empty($symbol)) {
                    $query = 'SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE name = "' . html_entity_decode($symbol, ENT_QUOTES, 'UTF-8') . '"';
                    $currency = $wpdb->get_row($query);
                    if (!empty($currency)) {
                        update_option('wpshop_shop_default_currency', $currency->id);
                        // Update the change rate of the default currency
                        $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('change_rate' => 1), array('id' => $currency->id));
                    }
                }

                // Update the field for variation and user definition field
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_for_variation' => 'yes'), array('is_user_defined' => 'yes'));
                // Update field type for frontend output selection
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'text'), array());
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'textarea'), array('backend_input' => 'textarea'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'select'), array('backend_input' => 'multiple-select'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_input' => 'select'), array('backend_input' => 'select'));

                add_option('wpshop_cart_option', array('product_added_to_cart' => array('dialog_msg'), 'product_added_to_quotation' => array('cart_page')));

                return true;
                break;

            case '30':
                /**    Update the current price piloting field for using it into variation specific attributes    */
                $price_piloting_attribute = constant('WPSHOP_PRODUCT_PRICE_' . WPSHOP_PRODUCT_PRICE_PILOT);
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => $price_piloting_attribute));

                /**    Update the product reference field for using it into variation specific attributes    */
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'product_reference'));

                /**    Insert new message for admin when a customer make an order    */
                $admin_new_order_message = get_option('WPSHOP_NEW_ORDER_ADMIN_MESSAGE');
                if (empty($admin_new_order_message)) {
                    wps_message_ctr::createMessage('WPSHOP_NEW_ORDER_ADMIN_MESSAGE');
                }
                /**    Update all amount for paypal orders    */
                $query = $wpdb->prepare("SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_key = %s AND meta_value = %s ", '_wpshop_payment_method', 'paypal');
                $paypal_payment_list = $wpdb->get_results($query);
                if (!empty($paypal_payment_list)) {
                    foreach ($paypal_payment_list as $post) {
                        $order_meta = get_post_meta($post->post_id, '_order_postmeta', true);
                        $order_payment_meta = get_post_meta($post->post_id, 'wpshop_payment_return_data', true);
                        if (!empty($order_meta['order_status']) && ($order_meta['order_status'] == 'incorrect_amount')) {
                            if (!empty($order_meta['order_grand_total']) && !empty($order_payment_meta['mc_gross'])) {
                                $order_amount_to_pay = number_format($order_meta['order_grand_total'], 5);
                                $order_amount_payed = number_format(floatval($order_payment_meta['mc_gross']), 5);
                                if ($order_amount_payed == $order_amount_to_pay) {
                                    wpshop_payment::setOrderPaymentStatus($order_id, 'completed');
                                }
                            }
                        }
                    }
                }

                /**    Save existing orders address information    */
                $billing_title = __('Billing address', 'wpshop');
                $shipping_title = __('Shipping address', 'wpshop');
                $results = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'posts_per_page' => -1));
                foreach ($results as $result) {
                    $address = get_post_meta($result->ID, '_order_info', true);
                    $address_format = array();

                    $billing_address = array();
                    if (!empty($address['billing']) && empty($address['billing']['id'])) {
                        if (!empty($address['billing']['civility'])) {
                            switch ($address['billing']['civility']) {
                                case 1:
                                    $civility = $mister_id;
                                    break;
                                case 2:
                                    $civility = $madam_id;
                                    break;
                                case 3:
                                    $civility = $miss_id;
                                    break;
                                default:
                                    $civility = $mister_id;
                                    break;
                            }
                        } else {
                            $civility = $mister_id;
                        }
                        $billing_address = array('address_title' => $billing_title,
                            'address_last_name' => !empty($address['billing']['last_name']) ? $address['billing']['last_name'] : '',
                            'address_first_name' => !empty($address['billing']['first_name']) ? $address['billing']['first_name'] : '',
                            'company' => !empty($address['billing']['company']) ? $address['billing']['company'] : '',
                            'address' => !empty($address['billing']['address']) ? $address['billing']['address'] : '',
                            'postcode' => !empty($address['billing']['postcode']) ? $address['billing']['postcode'] : '',
                            'city' => !empty($address['billing']['city']) ? $address['billing']['city'] : '',
                            'state' => !empty($address['billing']['state']) ? $address['billing']['state'] : '',
                            'country' => !empty($address['billing']['country']) ? $address['billing']['country'] : '',
                            'address_user_email' => !empty($address['billing']['email']) ? $address['billing']['email'] : '',
                            'phone' => !empty($address['billing']['phone']) ? $address['billing']['phone'] : '',
                            'tva_intra' => !empty($address['billing']['company_tva_intra']) ? $address['billing']['company_tva_intra'] : '',
                            'civility' => $civility,
                        );
                        $billing_address_option = get_option('wpshop_billing_address');
                        $address_format['billing']['id'] = $billing_address_option['choice'];
                        $address_format['billing']['address'] = $shipping_address;
                    }

                    $shipping_address = array();
                    if (!empty($address['shipping']) && empty($address['shipping']['id'])) {
                        if (!empty($address['shipping']['civility'])) {
                            switch ($address['shipping']['civility']) {
                                case 1:
                                    $civility = $mister_id;
                                    break;
                                case 2:
                                    $civility = $madam_id;
                                    break;
                                case 3:
                                    $civility = $miss_id;
                                    break;
                            }
                        } else {
                            $civility = $mister_id;
                        }
                        $shipping_address = array('address_title' => $shipping_title,
                            'address_last_name' => !empty($address['shipping']['last_name']) ? $address['shipping']['last_name'] : '',
                            'address_first_name' => !empty($address['shipping']['first_name']) ? $address['shipping']['first_name'] : '',
                            'company' => !empty($address['shipping']['company']) ? $address['shipping']['company'] : '',
                            'address' => !empty($address['shipping']['address']) ? $address['shipping']['address'] : '',
                            'postcode' => !empty($address['shipping']['postcode']) ? $address['shipping']['postcode'] : '',
                            'city' => !empty($address['shipping']['city']) ? $address['shipping']['city'] : '',
                            'state' => !empty($address['shipping']['state']) ? $address['shipping']['state'] : '',
                            'country' => !empty($address['shipping']['country']) ? $address['shipping']['country'] : '',
                            'civility' => $civility,
                        );
                        $shipping_address_options = get_option('wpshop_shipping_address_choice');
                        $address_format['shipping']['id'] = $shipping_address_options['choice'];
                        $address_format['shipping']['address'] = $shipping_address;
                    }

                    if (!empty($address_format)) {
                        update_post_meta($result->ID, '_order_info', $address_format);
                    }
                }

                /**    Delete username from frontend form    */
                $attribute_login = wpshop_attributes::getElement('user_login', "'valid'", 'code');
                if (!empty($attribute_login)) {
                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'deleted', 'last_update_date' => current_time('mysql', 0), 'position' => 0), array('attribute_id' => $attribute_login->id));
                }

                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'position' => 0), array('status' => 'deleted'));

                return true;
                break;

            case '31':
                /**    Change order structure in order to support several payment    */
                $existing_orders = query_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'posts_per_page' => -1, 'post_status' => array('draft', 'trash', 'publish')));
                if (!empty($existing_orders)) {
                    foreach ($existing_orders as $order_main_informations) {
                        /**    Transfer payment return data form old meta to new    */
                        $order_payment_return_data = get_post_meta($order_main_informations->ID, 'wpshop_payment_return_data', true);
                        update_post_meta($order_main_informations->ID, '_wpshop_payment_return_data', $order_payment_return_data);
                        delete_post_meta($order_main_informations->ID, 'wpshop_payment_return_data');

                        /**    Transfer old payment storage method to new storage method    */
                        $order_meta = get_post_meta($order_main_informations->ID, '_order_postmeta', true);
                        if (!empty($order_meta['order_status'])) {
                            $order_meta['order_payment']['customer_choice'] = array('method' => (!empty($order_meta['payment_method']) ? $order_meta['payment_method'] : (!empty($order_meta['order_payment']['customer_choice']) ? $order_meta['order_payment']['customer_choice'] : '')));
                            unset($order_meta['payment_method']);
                            $order_meta['order_payment']['received'][0]['waited_amount'] = !empty($order_meta['order_grand_total']) ? $order_meta['order_grand_total'] : 0;
                            $order_meta['order_payment']['received'][0]['method'] = $order_meta['order_payment']['customer_choice']['method'];
                            $order_meta['order_payment']['received'][0]['date'] = $order_meta['order_date'];
                            $order_meta['order_payment']['received'][0]['status'] = 'waiting_payment';
                            $order_meta['order_payment']['received'][0]['comment'] = '';
                            $order_meta['order_payment']['received'][0]['author'] = $order_meta['order_payment']['customer_choice'] == 'check' ? 1 : $order_meta['customer_id'];
                            if (in_array($order_meta['order_status'], array('completed', 'shipped'))) {
                                $order_meta['order_payment']['received'][0]['received_amount'] = $order_meta['order_grand_total'];
                                $order_meta['order_payment']['received'][0]['payment_reference'] = wpshop_payment::get_payment_transaction_number_old_way($order_main_informations->ID);
                                $order_meta['order_payment']['received'][0]['date'] = $order_meta['order_payment_date'];
                                $order_meta['order_payment']['received'][0]['status'] = 'payment_received';
                                $order_meta['order_payment']['received'][0]['comment'] = '';
                                $order_meta['order_payment']['received'][0]['author'] = $order_meta['order_payment']['customer_choice'] == 'check' ? 1 : $order_meta['customer_id'];
                                $order_meta['order_payment']['received'][0]['invoice_ref'] = $order_meta['order_invoice_ref'];
                            }
                            update_post_meta($order_main_informations->ID, '_order_postmeta', $order_meta);

                            if (!empty($order_meta['order_payment']['customer_choice'])) {
                                switch ($order_meta['order_payment']['customer_choice']) {
                                    case 'check':
                                        delete_post_meta($order_main_informations->ID, '_order_check_number', get_post_meta($order_main_informations->ID, '_order_check_number', true));
                                        break;
                                    case 'paypal':
                                        delete_post_meta($order_main_informations->ID, '_order_paypal_txn_id', get_post_meta($order_main_informations->ID, '_order_paypal_txn_id', true));
                                        break;
                                    case 'cic':
                                        delete_post_meta($order_main_informations->ID, '_order_cic_txn_id', get_post_meta($order_main_informations->ID, '_order_cic_txn_id', true));
                                        break;
                                }
                            }
                        }
                    }
                }
                $wps_messages = new wps_message_ctr();
                $wps_messages->wpshop_messages_historic_correction();
                wp_reset_query();

                $default_currency = get_option('wpshop_shop_default_currency');
                foreach (unserialize(WPSHOP_SHOP_CURRENCIES) as $k => $v) {
                    if ($default_currency == $k) {
                        $symbol = $v;
                    }
                }
                if (!empty($symbol)) {
                    $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE name = "' . html_entity_decode($symbol, ENT_QUOTES, 'UTF-8') . '"', '');
                    $currency = $wpdb->get_row($query);
                    if (!empty($currency)) {
                        update_option('wpshop_shop_default_currency', $currency->id);
                        // Update the change rate of the default currency
                        $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('change_rate' => 1), array('id' => $currency->id));
                    }
                }

                $shipping_confirmation_message = get_option('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE');
                if (!empty($shipping_confirmation_message)) {
                    $message = __('Hello [customer_first_name] [customer_last_name], this email confirms that your order ([order_key]) has just been shipped (order date : [order_date], tracking number : [order_trackingNumber]). Thank you for your loyalty. Have a good day.', 'wpshop');
                    $post = array('ID' => $shipping_confirmation_message, 'post_content' => $message);
                    wp_update_post($post);
                }
                return true;
                break;
            case '32':
                /**    Update product set id that are null     */
                $query = $wpdb->prepare("UPDATE " . $wpdb->postmeta . " SET meta_value = (SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE default_set = 'yes' AND entity_id = '" . wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) . "') WHERE meta_key = %s AND ((meta_value = '') OR (meta_value = null))", '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id');
                $wpdb->query($query);

                $addons_options = get_option(WPSHOP_ADDONS_OPTION_NAME);
                if (!empty($addons_options) && !empty($addons_options['WPSHOP_ADDONS_QUOTATION']) && !empty($addons_options['WPSHOP_ADDONS_QUOTATION']['activate']) && $addons_options['WPSHOP_ADDONS_QUOTATION']['activate']) {
                    $admin_new_quotation_message = get_option('WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE');
                    if (empty($admin_new_quotation_message)) {
                        wps_message_ctr::createMessage('WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE');
                    }
                    $admin_new_quotation_confirm_message = get_option('WPSHOP_QUOTATION_CONFIRMATION_MESSAGE');
                    if (empty($admin_new_quotation_confirm_message)) {
                        wps_message_ctr::createMessage('WPSHOP_QUOTATION_CONFIRMATION_MESSAGE');
                    }
                }

                /**    Allows the administrator to manage a little bit more the catalog rewrite parameters    */
                $options = get_option('wpshop_catalog_product_option');
                $options['wpshop_catalog_product_slug_with_category'] = empty($options['wpshop_catalog_product_slug_with_category']) ? 'yes' : $options['wpshop_catalog_product_slug_with_category'];
                update_option('wpshop_catalog_product_option', $options);

                /**    Create a new page for unsuccessfull payment return    */
                self::wpshop_insert_default_pages($wpshop_shop_type);
                wp_cache_flush();

                /**    Update the iso code of currencies    */
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('code_iso' => 'EUR'), array('name' => 'euro'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('code_iso' => 'USD'), array('name' => 'dollar'));

                /** Update VAT Rate*/
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE code = %s OR code = %s', 'tx_tva', 'eco_taxe_rate_tva');
                $attribute_ids = $wpdb->get_results($query);
                foreach ($attribute_ids as $attribute_id) {
                    $query = $wpdb->prepare('UPDATE ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' SET value = replace(value, "-", ".") WHERE attribute_id = %d', $attribute_id->id);
                    $wpdb->query($query);
                }

                return true;
                break;

            case '33':
                /** Update the user_mail for the new system of log in/register */
                $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "yes" WHERE code = "user_email"');

                /** Put discount attributes in price attribute set section*/
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_GROUP . ' WHERE code = %s', 'prices');
                $prices_section_id = $wpdb->get_var($query);

                $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE code = %s OR code = %s', 'discount_rate', 'discount_amount');
                $attributes = $wpdb->get_results($query);
                if (!empty($attributes) && !empty($prices_section_id)) {
                    foreach ($attributes as $attribute) {
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE_DETAILS . ' SET attribute_group_id = ' . $prices_section_id . ' WHERE attribute_id = ' . $attribute->id);
                    }
                }
                return true;
                break;

            case '34':
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_GROUP . ' WHERE code = %s', 'prices');
                $prices_section_id = $wpdb->get_var($query);

                $query = $wpdb->prepare('SELECT MAX(position) AS max_position FROM ' . WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE attribute_group_id = %d', $prices_section_id);
                $last_position_id = $wpdb->get_var($query);

                $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE  attribute_group_id = %d AND position = %d', $prices_section_id, $last_position_id);
                $attribute_example = $wpdb->get_row($query);

                $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE code = %s OR code = %s', 'special_from', 'special_to');
                $attributes = $wpdb->get_results($query);
                $i = 1;
                if (!empty($attributes) && !empty($prices_section_id)) {

                    foreach ($attributes as $attribute) {
                        $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('attribute_group_id' => $prices_section_id), array('attribute_id' => $attribute->id));
                        $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $attribute_example->entity_type_id, 'attribute_set_id' => $attribute_example->attribute_set_id, 'attribute_group_id' => $prices_section_id, 'attribute_id' => $attribute->id, 'position' => $last_position_id + $i));
                        $i++;
                    }
                }
                $discount_options = get_option('wpshop_catalog_product_option');
                $status = (!empty($discount_options) && !empty($discount_options['discount'])) ? 'valid' : 'notused';
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_label' => __('Discount from', 'wpshop'), 'status' => $status), array('code' => 'special_from'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('frontend_label' => __('Discount to', 'wpshop'), 'status' => $status), array('code' => 'special_to'));
                return true;
                break;

            case '35':
                $wpdb->update($wpdb->posts, array('post_status' => 'draft'), array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS));
                return true;
                break;

            case '36':
                wpshop_entities::create_cpt_attributes_from_csv_file(WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS);
                @set_time_limit(900);
                /** Change the path for old categories pictures */
                @chmod(WPSHOP_UPLOAD_DIR . 'wpshop_product_category', 0755);

                $query = 'SELECT * FROM ' . $wpdb->terms;
                $terms = $wpdb->get_results($query);
                if (!empty($terms)) {
                    foreach ($terms as $term) {
                        @chmod(WPSHOP_UPLOAD_DIR . 'wpshop_product_category/' . $term->term_id, 0755);
                        /** Check if a picture exists **/
                        $term_option = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $term->term_id);
                        if (!empty($term_option) && !empty($term_option['wpshop_category_picture']) && is_file(WPSHOP_UPLOAD_DIR . $term_option['wpshop_category_picture'])) {
                            $wp_upload_dir = wp_upload_dir();
                            $img_path = WPSHOP_UPLOAD_DIR . $term_option['wpshop_category_picture'];
                            $img_basename = basename($img_path);
                            $wp_filetype = wp_check_filetype($img_basename, null);
                            /** Check if there is an image with the same name, if yes we add a rand number to image's name **/
                            $rand_name = (is_file($wp_upload_dir['path'] . '/' . $img_basename)) ? rand() : '';
                            $img_basename = (!empty($rand_name)) ? $rand_name . '_' . $img_basename : $img_basename;
                            if (copy($img_path, $wp_upload_dir['path'] . '/' . $img_basename)) {
                                $attachment = array(
                                    'guid' => $wp_upload_dir['url'] . '/' . $img_basename,
                                    'post_mime_type' => $wp_filetype['type'],
                                    'post_title' => preg_replace('/\.[^.]+$/', '', $img_basename),
                                    'post_content' => '',
                                    'post_status' => 'inherit',
                                );
                                $attach_id = wp_insert_attachment($attachment, $wp_upload_dir['path'] . '/' . $img_basename);
                                /** Generate differnts sizes for this image **/
                                require_once ABSPATH . 'wp-admin/includes/image.php';
                                $attach_data = wp_generate_attachment_metadata($attach_id, $wp_upload_dir['path'] . '/' . $img_basename);
                                wp_update_attachment_metadata($attach_id, $attach_data);
                                /** Update option picture **/
                                $term_option['wpshop_category_picture'] = $attach_id;
                                update_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $term->term_id, $term_option);
                            }
                        }
                    }
                }

                /** Change metabox Hidden Nav Menu Definition to display WPShop categories' metabox **/
                $query = $wpdb->prepare('SELECT * FROM ' . $wpdb->usermeta . ' WHERE meta_key = %s', 'metaboxhidden_nav-menus');
                $meta_keys = $wpdb->get_results($query);
                if (!empty($meta_keys) && is_array($meta_keys)) {
                    foreach ($meta_keys as $meta_key) {
                        $user_id = $meta_key->user_id;
                        $meta_value = unserialize($meta_key->meta_value);
                        if (!empty($meta_value) && is_array($meta_value)) {
                            $data_to_delete = array_search('add-wpshop_product_category', $meta_value);
                            if ($data_to_delete !== false) {
                                unset($meta_value[$data_to_delete]);
                            }
                        }
                        update_user_meta($user_id, 'metaboxhidden_nav-menus', $meta_value);
                    }
                }
                return true;
                break;

            case '37':
                @set_time_limit(900);
                /** Change the path for old categories pictures */
                @chmod(WPSHOP_UPLOAD_DIR . 'wpshop/wpshop_product_category', 0755);
                /** Read all categories folders **/
                $categories_main_dir = WPSHOP_UPLOAD_DIR . 'wpshop/wpshop_product_category';
                if (file_exists($categories_main_dir)) {
                    $main_folder_content = scandir($categories_main_dir);
                    /** For each category folder **/
                    foreach ($main_folder_content as $category_folder) {
                        if ($category_folder && substr($category_folder, 0, 1) != '.') {
                            $category_id = $category_folder;
                            @chmod(WPSHOP_UPLOAD_DIR . 'wpshop/wpshop_product_category/' . $category_id, 0755);
                            $scan_category_folder = opendir($categories_main_dir . '/' . $category_folder);
                            /** For each Picture of category **/
                            $file_time = 0;
                            $save_this_picture = false;
                            while (false !== ($fichier = readdir($scan_category_folder))) {
                                if ($fichier && substr($fichier, 0, 1) != '.') {
                                    if ($file_time < filemtime($categories_main_dir . '/' . $category_id . '/' . $fichier)) {
                                        $save_this_picture = true;
                                        $file_time = filemtime($categories_main_dir . '/' . $category_id . '/' . $fichier);
                                    }
                                    /** Select category option **/
                                    $term_option = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
                                    $wp_upload_dir = wp_upload_dir();
                                    $img_path = $categories_main_dir . '/' . $category_id . '/' . $fichier;
                                    $img_basename = basename($img_path);
                                    $wp_filetype = wp_check_filetype($img_basename, null);
                                    /** Check if there is an image with the same name, if yes we add a rand number to image's name **/
                                    $rand_name = (is_file($wp_upload_dir['path'] . '/' . $img_basename)) ? rand() : '';
                                    $img_basename = (!empty($rand_name)) ? $rand_name . '_' . $img_basename : $img_basename;

                                    if (copy($img_path, $wp_upload_dir['path'] . '/' . $img_basename)) {
                                        $attachment = array(
                                            'guid' => $wp_upload_dir['url'] . '/' . $img_basename,
                                            'post_mime_type' => $wp_filetype['type'],
                                            'post_title' => preg_replace('/\.[^.]+$/', '', $img_basename),
                                            'post_content' => '',
                                            'post_status' => 'inherit',
                                        );
                                        $attach_id = wp_insert_attachment($attachment, $wp_upload_dir['path'] . '/' . $img_basename);
                                        /** Generate differnts sizes for this image **/
                                        require_once ABSPATH . 'wp-admin/includes/image.php';
                                        $attach_data = wp_generate_attachment_metadata($attach_id, $wp_upload_dir['path'] . '/' . $img_basename);
                                        wp_update_attachment_metadata($attach_id, $attach_data);
                                        /** Update option picture **/
                                        $term_option['wpshop_category_picture'] = $attach_id;
                                        if ($save_this_picture) {
                                            update_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id, $term_option);
                                        }
                                        $save_this_picture = false;
                                    }
                                }
                            }
                        }
                    }
                }
                return true;
                break;

            case '38':
                wps_message_ctr::createMessage('WPSHOP_QUOTATION_UPDATE_MESSAGE');
                return true;
                break;

            case '39':
                $attribute_def = wpshop_attributes::getElement('tx_tva', "'valid'", 'code');
                /** Check if the 7% VAT Rate is not already created **/
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE attribute_id = %d AND value = %s', $attribute_def->id, '7');
                $exist_vat_rate = $wpdb->get_results($query);

                if (empty($exist_vat_rate)) {
                    /** Get Max Position **/
                    $query = $wpdb->prepare('SELECT MAX(position) as max_position FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE attribute_id = %d', $attribute_def->id);
                    $max_position = $wpdb->get_var($query);

                    if (!empty($attribute_def) && !empty($attribute_def->id)) {
                        $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $attribute_def->id, 'position' => (int) $max_position + 1, 'value' => '7', 'label' => '7'));
                    }
                }

                /** Filter Search optimization **/
                @set_time_limit(900);
                $query = $wpdb->prepare('SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE taxonomy = %s ', WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
                $categories = $wpdb->get_results($query);
                $cats = array();
                if (!empty($categories)) {
                    foreach ($categories as $category) {
                        $cats[] = $category->term_id;
                    }
                    $wpshop_filter_search = new wps_filter_search();
                    $wpshop_filter_search->stock_values_for_attribute($cats);
                }
                return true;
                break;

            case '40':
                /**    Store watt in puissance unit group    */
                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " WHERE name = %s", __('puissance', 'wpshop'));
                $puissance_unit_group_id = $wpdb->get_var($query);
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('group_id' => $puissance_unit_group_id), array('unit' => 'watt'));

                /**    Store day/week/year in duration unit group    */
                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " WHERE name = %s", __('duration', 'wpshop'));
                $duration_unit_group_id = $wpdb->get_var($query);
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('group_id' => $duration_unit_group_id), array('unit' => 'day'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('group_id' => $duration_unit_group_id), array('unit' => 'week'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('group_id' => $duration_unit_group_id), array('unit' => 'year'));

                /**    Store day/week/year in duration unit group    */
                $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " WHERE name = %s", __('length', 'wpshop'));
                $length_unit_group_id = $wpdb->get_var($query);
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_UNIT, array('group_id' => $length_unit_group_id), array('unit' => 'cm'));
                return true;
                break;

            case '41':
                /**    Get distinct attribute set and delete doublons    */
                $query = "SELECT DISTINCT( name ) AS name, MIN( id ) as min_id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " GROUP BY name HAVING COUNT(id) > 1";
                $list_of_set = $wpdb->get_results($query);
                foreach ($list_of_set as $set_infos) {
                    $query = $wpdb->prepare("DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE name = %s AND id != %d", $set_infos->name, $set_infos->min_id);
                    $wpdb->query($query);
                }
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_SET);

                /**    Get and delete attribute set section    */
                $query = "DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id NOT IN ( SELECT DISTINCT(id) FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " )";
                $wpdb->query($query);
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_GROUP);

                /**    Get and delete attribute set details    */
                $query = "DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE attribute_set_id NOT IN ( SELECT DISTINCT(id) FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " ) OR attribute_group_id NOT IN ( SELECT DISTINCT(id) FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " )";
                $wpdb->query($query);
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_DETAILS);

                $query = "SELECT attribute_set_id, attribute_group_id, attribute_id, MIN(id) as min_id FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " GROUP BY attribute_set_id, attribute_group_id, attribute_id HAVING COUNT(id) > 1";
                $affectation_list = $wpdb->get_results($query);
                foreach ($affectation_list as $affectation_to_treat) {
                    $query = $wpdb->prepare("DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE attribute_set_id = %d AND attribute_group_id = %d AND attribute_id = %d AND id != %d", $affectation_to_treat->attribute_set_id, $affectation_to_treat->attribute_group_id, $affectation_to_treat->attribute_id, $affectation_to_treat->min_id);
                    $wpdb->query($query);
                }
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_DETAILS);

                /**    Get and delete double unit    */
                $query = "SELECT DISTINCT( unit ) AS unit, MIN( id ) as min_id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " GROUP BY unit HAVING COUNT(id) > 1";
                $list_of_set = $wpdb->get_results($query);
                foreach ($list_of_set as $set_infos) {
                    $query = $wpdb->prepare("DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT . " WHERE unit = %s AND id != %d", $set_infos->unit, $set_infos->min_id);
                    $wpdb->query($query);
                }
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_UNIT);

                $query = "SELECT DISTINCT( name ) AS name, MIN( id ) as min_id FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " GROUP BY name HAVING COUNT(id) > 1";
                $list_of_set = $wpdb->get_results($query);
                foreach ($list_of_set as $set_infos) {
                    $query = $wpdb->prepare("DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP . " WHERE name = %s AND id != %d", $set_infos->name, $set_infos->min_id);
                    $wpdb->query($query);
                }
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);

                /**    Get and delete attribute set details    */
                $query = "DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE attribute_set_id NOT IN ( SELECT DISTINCT(id) FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " ) OR attribute_group_id NOT IN ( SELECT DISTINCT(id) FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " )";
                $wpdb->query($query);
                $query = "SELECT GROUP_CONCAT( id ) AS list_id, MIN( id ) as min_id FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " GROUP BY attribute_set_id, attribute_group_id, attribute_id HAVING COUNT(id) > 1";
                $affectation_list = $wpdb->get_results($query);
                foreach ($affectation_list as $list) {
                    $query = $wpdb->prepare("DELETE FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE id IN (" . (substr($list->list_id, -1) == ',' ? substr($list->list_id, 0, -1) : $list->list_id) . ") AND id != %d", $list->min_id, '');
                    $wpdb->query($query);
                }
                $wpdb->query("OPTIMIZE TABLE " . WPSHOP_DBT_ATTRIBUTE_DETAILS);

                return true;
                break;

            case '42':
                $available_downloadable_product = get_option('WPSHOP_DOWNLOADABLE_FILE_IS_AVAILABLE');
                if (empty($available_downloadable_product)) {
                    wps_message_ctr::createMessage('WPSHOP_DOWNLOADABLE_FILE_IS_AVAILABLE');
                }
                return true;
                break;

            case '43':
                $available_downloadable_product = get_option('WPSHOP_ORDER_IS_CANCELED');
                if (empty($available_downloadable_product)) {
                    wps_message_ctr::createMessage('WPSHOP_ORDER_IS_CANCELED');
                }
                return true;
                break;

            case '44':
                $display_option = get_option('wpshop_display_option');
                if (!empty($display_option) && empty($display_option['latest_products_ordered'])) {
                    $display_option['latest_products_ordered'] = 3;
                    update_option('wpshop_display_option', $display_option);
                }

                /** Check messages for customization **/
                // @since 1.4.3.7 Deleted messages constants
                /*$messages = array('WPSHOP_SIGNUP_MESSAGE' => WPSHOP_SIGNUP_MESSAGE, 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE' => WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE' => WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE, 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE' => WPSHOP_SHIPPING_CONFIRMATION_MESSAGE, 'WPSHOP_ORDER_UPDATE_MESSAGE' => WPSHOP_ORDER_UPDATE_MESSAGE, 'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE' => WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE, 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE' => WPSHOP_NEW_ORDER_ADMIN_MESSAGE, 'WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE' => WPSHOP_NEW_QUOTATION_ADMIN_MESSAGE, 'WPSHOP_QUOTATION_CONFIRMATION_MESSAGE' => WPSHOP_QUOTATION_CONFIRMATION_MESSAGE, 'WPSHOP_QUOTATION_UPDATE_MESSAGE' => WPSHOP_QUOTATION_UPDATE_MESSAGE, 'WPSHOP_DOWNLOADABLE_FILE_IS_AVAILABLE' => WPSHOP_DOWNLOADABLE_FILE_IS_AVAILABLE, 'WPSHOP_ORDER_IS_CANCELED' => WPSHOP_ORDER_IS_CANCELED);
                if (!empty($messages)) {
                    foreach ($messages as $key => $message) {
                        $message_option = get_option($key);
                        if (!empty($message_option)) {
                            $post_message = get_post($message_option);
                            $original_message = (!empty($post_message) && !empty($post_message->post_content)) ? $post_message->post_content : '';
                            $tags = array('<p>', '</p>');
                            if (str_replace($tags, '', $original_message) == str_replace($tags, '', __($message, 'wpshop'))) {
                                wp_update_post(array('ID' => $message_option, 'post_content' => wps_message_ctr::customize_message($original_message)));
                            }
                        }
                    }
                }*/

                return true;
                break;

            case '45':
                $shipping_mode_ctr = new wps_shipping_mode_ctr();
                $shipping_mode_ctr->migrate_default_shipping_mode();
                return true;
                break;

            case '46':
                wps_message_ctr::createMessage('WPSHOP_FORGOT_PASSWORD_MESSAGE');
                wps_message_ctr::customize_message(WPSHOP_FORGOT_PASSWORD_MESSAGE);
                return true;
                break;

            case '47':
                wps_payment_mode::migrate_payment_modes();
                return true;
                break;

            case '48':
                @ini_set('max_execution_time', '500');

                $count_products = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
                $output_type_option = get_option('wpshop_display_option');
                $output_type = $output_type_option['wpshop_display_list_type'];

                for ($i = 0; $i <= $count_products->publish; $i += 20) {
                    $query = $wpdb->prepare('SELECT * FROM ' . $wpdb->posts . ' WHERE post_type = %s AND post_status = %s ORDER BY ID DESC LIMIT ' . $i . ', 20', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish');
                    $products = $wpdb->get_results($query);
                    if (!empty($products)) {
                        foreach ($products as $product) {
                            $p = wpshop_products::get_product_data($product->ID);
                            $price = wpshop_prices::get_product_price($p, 'just_price_infos', array('mini_output', $output_type));
                            update_post_meta($product->ID, '_wps_price_infos', $price);
                        }
                    }
                }

                return true;
                break;

            case '49':
                update_option('wpshop_send_invoice', true);
                return true;
                break;

            case '50':
                $price_display_option = get_option('wpshop_catalog_product_option');
                $price_display_option['price_display']['text_from'] = 'on';
                $price_display_option['price_display']['lower_price'] = 'on';
                update_option('wpshop_catalog_product_option', $price_display_option);

                self::wpshop_insert_default_pages();

                return true;
                break;

            case '51':
                /**    Insert new message for direct payment link    */
                $direct_payment_link_message = get_option('WPSHOP_DIRECT_PAYMENT_LINK_MESSAGE');
                if (empty($direct_payment_link_message)) {
                    wps_message_ctr::createMessage('WPSHOP_DIRECT_PAYMENT_LINK_MESSAGE');
                }
                return true;
                break;

            case '52':
                $account_page_option = get_option('wpshop_myaccount_page_id');
                if (!empty($account_page_option)) {
                    $page_account = get_post($account_page_option);
                    $page_content = (!empty($page_account) && !empty($page_account->post_content)) ? str_replace('[wpshop_myaccount]', '[wps_account_dashboard]', $page_account->post_content) : '[wps_account_dashboard]';
                    wp_update_post(array('ID' => $account_page_option, 'post_content' => $page_content));
                }
                return true;
                break;

            case '53':
                $payment_modes_option = get_option('wps_payment_mode');
                if (!empty($payment_modes_option) && !empty($payment_modes_option['mode'])) {
                    $payment_modes_option['mode']['cash_on_delivery'] = array(
                        'name' => __('Cash on delivery', 'wpshop'),
                        'logo' => WPSHOP_TEMPLATES_URL . 'wpshop/cheque.png',
                        'description' => __('Pay your order on delivery', 'wpshop'));

                    update_option('wps_payment_mode', $payment_modes_option);
                }

                // Mass action on products to add a flag on variation definition
                $products = get_posts(array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
                if (!empty($products)) {
                    foreach ($products as $p) {
                        $post_id = $p->ID;
                        $check_product_have_variations = wpshop_products::get_variation($post_id);
                        if (!empty($check_product_have_variations)) {
                            $variation_flag = wpshop_products::check_variation_type($post_id);
                            $variation_defining = get_post_meta($post_id, '_wpshop_variation_defining', true);
                            $variation_defining['variation_type'] = $variation_flag;
                            update_post_meta($post_id, '_wpshop_variation_defining', $variation_defining);
                        }
                    }
                }
                return true;
                break;

            case '54':
                // Change shortcode of sign up page
                $signup_page_id = get_option('wpshop_signup_page_id');
                if (!empty($signup_page_id)) {
                    $signup_page = get_post($signup_page_id);
                    $signup_page_content = (!empty($signup_page) && !empty($signup_page->post_content)) ? str_replace('[wpshop_signup]', '[wps_account_dashboard]', $signup_page->post_content) : '[wps_account_dashboard]';
                    wp_update_post(array('ID' => $signup_page_id, 'post_content' => $signup_page_content));
                }

                // Change Terms of sale default content
                $terms_page_id = get_option('wpshop_terms_of_sale_page_id');
                if (!empty($terms_page_id)) {
                    $terms_sale_page = get_post($terms_page_id);
                    if (!empty($terms_sale_page) && !empty($terms_sale_page->post_content) && $terms_sale_page->post_content == '[wpshop_terms_of_sale]') {
                        $data = '<h1>' . __('Your terms of sale', 'wpshop') . '</h1>';
                        $data .= '<h3>' . __('Rule', 'wpshop') . ' 1</h3>';
                        $data .= '<p>Ut enim quisque sibi plurimum confidit et ut quisque maxime virtute et sapientia sic munitus est, ut nullo egeat suaque omnia in se ipso posita iudicet, ita in amicitiis expetendis colendisque maxime excellit.</p>';
                        $data .= '<h3>' . __('Rule', 'wpshop') . ' 2</h3>';
                        $data .= '<p>Ut enim quisque sibi plurimum confidit et ut quisque maxime virtute et sapientia sic munitus est, ut nullo egeat suaque omnia in se ipso posita iudicet, ita in amicitiis expetendis colendisque maxime excellit.</p>';
                        $data .= '<h3>' . __('Rule', 'wpshop') . ' 3</h3>';
                        $data .= '<p>Ut enim quisque sibi plurimum confidit et ut quisque maxime virtute et sapientia sic munitus est, ut nullo egeat suaque omnia in se ipso posita iudicet, ita in amicitiis expetendis colendisque maxime excellit.</p>';
                        $data .= '<h3>' . __('Credits', 'wpshop') . '</h3>';
                        $data .= sprintf(__('%s uses <a href="http://www.wpshop.fr/" target="_blank" title="%s uses WPShop e-commerce plug-in for Wordpress">WPShop e-commerce for Wordpress</a>', 'wpshop'), get_bloginfo('name'), get_bloginfo('name'));
                        wp_update_post(array('ID' => $terms_page_id, 'post_content' => $data));
                    }
                }

                return true;
                break;

            case '55':
                $checkout_page_id = get_option('wpshop_checkout_page_id');
                $checkout_page = get_post($checkout_page_id);
                $checkout_page_content = (!empty($checkout_page) && !empty($checkout_page->post_content)) ? str_replace('[wpshop_checkout]', '[wps_checkout]', $checkout_page->post_content) : '[wps_checkout]';
                wp_update_post(array('ID' => $checkout_page_id, 'post_content' => $checkout_page_content));

                // Update cart page id
                update_option('wpshop_cart_page_id', $checkout_page_id);
                return true;
                break;

            case '56':
                $wps_entities = new wpshop_entities();
                $customer_entity_id = $wps_entities->get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
                $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d ORDER BY id LIMIT 1', $customer_entity_id);
                $set = $wpdb->get_row($query);
                if (!empty($set)) {
                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET,
                        array('default_set' => 'yes'),
                        array('id' => $set->id));
                }
                // Update Opinions activation option
                update_option('wps_opinion', array('active' => 'on'));
                return true;
                break;

            case '57':
                $wpshop_cart_option = get_option('wpshop_cart_option');
                $wpshop_cart_option['display_newsletter']['site_subscription'][] = 'yes';
                $wpshop_cart_option['display_newsletter']['partner_subscription'][] = 'yes';

                update_option('wpshop_cart_option', $wpshop_cart_option);
                return true;
                break;

            case '58':
                /** Turn customers publish into draft **/
                $query = $wpdb->prepare("UPDATE {$wpdb->posts} SET post_status = %s WHERE post_type = %s", "draft", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
                $wpdb->query($query);

                $attribute_def = wpshop_attributes::getElement('tx_tva', "'valid'", 'code');
                /** Check if the 0% VAT Rate is not already created **/
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE attribute_id = %d AND value = %s', $attribute_def->id, '0');
                $exist_vat_rate = $wpdb->get_results($query);

                if (empty($exist_vat_rate)) {
                    /** Get Max Position **/
                    $query = $wpdb->prepare('SELECT MAX(position) as max_position FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . ' WHERE attribute_id = %d', $attribute_def->id);
                    $max_position = $wpdb->get_var($query);

                    if (!empty($attribute_def) && !empty($attribute_def->id)) {
                        $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $attribute_def->id, 'position' => (int) $max_position + 1, 'value' => '0', 'label' => '0'));
                    }
                }
                return true;
                break;

            case '59':
                /** Move old images gallery to the new gallery, and remove old links **/
                $allowed = get_allowed_mime_types();
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
                    'post_status' => array('publish', 'draft', 'trash'),
                );
                $posts = get_posts($args);
                $result = array();
                foreach ($posts as $post) {
                    $array = array();
                    $array['Post'] = $post;
                    $array['PrincipalThumbnailID'] = get_post_meta($post->ID, '_thumbnail_id', true);
                    $array['Attachments'] = get_attached_media($allowed, $post->ID);
                    $array['TrueAttachmentsString'] = get_post_meta($post->ID, '_wps_product_media', true);
                    if (!empty($array['TrueAttachmentsString'])) {
                        $TrueAttachments_id = explode(',', $array['TrueAttachmentsString']);
                    }
                    $array['OldAttachmentsString'] = '';
                    foreach ($array['Attachments'] as $attachment) {
                        $filename = basename(get_attached_file($attachment->ID));
                        $pos_ext = strrpos($filename, '.');
                        $filename_no_ext = substr($filename, 0, $pos_ext);
                        if ((empty($TrueAttachments_id) || !in_array($attachment->ID, $TrueAttachments_id)) && !(preg_match('#' . $filename_no_ext . '#', $post->post_content)) && ((empty($array['PrincipalThumbnailID']) || $attachment->ID != $array['PrincipalThumbnailID']))) {
                            $array['OldAttachmentsString'] .= $attachment->ID . ',';
                        }
                    }
                    unset($TrueAttachments_id);
                    $result[$post->ID] = $array['TrueAttachmentsString'] . $array['OldAttachmentsString'];
                    update_post_meta($post->ID, '_wps_product_media', $result[$post->ID]);
                }
                return true;
                break;

            case '60':
                /* Create default emails */
                wps_message_ctr::create_default_message();

                /** Update entries for quick add */
                $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_required = "yes", is_used_in_quick_add_form = "yes" WHERE code = "barcode"');
                $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "yes" WHERE code = "product_stock"');
                $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "yes" WHERE code = "manage_stock"');
                switch (WPSHOP_PRODUCT_PRICE_PILOT) {
                    case 'HT':
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "yes", is_used_in_variation = "yes" WHERE code = "price_ht"');
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "no" WHERE code = "tx_tva"');
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "no", is_used_in_variation = "no" WHERE code = "product_price"');
                        break;
                    default:
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "yes", is_used_in_variation = "yes" WHERE code = "product_price"');
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "yes" WHERE code = "tx_tva"');
                        $query = $wpdb->query('UPDATE ' . WPSHOP_DBT_ATTRIBUTE . ' SET is_used_in_quick_add_form = "no", is_used_in_variation = "no" WHERE code = "price_ht"');
                        break;
                }

                /* Default country with WP language */
                $wpshop_country_default_choice_option = get_option('wpshop_country_default_choice');
                if (empty($wpshop_country_default_choice_option)) {
                    update_option('wpshop_country_default_choice', substr(get_bloginfo('language'), 3));
                }
                return true;
                break;

            case '61':
                /** Import the xml for guided tour */
                wpsBubble_ctr::import_xml();

                /* Hide admin bar */
                $wpshop_display_option = get_option('wpshop_display_option');
                if (!empty($wpshop_display_option) && empty($wpshop_display_option['wpshop_hide_admin_bar'])) {
                    $wpshop_display_option['wpshop_hide_admin_bar'] = 'on';
                    update_option('wpshop_display_option', $wpshop_display_option);
                }

                return true;
                break;

            case '62':
                /** Install user default for POS */
                wps_pos_addon::action_to_do_on_activation();

                return true;
                break;

            case '63':
                $data = get_option('wps_shipping_mode');
                if (empty($data['modes']['default_shipping_mode_for_pos'])) {
                    $data['modes']['default_shipping_mode_for_pos']['name'] = __('No Delivery', 'wpshop');
                    $data['modes']['default_shipping_mode_for_pos']['explanation'] = __('Delivery method for point of sale.', 'wpshop');
                    update_option('wps_shipping_mode', $data);
                }
                return true;
                break;

            case '64':
                $options = get_option('wpshop_catalog_product_option');
                if (!empty($options['wpshop_catalog_product_slug_with_category'])) {
                    unset($options['wpshop_catalog_product_slug_with_category']);
                    update_option('wpshop_catalog_product_option', $options);
                }
                return true;
                break;

            case '65':
                $entity_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE code = %s AND entity_id = %d', 'company_customer', $entity_id);
                $company_id = $wpdb->get_var($query);
                if (!isset($company_id)) {
                    $wpdb->insert(WPSHOP_DBT_ATTRIBUTE, array(
                        'is_visible_in_front' => 'no',
                        'is_visible_in_front_listing' => 'yes',
                        'is_global' => 'no',
                        'is_user_defined' => 'no',
                        'is_required' => 'no',
                        'is_visible_in_advanced_search' => 'no',
                        'is_searchable' => 'no',
                        'is_filterable' => 'no',
                        'is_comparable' => 'no',
                        'is_html_allowed_on_front' => 'no',
                        'is_unique' => 'no',
                        'is_filterable_in_search' => 'no',
                        'is_used_for_sort_by' => 'no',
                        'is_configurable' => 'no',
                        'is_requiring_unit' => 'no',
                        'is_recordable_in_cart_meta' => 'no',
                        'is_used_in_admin_listing_column' => 'no',
                        'is_used_in_quick_add_form' => 'no',
                        'is_used_for_variation' => 'no',
                        'is_used_in_variation' => 'no',
                        '_display_informations_about_value' => 'no',
                        '_need_verification' => 'no',
                        '_unit_group_id' => null,
                        '_default_unit' => null,
                        'is_historisable' => 'yes',
                        'is_intrinsic' => 'no',
                        'data_type_to_use' => 'custom',
                        'use_ajax_for_filling_field' => 'no',
                        'data_type' => 'varchar',
                        'backend_table' => null,
                        'backend_label' => 'Company',
                        'backend_input' => 'text',
                        'frontend_label' => 'Company',
                        'frontend_input' => 'text',
                        'frontend_verification' => null,
                        'code' => 'company_customer',
                        'note' => '',
                        'default_value' => '',
                        'frontend_css_class' => 'company_customer',
                        'backend_css_class' => null,
                        'frontend_help_message' => null,
                        'entity_id' => $entity_id,
                    ));
                    $company_id = $wpdb->insert_id;
                }

                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE code = %s AND entity_id = %d', 'is_provider', $entity_id);
                $is_provider_id = $wpdb->get_var($query);
                if (!isset($is_provider_id)) {
                    $wpdb->insert(WPSHOP_DBT_ATTRIBUTE, array(
                        'is_visible_in_front' => 'no',
                        'is_visible_in_front_listing' => 'yes',
                        'is_global' => 'no',
                        'is_user_defined' => 'no',
                        'is_required' => 'yes',
                        'is_visible_in_advanced_search' => 'no',
                        'is_searchable' => 'no',
                        'is_filterable' => 'no',
                        'is_comparable' => 'no',
                        'is_html_allowed_on_front' => 'no',
                        'is_unique' => 'no',
                        'is_filterable_in_search' => 'no',
                        'is_used_for_sort_by' => 'no',
                        'is_configurable' => 'no',
                        'is_requiring_unit' => 'no',
                        'is_recordable_in_cart_meta' => 'no',
                        'is_used_in_admin_listing_column' => 'no',
                        'is_used_in_quick_add_form' => 'no',
                        'is_used_for_variation' => 'no',
                        'is_used_in_variation' => 'no',
                        '_display_informations_about_value' => 'no',
                        '_need_verification' => 'no',
                        '_unit_group_id' => null,
                        '_default_unit' => null,
                        'is_historisable' => 'yes',
                        'is_intrinsic' => 'no',
                        'data_type_to_use' => 'custom',
                        'use_ajax_for_filling_field' => 'no',
                        'data_type' => 'integer',
                        'backend_table' => null,
                        'backend_label' => 'Provider',
                        'backend_input' => 'select',
                        'frontend_label' => 'Provider',
                        'frontend_input' => 'select',
                        'frontend_verification' => null,
                        'code' => 'is_provider',
                        'note' => '',
                        'default_value' => 'no',
                        'frontend_css_class' => 'is_provider',
                        'backend_css_class' => null,
                        'frontend_help_message' => null,
                        'entity_id' => $entity_id,
                    ));
                    $is_provider_id = $wpdb->insert_id;
                    $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array(
                        'status' => 'valid',
                        'attribute_id' => $is_provider_id,
                        'creation_date_value' => current_time('mysql'),
                        'value' => 'yes',
                        'label' => 'Yes',
                    ));
                    $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array(
                        'status' => 'valid',
                        'attribute_id' => $is_provider_id,
                        'creation_date_value' => current_time('mysql'),
                        'value' => 'no',
                        'label' => 'No',
                    ));
                    $default_value = $wpdb->insert_id;
                    $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('default_value' => $default_value), array('id' => $is_provider_id));
                }

                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE attribute_id = %s', $company_id);
                $company_section_detail_id = $wpdb->get_var($query);

                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE attribute_id = %s', $is_provider_id);
                $is_provider_section_detail_id = $wpdb->get_var($query);

                if (!isset($is_provider_section_detail_id) || !isset($company_section_detail_id)) {
                    $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d', $entity_id);
                    $attribute_set_id = $wpdb->get_var($query);

                    $query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, 'account');
                    $attribute_set_section_id = $wpdb->get_var($query);

                    $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d', $entity_id, $attribute_set_id, $attribute_set_section_id);
                    $attributes_set_details = $wpdb->get_results($query);
                    $set_details_id_postion_order = array();
                    foreach ($attributes_set_details as $attribute_set_detail) {
                        $query = $wpdb->prepare('SELECT code FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE id = %d', $attribute_set_detail->attribute_id);
                        $attribute_set_detail_code = $wpdb->get_var($query);
                        if ($attribute_set_detail_code == 'last_name') {
                            $set_details_id_postion_order[1] = $attribute_set_detail->attribute_id;
                        }
                        if ($attribute_set_detail_code == 'first_name') {
                            $set_details_id_postion_order[2] = $attribute_set_detail->attribute_id;
                        }
                        if ($attribute_set_detail_code == 'user_email') {
                            $set_details_id_postion_order[3] = $attribute_set_detail->attribute_id;
                        }
                        if ($attribute_set_detail_code == 'user_pass') {
                            $set_details_id_postion_order[4] = $attribute_set_detail->attribute_id;
                        }
                    }
                    $max_position = count($set_details_id_postion_order);

                    if (!isset($company_section_detail_id)) {
                        $max_position = $max_position + 1;
                        $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $company_id, 'position' => (int) $max_position));
                        $set_details_id_postion_order[$max_position] = $company_id;
                        $company_section_detail_id = $wpdb->insert_id;
                    }

                    if (!isset($is_provider_section_detail_id)) {
                        $max_position = $max_position + 1;
                        $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $is_provider_id, 'position' => (int) $max_position));
                        $set_details_id_postion_order[$max_position] = $is_provider_id;
                        $is_provider_section_detail_id = $wpdb->insert_id;
                    }

                    foreach ($set_details_id_postion_order as $pos => $attr_id) {
                        $wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('position' => $pos), array('attribute_id' => $attr_id, 'attribute_set_id' => $attribute_set_id, 'entity_type_id' => $entity_id, 'attribute_group_id' => $attribute_set_section_id), array('%d'), array('%d', '%d', '%d', '%d'));
                    }
                }

                $wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('name' => __('Free product', 'wpshop'), 'slug' => 'free_product'), array('name' => 'free_product'), array('%s', '%s'), array('%s'));

                return true;
                break;

            case 66:
                $price_behaviour_entity_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
                $wpdb->insert(WPSHOP_DBT_ATTRIBUTE, array(
                    'is_visible_in_front' => 'no',
                    'is_visible_in_front_listing' => 'yes',
                    'is_global' => 'no',
                    'is_user_defined' => 'no',
                    'is_required' => 'no',
                    'is_visible_in_advanced_search' => 'no',
                    'is_searchable' => 'no',
                    'is_filterable' => 'no',
                    'is_comparable' => 'no',
                    'is_html_allowed_on_front' => 'no',
                    'is_unique' => 'no',
                    'is_filterable_in_search' => 'no',
                    'is_used_for_sort_by' => 'no',
                    'is_configurable' => 'no',
                    'is_requiring_unit' => 'no',
                    'is_recordable_in_cart_meta' => 'no',
                    'is_used_in_admin_listing_column' => 'no',
                    'is_used_in_quick_add_form' => 'no',
                    'is_used_for_variation' => 'no',
                    'is_used_in_variation' => 'yes',
                    '_display_informations_about_value' => 'no',
                    '_need_verification' => 'no',
                    '_unit_group_id' => null,
                    '_default_unit' => null,
                    'is_historisable' => 'yes',
                    'is_intrinsic' => 'no',
                    'data_type_to_use' => 'custom',
                    'use_ajax_for_filling_field' => 'no',
                    'data_type' => 'integer',
                    'backend_table' => null,
                    'backend_label' => null,
                    'backend_input' => 'select',
                    'frontend_label' => 'price_behaviour',
                    'frontend_input' => 'select',
                    'frontend_verification' => null,
                    'code' => 'price_behaviour',
                    'note' => '',
                    'default_value' => '',
                    'frontend_css_class' => 'price_behaviour',
                    'backend_css_class' => null,
                    'frontend_help_message' => null,
                    'entity_id' => $price_behaviour_entity_id,
                ));
                $price_behaviour = $wpdb->insert_id;
                $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array(
                    'status' => 'valid',
                    'attribute_id' => $price_behaviour,
                    'creation_date' => current_time('mysql'),
                    'value' => '+',
                    'label' => '+',
                ));
                $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array(
                    'status' => 'valid',
                    'attribute_id' => $price_behaviour,
                    'creation_date' => current_time('mysql'),
                    'value' => '=',
                    'label' => '=',
                ));
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d AND ( name = %s OR slug = %s )', $price_behaviour_entity_id, 'default', 'default');
                $price_behaviour_section_id = $wpdb->get_var($query);
                $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_GROUP . ' WHERE attribute_set_id = %d AND code = %s', $price_behaviour_section_id, 'prices');
                $price_behaviour_section_detail_id = $wpdb->get_var($query);
                $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array(
                    'status' => 'deleted',
                    'creation_date' => current_time('mysql', 0),
                    'entity_type_id' => $price_behaviour_entity_id,
                    'attribute_set_id' => $price_behaviour_section_id,
                    'attribute_group_id' => $price_behaviour_section_detail_id,
                    'attribute_id' => $price_behaviour,
                    'position' => 0,
                ));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'is_downloadable_'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'tva'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'price_ht'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'product_stock'));
                $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('is_used_in_variation' => 'yes', 'last_update_date' => current_time('mysql', 0)), array('code' => 'product_weight'));
                return true;
                break;

            case 67:
                $admin_new_version_message = get_option('WPSHOP_NEW_VERSION_ADMIN_MESSAGE');
                if (empty($admin_new_version_message)) {
                    wps_message_ctr::createMessage('WPSHOP_NEW_VERSION_ADMIN_MESSAGE');
                }
                $wpshop_cart_option = get_option('wpshop_cart_option');
                if (!empty($wpshop_cart_option) && !empty($wpshop_cart_option['total_nb_of_item_allowed'])) {
                    $wpshop_cart_option['total_nb_of_item_allowed'][0] = (int) filter_var($wpshop_cart_option['total_nb_of_item_allowed'][0], FILTER_VALIDATE_BOOLEAN);
                }
                update_option('wpshop_cart_option', $wpshop_cart_option);
                return true;
                break;

			case 68:
				wps_message_ctr::create_default_message();
				return true;
				break;

            /*    Always add specific case before this bloc    */
            case 'dev':

                //wp_cache_flush();
                // Newsletters options
                //$wp_rewrite->flush_rules();
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
    public function uninstall_wpshop()
    {
        global $wpdb;

        if (WPSHOP_DEBUG_MODE_ALLOW_DATA_DELETION && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_MODE_ALLOWED_IP))) {
            $query = $wpdb->query("DROP TABLE `wp_wpshop__attribute`, `wp_wpshop__attributes_unit`, `wp_wpshop__attributes_unit_groups`, `wp_wpshop__attribute_set`, `wp_wpshop__attribute_set_section`, `wp_wpshop__attribute_set_section_details`, `wp_wpshop__attribute_value_datetime`, `wp_wpshop__attribute_value_decimal`, `wp_wpshop__attribute_value_integer`, `wp_wpshop__attribute_value_text`, `wp_wpshop__attribute_value_varchar`, `wp_wpshop__attribute_value__histo`, `wp_wpshop__cart`, `wp_wpshop__cart_contents`, `wp_wpshop__documentation`, `wp_wpshop__entity`, `wp_wpshop__historique`, `wp_wpshop__message`, `wp_wpshop__attribute_value_options`;");
            $query = $wpdb->query("DELETE FROM " . $wpdb->options . " WHERE `option_name` LIKE '%wpshop%';");

            $wpshop_products_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE 'wpshop_%';");
            $list = '  ';
            foreach ($wpshop_products_posts as $post) {
                $list .= "'" . $post->ID . "', ";
            }
            $list = substr($list, 0, -2);

            $wpshop_products_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_parent IN (" . $list . ");");
            $list_attachment = '  ';
            foreach ($wpshop_products_posts as $post) {
                $list_attachment .= "'" . $post->ID . "', ";
            }
            $list_attachment = substr($list_attachment, 0, -2);

            $query = $wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE post_id IN (" . $list . ");");
            $query = $wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE post_id IN (" . $list_attachment . ");");
            $query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE ID IN (" . $list . ");");
            $query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE ID IN (" . $list_attachment . ");");
            $query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE post_content LIKE '%wpshop%';");
        }

        /*    Unset administrator permission    */
        $adminRole = get_role('administrator');
        foreach ($adminRole->capabilities as $capabilityName => $capability) {
            if (substr($capabilityName, 0, 7) == 'wpshop_') {
                if ($adminRole->has_cap($capabilityName)) {
                    $adminRole->remove_cap($capabilityName);
                }
            }
        }
    }

}
