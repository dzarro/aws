<?php

// Set value of checked files that are pending upload 
// Written: Zarro (ADNET) 17-Mar-2018

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
 exit;
}

$seed="";
$value="0";
$index="0";
if (isset($_POST['seed'])) {$seed=trim($_POST['seed']);}
if (isset($_POST['index'])) {$index=trim($_POST['index']);}
if (isset($_POST['value'])) {$value=trim($_POST['value']);}

$value = filter_var($value, FILTER_SANITIZE_STRING);
$seed=filter_var($seed, FILTER_SANITIZE_STRING);
$index=filter_var($index, FILTER_SANITIZE_STRING);

if ( empty($seed) || !is_numeric($value) ) {exit;}

// Write number of pending uploaded files to file named "seed" in temporary directory

$temp_dir=sys_get_temp_dir();
$ofile=$temp_dir."/d_".$index."_".$seed;
file_put_contents($ofile, $value);
chmod($ofile,0770);

exit;
?>
