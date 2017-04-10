<?php
/**
 * Template for recent orders displaying
 *
 * @package wpshop
 * @subpackage dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo $this->wpshop_dashboard_orders(); // WPCS : XSS ok.
