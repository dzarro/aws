<?php

// Delete uploaded file 
// 25-Aug-2018, Zarro (ADNET) - written   

require_once './aws_lib.php';

$status=aws_check($file,$roses_id,$pi_name,$result,$method='POST');
if (!$status) {
 json_print($result);
 exit;		
}
$result['status']=0;
$result['message'] = 'File not found';
$dirs=aws_dirs($roses_id,$pi_name);
$ufile=$dirs['uploaded'].$file;
$cfile=$dirs['confirmed'].$file;
$vfile=$dirs['vetted'].$file;
$dfile=$dirs['deleted'].$file;
$sfile=$dirs['staged'].$file;

// Create backup directory for deleted file

$del_dir=$dirs['deleted'];
if (!is_dir($del_dir)) {
 $old=umask(0);
 $succ=@mkdir($del_dir,0770,true);
 umask($old);
}

if (!is_dir($del_dir)) {
 $result['message']='Failed to create backup directory';
 json_print($result);
 exit;		
} 

// Moved latest vetted, confirmed, or uploaded file to backup directory

if (is_file($vfile)) {
 rename($vfile,$dfile);
 if (is_file($cfile)) unlink($cfile);
 if (is_file($ufile)) unlink($ufile);
}

if (is_file($cfile)) {
 rename($cfile,$dfile);
 if (is_file($ufile)) unlink($ufile);
}

if (is_file($ufile)) rename($ufile,$dfile);
if (is_file($sfile)) unlink($sfile);
if (is_file($dfile)) {
 $result['message']='File deleted';
 $result['status']=1;
}

json_print($result);

exit;
 
?>

