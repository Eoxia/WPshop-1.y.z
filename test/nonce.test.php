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
    preg_match_all( '#function ajax_.+\{.+\}#isU', htmlentities($file), $matches );

    if ( !empty( $matches ) ) {
      foreach( $matches[0] as $matched_string ) {
        if ( !empty( $matched_string ) && !empty( $matched_string ) ) {
          if ( !preg_match( '#wp_verify_nonce#', $matched_string ) ) {
            preg_match( '#function(.+)\(#', $matched_string, $fonction_name );
            if ( !empty( $fonction_name ) && !empty( $fonction_name[1] ) ) {
              $string_post_unsecured[$test][$fonction_name[1]] = "wp_security_nonce() not found";
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
        echo "[+] Not found wp_verify_nonce on : " . $fonction_name . PHP_EOL . '<br />';
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
