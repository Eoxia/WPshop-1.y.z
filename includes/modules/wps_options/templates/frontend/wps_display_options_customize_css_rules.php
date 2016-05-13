<?php if ( !defined( 'ABSPATH' ) ) exit;
 if( !empty($wpshop_customize_display_option) ) : ?>
	<!--  WPSHOP CUSTOM CSS RULES -->
	<style>
		<?php if( !empty($wpshop_customize_display_option['first']) ) : ?>
		[class*="wps"][class*="bton"][class*="first"] {
			<?php if( !empty($wpshop_customize_display_option['first']['background']) ) : ?>
				background : <?php echo $wpshop_customize_display_option['first']['background']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['first']['shadow']) ) : ?>
				box-shadow: 0 0.25em 0 0 <?php echo $wpshop_customize_display_option['first']['shadow']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['first']['text']) ) : ?>
				color : <?php echo $wpshop_customize_display_option['first']['text']; ?>!important;
			<?php endif; ?>
	
		}
		
		[class*="wps"][class*="bton"][class*="first"]:hover {
			<?php if( !empty($wpshop_customize_display_option['first']['background_hover']) ) : ?>
				background : <?php echo $wpshop_customize_display_option['first']['background_hover']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['first']['shadow_hover']) ) : ?>
				box-shadow: 0 0.25em 0 0 <?php echo $wpshop_customize_display_option['first']['shadow_hover']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['first']['text_hover']) ) : ?>
				color : <?php echo $wpshop_customize_display_option['first']['text_hover']; ?>!important;
			<?php endif; ?>
		}
		<?php endif; ?>
		
		<?php if( !empty($wpshop_customize_display_option['second']) ) : ?>
		[class*="wps"][class*="bton"][class*="second"] {
			<?php if( !empty($wpshop_customize_display_option['second']['background']) ) : ?>
				background : <?php echo $wpshop_customize_display_option['second']['background']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['second']['shadow']) ) : ?>
				box-shadow: 0 0.25em 0 0 <?php echo $wpshop_customize_display_option['second']['shadow']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['second']['text']) ) : ?>
				color : <?php echo $wpshop_customize_display_option['second']['text']; ?>!important;
			<?php endif; ?>
	
		}
		
		[class*="wps"][class*="bton"][class*="second"]:hover {
			<?php if( !empty($wpshop_customize_display_option['second']['background_hover']) ) : ?>
				background : <?php echo $wpshop_customize_display_option['second']['background_hover']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['second']['shadow_hover']) ) : ?>
				box-shadow: 0 0.25em 0 0 <?php echo $wpshop_customize_display_option['second']['shadow_hover']; ?>!important;
			<?php endif; ?>
			<?php if( !empty($wpshop_customize_display_option['second']['text_hover']) ) : ?>
				color : <?php echo $wpshop_customize_display_option['second']['text_hover']; ?>!important;
			<?php endif; ?>
		}
		<?php endif; ?>
		
		
		<?php if( !empty($wpshop_customize_display_option['account']['activ_element_background']) ) : ?>
			.wps-section-taskbar ul li.wps-activ a {
				background : <?php echo $wpshop_customize_display_option['account']['activ_element_background']; ?>!important
			}
			.wps-section-taskbar ul li.wps-activ a:after {
				border-left-color : <?php echo $wpshop_customize_display_option['account']['activ_element_background']; ?>!important;
			}
		<?php endif; ?>
		
		<?php if( !empty($wpshop_customize_display_option['account']['part_background']) ) : ?>
			.wps-section-taskbar ul li a {
				background : <?php echo $wpshop_customize_display_option['account']['part_background']; ?>!important;
			}
		<?php endif; ?>
		
		<?php if( !empty($wpshop_customize_display_option['account']['information_box_background']) ) : ?>
			.wps-section-taskbar ul li a span:before {
				border-right-color : <?php echo $wpshop_customize_display_option['account']['information_box_background']; ?>!important
			}
			.wps-section-taskbar ul li a span {
				background : <?php echo $wpshop_customize_display_option['account']['information_box_background']; ?>!important
			}
		<?php endif; ?>
		
		<?php if( !empty($wpshop_customize_display_option['shipping']['active_element']) ) : ?>
		.wps-itemList li.wps-activ {
			background : <?php echo $wpshop_customize_display_option['shipping']['active_element']; ?>!important;
		}
		<?php endif; ?>
		
		.wps-itemList li {
			<?php if( !empty($wpshop_customize_display_option['shipping']['background']) ) : ?>
			background : <?php echo $wpshop_customize_display_option['shipping']['background']; ?>!important;
			<?php endif; ?>
		}
		
		<?php if( !empty($wpshop_customize_display_option['shipping']['text']) ) : ?>
		.wps-itemList li span {
			color : <?php echo $wpshop_customize_display_option['shipping']['text']; ?>!important;
		}
		
		.wps-itemList li div {
			<?php if( !empty($wpshop_customize_display_option['shipping']['active_element']) ) : ?>
			background : <?php echo $wpshop_customize_display_option['shipping']['active_element']; ?>!important;
			<?php endif; ?>
		}
		<?php endif; ?>
		
	</style>
<?php endif; ?>
