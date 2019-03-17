<?php

// Post files selected in aws_upload.php to AWS EC2 
// 17-Mar-2018, Zarro (ADNET) - written                         
// 31-Oct-2018, Zarro (ADNET) - added check for previous uploading  
//  5-Feb-2019, Zarro (ADNET) - added check for staged file   

// Check if POST 

require_once './aws_lib.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
 $mess="Invalid request";
 json_print($mess);
 exit;
}
 
$pattern="/\.(pdf|gif|jpe?g|pptx?|docx?|bmp|mpe?g4?|png|txt|dat|tiff?|xlsx?|sav)$/i";
$top_dir=aws_top();
$pi_name=""; 
$seed="";
$ptype="0";
$roses_id="";
$maxsize="200000000";
$clobber="0";
$index="0";
$list="";


if (isset($_GET['processed'])) $processed=trim($_GET['processed']);
if (isset($_POST['pi_name'])) $pi_name=trim($_POST['pi_name']);
if (isset($_POST['seed'])) $seed=trim($_POST['seed']);
if (isset($_POST['ptype'])) $ptype=trim($_POST['ptype']);
if (isset($_POST['roses_id'])) $roses_id=trim($_POST['roses_id']);
if (isset($_POST['maxsize'])) $maxsize=trim($_POST['maxsize']);
if (isset($_POST['clobber'])) $clobber=trim($_POST['clobber']);
if (isset($_POST['pattern'])) $pattern=trim($_POST['pattern']);
if (isset($_POST['index'])) $index=trim($_POST['index']);
if (isset($_POST['list'])) $list=trim($_POST['list']);

$roses_id = filter_var($roses_id, FILTER_SANITIZE_STRING);
$pi_name = filter_var($pi_name, FILTER_SANITIZE_STRING);
$ptype = filter_var($ptype, FILTER_SANITIZE_STRING);
$seed=filter_var($seed, FILTER_SANITIZE_STRING);
$index=filter_var($index, FILTER_SANITIZE_STRING);
$clobber=filter_var($clobber, FILTER_SANITIZE_STRING);
$list=filter_var($list, FILTER_SANITIZE_STRING);

if (is_numeric($maxsize)) $fmaxsize=floatval($maxsize);
	
if (!valid_str($pi_name) || !valid_str($roses_id)){ 
 $mess="Error uploading - valid ROSES ID and PI Name required";
 json_print($mess);
 exit;
}
 
// Bail if something went wrong or no files selected for upload

if (!isset($_FILES["files"])){
 $mess="Error uploading files - check server configuration";
 json_print($mess);
 exit;
}
 
$total = count($_FILES['files']['name']);
if ($total === 0) {
 $mess="No file(s) selected";
 json_print($mess);
 exit;
}

// Create subdirectory based on ROSES and PI ID's

$dirs=aws_dirs($roses_id,$pi_name);
$udir=$dirs['uploaded'];
$sdir=$dirs['staged'];

if (!is_dir($udir)) {
 $old=umask(0);
 $succ=@mkdir($udir,0770,true);
 umask($old);
}

if (!is_dir($udir)) {
 $mess="Failed to create upload directory";
 json_print($mess);
 exit;   
}

// Examine seed file for uploaded file names in current session

$c_ufiles=0;
if (valid_str($seed)) {
 $temp_dir=sys_get_temp_dir();
 $sdfile=$temp_dir."/s_".$seed;
 if (is_file($sdfile)) {
  $ufiles = file($sdfile,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
  $ufiles =array_unique($ufiles);
  $c_ufiles=count($ufiles);
 }
}
 
// Examine uploaded file names passed from client

$lfiles=[];
if (valid_str($list)) {
 $lfiles=explode(',',$list);
 $lfiles = array_map('trim', $lfiles);
}
$c_lfiles=count($lfiles);
	
$fmess=[];
$rmess=[];
for($i=0; $i<$total; $i++) {
	
 $file =$_FILES["files"]["name"][$i];
 $rfile=$_FILES["files"]["tmp_name"][$i];
 $rsize=$_FILES["files"]["size"][$i];
 $rtype=$_FILES["files"]["type"][$i];

// Check for error codes

 $rerror=$_FILES["files"]["error"][$i]; 
 
 if ($rerror === 1) {
  $omess="Exceeds maximum total file upload size"; 
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
 if ($rerror === 2 || $rsize > $fmaxsize) {
  $omess="Exceeds maximum individual file upload size ($maxsize bytes)"; 
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
 if (!valid_str($file)) {
  $omess="Blank file name";
  $amess=['file' => 'blank', 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }

 if (!valid_str($rfile)) {
  $omess="Blank uploaded file name";
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
 if (!is_file($rfile)) {
  $omess="Not a regular file type";
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
// Check for supported file type

 if (!preg_match($pattern, $file)) {
  $omess="Unsupported file type";
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
 if ($rsize === 0) {
  $omess="Zero file size";
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
 if ($rerror === 3) {
  $omess="Partially uploaded"; 
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
 if ($rerror === 4) {
  $omess="Not selected for upload"; 
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
  if ($rerror !== 0) {
  $serror=(string)$rerror;
  $omess="Unknown upload error ($serror)";
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
  
// Replace all spaces by _  

 $ofile = preg_replace('/\s+/', '_', trim($file));
 $ofile=$index."_".$ptype."_".$ofile;
 $ufile=$udir.$ofile;
 $sfile=$sdir.$ofile;
 $lname=$roses_id."/".$pi_name."/".$ofile;
 
// Check seed file if file uploaded in current session

 $uploaded=false;
 if ($clobber == "0") { 
  if ($c_lfiles > 0) $uploaded=in_array($ofile,$lfiles);
  if (!$uploaded && $c_ufiles > 0) $uploaded=in_array($lname,$ufiles);
 }
 
 if ($uploaded) {
  $omess="Uploaded previously";
  $amess=['file' => $file, 'result' => $omess];
  array_push($rmess,$amess);
  continue;
 }
 
// Move file if not already uploaded	
 
 rename($rfile,$ufile);
 if (is_file($ufile)) {
  $omess= "Uploaded successfully";
  chmod($ufile,0770);
  if (is_file($sfile)) unlink($sfile);
  array_push($fmess,$lname."\n");
 } else {
  $omess="Upload failed";
 } 
 $amess=['file' => $file, 'result' => $omess];
 array_push($rmess,$amess);
}

// Clean up temp files

for($i=0; $i<$total; $i++) {
 $rfile=$_FILES["files"]["tmp_name"][$i];
 if (is_file($rfile)) {unlink($rfile);}
}

// Write names of uploaded files to file named "seed" in temporary directory

if (!empty($fmess) && valid_str($seed)) {
 file_put_contents($sdfile, $fmess, FILE_APPEND | LOCK_EX);
 chmod($sdfile,0770);
}

// Return results to upload form

json_print($rmess);
exit;
?>
