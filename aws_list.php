<?php

// List recently uploaded files on AWS server
// Written: Zarro (ADNET) 17-Oct-2017
// 17-Oct-2017, Zarro (ADNET) - written                         
// 31-Oct-2018, Zarro (ADNET) - added logic for INDEX in filename 

// Check if not GET

if ($_SERVER['REQUEST_METHOD'] != 'GET') exit;

require_once './aws_lib.php';

$callback ="";
if(isset($_GET['callback'])) $callback = trim($_GET['callback']); 
$seed="";
if (isset($_GET['seed'])) $seed=trim($_GET['seed']);
$clean="0";
if (isset($_GET['clean'])) $clean=trim($_GET['clean']); 
$ptype="-1";
if (isset($_GET['ptype'])) $ptype=trim($_GET['ptype']);
$index="-1";
if (isset($_GET['index'])) $index=trim($_GET['index']);

$seed=filter_var($seed, FILTER_SANITIZE_STRING);
$callback=filter_var($callback, FILTER_SANITIZE_STRING);

if (!valid_str($seed)) {
 $mess=["No seed entered."];
 json_print($mess,$callback=$callback);
 exit;
}

$temp_dir=sys_get_temp_dir();
$ofile=$temp_dir."/s_".$seed;

// Build associative array

$mess=[];
if(is_file($ofile)) {
 $fh = fopen($ofile,'r');
 while($line = fgets($fh)) {
  $fname=trim($line);
  if (!valid_str($fname)) continue;
  $bname=basename($fname);
  $pieces=explode('_',$bname);
  $findex=$pieces[0];
  $ftype=$pieces[1];	
  if ($ptype != '-1' && $ptype != $ftype) continue;
  if ($index != '-1' && $index != $findex) continue;
  
  if(!array_key_exists("$findex", $mess)) {
   $mess["$findex"]=[$bname];
   continue;
  } 
  
  $sname=$mess["$findex"];
  if(!in_array($bname,$sname)) {         
   array_push($sname,$bname);
   $mess["$findex"]=$sname;
  }
 }
 fclose($fh);
}
// Clean up temp files

if ($clean == "1") {	
 if (is_file($ofile)) unlink($ofile);
 $keys=array_keys($mess);
 $nkeys=count($keys);
 for($i=0; $i<$nkeys; $i++) {
  $dfile=$temp_dir."/d_".$keys[$i]."_".$seed;
  if (is_file($dfile)) unlink($dfile);
 }
}

// Convert to array object for Kevin

$arr=[];
$new=[];
foreach ($mess as $key => $value) {
 $arr['num']=intval($key);
 $arr['files']=$value;
 array_push($new,$arr);
}

json_print($new,$callback=$callback);

exit;

?>
