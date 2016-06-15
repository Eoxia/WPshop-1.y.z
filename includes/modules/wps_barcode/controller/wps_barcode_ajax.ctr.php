<?php if ( !defined( 'ABSPATH' ) ) exit;

class wps_barcode_ajax {
	public function __construct() {
		add_action( 'wp_ajax_barcode_img_product', array($this, 'imgProduct') );
		add_action( 'wp_ajax_barcode_img_coupons', array($this, 'imgCoupons') );
	}

	public function imgProduct() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'imgProduct' ) )
			wp_die();

		global $meta, $barcode, $post_ID, $wpdb, $table_prefix, $gg;
		require_once('wps_barcodegen.ctr.php');

		$barcode = new wps_barcodegen;

		$post_ID = (int)$_REQUEST['postID'];

		$country = '000';

		/*Select value of barcode*/
		$result = $wpdb->get_results(
				'SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.
				' WHERE attribute_id=(SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE.
				' WHERE code = "barcode") AND entity_id="'.$post_ID.'"', ARRAY_A);
		$meta = !empty($result) ? $result[0]['value'] : '';

		/*Get price of product*/
		$result = $wpdb->get_results('SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.
				' WHERE attribute_id=(SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE.
				' WHERE code="'.WPSHOP_PRODUCT_PRICE_TTC.'") AND entity_id='.$post_ID, ARRAY_A);

		if ( !empty($result) && $result[0]['value'] >= 0) {
			$price = $result[0]['value'];

			$post = get_post($post_ID, ARRAY_A);
			$ref = substr($post['post_title'], 0, 10);
		}

		$barcode = $this->generate_image($barcode, $meta, __('product', 'wps_barcode'), $price, $ref);
		$array = array('img' => $barcode);
		echo json_encode($array);
		wp_die();
	}

	public function imgCoupons() {
		$_wpnonce = !empty( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

		if ( !wp_verify_nonce( $_wpnonce, 'imgProduct' ) )
			wp_die();

		global $meta, $barcode, $post_ID, $wpdb, $table_prefix;

		$post_ID = (int)$_REQUEST['postID'];

		$continue = false;

		require_once('wps_barcodegen.ctr.php');

		$barcode = new wps_barcodegen;

		$country = '000';
		$result = get_post_meta($post_ID);

		if ( !empty($result) ) {
			if ( empty($result['wpshop_coupon_barcode']) ) {
				$conf = get_option('wps_barcode');
				if ($conf['type'] === 'internal') {
					$type = $conf['internal_coupons'];

					$query = $wpdb->get_results('SELECT post_date FROM '.
							$table_prefix.'posts WHERE ID='.$post_ID, ARRAY_A);

					$pDate = new DateTime($query[0]['post_date']);
					$date = $pDate->format('my');
				}

				$len = strlen($post_ID);
				$ref = '';
				if ( $len < 5 ) {
					for ($i=0; $i <= $len; $i++) {
						$ref .= '0';
					}
				}
				$id = $ref.$post_ID;
				$code = $type.$date.$id;
				$meta = $barcode->checksum($code);
				add_post_meta($post_ID, 'wpshop_coupon_barcode', $meta);
			}
			else {
				$meta = $result['wpshop_coupon_barcode'][0];
			}

			$query = $wpdb->get_results('SELECT post_title FROM '.
					$table_prefix.'posts WHERE ID='.$post_ID, ARRAY_A);

			$post = get_post($post_ID, ARRAY_A);

			$barcode = $this->generate_image($barcode, $meta, __('coupon', 'wps_barcode'),
				$result['wpshop_coupon_discount_value'][0], $query[0]['post_title'], $post_ID);
			$array = array('img' => $barcode);
			echo json_encode($array);
			wp_die();
		}
		else {
			echo '<p>'.__('None bardcode generated as coupon has not created.',
					'wps_barcode').'</p>';
			wp_die();
		}
	}

	/**
	 * Generate barcode image
	 * @param object $barcode Instance of wps_barcodegen
	 * @param string $meta Numerical code of barcode
	 * @param string $type Texte for complete message
	 * @param string $price Price of product
	 * @param string $title Title of product
	 */
	public function generate_image(&$barcode, $meta, $type, $price, $title, $post_ID = 0) {
		if ( !extension_loaded('gd') ) {
			return '<p>'.__('Library GD is requiered.', 'wps_barcode').'</p>';
		}
		if ( !empty($meta) ) {
			$barcode->setGenerateCode($meta);
			$binCode = $barcode->getBinCode();

			$px = 3.779528;
			$x = round(66*$px); //249.449 px
			$y = round(50*$px); //188.976 px
			$bar_size = round(63.393/72); //0.880 px

			$len = 0;
			$test = '';

			while ($len !== strlen($binCode) ) {
				$test .= substr($binCode, $len, 7).' ';
				$len = $len+7;
			}

			$im = imagecreate($x, $y);
			$background_color = imagecolorallocate($im, 255, 255, 255);
			$start = round(5.79*$px); //21.883

			/*Write First left guard*/
			$this->imgNormalGuard($im, $start, $start+$bar_size,
					imagecolorallocate($im, 0, 0, 0));
			$this->imgNormalGuard($im, $start+($bar_size*2),
					$start+($bar_size*3), imagecolorallocate($im, 255, 255, 255));
			$this->imgNormalGuard($im, $start+($bar_size*4),
					$start+($bar_size*5), imagecolorallocate($im, 0, 0, 0));

			$pos = $start+($bar_size*7);
			$newPos = $pos+$bar_size;

			/*Write left barcode*/
			for ($i=0; $i<42 ; $i++) {
				if( substr($binCode, $i, 1) === '0' ) {
					$color = imagecolorallocate($im, 255, 255, 255);
				}
				else {
					$color = imagecolorallocate($im, 0, 0, 0);
				}

				$this->imgSymbole($im, $pos, $newPos, $color);
				$newPos = $pos+$bar_size;
				$pos = $newPos+$bar_size;
			}

			/*Writer center guard*/
			$pos = $newPos;
			$this->imgNormalGuard($im, $pos, $newPos+$bar_size,
					imagecolorallocate($im, 255, 255, 255));
			$this->imgNormalGuard($im, $pos+($bar_size*2),
					$newPos+($bar_size*3), imagecolorallocate($im, 0, 0, 0));
			$this->imgNormalGuard($im, $pos+($bar_size*4),
					$newPos+($bar_size*5), imagecolorallocate($im, 255, 255, 255));
			$this->imgNormalGuard($im, $pos+($bar_size*6),
					$newPos+($bar_size*7), imagecolorallocate($im, 0, 0, 0));
			$this->imgNormalGuard($im, $pos+($bar_size*8),
					$newPos+($bar_size*9), imagecolorallocate($im, 255, 255, 255));

			$pos = $newPos+($bar_size*10);

			/*Write right barcode*/
			for ($i=42; $i<84 ; $i++) {
				if( substr($binCode, $i, 1) === '0' ) {
					$color = imagecolorallocate($im, 255, 255, 255);
				}
				else {
					$color = imagecolorallocate($im, 0, 0, 0);
				}

				$newPos = $pos+$bar_size;

				$this->imgSymbole($im, $pos, $newPos, $color);
				$pos = $newPos+$bar_size;
			}

			/*Write right guard*/
			$pos = $newPos+$bar_size;
			$this->imgNormalGuard($im, $pos, $pos+$bar_size,
					imagecolorallocate($im, 0, 0, 0));
			$this->imgNormalGuard($im, $pos+($bar_size*2),
					$pos+($bar_size*3), imagecolorallocate($im, 255, 255, 255));
			$this->imgNormalGuard($im, $pos+($bar_size*4),
					$pos+($bar_size*5), imagecolorallocate($im, 0, 0, 0));

			$textSize = 16;
			$font = WPS_BARCODE_FONTS.'/arialbd.ttf';
			imagettftext($im, $textSize, 0, 8, $y-$start-5,
			imagecolorallocate($im, 0, 0, 0), $font, substr($meta, 0, 1));

			$continue = true;
			$i = 28; $j = 0;

			/*Write left number code*/
			while ($j<5) {
				$j=$j+1;
				imagettftext($im, $textSize, 0, $i, $y-$start-5,
				imagecolorallocate($im, 0, 0, 0), $font, substr($meta, $j, 1));
				$i = $i+14;
			}

			/*Write right number code*/
			while ($j<11) {
				$j=$j+1;
				imagettftext($im, $textSize, 0, $i+6, $y-$start-5,
					imagecolorallocate($im, 0, 0, 0), $font, substr($meta, $j, 1));
				$i = $i+15;
			}
			imagettftext($im, $textSize, 0, $i+4, $y-$start-5,
				imagecolorallocate($im, 0, 0, 0), $font, substr($meta, $j+1, 1));

			/*Write ref product and price*/
			$textSize = 12;
			$currency = (wpshop_tools::wpshop_get_currency() === '&euro;') ? "€" : wpshop_tools::wpshop_get_currency();

			if ( $type === __('coupon', 'wps_barcode') ) {
				$coupon_postmeta = get_post_meta($post_ID, 'wpshop_coupon_discount_type');
				if ( $coupon_postmeta[0] === 'percent' ) {
					$price = $price.' %';
				}
				else {
					$price = sprintf( number_format( $price, 2 ). ' '.$currency);
				}
			}
			else {
				$price = sprintf( number_format( $price, 2 ). ' '.$currency);
			}

			imagettftext($im, $textSize, 0, 20, round(6*$px),
				imagecolorallocate($im, 0, 0, 0), $font, $title);
			imagettftext($im, $textSize, 0, ($x/2)+40, round(6*$px),
				imagecolorallocate($im, 0, 0, 0), $font, $price);

			ob_start();
			imagepng($im);
			$img = ob_get_clean();

			return '<p><img src="data:image/png;base64,'.base64_encode($img).
			'" id="barcode" width="160" height="90" /></p>'.

			'<p style="text-align: right"><button class="button '.
					'button-primary button-large" type="button"'.
					'id="print_barcode">'.
					__('Print', 'wps_barcode').'</button></p>';

					/*wp_mkdir_p( WPS_BARCODE_UPLOAD );

					file_put_contents(WPS_BARCODE_UPLOAD.$meta.'.png', $img);*/

					/*Generate ODT File*/
					/*try {
						if( !class_exists('Odf') ) {
							require_once(WPS_BARCODE_PATH.'/librairies/odtphp/odf.php');
						}
						$odf = new Odf(WPS_BARCODE_PATH.'assets/medias/avery_a4_991_677.ott');
						$odf->setImage('barcode', WPS_BARCODE_UPLOAD.$meta.'.png');
						$odf->saveToDisk(WPS_BARCODE_UPLOAD.$meta.'.odt');
					} catch (Exception $e) {
						echo __('Generation problem', 'wps_barcode');
					}*/
		}
		else {
			return '<p>'.sprintf( __('None bardcode generated as you did create %s.',
					'wps_barcode'), $type).'</p>';
		}
	}

	/**
	 * Generate one bar for normal guard
	 * @param resource $image Resource of barcode image generate with GD2 Lib
	 * @param integer $pos X position of start rectangle
	 * @param integer $size Size of rectangle
	 * @param integer $color Color of rectangle
	 */
	private function imgNormalGuard(&$image, $pos, $size, $color) {
		imagefilledrectangle($image, $pos, 180*0.25,$size, 180-10, $color );
	}

	/**
	 * Generate one bar for barcode
	 * @param resource $image Resource of barcode image generate with GD2 Lib
	 * @param integer $pos X position of start rectangle
	 * @param integer $size Size of rectangle
	 * @param integer $color Color of rectangle
	 */
	private function imgSymbole(&$image, $pos, $size, $color) {
		imagefilledrectangle($image, $pos, 180*0.25,$size, 180-40, $color );
	}
}
