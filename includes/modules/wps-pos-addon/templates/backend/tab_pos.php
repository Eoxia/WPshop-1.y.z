<h2 class="nav-tab-wrapper"><?php
	$current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'dashboard';
	$tabs = array( 'dashboard' => __( 'Order', 'wps-pos-i18n'), 'bank_deposit' => __( 'Bank deposit', 'wps-pos-i18n' ) );
	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=wps-pos&tab=$tab'>$name</a>";
	}
?></h2>