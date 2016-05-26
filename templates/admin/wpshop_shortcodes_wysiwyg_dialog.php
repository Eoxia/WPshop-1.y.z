<?php if ( !defined( 'ABSPATH' ) ) exit;

$content = $content_explanation = '';
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : null;
$post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : null;

$display_option_display_type = true;
$display_option_grouped = true;
$display_search_form = true;
if ( !empty($type) ) {
	switch ($type) {
		case 'attribute_value':
				$display_option_grouped = false;
				$display_search_form = false;
				$display_option_grouped = false;
				$wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier = 'wpshop_att_val attid';
				$content = wpshop_attributes::get_attribute_list(null, $type, $post_type);
			break;

		case 'categories':
				$content = wpshop_categories::product_list_cats(true);
				$wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier = 'wpshop_category cid';
				ob_start();
?>
			<p><?php _e('Shortcode options', 'wpshop'); ?></p>
			<div id="wpshop_wysiwyg_shortcode_categorie_options_container" >
				<input type="checkbox" name="wpshop_wysiwyg_shortcode_options[]" id="wpshop_wysiwyg_shortcode_options_categorie_display_product" class="wpshop_wysiwyg_shortcode_options wpshop_wysiwyg_shortcode_options_categories_display_product" value="only_cat" checked ><label for="wpshop_wysiwyg_shortcode_options_categorie_display_product" > <?php _e('Do not display product', 'wpshop'); ?></label>
			</div>
<?php
				$specific_options = ob_get_contents();
				ob_end_clean();
			break;

		case 'product':
				$query = $wpdb->prepare("SELECT COUNT(ID) as WPSHOP_PRODUCT_NB FROM " . $wpdb->posts . " WHERE post_type=%s AND post_status=%s", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish');
				$wpshop_product_nb = $wpdb->get_var($query);
				$content = sprintf(__('Due to product number, you have to search for product or click on "%s" button', 'wpshop'), __('View all element','wpshop'));
				if ( $wpshop_product_nb <= 100 ) {
					$content = wpshop_products::product_list(true);
				}
				$wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier = 'wpshop_products pid';
			break;

		case 'product_by_attribute':
				$content_explanation = '<p>' . __('Generate product listing shortcode from a selected attribute value', 'wpshop') . '</p>';
				$content = wpshop_attributes::get_attribute_list(null, $type);
				$wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier = 'wpshop_products att_name';
				$display_option_grouped = false;
				$display_search_form = false;
			break;
	}
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php _e('WpShop shortcodes insertion', 'wpshop'); ?></title>

	<script type="text/javascript" src="<?php echo bloginfo('url'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script type="text/javascript" src="<?php echo bloginfo('url'); ?>/wp-includes/js/jquery/jquery.js"></script>
	<?php wpshop_init::admin_print_js(); ?>
	<script type="text/javascript" src="<?php echo WPSHOP_JS_URL; ?>pages/wpshop_wysiwyg.js?v=<?php echo WPSHOP_VERSION;?>" ></script>
	<script type="text/javascript" src="<?php echo WPSHOP_JS_URL; ?>jquery-libs/chosen.jquery.min.js?v=<?php echo WPSHOP_VERSION;?>" ></script>
	<script type="text/javascript">
//		var WPSHOP_AJAX_FILE_URL = "<?php echo admin_url('admin-ajax.php'); ?>";
		var wpshop_wysiwyg_shortcode_insertion_search = "<?php echo wp_create_nonce('wpshop_element_search'); ?>";
		var WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT = "<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>";
	</script>

	<?php wp_admin_css( 'wp-admin', true ); ?>
	<link rel='stylesheet' href="<?php echo WPSHOP_CSS_URL; ?>pages/wpshop_wysiwyg_dialog.css?v=<?php echo WPSHOP_VERSION;?>" type="text/css" media="all" />
	<link rel='stylesheet' href="<?php echo WPSHOP_CSS_URL; ?>jquery-libs/chosen.css?v=<?php echo WPSHOP_VERSION;?>" type="text/css" media="all" />
</head>
<body>
	<form onsubmit="wpShop_Dialog_Action.insert();" action="#">
		<input type="hidden" value="<?php echo $wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier; ?>" id="wpshop_wysiwyg_shortcode_inserter_shortcode_main_identifier" />
		<input type="hidden" value="<?php echo $type; ?>" id="wpshop_wysiwyg_shortcode_inserter_type" />
<?php if ( !empty($type) ) : ?>
<?php
		if ($display_search_form) {
?>
		<div class="search_element_container" >
			<input type="text" value="" placeholder="<?php _e('Search...','wpshop'); ?>"  id="search_element_text" />
			<button type="button" name="search_element_button" id="search_element_button" ><?php _e('Search','wpshop'); ?></button>
			<button type="button" name="view_all_element_button" id="view_all_element_button" ><?php _e('View all element','wpshop'); ?></button>
		</div>
<?php
		}
?>
		<div id="selected_content_container" >
			<div id="wpshop_loading"></div>
			<?php echo $content_explanation; ?>
			<ul id="selected_content" class="wpshop_element_search_result" >
				<?php echo $content; ?>
			</ul>
		</div>

		<div class="wpshop_shortcode_options_container" >
<?php
		echo $specific_options;

		if ($display_option_display_type) {
?>
			<?php if ( !empty($specific_options) ) : ?><p><?php _e('Generic options', 'wpshop'); ?></p><?php endif; ?>
			<div class="wpshop_wysiwyg_shortcode_display_type_container" >
				<?php _e('Display as', 'wpshop'); ?>
				<input type="radio" name="wpshop_wysiwyg_shortcode_display_type" id="wpshop_wysiwyg_shortcode_display_type_grid" class="wpshop_wysiwyg_shortcode_display_option wpshop_wysiwyg_shortcode_display_type" value="grid" checked > <label for="wpshop_wysiwyg_shortcode_display_type_grid" ><?php _e('Grid', 'wpshop'); ?></label>
				<input type="radio" name="wpshop_wysiwyg_shortcode_display_type" id="wpshop_wysiwyg_shortcode_display_type_list" class="wpshop_wysiwyg_shortcode_display_option wpshop_wysiwyg_shortcode_display_type" value="list" > <label for="wpshop_wysiwyg_shortcode_display_type_list" ><?php _e('List', 'wpshop'); ?></label>
			</div>
<?php
		}

		if ($display_option_grouped) {
?>
			<div id="wpshop_wysiwyg_shortcode_group_container" >
				<input type="checkbox" name="wpshop_wysiwyg_shortcode_group" id="wpshop_wysiwyg_shortcode_group" class=" wpshop_wysiwyg_shortcode_display_option wpshop_wysiwyg_shortcode_group" value="grouped" checked ><label for="wpshop_wysiwyg_shortcode_group" > <?php _e('Add only one shortcode for the entire selection', 'wpshop'); ?></label>
			</div>
<?php
		}
?>
		</div>


		<div class="wpshop_created_shortcode_container" >
			<div class="wpshop_shortcode_result" >
				<?php _e('Here is the result that will be inserted into content', 'wpshop'); ?>
				<textarea readonly name="wpshop_created_shortcode" id="wpshop_created_shortcode" ></textarea>
			</div>
		</div>
		<div class="mceActionPanel">
			<div style="float: left">
				<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
			</div>
			<div style="float: right">
				<input type="button" id="insert" name="insert" value="{#insert}" onclick="wpShop_Dialog_Action.insert();" />
			</div>
		</div>
<?php else: ?>
		<span class="wpshop_wysiwyg_error_msg" ><?php _e('You are not allowed to continue here', 'wpshop'); ?></span>
<?php endif; ?>
	</form>
</body>
</html>