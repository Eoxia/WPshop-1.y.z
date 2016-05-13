<?php if ( !defined( 'ABSPATH' ) ) exit;

/**	Option main page	*/
ob_start();
?>
<div id="icon-options-general" class="icon32"></div>
<h2><?php _e('WP-Shop options', 'wpshop'); ?></h2>
<div id="options-tabs" class="wpshop_tabs wpshop_full_page_tabs wpshop_options_tabs" >
	<ul>{WPSHOP_ADMIN_OPTIONS_TAB_LIST}</ul>
	<form action="options.php" method="post" id="wpshop_option_form" >
		{WPSHOP_ADMIN_OPTIONS_FIELDS_FOR_NONCE}
		{WPSHOP_ADMIN_OPTIONS_TAB_CONTENT_LIST}

		<?php if(current_user_can('wpshop_edit_options')): ?>
			<p class="submit">
				<input class="button-primary" name="Submit" type="submit" value="<?php _e('Save Changes','wpshop'); ?>" />
			</p>
		<?php endif; ?>
	</form>
</div>
<span class="infobulle"></span><?php
$tpl_element['wpshop_admin_options_main_page'] = ob_get_contents();
ob_end_clean();


/**	Option group tab	*/
ob_start();
?><li class="wpshop_options_tab {WPSHOP_ADMIN_OPTIONS_TAB_KEY}" ><a href="#{WPSHOP_ADMIN_OPTIONS_TAB_KEY}">{WPSHOP_ADMIN_OPTIONS_TAB_LABEL}</a></li><?php
$tpl_element['wpshop_admin_options_group_tab'] = ob_get_contents();
ob_end_clean();


/**	Option group main container	*/
ob_start();
?><div id="{WPSHOP_ADMIN_OPTIONS_TAB_KEY}">{WPSHOP_ADMIN_OPTIONS_GROUP_CONTENT}</div><?php
$tpl_element['wpshop_admin_options_group_container'] = ob_get_contents();
ob_end_clean();


/**	Option subgroup container	*/
ob_start();
?><div class="wpshop_admin_box wpshop_admin_box_options{WPSHOP_ADMIN_OPTIONS_SUBGROUP_CLASS}">{WPSHOP_ADMIN_OPTIONS_SUBGROUP_CONTENT}</div><?php
$tpl_element['wpshop_admin_options_subgroup_container'] = ob_get_contents();
ob_end_clean();