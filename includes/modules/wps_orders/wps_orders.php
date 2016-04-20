<?php
DEFINE('WPS_ORDERS_DIR', basename(dirname(__FILE__)));
DEFINE('WPS_ORDERS_PATH', str_replace( "\\", "/", str_replace( WPS_ORDERS_DIR, "", dirname( __FILE__ ) ) ) );
DEFINE('WPS_ORDERS_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_ORDERS_PATH ) );
DEFINE( 'WPS_ORDERS_BASE', plugin_dir_path( __FILE__ ) );
include( plugin_dir_path( __FILE__ ).'/controller/wps_orders_ctr.php' );include( plugin_dir_path( __FILE__ ).'/controller/wps_orders_in_back_office.php' );
include( plugin_dir_path( __FILE__ ).'/model/wps_orders_mdl.php' );include( plugin_dir_path( __FILE__ ).'/model/wps_back_office_orders_mdl.php' );
$wps_orders_in_back_office = new wps_orders_in_back_office();
$wps_orders = new wps_orders_ctr();