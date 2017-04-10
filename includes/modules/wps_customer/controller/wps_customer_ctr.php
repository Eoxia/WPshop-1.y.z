<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manage Customer general and front-end functions
 * @author ALLEGRE Jérôme - EOXIA
 *
 */
class wps_customer_ctr
{

	public static $customer_user_identifier_cache = array();

    public function __construct()
    {
        /**    Create customer entity type on wordpress initilisation*/
        add_action('init', array($this, 'create_customer_entity'));

        /**    Call style for administration    */
        add_action('admin_enqueue_scripts', array(&$this, 'admin_css'));

        add_action('admin_init', array($this, 'customer_action_on_plugin_init'));
        add_action('admin_init', array($this, 'redirect_new_user'));
        add_action('admin_menu', array($this, 'customer_action_on_menu'));

        /**    When a wordpress user is created, create a customer (post type)    */
        add_action('user_register', array($this, 'create_entity_customer_when_user_is_created'));
        add_action('edit_user_profile_update', array($this, 'update_entity_customer_when_profile_user_is_update'));

        /** When save customer update */
        add_action('save_post', array($this, 'save_entity_customer'), 10, 2);
        //add_action( 'admin_notices', array( $this, 'notice_save_post_customer_informations' ) );

        /**    Add filters for customer list    */
        add_filter('bulk_actions-edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, array($this, 'customer_list_table_bulk_actions'));
        add_filter('manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . '_columns', array($this, 'list_table_header'));
        add_action('manage_' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . '_posts_custom_column', array($this, 'list_table_column_content'), 10, 2);
        add_action('restrict_manage_posts', array(&$this, 'list_table_filters'));
        add_filter('parse_query', array(&$this, 'list_table_filter_parse_query'));

        /**    Filter search for customers    */
        //add_filter( 'pre_get_posts', array( $this, 'customer_search' ) );

        /** Customer options for the shop */
        add_action('wsphop_options', array(&$this, 'declare_options'), 8);
    }

    /**
     * Customer options for the shop
     */
    public static function declare_options()
    {
        if (WPSHOP_DEFINED_SHOP_TYPE == 'sale') {
            $wpshop_shop_type = !empty($_POST['wpshop_shop_type']) ? sanitize_text_field($_POST['wpshop_shop_type']) : '';
            $old_wpshop_shop_type = !empty($_POST['old_wpshop_shop_type']) ? sanitize_text_field($_POST['old_wpshop_shop_type']) : '';

            if (($wpshop_shop_type == '' || $wpshop_shop_type != 'presentation')
                && ($old_wpshop_shop_type == '' || $old_wpshop_shop_type != 'presentation')) {
                /**    Add module option to wpshop general options    */
                register_setting('wpshop_options', 'wpshop_cart_option', array('wps_customer_ctr', 'wpshop_options_validate_customers_newsleters'));
                add_settings_field('display_newsletters_subscriptions', __('Display newsletters subscriptions', 'wpshop'), array('wps_customer_ctr', 'display_newsletters_subscriptions'), 'wpshop_cart_info', 'wpshop_cart_info');

            }
        }
    }

    /**
     * Validate Options Customer
     * @param unknown_type $input
     * @return unknown
     */
    public static function wpshop_options_validate_customers_newsleters($input)
    {
        return $input;
    }

    public static function display_newsletters_subscriptions()
    {
        $cart_option = get_option('wpshop_cart_option', array());
        $output = '';

        $input_def = array();
        $input_def['name'] = '';
        $input_def['id'] = 'wpshop_cart_option_display_newsletter_site_subscription';
        $input_def['type'] = 'checkbox';
        $input_def['valueToPut'] = 'index';
        $input_def['value'] = !empty($cart_option['display_newsletter']['site_subscription']) ? $cart_option['display_newsletter']['site_subscription'][0] : 'no';
        $input_def['possible_value'] = 'yes';
        $output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[display_newsletter][site_subscription]') . '<label for="' . $input_def['id'] . '">' . __('Newsletters of the site', 'wpshop') . '</label>' . '<a href="#" title="' . __('Check this box if you want display newsletter site subscription', 'wpshop') . '" class="wpshop_infobulle_marker">?</a>' . '<br>';

        $input_def = array();
        $input_def['name'] = '';
        $input_def['id'] = 'wpshop_cart_option_display_newsletter_partner_subscription';
        $input_def['type'] = 'checkbox';
        $input_def['valueToPut'] = 'index';
        $input_def['value'] = !empty($cart_option['display_newsletter']['partner_subscription']) ? $cart_option['display_newsletter']['partner_subscription'][0] : 'no';
        $input_def['possible_value'] = 'yes';
        $output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[display_newsletter][partner_subscription]') . '<label for="' . $input_def['id'] . '">' . __('Newsletters of the partners', 'wpshop') . '</label>' . '<a href="#" title="' . __('Check this box if you want display newsletter partners subscription', 'wpshop') . '" class="wpshop_infobulle_marker">?</a>' . '<br>';

        echo $output;
    }

    /**
     * Include stylesheets
     */
    public function admin_css()
    {
        wp_register_style('wpshop-modules-customer-backend-styles', WPS_ACCOUNT_URL . '/' . WPS_ACCOUNT_DIR . '/assets/backend/css/backend.css', '', WPSHOP_VERSION);
        wp_enqueue_style('wpshop-modules-customer-backend-styles');
    }

    /**
     * Return a list  of users
     * @param array $customer_list_params
     * @param integer $selected_user
     * @param boolean $multiple
     * @param boolean $disabled
     * @return string
     */
    public function custom_user_list($customer_list_params = array('name' => 'user[customer_id]', 'id' => 'user_customer_id'), $selected_user = "", $multiple = false, $disabled = false)
    {
        global $wpdb;
        $content_output = '';

        // USERS
        $wps_customer_mdl = new wps_customer_mdl();
        $users = $wps_customer_mdl->getUserList();
        $select_users = '';
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($user->ID != 1) {
                    $lastname = get_user_meta($user->ID, 'last_name', true);
                    $firstname = get_user_meta($user->ID, 'first_name', true);
                    $customer_entity_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
                    if (!empty($customer_entity_id)) {
                        $company_attr = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE . ' WHERE entity_id = %s AND code = %s AND status = %s', $customer_entity_id, 'company_customer', 'valid'));
                        if (!empty($company_attr)) {
                            $query = $wpdb->prepare('SELECT value  FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($company_attr->data_type) . ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d ', $customer_entity_id, $company_attr->id, wps_customer_ctr::get_customer_id_by_author_id($user->ID));
                            $company_value = $wpdb->get_var($query);
                        }
                    }
                    ob_start();?>
					<option value="<?php echo $user->ID; ?>" <?php echo ((!$multiple) && ($selected_user == $user->ID)) ? ' selected="selected"' : ''; ?>>
						<?php echo $lastname; ?> <?php echo $firstname; ?> (<?php echo $user->user_email; ?>)<?php echo isset($company_value) ? ' : ' . $company_value : ''; ?>
					</option>
					<?php
$select_users .= ob_get_clean();
                }
            }
            $content_output = '
			<select name="' . $customer_list_params['name'] . '" id="' . $customer_list_params['id'] . '" data-placeholder="' . __('Choose a customer', 'wpshop') . '" class="chosen_select"' . ($multiple ? ' multiple="multiple" ' : '') . '' . ($disabled ? ' disabled="disabled" ' : '') . '>
				<option value="0" ></option>
				' . $select_users . '
			</select>';
        }
        return $content_output;
    }

    /**
     * Action on plug-on action
     */
    public static function customer_action_on_plugin_init()
    {
        return;
    }

    /**
     * Create the customer entity
     */
    public function create_customer_entity()
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT P.post_title, PM.meta_value FROM {$wpdb->posts} AS P INNER JOIN {$wpdb->postmeta} AS PM ON (PM.post_id = P.ID) WHERE P.post_name = %s AND PM.meta_key = %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, '_wpshop_entity_params');
        $customer_entity_definition = $wpdb->get_row($query);
        $current_entity_params = !empty($customer_entity_definition) && !empty($customer_entity_definition->meta_value) ? unserialize($customer_entity_definition->meta_value) : null;

        $post_type_params = array(
            'labels' => array(
                'name' => __('Customers', 'wpshop'),
                'singular_name' => __('Customer', 'wpshop'),
                'add_new_item' => __('New customer', 'wpshop'),
                'add_new' => __('New customer', 'wpshop'),
                'edit_item' => __('Edit customer', 'wpshop'),
                'new_item' => __('New customer', 'wpshop'),
                'view_item' => __('View customer', 'wpshop'),
                'search_items' => __('Search in customers', 'wpshop'),
                'not_found' => __('No customer found', 'wpshop'),
                'not_found_in_trash' => __('No customer founded in trash', 'wpshop'),
                'parent_item_colon' => '',
            ),
            'description' => '',
            'supports' => !empty($current_entity_params['support']) ? $current_entity_params['support'] : array('title'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true, //'edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'can_export' => false,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'rewrite' => false,
            'menu_icon' => 'dashicons-id-alt',
            'capabilities' => array(
                'create_posts' => 'wpshop_view_dashboard',
                'edit_post' => 'wpshop_view_dashboard',
                'edit_posts' => 'wpshop_view_dashboard',
                'edit_others_posts' => 'wpshop_view_dashboard',
                'publish_posts' => 'wpshop_view_dashboard',
                'read_post' => 'wpshop_view_dashboard',
                'read_private_posts' => 'wpshop_view_dashboard',
                'delete_posts' => 'delete_product',
            ),
        );
        register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $post_type_params);
    }

    /**
     * Link for redirect new customer to new user
     */
    public static function customer_action_on_menu()
    {
        global $submenu;
        //$submenu['edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS][10] = array( __( 'New customer', 'wpshop' ), 'create_users', admin_url( 'user-new.php?redirect_to=edit.php%3Fpost_type%3D' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) );
    }

    /**
     * Redirect when create new customer in admin
     */
    public function redirect_new_user()
    {
        global $pagenow;

        /* Check current admin page. */
        if ($pagenow != 'user-new.php' && isset($_SESSION['redirect_to_customer'])) {
            $redirect = $_SESSION['redirect_to_customer'];
            unset($_SESSION['redirect_to_customer']);
            if ($pagenow == 'users.php') {
                wp_redirect(admin_url($redirect, 'http'));
                exit;
            }
        }

        /* Redirect to new user */
        $post_type = !empty($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
        if ($pagenow == 'post-new.php' && isset($post_type) && $post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS) {
            $_SESSION['redirect_to_customer'] = 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
            wp_redirect(admin_url('user-new.php', 'http'), 301);
            exit;
        }

        /* Include JS on new user customer */
        if ($pagenow == 'user-new.php' && isset($_SESSION['redirect_to_customer'])) {
            add_action('admin_print_scripts', array($this, 'admin_user_customer_js'), 20);
        }
    }
    /* JS to select customer in place of suscriber */
    public function admin_user_customer_js()
    {
        echo "<script type='text/javascript'>\n";
        echo "jQuery(document).ready(function($) {";
        echo "\n$('#role').val('customer').change();";
        echo "\n});\n</script>";
    }

    /**
     * Create an entity of customer type when a new user is created
     *
     * @param integer $user_id
     */
    public static function create_entity_customer_when_user_is_created($user_id)
    {
        $user_data = get_userdata($user_id);
        $user_info = array_merge(get_object_vars($user_data->data), array_map('self::array_map_create_entity_customer_when_user_is_created', get_user_meta($user_id)));
        $customer_post_ID = wp_insert_post(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'post_author' => $user_id, 'post_title' => $user_data->user_nicename));
        self::save_customer_synchronize($customer_post_ID, $user_id, $user_info);

        /** Change metabox Hidden Nav Menu Definition to display WPShop categories' metabox **/
        $usermeta = get_post_meta($user_id, 'metaboxhidden_nav-menus', true);
        if (!empty($usermeta) && is_array($usermeta)) {
            $data_to_delete = array_search('add-wpshop_product_category', $usermeta);
            if ($data_to_delete !== false) {
                unset($usermeta[$data_to_delete]);
                update_user_meta($user_id, 'metaboxhidden_nav-menus', $usermeta);
            }
        }
    }
    private static function array_map_create_entity_customer_when_user_is_created($a)
    {
        return $a[0];
    }

    /**
     * Update an entity of customer type when a user profile is update
     *
     * @param integer $user_id
     */
    public static function update_entity_customer_when_profile_user_is_update($user_id)
    {
        $user_data = get_userdata($user_id);
        $user_info = array_merge(get_object_vars($user_data->data), array_map('self::array_map_create_entity_customer_when_user_is_created', get_user_meta($user_id)));
        $customer_post_ID = self::get_customer_id_by_author_id($user_id);
        self::save_customer_synchronize($customer_post_ID, $user_id, $user_info);
    }
    private static function array_map_update_entity_customer_when_profile_user_is_update($a)
    {
        return $a[0];
    }

    /**
     * Add metas in user when customer is modified
     *
     * @param integer $post_id
     * @param WP_Post $post
     */
    public static function save_entity_customer($customer_post_ID, $post)
    {
        if (WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS != $post->post_type || $post->post_status == 'auto-draft' || wp_is_post_revision($customer_post_ID)) {
            return;
        }
        $user_id = $post->post_author;
        $user_info = array();
        $attribute = !empty($_POST['attribute']) ? (array) $_POST['attribute'] : array();
        if (!empty($attribute)) {
            foreach ($attribute as $type => $attributes) {
                foreach ($attributes as $meta => $attribute) {
                    $user_info[$meta] = sanitize_text_field($attribute);
                }
            }
        }
        self::save_customer_synchronize($customer_post_ID, $user_id, $user_info);
        /** Update newsletter user preferences **/
        $newsletter_preferences = array();
        $newsletter_site = !empty($_POST['newsletters_site']) ? sanitize_text_field($_POST['newsletters_site']) : '';
        if (!empty($newsletter_site)) {
            $newsletter_preferences['newsletters_site'] = 1;
        }
        $newsletters_site_partners = !empty($_POST['newsletters_site_partners']) ? sanitize_text_field($_POST['newsletters_site_partners']) : '';
        if (!empty($newsletters_site_partners)) {
            $newsletter_preferences['newsletters_site_partners'] = 1;
        }
        update_user_meta($user_id, 'user_preferences', $newsletter_preferences);
        return;
    }

	public static function prevent_send_mail_from_wordpress() {
		return false;
	}
    public static function save_customer_synchronize($customer_post_ID, $user_id, $user_info)
    {
        global $wpdb;
        global $wpshop;
        $exclude_user_meta = array('user_login', 'user_nicename', 'user_email', 'user_pass', 'user_url', 'user_registered', 'user_activation_key', 'user_status', 'display_name');
        $wps_entities = new wpshop_entities();
        $element_id = $wps_entities->get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
        $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d', $element_id);
        $attribute_set_id = $wpdb->get_var($query);
        $attributes_default = array();
        if (!empty($attribute_set_id)) {
            $group = wps_address::get_addresss_form_fields_by_type($attribute_set_id);
            foreach ($group as $attribute_sets) {
                foreach ($attribute_sets as $attribute_set_field) {
                    foreach ($attribute_set_field['content'] as $attribute) {
                        if (isset($attribute['value'])) {
                            if (is_serialized($attribute['value'])) {
                                $unserialized_value = unserialize($attribute['value']);
                                if (isset($unserialized_value['default_value'])) {
                                    $attributes_default[$attribute['name']] = $unserialized_value['default_value'];
                                }
                            } else {
                                $attributes_default[$attribute['name']] = $attribute['value'];
                            }
                        }
                    }
                }
            }
        }
        $user_info = array_merge($attributes_default, $user_info);
		add_filter( 'send_password_change_email', array( get_class(), 'prevent_send_mail_from_wordpress' ) );
        foreach ($user_info as $user_meta => $user_meta_value) {
            $attribute_def = wpshop_attributes::getElement($user_meta, "'valid'", 'code');
            if (!empty($attribute_def)) {
                //Save data in user meta
                if (in_array($user_meta, $exclude_user_meta)) {
                    if ($user_meta == 'user_pass') {
                        $new_password = wpshop_tools::varSanitizer($user_meta_value);
                        if (wp_hash_password($new_password) == get_user_meta($user_id, $user_meta, true)) {
                            continue;
                        }
                    }
                    wp_update_user(array('ID' => $user_id, $user_meta => wpshop_tools::varSanitizer($user_meta_value)));
                } else {
                    update_user_meta($user_id, $user_meta, wpshop_tools::varSanitizer($user_meta_value));
                }
                //Save data in attribute tables, ckeck first if exist to know if Insert or Update
                $query = $wpdb->prepare('SELECT * FROM ' . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($attribute_def->data_type) . ' WHERE entity_type_id = %d AND entity_id = %d AND attribute_id = %d', $element_id, $customer_post_ID, $attribute_def->id);
                $checking_attribute_exist = $wpdb->get_results($query);
                if (!empty($checking_attribute_exist)) {
                    $wpdb->update(
                        WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($attribute_def->data_type),
                        array(
                            'value' => wpshop_tools::varSanitizer($user_meta_value)),
                        array(
                            'entity_type_id' => $element_id,
                            'entity_id' => $customer_post_ID,
                            'attribute_id' => $attribute_def->id,
                        )
                    );
                } else {
                    $wpdb->insert(
                        WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($attribute_def->data_type),
                        array(
                            'entity_type_id' => $element_id,
                            'attribute_id' => $attribute_def->id,
                            'entity_id' => $customer_post_ID,
                            'user_id' => $user_id,
                            'creation_date_value' => current_time('mysql', 0),
                            'language' => 'fr_FR',
                            'value' => wpshop_tools::varSanitizer($user_meta_value),
                        )
                    );
                }
            }
        }
		remove_filter( 'send_password_change_email', array( get_class(), 'prevent_send_mail_from_wordpress' ) );
    }

    /**
     * Notice for errors on admin
     */
    public function notice_save_post_customer_informations()
    {
        $errors = isset($_SESSION['save_post_customer_informations_errors']) ? $_SESSION['save_post_customer_informations_errors'] : '';
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $class = "error";
                $message = $error;
                echo "<div class=\"$class\"> <p>$message</p></div>";
            }
            unset($_SESSION['save_post_customer_informations_errors']);
        }
    }

    /**
     * Change the customer list table header to display custom informations
     *
     * @param array $current_header The current header list displayed to filter and modify for new output
     *
     * @return array The new header to display
     */
    public function list_table_header($current_header)
    {
        unset($current_header['title']);
        unset($current_header['date']);

        $current_header['customer_identifier'] = __('Customer ID', 'wpshop');
        $current_header['customer_name'] = '<span class="wps-customer-last_name" >' . __('Last-name', 'wpshop') . '</span><span class="wps-customer-first_name" >' . __('First-name', 'wpshop') . '</span>';
        $current_header['customer_email'] = __('E-mail', 'wpshop');
        $current_header['customer_orders'] = __('Customer\'s orders', 'wpshop');
        $current_header['customer_date_subscription'] = __('Subscription', 'wpshop');
        $current_header['customer_date_lastlogin'] = __('Last login date', 'wpshop');

        return $current_header;
    }

    /**
     * Display the content into list table column
     *
     * @param string $column THe column identifier to modify output for
     * @param integer $post_id The current post identifier
     */
    public function list_table_column_content($column, $post_id)
    {
        global $wpdb;
        /**    Get wp_users idenfifier from customer id    */
        $current_user_id_in_list = self::get_author_id_by_customer_id($post_id);

        /**    Get current post informations    */
        $customer_post = get_post($post_id);

        /**    Get user data    */
        $current_user_datas = get_userdata($current_user_id_in_list);

        /**    Switch current column for custom case    */
        $use_template = true;
        switch ($column) {
            case 'customer_identifier':
                echo $post_id;
                $use_template = false;
                break;
            case 'customer_date_subscription':
                echo mysql2date(get_option('date_format'), $current_user_datas->user_registered, true);
                $use_template = false;
                break;
            case 'customer_date_lastlogin':
                $last_login = get_user_meta($current_user_id_in_list, 'last_login_time', true);
                if (!empty($last_login)):
                    echo mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $last_login, true);
                else:
                    _e('Never logged in', 'wpshop');
                endif;
                $use_template = false;
                break;
        }

        /**    Require the template for displaying the current column    */
        if ($use_template) {
            $template = wpshop_tools::get_template_part(WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/', 'backend', 'customer_listtable/' . $column);
            if (is_file($template)) {
                require $template;
            }
        }
    }

    /**
     * Filter bulk actions into customer list table
     *
     * @param array $actions Current available actions list
     *
     * @return array The new action list to use into customer list table
     */
    public function customer_list_table_bulk_actions($actions)
    {
        unset($actions['edit']);
        unset($actions['trash']);

        return $actions;
    }

    public function list_table_filters()
    {
        $post_type = !empty($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
        if (isset($post_type)) {
            if (post_type_exists($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS)) {
                $filter_possibilities = array();
                $filter_possibilities[''] = __('-- Select Filter --', 'wpshop');
                $filter_possibilities['orders'] = __('List customers with orders', 'wpshop');
                $filter_possibilities['no_orders'] = __('List customers without orders', 'wpshop');
                echo wpshop_form::form_input_select('entity_filter', 'entity_filter', $filter_possibilities, (!empty($_GET['entity_filter']) ? sanitize_text_field($_GET['entity_filter']) : ''), '', 'index');
            }
        }
    }

    public function list_table_filter_parse_query($query)
    {
        global $pagenow, $wpdb;
        $post_type = !empty($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
        $entity_filter = !empty($_GET['entity_filter']) ? sanitize_text_field($_GET['entity_filter']) : '';
        if (is_admin() && ($pagenow == 'edit.php') && !empty($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS) && !empty($entity_filter)) {
            $check = null;
            switch ($entity_filter) {
                case 'orders':
                    $sql_query = $wpdb->prepare(
                        "SELECT ID
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s
						AND post_author IN (
						SELECT post_author
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s)",
                        WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
                        'auto-draft',
                        WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
                        'auto-draft');
                    $check = 'post__in';
                    break;
                case 'no_orders':
                    $sql_query = $wpdb->prepare(
                        "SELECT ID
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s
						AND post_author NOT IN (
						SELECT post_author
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s)",
                        WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
                        'auto-draft',
                        WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
                        'auto-draft');
                    $check = 'post__in';
                    break;
            }

            if (!empty($check)) {
                $results = $wpdb->get_results($sql_query);
                $user_id_list = array();
                foreach ($results as $item) {
                    $user_id_list[] = $item->ID;
                }
                if (empty($post_id_list)) {
                    $post_id_list[] = 'no_result';
                }
                $query->query_vars[$check] = $user_id_list;
            }
            $query->query_vars['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
            $query->query_vars['post_status'] = 'any';
        }
    }

    /**
     * Récupère la liste de tous les clients / Get the list of all customers
     *
     * @return array
     */
    public static function get_all_customer($post_status = 'publish')
    {
        global $wpdb;

        $query = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $post_status);
        $list_customer = $wpdb->get_results($query);

        return $list_customer;
    }

    /**
     * Récupère l'id du client selon son author id / Get customer id by the author id
     *
     * @param int $author_id
     * @return int $customer_id
     */
    public static function get_customer_id_by_author_id($author_id)
    {
		if( !isset( self::$customer_user_identifier_cache[$author_id] ) ) {
	        global $wpdb;
	        $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_author = %d AND post_type = %s", $author_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
	        self::$customer_user_identifier_cache[$author_id] = $wpdb->get_var($query);
		}

        return self::$customer_user_identifier_cache[$author_id];
    }

    /**
     * Récupère l'id de l'utilisateur selon l'id du client / Get author id by customer id
     *
     * @method get_author_id_by_customer_id
     * @param  int $customer_id ID in wpshop_customers type.
     * @return int
     */
    public static function get_author_id_by_customer_id($customer_id)
    {
		$flipped = array_flip(self::$customer_user_identifier_cache);
		if( !isset( $flipped[$customer_id] ) ) {
	        global $wpdb;
			$author_id = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", $customer_id));
	        self::$customer_user_identifier_cache[$author_id] = $customer_id;
			$flipped[$customer_id] = $author_id;
		}

        return $flipped[$customer_id];
    }

    /**
     * Essaie de créer une corrélation entre le formulaire que l'on passe et customer ( ex: billing_address => customer )
     *
     * @param array $form Passer directement le $_POST pour créer un client.
     * @return array ou int : 1 = Aucun mail, 2 = L'utilisateur existe déjà
     */
    public static function quick_add_customer($form)
    {
        $return = 1;
        if (!empty($form)) {
            global $wpdb;
            $customer_type_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
            $query = $wpdb->prepare('SELECT id FROM ' . WPSHOP_DBT_ATTRIBUTE_SET . ' WHERE entity_id = %d', $customer_type_id);
            $customer_entity_id = $wpdb->get_var($query);
            $attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
            $account_attributes = wpshop_attributes_set::getAttributeSetDetails((!empty($attributes_set->id)) ? $attributes_set->id : '', "'valid'");

            $customer_attributes_to_save = $customer_attributes_to_save_temp = array();

            foreach ($form['attribute'] as $attribute_type => $attributes) {
                foreach ($attributes as $attribute_code => $attribute_value) {
                    $customer_attributes_compare[$attribute_code] = $attribute_type;
                }
            }

            foreach ($account_attributes as $account_attribute_group) {
                foreach ($account_attribute_group['attribut'] as $attribute) {
                    foreach (preg_grep('/' . $attribute->code . '/', array_keys($customer_attributes_compare)) as $codeForm) {
                        if ($customer_attributes_compare[$codeForm] == $attribute->data_type) {
                            $user[$attribute->code] = array('attribute_id' => $attribute->id, 'data_type' => $attribute->data_type, 'value' => sanitize_text_field($_POST['attribute'][$attribute->data_type][$codeForm]));
                            if ($attribute->code == 'user_email') {
                                $email_founded = true;
                            }
                        }
                    }
                }
            }

            if ($email_founded && is_email($user['user_email']['value'])) {
                $user_id = username_exists($user['user_email']['value']);
                if (empty($user_id)) {
                    $user_name = isset($user['user_login']['value']) ? $user['user_login']['value'] : $user['user_email']['value'];
                    $user_pass = isset($user['user_pass']['value']) ? $user['user_pass']['value'] : wp_generate_password(12, false);
                    $user_id = wp_create_user($user_name, $user_pass, $user['user_email']['value']);

                    if (!is_wp_error($user_id)) {
                        $customer_entity_request = $wpdb->prepare('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $user_id);
                        $customer_entity_id = $wpdb->get_var($customer_entity_request);
                        $return = array();
                        $return['integer']['ID'] = $user_id;

                        foreach ($user as $meta_key => $meta_values) {
                            update_user_meta($user_id, $meta_key, $meta_values['value']);
                            $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . strtolower($meta_values['data_type']), array('entity_type_id' => $customer_type_id, 'attribute_id' => $meta_values['attribute_id'], 'entity_id' => $customer_entity_id, 'user_id' => $user_id, 'creation_date_value' => current_time('mysql', 0), 'language' => 'fr_FR', 'value' => $meta_values['value']));
                            $return[$meta_values['data_type']][$meta_key] = $meta_values['value'];
                        }
                    }
                } else {
                    $return = 2;
                }
            }
        }

        return $return;
    }

    /**
     * Search function for customer / Fonction de recherche des clients
     *
     * @param string $term The term we have to search into database for getting existing users / Le terme qu'il faut rechercher pour retrouver les utilisateurs dans la base de données
     */
    public function search_customer($term)
    {
        global $wpdb;

        /**    Build the customer search    */
        $args = array(
            'search' => "*" . $term . "*",
            'search_columns' => array('user_login', 'user_email', 'user_nicename'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => $term,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'last_name',
                    'value' => $term,
                    'compare' => 'LIKE',
                ),
            ),
        );
        $customer_search = new WP_User_Query($args);

        $customer_list = array();
        if (!empty($customer_search->results)) {
            foreach ($customer_search->results as $customer) {
                /** Check the username, if last name and first name are empty we select email **/
                $last_name_meta = get_user_meta($customer->ID, 'last_name', true);
                $first_name_meta = get_user_meta($customer->ID, 'first_name', true);
                $user_data = $customer->data;
                $email_data = $user_data->user_email;

                if (!empty($last_name_meta)) {
                    $user_name = $last_name_meta;
                } elseif (!empty($first_name_meta)) {
                    $user_name = $first_name_meta;
                } else {
                    $user_name = $email_data;
                }

                $customer_list[] = array(
                    'ID' => $customer->ID,
                    'last_name' => $last_name_meta,
                    'first_name' => $first_name_meta,
                    'email' => $email_data,
                );
            }
        }

        return $customer_list;
    }

}
