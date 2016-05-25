<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Plugin permission manager
*
* Define the different method to manage the different permission into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different permission into the plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_permissions {

	/**
	*	Define the different permission for the plugin. Define an array containing the permission defined with a sub-array
	*
	*	@return array $permission An array with the permission list for the plugin
	*/
	function permission_list() {
		$permission = array();

		$permission['wpshop_view_dashboard'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'dashboard', 'permission_sub_module' => '');

		$permission['wpshop_view_product'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'product', 'permission_sub_module' => '');
		$permission['wpshop_add_product'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'product', 'permission_sub_module' => '');
		$permission['wpshop_edit_product'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'product', 'permission_sub_module' => '');

		$permission['wpshop_view_orders'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'product_orders', 'permission_sub_module' => '');

		$permission['wpshop_view_coupons'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'coupons', 'permission_sub_module' => '');
		$permission['wpshop_view_groups'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'groups', 'permission_sub_module' => '');
		$permission['wpshop_view_addons'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'addons', 'permission_sub_module' => '');

		$permission['wpshop_manage_product_categories'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'product_categories', 'permission_sub_module' => '');

		$permission['wpshop_view_options'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'options', 'permission_sub_module' => '');
		$permission['wpshop_edit_options'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'options', 'permission_sub_module' => '');
		$permission['wpshop_view_advanced_options'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'options', 'permission_sub_module' => 'advanced');
		$permission['wpshop_edit_advanced_options'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'options', 'permission_sub_module' => 'advanced');

		$permission['wpshop_view_attributes_unit'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit');
		$permission['wpshop_edit_attributes_unit'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit');
		$permission['wpshop_add_attributes_unit'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit');
		$permission['wpshop_delete_attributes_unit'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit');

		$permission['wpshop_view_attributes_unit_group'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit_group');
		$permission['wpshop_edit_attributes_unit_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit_group');
		$permission['wpshop_add_attributes_unit_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit_group');
		$permission['wpshop_delete_attributes_unit_group'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_unit_group');

		$permission['wpshop_view_attributes'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes');
		$permission['wpshop_edit_attributes'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes');
		$permission['wpshop_add_attributes'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes');
		$permission['wpshop_delete_attributes'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes');

		$permission['wpshop_add_attributes_select_values'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_select_values');
		$permission['wpshop_edit_attributes_select_values'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_select_values');
		$permission['wpshop_delete_attributes_select_values'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_select_values');

		// Shortcodes permissions
		$permission['wpshop_view_shortcodes'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'shortcodes', 'permission_sub_module' => 'shortcodes');

		// Messages permissions
		$permission['wpshop_view_messages'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'messages', 'permission_sub_module' => 'messages');

		$permission['wpshop_view_attribute_set'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_set');
		$permission['wpshop_view_attribute_set_details'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => 'details', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_set');
		$permission['wpshop_edit_attribute_set'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_set');
		$permission['wpshop_add_attribute_set'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_set');
		$permission['wpshop_delete_attribute_set'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_set');

		$permission['wpshop_view_attribute_group'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_group');
		$permission['wpshop_edit_attribute_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_group');
		$permission['wpshop_add_attribute_group'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'add', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_group');
		$permission['wpshop_delete_attribute_group'] = array('set_by_default' => 'no', 'permission_type' => 'delete', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_group');
		$permission['wpshop_view_attribute_group_details'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_group');
		$permission['wpshop_edit_attribute_group_details'] = array('set_by_default' => 'no', 'permission_type' => 'write', 'permission_sub_type' => 'edit', 'permission_module' => 'eav', 'permission_sub_module' => 'attributes_group');

		$permission['wpshop_view_documentation_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'documentation', 'permission_sub_module' => '');

		$permission['wpshop_view_import_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'import', 'permission_sub_module' => '');

		$permission['wpshop_view_tools_menu'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'tools', 'permission_sub_module' => '');
		$permission['wpshop_view_statistics'] = array('set_by_default' => 'no', 'permission_type' => 'read', 'permission_sub_type' => '', 'permission_module' => 'stats', 'permission_sub_module' => '');

		return $permission;
	}

	/**
	*	Set the different permission for the administrator role. Add all existing permission for this role.
	*	@see wpshop_permissions::permission_list()
	*/
	function set_administrator_role_permission() {
		$adminRole 	= get_role('administrator');
		$permissionList = wpshop_permissions::permission_list();
		foreach ($permissionList as $permissionName => $permissionDef) {
			if ( ($adminRole != null) && !$adminRole->has_cap($permissionName) ) {
				$adminRole->add_cap($permissionName);
			}
		}
		unset($adminRole);
	}

	function wpshop_init_roles() {
		global $wp_roles;

		if (class_exists('WP_Roles')) if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		if (is_object($wp_roles)) :

			// Customer role
			add_role('customer', __('Customer', 'wpshop'), array(
				'read' => true,
				'edit_posts' => false,
				'delete_posts' => false
			));

			// Main Shop capabilities
			$wp_roles->add_cap('administrator', 'manage_wpshop');

		endif;
	}

}