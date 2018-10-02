<?php
/**
 * Display prospect status
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2006-2018 Evarisk <dev@evarisk.com>.
 *
 * @license   GPLv3 <https://spdx.org/licenses/GPL-3.0-or-later.html>
 *
 * @package   WPshop\Templates
 *
 * @since     1.7.0
 */

defined( 'ABSPATH' ) || exit; ?>

<ul class="inside">
	<?php
	if ( ! empty( $statuses ) ) :
		foreach ( $statuses as $key => $status ) :
			$checked = '';

			if ( $current_status === $status['id'] ) :
				$checked = 'checked="checked"';
			endif;
			?>
			<li>
				<input <?php echo esc_attr( $checked ); ?> type="radio" id="<?php echo esc_attr( $key ); ?>" name="fk_stcomm" value="<?php echo esc_attr( $status['id'] ); ?>" />
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $status['text'] ); ?></label>
			</li>
			<?php
		endforeach;
	endif;
	?>
</ul>
