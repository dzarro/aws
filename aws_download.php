<?php

// Download vetted file if it exists
// 25-Aug-2018, Zarro (ADNET) - written  

require_once './aws_lib.php';

$check=aws_check($file,$roses_id,$pi_name,$result);

if (!$check) {
 json_print($result);
 exit;
}
// Download file if vetted
	
$dirs=aws_dirs($roses_id,$pi_name);
$vfile=$dirs['vetted'].$file;

if (!is_file($vfile)) {
 $result['message']='File not yet vetted';	
 json_print($result);
 exit;
}

$status=php_download($vfile,$result);
if (!$status) json_print($result);

exit;

?>