<?php
/**
* @author: Jimmy Latour lelabodudev@gmail.com
*/

echo "[+] Starting Nonce Tests" . PHP_EOL . PHP_EOL;

// Search for test files
$unitList = search_files('../', "/^.*\.php$/");
$string_post_unsecured = array();
$total_unsecured_line = 0;

// Loop on unitList
foreach($unitList as $test)
{
	// echo "[+] Testing -> " . $test . PHP_EOL;
if ( $test != '../test/request.test.php' ) {
	$string_post_unsecured[$test] = array();

    $file = file_get_contents( $test );
    preg_match_all( '#add_action\( *(\'|")wp_ajax_(nopriv_)?.+(\'|"),.+(\'|")(.+)(\'|").+\)#isU', $file, $matches );
	$matched_function = array();
	if ( !empty( $matches[5] ) ) {
		foreach ( $matches[5] as $matched_string ) {
			preg_match_all( '#(.+)?\', *(.+)?\'#isU', $matched_string, $exploded );
			if ( empty( $exploded[0] ) ) {
				$matched_string = str_replace( '(', '', $matched_string );
				$matched_string = str_replace( ')', '', $matched_string );
				$matched_string = trim($matched_string);
				preg_match( '#function *' . $matched_string . ' *\((.+)?\}#isU', $file, $function );

				if ( !empty( $function[0] ) ) {
					$function = $function[0];
					if ( !preg_match( '#wp_verify_nonce#', $function ) &&
					 	!preg_match( '#check_ajax_referer#', $function ) ) {
						$string_post_unsecured[$test][$matched_string] = "wp_security_nonce() or check_ajax_referer not found";
						$total_unsecured_line++;
					}
				}
			}
		}
	}

}
}

echo "[+] Total unsecured nonce : " . $total_unsecured_line . PHP_EOL . '<br />';

if ( !empty( $string_post_unsecured ) ) {
  foreach ( $string_post_unsecured as $name_file => $file ) {
    if ( !empty( $file ) ) {
      echo "[+] File : " . $name_file . ' => Unsecured nonce ' . count( $file ) . PHP_EOL . '<br />';
      foreach ( $file as $fonction_name => $content ) {
        echo "[+] <span style='color: #ea6153'>Not found wp_verify_nonce on : " . $fonction_name . PHP_EOL . '</span><br />';
      }
    }
  }
}

if ( $total_unsecured_line != 0 )
  trigger_error( "[+] Total unsecured nonce : " . $total_unsecured_line, E_USER_ERROR );

echo "[+] Nonce Tests Finished" . PHP_EOL;

function search_files($folder, $pattern)
{
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
