<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<div id="shortcode-tabs" class="wpshop_tabs wpshop_full_page_tabs wpshop_shortcode_tabs" >
	<ul>
		<li><a href="#products"><?php _e('Products', 'wpshop'); ?></a></li>
		<li><a href="#category"><?php _e('Categories', 'wpshop'); ?></a></li>
		<li><a href="#attributs"><?php _e('Attributs', 'wpshop'); ?></a></li>
		<li><a href="#widgets"><?php _e('Widgets', 'wpshop'); ?></a></li>
		<li><a href="#customs_emails"><?php _e('Customs emails', 'wpshop'); ?></a></li>
	</ul>

	<div id="products">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_product" >
			<h3><?php _e('Simple product','wpshop'); ?></h3>
			<?php self::output_shortcode('simple_product'); ?>
			<h3><?php _e( 'Product title', 'wpshop' ); ?></h3>
			<?php self::output_shortcode( 'wpshop_product_title' ); ?>
			<h3><?php _e( 'Product content', 'wpshop' ); ?></h3>
			<?php self::output_shortcode( 'wpshop_product_content' ); ?>
			<h3><?php _e( 'Product thumbnail', 'wpshop' ); ?></h3>
			<?php self::output_shortcode( 'wpshop_product_thumbnail' ); ?>
			<h3><?php _e('Products listing','wpshop'); ?></h3>
			<?php self::output_shortcode('product_listing'); ?>
			<h3><?php _e('Products listing specific','wpshop'); ?></h3>
			<?php self::output_shortcode('product_listing_specific'); ?>
			<h3><?php _e('Products listing by attributes','wpshop'); ?></h3>
			<?php self::output_shortcode('product_by_attribute'); ?>
			<h3><?php _e( 'Related products', 'wpshop'); ?></h3>
			<?php self::output_shortcode('related_products'); ?>
		</div>
	</div>

	<div id="category">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_category" >
			<h3><?php _e('Simple category','wpshop'); ?></h3>
			<?php self::output_shortcode('simple_category'); ?>
		</div>
	</div>

	<div id="attributs">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_attributes" >
			<h3><?php _e('Simple attribute','wpshop'); ?></h3>
			<?php self::output_shortcode('simple_attribute'); ?>
			<h3><?php _e('Attributes set','wpshop'); ?></h3>
			<?php self::output_shortcode('attributes_set'); ?>
		</div>
	</div>

	<div id="widgets">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_cart" >
			<h3><?php _e('Cart','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_cart_full'); ?>
			<?php self::output_shortcode('widget_cart_mini'); ?>
			<?php self::output_shortcode('wpshop_button_add_to_cart'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_checkout" >
			<h3><?php _e('Checkout','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_checkout'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_customer_account" >
			<h3><?php _e('Account','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_account'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_shop" >
			<h3><?php _e('Shop','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_shop'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_custom_search" >
			<h3><?php _e('Custom search','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_custom_search'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_custom_search" >
			<h3><?php _e('Filter Search','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_filter_search'); ?>
			<a href="http://www.wpshop.fr/documentations/la-recherche-par-filtre/" target="_blank"><?php _e( 'Read the filter search tutorial', 'wpshop'); ?></a>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_custom_search" >
			<h3><?php _e('Breadcrumb WPShop','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_wps_breadcrumb'); ?>
		</div>
	</div>

	<div id="customs_emails">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_emails" >
			<h3><?php _e('Available tags for emails cutomization','wpshop'); ?></h3>
			<ul >
				<li><span class="wpshop_customer_tag_name" ><?php _e('Customer first name', 'wpshop'); ?></span><code>[customer_first_name]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Customer last name', 'wpshop'); ?></span><code>[customer_last_name]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Customer email', 'wpshop'); ?></span><code>[customer_email]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order id', 'wpshop'); ?></span><code>[order_key]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Paypal transaction id', 'wpshop'); ?></span><code>[paypal_order_key]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Payment method', 'wpshop'); ?></span><code>[order_payment_method]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order content', 'wpshop'); ?></span><code>[order_content]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Customer personnal informations', 'wpshop'); ?></span><code>[order_personnal_informations]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order Billing Address', 'wpshop'); ?></span><code>[order_billing_address]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order shipping Address', 'wpshop'); ?></span><code>[order_shipping_address]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order shipping method', 'wpshop'); ?></span><code>[order_shipping_method]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order customer comment', 'wpshop'); ?></span><code>[order_customer_comments]</code><li>
			</ul>
		</div>
	</div>
</div>