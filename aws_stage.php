<?php

// Stage file for deletion 
// 25-Dec-2018, Zarro (ADNET) - written   
//  6-Feb-2019, Zarro (ADNET) - added STATUS key

require_once './aws_lib.php';

$status=aws_check($file,$roses_id,$pi_name,$result);
if (!$status) {
 json_print($result);
 exit;		
}

$result['status']=0;
$result['message'] = 'File not staged';
$dirs=aws_dirs($roses_id,$pi_name);
$stage_dir=$dirs['staged'];
$sfile=$stage_dir.$file;

if (!is_dir($stage_dir)) {
 $old=umask(0);
 $succ=@mkdir($stage_dir,0770,true);
 umask($old);
}

touch($sfile);
if (is_file($sfile)) {
 $result['message']='File staged for deletion';
 $result['status']=1;
}

json_print($result);

exit;
 
?>

