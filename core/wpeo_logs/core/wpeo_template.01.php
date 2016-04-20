<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
* wpeo_template_01 file definition for project plugin
*
* @author Eoxia development team <dev@eoxia.com>
* @version 1.0
*/

/**
 * wpeo_template_01 file definition for project plugin
*
* @author Eoxia development team <dev@eoxia.com>
* @version 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'wpeo_template_01' ) ) {
	class wpeo_template_01 {
		/**
		 * INTERNAL LIB - Check and get the template file path to use for a given display part
		 * @uses locate_template()
		 * @uses get_template_part()
		 * @param string $plugin_dir_name The main directory name containing the plugin
		 * @param string $main_template_dir THe main directory containing the templates used for display
		 * @param string $side The website part were the template will be displayed. Backend or frontend
		 * @param string $slug The slug name for the generic template.
		 * @param string $name The name of the specialised template.
		 * @return string The template file path to use
		 */
		static function get_template_part( $plugin_dir_name, $main_template_dir, $side, $slug, $name=null, $debug = null ) {
			$path = '';

			$templates = array();
			$name = (string)$name;
			if ( '' !== $name )
				$templates[] = "{$side}/{$slug}-{$name}.php";
			$templates[] = "{$side}/{$slug}.php";

			/**	Check if required template exists into current theme	*/
			$check_theme_template = array();
			foreach ( $templates as $template ) {
				$check_theme_template = $plugin_dir_name . "/" . $template;
				$path = locate_template( $check_theme_template, false );
				if ( !empty( $path ) ) {
					break;
				}
			}

			/**	Allow debugging	*/
			if ( !empty( $debug ) ) {
				echo '--- Debug mode ON - Start ---<br/>';
				echo __FILE__ . '<br/>';
				echo 'Debug for display method<br/>';
			}

			if ( empty( $path ) ) {
				foreach ( (array) $templates as $template_name ) {
					if ( !$template_name )
						continue;

					if ( !empty( $debug ) ) {
						echo __LINE__ . ' - ' . $main_template_dir . $template_name . '<hr/>';
					}

					if ( file_exists( $main_template_dir . $template_name ) ) {
						$path = $main_template_dir . $template_name;
						break;
					}
				}
			}
			else {
				echo '';
			}

			/**	Allow debugging	*/
			if ( !empty( $debug ) ) {
				echo '--- Debug mode ON - END ---<br/><br/>';
			}

			return $path;
		}
	}
}

?>
