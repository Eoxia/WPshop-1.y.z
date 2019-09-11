<?php if ( !defined( 'ABSPATH' ) ) exit;

class WP_Widget_Wpshop_Products extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct() {
		add_action('widgets_init', function() {
			return register_widget("WP_Widget_Wpshop_Products");
		});

		$widget_ops = array( 'classname' => 'WP_Widget_Wpshop_Products', 'description' => __('Allows you to display a widget with a list of product into your shop', 'wpshop') );
		parent::__construct( 'WP_Widget_Wpshop_Products', __( '• Wpshop products', 'wpshop' ), $widget_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		extract($args);

		$wpshop_select_wpshop_products = isset( $instance['wpshop_select_wpshop_products'] ) ? $instance['wpshop_select_wpshop_products'] : false;
		$wpshop_select_wpshop_categorie = isset( $instance['wpshop_select_wpshop_categorie'] ) ? $instance['wpshop_select_wpshop_categorie'] : false;
		$wpshop_nb_wpshop_products = isset( $instance['wpshop_nb_wpshop_products'] ) ? $instance['wpshop_nb_wpshop_products'] : false;
		$wpshop_select_wpshop_products_mode = isset( $instance['wpshop_select_wpshop_products_mode'] ) ? $instance['wpshop_select_wpshop_products_mode'] : false;
		$wpshop_wpshop_products_title = isset( $instance['wpshop_wpshop_products_title'] ) ? $instance['wpshop_wpshop_products_title'] : false;

		$wpshop_nb_par_ligne_wpshop_products = isset( $instance['wpshop_nb_par_ligne_wpshop_products'] ) ? $instance['wpshop_nb_par_ligne_wpshop_products'] : false;

		$wpshop_tri_wpshop_products = isset( $instance['wpshop_tri_wpshop_products'] ) ? $instance['wpshop_tri_wpshop_products'] : false;
		$display_pagination = !empty($instance['wpshop_display_pagination']) ? $instance['wpshop_display_pagination'] : "yes";

		echo $before_widget; ?>
		<?php if ($wpshop_wpshop_products_title){ ?>
		<h3 class="widget-title"><?php echo $wpshop_wpshop_products_title; ?></h3>
		<?php }

		$chaine_shortcode = '[wpshop_products sorting="no" limit="'.$wpshop_nb_wpshop_products.'" ';
		if($wpshop_select_wpshop_products == 'random'){
			$chaine_shortcode .= 'order="rand" ';
		}else if ($wpshop_select_wpshop_products == 'vedette'){
			$chaine_shortcode .= 'att_name="highlight_product" att_value="yes" ';
		}else if ($wpshop_select_wpshop_products == 'nouveaux'){
			$chaine_shortcode .= 'att_name="declare_new" att_value="yes" ';
		}else {
			$chaine_shortcode .= 'cid="'.$wpshop_select_wpshop_categorie.'" type="'.$wpshop_select_wpshop_products_mode.'" ';
		}
		// if($wpshop_select_wpshop_products_mode == 'list'){
			$chaine_shortcode .= 'type="list" ';
		// }
		// elseif( $wpshop_select_wpshop_products_mode == 'grid' ) {
		// 	$chaine_shortcode .= 'type="grid" ';
		// }
		if($wpshop_nb_par_ligne_wpshop_products){
			$chaine_shortcode .= 'grid_element_nb_per_line="'.$wpshop_nb_par_ligne_wpshop_products.'" ';
		}
		//$chaine_shortcode .= ' display_pagination="' . $display_pagination . '" ';
		// [grid_element_nb_per_line]
		$chaine_shortcode .= ' ]';
		echo do_shortcode($chaine_shortcode);
		?>

		<?php echo $after_widget;
		wp_reset_query();
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new, $old ) {
		return $new;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {
		$instance = wp_parse_args((array) $instance, array(
			'wpshop_select_wpshop_products' => false,
			'wpshop_type_wpshop_product' => false,
			'wpshop_tri_wpshop_products' => false,
			'wpshop_wpshop_products_title' => '',
			'wpshop_nb_wpshop_products' => '',
			'wpshop_select_wpshop_products_mode' => '',
			'wpshop_nb_par_ligne_wpshop_products' => '',
			'wpshop_select_wpshop_categorie' => '',
		));
?>
<fieldset>
	<p>
		<label for="<?php echo $this->get_field_id( 'wpshop_wpshop_products_title' ); ?>"><?php _e('Widget title', 'wpshop'); ?> : <br></label>
		<input value="<?php echo $instance['wpshop_wpshop_products_title']; ?>" type="text"  id="<?php echo $this->get_field_id( 'wpshop_wpshop_products_title' ); ?>" name="<?php echo $this->get_field_name( 'wpshop_wpshop_products_title' ); ?>" />
	</p>

	<p class="<?php echo $this->id; ?>-select">
		<label for="<?php echo $this->get_field_id( 'wpshop_select_wpshop_products' ); ?>"><?php _e('Choose what type of product to display', 'wpshop'); ?> : <br></label>
	 	<select name="<?php echo $this->get_field_name( 'wpshop_select_wpshop_products' ); ?>" class="widefat wpshop_widget_wpshop_pdt_display_selector">
			<option value="random" <?php selected( $instance['wpshop_select_wpshop_products'], 'random' ); ?>><?php _e('Random products', 'wpshop'); ?></option>
			<option value="vedette" <?php selected( $instance['wpshop_select_wpshop_products'], 'vedette' ); ?>><?php _e('Featured products', 'wpshop'); ?></option>
			<option value="nouveaux" <?php selected( $instance['wpshop_select_wpshop_products'], 'nouveaux' ); ?>><?php _e('New products', 'wpshop'); ?></option>
			<option value="categorie" <?php selected( $instance['wpshop_select_wpshop_products'], 'categorie' ); ?>><?php _e('Product from a category', 'wpshop'); ?></option>
		</select>
	</p>

	<p class="<?php echo $this->id; ?>-select-cat wpshop_widget_wpshop_pdt_display_container" >
		<label for="<?php echo $this->get_field_id( 'wpshop_select_wpshop_categorie' ); ?>"><?php _e('Choose the category', 'wpshop'); ?> : <br></label>
		<select name="<?php echo $this->get_field_name( 'wpshop_select_wpshop_categorie' ); ?>" class="widefat">
<?php
			$options_wpshop_categories = array();
			$args = array(
				'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
				'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES
			);
			$options_categories_obj = get_categories($args);
			foreach ($options_categories_obj as $category) {
				$options_wpshop_categories[$category->cat_ID] = $category->cat_name;
?>
				<option value="<?php echo $category->cat_ID; ?>" <?php selected( $instance['wpshop_select_wpshop_categorie'], $category->cat_ID ); ?>><?php echo $category->cat_name; ?></option>
<?php
			}
?>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'wpshop_nb_wpshop_products' ); ?>"><?php _e('Number of product to display', 'wpshop'); ?> : <br></label>
		<input value="<?php echo $instance['wpshop_nb_wpshop_products']; ?>" type="text"  id="<?php echo $this->get_field_id( 'wpshop_nb_wpshop_products' ); ?>" name="<?php echo $this->get_field_name( 'wpshop_nb_wpshop_products' ); ?>" />
	</p>

	<!-- <p class="<?php echo $this->id; ?>-select_nb_ligne">
		<label for="<?php echo $this->get_field_id( 'wpshop_select_wpshop_products_mode' ); ?>"><?php _e("Display mode", 'wpshop'); ?> : <br></label>
	 	<select name="<?php echo $this->get_field_name( 'wpshop_select_wpshop_products_mode' ); ?>" class="widefat wpshop_widget_wpshop_nb_ligne_selector">
			<option value="grid" <?php selected( $instance['wpshop_select_wpshop_products_mode'], 'grid' ); ?>><?php _e("Grid mode", 'wpshop'); ?></option>
			<option value="list" <?php selected( $instance['wpshop_select_wpshop_products_mode'], 'list' ); ?>><?php _e("List mode", 'wpshop'); ?></option>
		</select>
	</p> -->

	<p class="<?php echo $this->id; ?>-select-container_nb_ligne wpshop_widget_wpshop_nb_ligne_container">
		<label for="<?php echo $this->get_field_id( 'wpshop_nb_par_ligne_wpshop_products' ); ?>"><?php _e('Product number per line', 'wpshop'); ?> : <br></label>
		<input value="<?php echo $instance['wpshop_nb_par_ligne_wpshop_products']; ?>" type="text"  id="<?php echo $this->get_field_id( 'wpshop_nb_par_ligne_wpshop_products' ); ?>" name="<?php echo $this->get_field_name( 'wpshop_nb_par_ligne_wpshop_products' ); ?>" />
	</p>

	<!-- Display pagination -->
	<!-- <p class="<?php echo $this->id; ?>-display_pagination">
			<label for="<?php echo $this->get_field_id('wpshop_display_pagination'); ?>"><?php _e("Display pagination", "wpshop"); ?> : <br /></label>
			<select name="<?php echo $this->get_field_name('wpshop_display_pagination'); ?> " class="widefat wpshop_widget_wpshop_nb_ligne_selector">
				<option value="yes" <?php echo selected( !empty($instance['wpshop_display_pagination']) ? $instance['wpshop_display_pagination'] : "", 'yes' ); ?>><?php _e("Yes", "wpshop"); ?></option>
				<option value="no" <?php echo selected( !empty($instance['wpshop_display_pagination']) ? $instance['wpshop_display_pagination'] : "", 'no' ); ?>><?php _e("No", "wpshop"); ?></option>
			</select>
	</p> -->
</fieldset>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		if($('.<?php echo $this->id; ?>-select select').val() != 'categorie'){
			$('.<?php echo $this->id; ?>-select-cat').hide();
		}
		$('.wpshop_widget_wpshop_pdt_display_selector').live('change', function() {
	  		if ($(this).val() == 'categorie') {
				$(this).parent().parent('fieldset').children('p.wpshop_widget_wpshop_pdt_display_container').slideDown();
			}else{
				$(this).parent().parent('fieldset').children('p.wpshop_widget_wpshop_pdt_display_container').slideUp();
			}
		});

		if($('.<?php echo $this->id; ?>-select_nb_ligne select').val() != 'grid'){
			$('.<?php echo $this->id; ?>-select-container_nb_ligne').hide();
		}

		$('.wpshop_widget_wpshop_nb_ligne_selector').live('change', function() {
	  		if ($(this).val() == 'grid') {
				$(this).parent().parent('fieldset').children('p.wpshop_widget_wpshop_nb_ligne_container').slideDown();
			}else{
				$(this).parent().parent('fieldset').children('p.wpshop_widget_wpshop_nb_ligne_container').slideUp();
			}
		});

	});
</script>
<?php
	}

}

new WP_Widget_Wpshop_Products();
