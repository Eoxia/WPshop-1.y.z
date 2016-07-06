<?php
/**
* @author: Jimmy Latour lelabodudev@gmail.com
*/

if ( !function_exists( 'search_files' ) ) {
	function search_files($folder, $pattern) {
		$dir = new RecursiveDirectoryIterator($folder);
		$ite = new RecursiveIteratorIterator($dir);
		$files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
		$fileList = array();
		foreach($files as $file)
		{
			$fileList[] = $file[0];
		}
		return $fileList;
	}
}

echo "[+] Starting Request Tests" . PHP_EOL . PHP_EOL;

// Search for test files
$unitList = search_files('../', "/^.*\.php$/");
$string_post_unsecured = array();
$total_unsecured_line = 0;
$pattern = '#\$_POST|\$_GET|\$_REQUEST#';

// Loop on unitList
foreach ( $unitList as $file_url )
{
	// echo "[+] Testing -> " . $file_url . PHP_EOL;
  $file = file_get_contents( $file_url );
  $string_post_unsecured[$file_url] = array();
  $lines = explode( PHP_EOL, $file );

	if ( !empty( $lines ) ) {
		foreach ( $lines as $key => $line ) {
	    if ( preg_match( $pattern, $line ) ) {
	      $lines[$key] = preg_replace( '#!empty\(.+?(\$_POST|\$_GET|\$_REQUEST)\[\'.+\'\].+?\) \?#isU', '', $lines[$key] );

			if ( basename( $file_url ) != "request.test.php" ) {
			  if ( !preg_match( '#sanitize_.+#', $lines[$key] ) &&
        !preg_match( '#esc_.+#', $lines[$key] ) &&
				!preg_match( '#\*#', $lines[$key] ) &&
				!preg_match( '#\\/\/#', $lines[$key] ) &&
				!preg_match( '#\( ?int ?\)#', $lines[$key] ) &&
				!preg_match( '#\( ?array ?\)#', $lines[$key] ) &&
				!preg_match( '#\( ?float ?\)#', $lines[$key] ) &&
				!preg_match( '#\( ?bool ?\)#', $lines[$key] ) &&
				!preg_match( '#intval#', $lines[$key] ) &&
				!preg_match( '#varSanitizer#', $lines[$key] ) &&
				!preg_match( '#filter_var#', $lines[$key] ) ) {
				  $string_post_unsecured[$file_url][$key + 1] = htmlentities( $lines[$key] );
				  $total_unsecured_line++;
			  }

			  if ( preg_match( '#(\$_POST|\$_GET|\$_REQUEST)\[\'.+\'\].+?\=#isU', $lines[$key] ) &&
        !preg_match( '#\* @#', $lines[$key] ) &&
        !preg_match( '#\\/\/#', $lines[$key] ) &&
        !preg_match( '#\*#', $lines[$key] ) ) {
  				$string_post_unsecured[$file_url][$key + 1] = htmlentities( $lines[$key] );
  				$total_unsecured_line++;
			  }
			}
		}
	}
  }
}

echo "[+] Total unsecured line : " . $total_unsecured_line . PHP_EOL . '<br />';

if ( !empty( $string_post_unsecured ) ) {
  foreach ( $string_post_unsecured as $file_url => $file ) {
    if ( !empty( $file ) ) {
      echo "[+] File : " . $file_url . ' => Unsecured $_POST|$_GET|$_REQUEST ' . count( $file ) . PHP_EOL . '<br />';
      foreach ( $file as $line => $content ) {
        $color = "black";
        if ( preg_match( '#\$_POST#', trim($content) ) ) {
          $color = "#ea6153";
        }
        else if( preg_match( '#\$_GET#', trim($content) ) ) {
          $color = "#3498db";
        }
        else if( preg_match( '#\$_REQUEST#', trim($content) ) ) {
          $color = "#2ecc71";
        }
        else if( preg_match( '#\$_SESSION#', trim($content) ) ) {
          $color = "#f1c40f";
        }

        echo "[+] <span style='color: " . $color . "'>Line : " . $line . " => " . trim($content) . PHP_EOL . '</span><br />';
      }
    }
  }
}

if ( $total_unsecured_line != 0 )
  trigger_error( "[+] Total unsecured line : " . $total_unsecured_line, E_USER_ERROR );

echo "[+] Request Tests Finished" . PHP_EOL;
