<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Products management method file
* 
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_groups
{
	/**
	 * G�rer les actions $_POST
	 */
	function manage_post() 
	{
		if (!empty($_POST)) {
		
			if ((!empty($_POST['addrole']) || !empty($_POST['editrole'])) && !empty($_POST['group-name'])) {
			
				// ROLES
				$roles = get_option('wp_user_roles', array());
					
				// AJOUT
				if (!empty($_POST['addrole'])) {
				
					$code = 'wpshop_'.str_replace('-', '_', sanitize_title($_POST['group-name']));
					
					// Si le role n'existe pas
					if (!isset($roles[$code])) {
						
						// On ajoute le role
						$rights = self::getRoleRights($_POST['group-parent']);
						add_role($code, $_POST['group-name'], $rights);
						
						// On enregistre les metas du groupe
						self::setGroupMetas($code, $_POST['group-description'], $_POST['group-parent']);
						
						// On affecte des utilisateurs au role
						self::affectUsersToGroup($code, $_POST['group-users']);
						
						// Redirect
						wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP.'&action=edit&code='.$code));
					}
					else echo __('This group already exist','wpshop');
				}
				// EDITION
				elseif (!empty($_POST['editrole']) && !empty($_GET['code'])) {
				
					$code = $_GET['code'];
					
					// Si le role existe
					if (isset($roles[$code])) {
					
						$current_role = self::getRole($code);
					
						self::setNewRoleRights($code, $current_role['parent'], $_POST['group-parent']);
						
						// On enregistre les metas du groupe
						self::setGroupMetas($code, $_POST['group-description'], $_POST['group-parent']);
						
						// On affecte des utilisateurs au role
						self::unaffectUsersToGroup($code); // !important
						self::affectUsersToGroup($code, $_POST['group-users']);
					}
				}
				else wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP));
			}
		}
	}
	
	/**
	 * Affecte des utilisateurs � un role
	 * @param $code identifiant du role
	 * @param $users liste d'utilisateurs a affecter
	 */
	function affectUsersToGroup($code, $users) 
	{
		// ROLES
		$roles = get_option('wp_user_roles', array());
		
		// Si le role existe
		if (isset($roles[$code])) {
		
			// On affecte des utilisateurs au role
			if (!empty($users)) {
				foreach ($users as $u) {
					$u = new WP_User($u);
					if (isset($u->roles[0])) { $u->remove_role($u->roles[0]); }
					$u->add_role($code);
				}
			}
			
		}
	}
	
	/**
	 * D�saffecte des utilisateurs � un role
	 * @param $code identifiant du role
	 */
	function unaffectUsersToGroup($code) 
	{
		// ROLES
		$roles = get_option('wp_user_roles', array());
		
		// Si le role existe
		if (isset($roles[$code])) {
		
			$users = wpshop_customer::getUserList();
			foreach($users as $user) {
				$u = new WP_User($user->ID);
				// Si l'utilisateur poss�de le role, on le retire de sa liste de droits
				if (isset($u->roles[0]) && $u->roles[0]==$code) { 
					$u->remove_role($u->roles[0]);
					$u->add_role('subscriber');
				}
			}
			
		}
	}
	
	/**
	 * Enregistre les metas pour un role donn�
	 * @param $code identifiant du role
	 * @param $desc description du role
	 * @param $parent parent du role
	 */
	function setGroupMetas($code, $desc, $parent) 
	{
		$wpshop_groups_meta = get_option('wpshop_groups_meta', array());
						
		// On enregistre la description du role
		$wpshop_groups_meta[$code] = array(
			'description' => $desc,
			'parent' => $parent
		);
		
		update_option('wpshop_groups_meta', $wpshop_groups_meta);
	}
	
	/**
	 * Retourne les droits pour un role donn�
	 * @param $code identifiant du role
	 */
	function getRoleRights($code)
	{
		$rights = array();
		
		if (!empty($code)) {
			$role_object = get_role($code);
			if (!empty($role_object->capabilities)) {
				foreach ($role_object->capabilities as $rcode => $bool) {
					$rights[$rcode] = $bool;
				}
			}
		}
		
		return $rights;
	}
	
	/**
	 * Enregistre les droits pour un role donn�
	 * @param $code identifiant du role
	 * @param $role identifiant du role actuel sur lequel le role est bas�
	 * @param $newrole identifiant du role sur lequel le role doit etre bas�
	 */
	function setNewRoleRights($code, $role, $newrole)
	{
		global $wp_roles;
		
		if ($role != $newrole) {
		
			// On retire les anciens droits
			$rights = self::getRoleRights($role);
			if (!empty($rights)) {
				foreach($rights as $c => $b) {
					$wp_roles->remove_cap($code, $c);
				}
			}
			
			// On ajoute les nouveaux droits
			$rights = self::getRoleRights($newrole);
			if (!empty($rights)) {
				foreach($rights as $c => $b) {
					$wp_roles->add_cap($code, $c);
				}
			}
			
		}
	}
	
	/**
	 * Retourne les infos sur le role donn�
	 * @param $code identifiant du role
	 */
	function getRole($code)
	{
		// ROLES
		$roles = get_option('wp_user_roles', array());
		$role = array();
				
		// Si le role existe pas
		if (isset($roles[$code])) {
		
			$wpshop_groups_meta = get_option('wpshop_groups_meta', array());
			
			$role['name'] = $roles[$code]['name'];
			$role['description'] = $wpshop_groups_meta[$code]['description'];
			$role['parent'] = $wpshop_groups_meta[$code]['parent'];
			
			return $role;
		}
		
		return array();
	}
	
	/**
	 * Affiche la page des groupes
	 */
	function display_page() 
	{
		self::manage_post();
		
		ob_start();
		wpshop_display::displayPageHeader(__('Groups', 'wpshop'), '', __('Groups', 'wpshop'), __('Groups', 'wpshop'), true, 'admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP.'&action=add', '');
		$content = ob_get_contents();
		ob_end_clean();
		
		// Si on re�oit une action
		if (!empty($_GET['action'])) {
		
			$readonly_name_field = '';
			
			switch ($_GET['action']) {
			
				case 'delete':
				
					if (!empty($_GET['code'])) {
					
						$roles = get_option('wp_user_roles', array());
						
						if (isset($roles[$_GET['code']]) && $_GET['code'] != 'customer' && $_GET['code'] != 'wpshop_customer') {
							unset($roles[$_GET['code']]);
							self::unaffectUsersToGroup($_GET['code']);
							update_option('wp_user_roles', $roles);
						}
					}
					
					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP));
				
					break;
					
				case 'edit':
				
					$readonly_name_field = 'readonly';
				
					if (!empty($_GET['code'])) {
					
						$role = self::getRole($_GET['code']);
						
						if (!empty($role)) {
					
							$group_name = $role['name'];
							$group_description = $role['description'];
							$group_parent = $role['parent'];
							$submit_button_value = __('Edit the group','wpshop');
							$submit_button_name = 'editrole';
							
							// ROLES
							$roles = get_option('wp_user_roles', array());
							$select_parent = '<option value="">--</option>';;
							foreach($roles as $code => $role) {
								if ($code != $_GET['code']) {
									$selected = $group_parent==$code ? 'selected' : '';
									$select_parent .= '<option value="'.$code.'" '.$selected.'>'.$role['name'].'</option>';
								}
							}
							
							// USERS
							$users = wpshop_customer::getUserList();
							$select_users = '';
							foreach($users as $user) {
								if ($user->ID != 1) {
									$u = new WP_User($user->ID);
									$selected = isset($u->roles[0]) && $u->roles[0]==$_GET['code'] ? 'selected' : '';
									$select_users .= '<option value="'.$user->ID.'" '.$selected.'>'.$user->user_login.'</option>';
								}
							}
							
						}
						else {wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP));exit;}
					}
					else {wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP));exit;}
					
				break;
				
				case 'add':
					
					
					$group_name = $group_description = '';
					$submit_button_value = __('Create the group','wpshop');
					$submit_button_name = 'addrole';
					
					// ROLES
					$roles = get_option('wp_user_roles', array());
					$select_parent = '<option value="">--</option>';;
					foreach($roles as $code => $role) {
						$select_parent .= '<option value="'.$code.'">'.$role['name'].'</option>';
					}
					
					// USERS
					$users = wpshop_customer::getUserList();
					$select_users = '';
					foreach($users as $user) {
						if ($user->ID != 1) {
							$select_users .= '<option value="'.$user->ID.'">'.$user->user_login.'</option>';
						}
					}
					
				break;
					
			}
			
			$content .= '
				<form method="post">
					<label>'.__('Name','wpshop').'</label><br /><input type="text" name="group-name" style="width:500px;" value="'.$group_name.'" '.$readonly_name_field.' /><br /><br />
					
					<label>'.__('Parent','wpshop').'</label><br /> 
					<select name="group-parent" class="chosen_select" style="width:500px;">
						'.$select_parent.'
					</select><br /><br />
					
					<label>'.__('Users','wpshop').'</label><br /> 
					<select name="group-users[]" class="chosen_select" multiple style="width:500px;">
						'.$select_users.'
					</select><br /><br />
					
					<label>'.__('Description','wpshop').'</label><br /><textarea name="group-description" style="width:500px;">'.$group_description.'</textarea><br /><br />
					
					<input type="submit" class="button-primary" name="'.$submit_button_name.'" value="'.$submit_button_value.'" /> &nbsp;&nbsp;&nbsp; <a href="admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP.'">'.__('Cancel','wpshop').'</a>
				</form>
			';
			
		}
		else {
		
		
			$wpshop_list_table = new wpshop_groups_custom_List_table();
			//Fetch, prepare, sort, and filter our data...
			$status="'valid'";
			if(!empty($_REQUEST['attribute_status'])){
				switch($_REQUEST['attribute_status']){
					case 'unactive':
						$status="'moderated', 'notused'";
						if(empty($_REQUEST['orderby']) && empty($_REQUEST['order'])){
							$_REQUEST['orderby']='status';
							$_REQUEST['order']='asc';
						}
						break;
					default:
						$status="'".$_REQUEST['attribute_status']."'";
						break;
				}
			}
			
			$roles = get_option('wp_user_roles', array());
			
			$i=0;
			$attribute_set_list=array();
			$group_not_to_display = array('administrator','editor','author','contributor','subscriber');
			$wpshop_groups_meta = get_option('wpshop_groups_meta', array());
			foreach($roles as $code => $role) {
				if (!in_array($code, $group_not_to_display)) {
					$description = !empty($wpshop_groups_meta[$code]['description']) ? $wpshop_groups_meta[$code]['description'] : '--';
					$attribute_set_list[$i]['name'] = $role['name'];
					$attribute_set_list[$i]['description'] = $description;
					$attribute_set_list[$i]['code'] = $code;
					$i++;
				}
			}
			$wpshop_list_table->prepare_items($attribute_set_list);
			
			ob_start();
			$wpshop_list_table->display();
			$element_output = ob_get_contents();
			ob_end_clean();
		
			$content .= $element_output;
		}
		
		$content .= '</div>';
		
		echo $content;
	}

}