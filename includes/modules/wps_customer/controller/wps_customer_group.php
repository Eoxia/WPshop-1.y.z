<?php if ( !defined( 'ABSPATH' ) ) exit;
class wps_customer_group {
	function __construct() {
		add_action('admin_menu', array( $this, 'register_customer_groups_submenu' ) );
	}

	function register_customer_groups_submenu() {
// 		if( in_array ( long2ip ( ip2long ( $_SERVER["REMOTE_ADDR"] ) ), unserialize( WPSHOP_DEBUG_MODE_ALLOWED_IP ) ) ) {
// 			add_submenu_page( WPSHOP_URL_SLUG_DASHBOARD, __('Groups', 'wpshop'), __('Groups', 'wpshop'), 'manage_options', WPSHOP_NEWTYPE_IDENTIFIER_GROUP, array( 'wps_customer_group,'display_page') );
// 		}
	}

	/**
	 * Gérer les actions $_POST
	 */
	function manage_post() {
		$addrole = !empty( $_POST['addrole'] ) ? sanitize_text_field( $_POST['addrole'] ) : '';
		$editrole = !empty( $_POST['editrole'] ) ? sanitize_text_field( $_POST['editrole'] ) : '';
		$group_name = !empty( $_POST['group-name'] ) ? sanitize_text_field( $_POST['group-name'] ) : '';
		$group_parent = !empty( $_POST['group-parent'] ) ? sanitize_text_field( $_POST['group-parent'] ) : '';
		$group_description = !empty( $_POST['group-description'] ) ? sanitize_text_field( $_POST['group-description'] ) : '';
		$group_users = !empty( $_POST['group-users'] ) ? (array) $_POST['group-users'] : array();
		$code = !empty( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';

		if ((!empty($addrole) || !empty($editrole)) && !empty($group_name)) {

			// ROLES
			$roles = get_option('wp_user_roles', array());

			// AJOUT
			if (!empty($addrole)) {

				$code = 'wpshop_'.str_replace('-', '_', sanitize_title($group_name));

				// Si le role n'existe pas
				if (!isset($roles[$code])) {

					// On ajoute le role
					$rights = $this->getRoleRights($group_parent);
					add_role($code, sanitize_text_field($group_name), $rights);

					// On enregistre les metas du groupe
					$this->setGroupMetas($code, sanitize_text_field( $group_description ), sanitize_text_field( $group_parent ) );

					// On affecte des utilisateurs au role
					$this->affectUsersToGroup($code, $group_users);

					// Redirect
					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP.'&action=edit&code='.$code));
				}
				else echo __('This group already exist','wpshop');
			}
			// EDITION
			elseif (!empty($editrole) && !empty($code)) {

				// Si le role existe
				if (isset($roles[$code])) {

					$current_role = $this->getRole($code);

					$this->setNewRoleRights($code, $current_role['parent'], sanitize_text_field( $group_parent ) );

					// On enregistre les metas du groupe
					$this->setGroupMetas($code, sanitize_text_field( $group_description ) , sanitize_text_field( $group_parent ) );

					// On affecte des utilisateurs au role
					$this->unaffectUsersToGroup($code); // !important
					$this->affectUsersToGroup($code, $group_users);
				}
			}
			else wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP));
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
			$wps_customer_mdl = new wps_customer_mdl();
			$users = $wps_customer_mdl->getUserList();
			if( !empty($users) ) {
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
			$rights = $this->getRoleRights($role);
			if (!empty($rights)) {
				foreach($rights as $c => $b) {
					$wp_roles->remove_cap($code, $c);
				}
			}

			// On ajoute les nouveaux droits
			$rights = $this->getRoleRights($newrole);
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
	public static function display_page()
	{
		self::manage_post();

		ob_start();
		wpshop_display::displayPageHeader(__('Groups', 'wpshop'), '', __('Groups', 'wpshop'), __('Groups', 'wpshop'), true, 'admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP.'&action=add', '');
		$content = ob_get_contents();
		ob_end_clean();
		$wps_customer_mdl = new wps_customer_mdl();
		$action = !empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$code = !empty( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';
		// Si on re�oit une action
		if ( !empty( $action ) ) {

			$readonly_name_field = '';

			switch ( $action ) {

				case 'delete':

					if ( !empty( $code ) ) {

						$roles = get_option('wp_user_roles', array());

						if (isset($roles[$code]) && $code != 'customer' && $code != 'wpshop_customer') {
							unset($roles[$code]);
							$this->unaffectUsersToGroup($code);
							update_option('wp_user_roles', $roles);
						}
					}

					wpshop_tools::wpshop_safe_redirect(admin_url('admin.php?page='.WPSHOP_NEWTYPE_IDENTIFIER_GROUP));

					break;

				case 'edit':

					$readonly_name_field = 'readonly';

					if (!empty($code)) {

						$role = $this->getRole($code);

						if (!empty($role)) {

							$group_name = $role['name'];
							$group_description = $role['description'];
							$group_parent = $role['parent'];
							$submit_button_value = __('Edit the group','wpshop');
							$submit_button_name = 'editrole';

							// ROLES
							$roles = get_option('wp_user_roles', array());
							$select_parent = '<option value="">--</option>';

							foreach($roles as $code => $role) {
								if ($code != $code) {
									$selected = $group_parent==$code ? 'selected' : '';
									$select_parent .= '<option value="'.$code.'" '.$selected.'>'.$role['name'].'</option>';
								}
							}

							// USERS
							$users = $wps_customer_mdl->getUserList();
							if( !empty($users) ) {
								$select_users = '';
								foreach($users as $user) {
									if ($user->ID != 1) {
										$u = new WP_User($user->ID);
										$selected = isset($u->roles[0]) && $u->roles[0]==$code ? 'selected' : '';
										$select_users .= '<option value="'.$user->ID.'" '.$selected.'>'.$user->user_login.'</option>';
									}
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
					$users = $wps_customer_mdl->getUserList();
					$select_users = '';
					if( !empty($users) ) {
						foreach($users as $user) {
							if ($user->ID != 1) {
								$select_users .= '<option value="'.$user->ID.'">'.$user->user_login.'</option>';
							}
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
			$attribute_status = !empty( $_REQUEST['attribute_status'] ) ? sanitize_text_field( $_REQUEST['attribute_status'] ) : '';
			$orderby = !empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : '';
			$order = !empty( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : '';
			if(!empty($attribute_status)){
				switch($attribute_status){
					case 'unactive':
						$status="'moderated', 'notused'";
						if(empty($orderby) && empty($order)){
							$orderby ='status';
							$order ='asc';
						}
						break;
					default:
						$status="'".$attribute_status."'";
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
