<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Tools file definition for WP-Shop pos addon plugin / Fichier des outils de l'extension de caisse pour WP-Shop
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 * @package WP-Shop POS
 * @subpackage Tools
 */

/**
 * Tools class definition for WP-Shop pos addon plugin / Classe des outils de l'extension de caisse pour WP-Shop
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 * @package WP-Shop POS
 * @subpackage Tools
 */
class wps_pos_tools {

	/**
	 * DISPLAY / Affichage - Display all letter buttons for element choice / Affiche les lettres de l'alphabet pour lister les éléments existants
	 *
	 * @param string $type The type of element to display alphabet for / Le type d'élément pour lequel on va afficher l'alphabet
	 *
	 * @return string The alphabet letter button / Les bouttons affichant les lettres de l'alphabet
	 */
	public static function alphabet_letters( $type = 'customer', $available_letters= array(), $chosen_letter = '') {
		global $wpdb;
		$alphabet = unserialize( WPSPOS_ALPHABET_LETTERS );

		$alphabet_interface = '';
		foreach ( $alphabet as $alpha ) {
			ob_start();
			require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend', 'alphabet', 'letters' ) );
			$alphabet_interface .= ob_get_contents();
			ob_end_clean();
		}
		return $alphabet_interface;
	}


}

?>