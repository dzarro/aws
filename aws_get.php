<?php

// Get value of checked files that are pending upload 
// Written: Zarro (ADNET) 17-Mar-2018

require_once './aws_lib.php';

$callback ='';
if(isset($_GET['callback'])) $callback = trim($_GET['callback']); 
$seed="";
if (isset($_GET['seed'])) $seed=trim($_GET['seed']);
$index="0";
if (isset($_GET['index'])) $index=trim($_GET['index']); 

$seed=filter_var($seed, FILTER_SANITIZE_STRING);
$callback=filter_var($callback, FILTER_SANITIZE_STRING);
$index=filter_var($index, FILTER_SANITIZE_STRING);

// Read number of pending uploaded files from seed file
// If no seed file, assume no pending uploads

$value="0";
if (!empty($seed)) {
 $temp_dir=sys_get_temp_dir();
 $ofile=$temp_dir."/d_".$index."_".$seed;
 if (is_file($ofile)) {
  $value=file_get_contents($ofile);
 }
}
	
json_print($value,$callback=$callback);

exit;

?>
