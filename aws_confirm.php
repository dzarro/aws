<?php

// Move uploaded file to confirmed directory 
// 25-Aug-2018, Zarro (ADNET) - written   
//  5-Feb-2019, Zarro (ADNET) - added check for expired file
//  6-Feb-2019, Zarro (ADNET) - added STATUS key
// 17-Mar-2019, Zarro (ADNET) - merged DEV and PRO

require_once './aws_lib.php';

$status=aws_check($file,$roses_id,$pi_name,$result,$method='POST');
if (!$status) {
 json_print($result);
 exit;		
}

$result['status']=0;
$dirs=aws_dirs($roses_id,$pi_name);
$ufile=$dirs['uploaded'].$file;
$cfile=$dirs['confirmed'].$file;
$sfile=$dirs['staged'].$file;
$dfile=$dirs['deleted'].$file;

if (!is_file($ufile)) {
 if (is_file($cfile)) {
  $result['message']='Uploaded file already confirmed'; 
  $result['status']=1;
  json_print($result);
  exit;
 }
 if (is_file($dfile)) {
  $result['message']='Uploaded file was deleted'; 
  json_print($result);
  exit;
 }
 $result['message']='Uploaded file not found';
 json_print($result);
 exit;
}

// skip if older than 48 hours

$limit=aws_limit();
$diff=time()-filemtime($ufile);
if ($diff > $limit) {
 $result['message']='Uploaded file date has expired';
 unlink($ufile);
 json_print($result);
 exit;
}
	
$confirm_dir=$dirs['confirmed'];
if (!is_dir($confirm_dir)) {
 $old=umask(0);
 $succ=@mkdir($confirm_dir,0770,true);
 umask($old);
}

if (!is_dir($confirm_dir)) {
 $result['message']='Failed to create confirmed directory';
 json_print($result);
 exit;   
}

// create vetted directory

$vet_dir=$dirs['vetted'];
if (!is_dir($vet_dir)) {
 $old=umask(0);
 $succ=@mkdir($vet_dir,0770,true);
 umask($old);
}

// move uploaded file to confirmed directory

rename($ufile,$cfile); 
if (!is_file($cfile)) {	
 $result['message'] = 'Failed to move uploaded file to confirmed directory';
 json_print($result);
 exit;
} 

if (is_file($sfile)) unlink($sfile);
$result['message']='Uploaded file confirmed';
$result['status']=1;
json_print($result);
exit;

?>

