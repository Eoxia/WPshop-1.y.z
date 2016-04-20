<?php if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'wpeo_util' ) ) {
	class wpeo_util {
		public static $array_exclude_module = array( 'wpeo_timeline', 'wpeo_files', 'wpeo_project_data_transfert', 'wpeo_calendar' );
		
		/**
		 * CORE - Install all extra-modules in "Core/Module" folder
		 */
		public static function install_in( $folder ) {
			/**     Define the directory containing all exrta-modules for current plugin    */
			$module_folder = WPEOMTM_TASK_PATH . '/' . $folder . '/';
		
			/**     Check if the defined directory exists for reading and including the different modules   */
			if( is_dir( $module_folder ) ) {
				$parent_folder_content = scandir( $module_folder );
				foreach ( $parent_folder_content as $folder ) {
					if ( $folder && substr( $folder, 0, 1) != '.' && !in_array( $folder, self::$array_exclude_module ) ) {
						if( is_dir ( $module_folder . $folder ) ) 
							$child_folder_content = scandir( $module_folder . $folder );
						
						if ( file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
							$f =  $module_folder . $folder . '/' . $folder . '.php';
							include( $f );
						}
					}
				}
			}
		}
	}
}