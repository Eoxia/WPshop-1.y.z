<?php if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Bootstrap file for plugin. Do main includes and create new instance for plugin components
 *
 * @author Eoxia <dev@eoxia.com>
 * @version 0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'constructor_model_ctr_01' ) ) {
	/** Define */
	DEFINE( 'WPEO_MODEL_VERSION', 0.1 );
	DEFINE( 'WPEO_MODEL_DIR', basename( dirname( __FILE__ ) ) );
	DEFINE( 'WPEO_MODEL_PATH', str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) );

	require_once( WPEO_MODEL_PATH . '/controller/constructor_model_ctr.01.php' );

	require_once( WPEO_MODEL_PATH . '/model/post_mdl.01.php' );
	require_once( WPEO_MODEL_PATH . '/model/comment_mdl.01.php' );
	require_once( WPEO_MODEL_PATH . '/model/user_mdl.01.php' );
	require_once( WPEO_MODEL_PATH . '/model/term_mdl.01.php' );

	require_once( WPEO_MODEL_PATH . '/controller/post_ctr.01.php' );
	require_once( WPEO_MODEL_PATH . '/controller/comment_ctr.01.php' );
	require_once( WPEO_MODEL_PATH . '/controller/user_ctr.01.php' );
	require_once( WPEO_MODEL_PATH . '/controller/term_ctr.01.php' );
}


?>
