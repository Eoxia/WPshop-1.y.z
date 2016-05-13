<?php if ( !defined( 'ABSPATH' ) ) exit;
?>
<ul class="module-list" >
	<li class="module-list-header" >
		<!--
		<span class="module-cb" ><?php _e( 'Activ', 'eo-modmanager-i18n' ); ?></span>
		 -->
		<span class="module-name" ><?php _e( 'Module name', 'eo-modmanager-i18n' ); ?></span>
		<span class="module-name" ><?php _e( 'Module description', 'eo-modmanager-i18n' ); ?></span>
		<span class="module-info" ><?php _e( 'Module info', 'eo-modmanager-i18n' ); ?></span>
	</li>

<?php $i = 0; ?>
<?php foreach ( $parent_folder_content as $folder ) : ?>
	<?php if ( $folder && ( substr( $folder, 0, 1) != '.' ) && file_exists( $module_folder . $folder . '/' . $folder . '.php') ) : ?>
	<?php
		$is_activ = false;
		if ( !empty( $module_option ) && ( array_key_exists( $folder, $module_option ) && ( 'on' == $module_option[ $folder ][ 'activated' ] ) ) ) {
			$is_activ = true;
		}
		$module_datas = get_plugin_data( $module_folder . $folder . '/' . $folder . '.php' );

		$module_id = 'module' . $folder;
	?>
	<li class="<?php echo ( $i == 0 ? "odd" : "even" ); ?>" >
		<!--
		<span class="module-cb module-cb-<?php echo $folder; ?>" >
			<input type="hidden" name="wpshop_modules[<?php echo $folder; ?>][old_activated]" value="<?php echo $is_activ ? 'on' : 'off'; ?>" />
			<input id="<?php echo $module_id; ?>" type="checkbox" name="wpshop_modules[<?php echo $folder; ?>][activated]" value="on" <?php checked( true, $is_activ, true); ?> />
		</span>
		 -->
		<span class="module-name module-name-<?php echo $folder; ?>" >
			<label for="<?php echo $module_id; ?>" ><?php echo !empty( $module_datas ) && !empty( $module_datas[ 'Name' ] ) ? $module_datas[ 'Name' ] : $folder; ?></label>
		</span>
		<span class="module-description" >
			<?php echo !empty( $module_datas ) && !empty( $module_datas[ 'Description' ] ) ? $module_datas[ 'Description' ] : ''; ?><br/>

			<?php echo !empty( $module_datas ) && !empty( $module_datas[ 'Version' ] ) ? $module_datas[ 'Version' ] : __( 'Unknown version', 'eo-modmanager-i18n' ); ?> |
			<?php echo !empty( $module_datas ) && !empty( $module_datas[ 'Author' ] ) ? $module_datas[ 'Author' ] : __( 'Unknown author', 'eo-modmanager-i18n' ); ?>
			<?php if ( !empty( $module_datas ) && !empty( $module_datas[ 'PluginURI' ] ) ) : ?> |
				<?php echo $module_datas[ 'Title' ]; ?>
			<?php endif; ?>
		</span>
		<span class="module-info module-info-<?php echo $folder; ?>" >
			<?php
				if ( 'auto' != $module_option[ $folder ][ 'author_on' ] ) :
					$user = get_userdata( $module_option[ $folder ][ 'author_on' ] );
					$author = $user->display_name;
				else :
					$author = __( 'automatic activation', 'eo-modmanager-i18n' );
				endif;
				printf( __( 'Last activation made on %1$s by %2$s', 'eo-modmanager-i18n' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $module_option[ $folder ][ 'date_on' ], true ), $author);
			?>
			<?php if ( !empty( $module_option[ $folder ][ 'date_off' ] ) ) : ?>
			<br/>
			<?php
				$user = get_userdata( $module_option[ $folder ][ 'author_off' ] );
				$author = $user->display_name;
				printf( __( 'Last deactivation made on %1$s by %2$s', 'eo-modmanager-i18n' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $module_option[ $folder ][ 'date_off' ], true ), $author);
			?>
			<?php endif; ?>

			<?php apply_filters( 'wps-addon-extra-info', $folder ); ?>
		</span>
	</li>
	<?php endif; ?>
	<?php
		$i++;
		if ( $i > 1 ) :
			$i = 0;
		endif;
	?>
<?php endforeach; ?>
</ul>