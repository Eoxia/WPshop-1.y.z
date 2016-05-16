<?php
// include wp emulation
include('wp-emulator.script.php');

define('END_TEST', "/^.*\.test\.php$/");

echo "[+] Starting Unit Tests" . PHP_EOL . PHP_EOL;

// Search for test files
$unitList = searchFiles('.' . DIRECTORY_SEPARATOR, END_TEST);

// Loop on unitList
foreach($unitList as $test)
{
	echo "[+] Testing -> " . $test . PHP_EOL;
	include($test);
}

echo "[+] Unit Tests Finished" . PHP_EOL;

/* Recursively search files
	folder = string => where to search
	patter = string => regexp for what to search
*/
function searchFiles($folder, $pattern)
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
?>
