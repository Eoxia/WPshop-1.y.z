<?php
/**
 * Le contenu de la modal contenant la notice de WPshop2.
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2019 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Templates
 *
 * @since     1.6.2
 */

defined( 'ABSPATH' ) || exit; ?>

<!-- Structure -->
<div class="wpeo-modal modal-active modal-notice-wpshop">
	<div class="modal-container">

		<!-- Entête -->
		<div class="modal-header">
			<h2 class="modal-title">WPshop2 compatibility.</h2>
			<div class="modal-close"><i class="fal fa-times"></i></div>
		</div>

		<!-- Corps -->
		<div class="modal-content">
			<p><?php echo $content; ?></p>
		</div>

		<!-- Footer -->
		<div class="modal-footer">
			<a class="wpeo-button button-main button-uppercase modal-close"><span><?php esc_html_e( 'I understand!', 'wpshop' ); ?></span></a>
		</div>
	</div>
</div>
