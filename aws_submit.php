<?php

// Parse JSON string of latest submitted edits
// 3-Feb-2019, Zarro (ADNET) - written  

require_once './aws_lib.php';

$out=['message' => '','status' => 0];
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
 $out['message']='Invalid request';
 json_print($out);
 exit;
}

$edits="";
if (isset($_REQUEST['edits'])) $edits=trim($_REQUEST['edits']);
$edits = filter_var($edits, FILTER_SANITIZE_STRING);

$out['message']='Invalid JSON';
if (!valid_str($edits)) {
 json_print($out);
 exit;
 
}

$json=json_decode($edits);
if (!$json) {
 json_print($out);
} else {
 json_print($json);
}

exit;

?>