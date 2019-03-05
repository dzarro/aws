<?php

// Return status of uploaded file as JSON string 
// 25-Aug-2018, Zarro (ADNET) - written 

require_once './aws_lib.php';

// Check if CALLBACK is passed (e.g. if called from Javascript/Ajax)

$callback ='';
if(isset($_GET['callback'])) {
 $callback = trim($_GET['callback']);
 $callback=filter_var($callback, FILTER_SANITIZE_STRING);
}

// Find location of file

$check=aws_check($file,$roses_id,$pi_name,$result);
if ($check) $result=aws_status($file,$roses_id,$pi_name);

// Output result as JSON

json_print($result,$callback=$callback); 

?>
