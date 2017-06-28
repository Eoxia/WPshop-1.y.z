<?php
/**
 * Affichage du nom du client dans la liste
 *
 * @package WPShop
 * @subpackage Customer
 *
 * @since 3.0.0.0
 * @version 3.0.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><a href="<?php echo admin_url( 'post.php?post=' . $post_id . '&amp;action=edit' ); ?>" ><?php echo $customer_post->post_title; ?></a>
