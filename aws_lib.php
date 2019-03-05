<?php
      
// AWS home directory

function aws_top() {
 return "/home/ubuntu/lwstrt/";
}

function aws_limit() {
 return 48.*3600.;

}

//////////////////////////////////////////////////////////////////////////
// Check $input is a valid non-blank string

function valid_str($input) {

 if (!isset($input)) return false;
 if (!is_string($input)) return false;
 if (preg_match('/\S/', $input)) return true;
 return false;

}

///////////////////////////////////////////////////////////////////////////
// Print $input as JSON-encoded string with optional callback

function json_print($input,$callback="") {

 $output=json_encode($input);
 if (valid_str($callback)) $output=$callback.'(' . $output . ')';
 cors();
 echo $output;
 
}

///////////////////////////////////////////////////////////////////////////
// AWS upload directories

function aws_dirs($roses_id,$pi_name) {
 if (!valid_str($roses_id) || !valid_str($pi_name)) return [];
 $lws_dir="/".$roses_id."/".$pi_name."/";
 $top_dir=aws_top();
 $upload_dir=$top_dir."uploaded".$lws_dir;
 $confirm_dir=$top_dir."confirmed".$lws_dir;
 $vet_dir=$top_dir."vetted".$lws_dir;
 $del_dir=$top_dir."deleted".$lws_dir; 
 $stage_dir=$top_dir."staged".$lws_dir;
 
 $dirs=array('uploaded' => $upload_dir, 'confirmed' => $confirm_dir, 'vetted' => $vet_dir, 'deleted' => $del_dir, 'staged' => $stage_dir);	
 return $dirs;
}

///////////////////////////////////////////////////////////////////////////
// AWS status of uploaded file
// 5-Feb-2019, Zarro (ADNET) - added checks for expired and staged files

function aws_status($file,$roses_id,$pi_name) {	
 $result=['message' => 'File not found', 'status' => 'FILENOTFOUND'];
 $dirs=aws_dirs($roses_id,$pi_name);
 $ufile=$dirs['uploaded'].$file;
 $cfile=$dirs['confirmed'].$file;
 $vfile=$dirs['vetted'].$file;
 $dfile=$dirs['deleted'].$file;
 $sfile=$dirs['staged'].$file;
 $values=array('Uploaded file awaiting confirmation by PI','Uploaded file awaiting vetting by moderator','File has been vetted','Uploaded file date has expired','File has been deleted','File staged for deletion');
 $status = array('UPLOADED','CONFIRMED','VETTED','EXPIRED','DELETED','STAGED');
 $utime=0.; 
 $vtime=0.;
 $ctime=0.;
 $dtime=0.;
 $stime=0.;
 $limit=aws_limit();
 if (is_file($ufile)) $utime=filemtime($ufile);
 if (is_file($vfile)) $vtime=filemtime($vfile);
 if (is_file($cfile)) $ctime=filemtime($cfile); 
 if (is_file($dfile)) $dtime=filemtime($dfile); 
 if (is_file($sfile)) $stime=filemtime($sfile);
 $now=time();
 $udiff=$now-$utime;
 $sdiff=$now-$stime;
 
// if uploaded file newer that confirmed and vetted file (and not older than 48 hours) then use it

 if ($utime > 0 && $udiff < $limit) { 
  if ($utime > $ctime && $utime > $vtime && $utime > $stime) {
   $result=['message' => $values[0], 'status' => $status[0]];
   return $result;
  }
 }
 
// if file recently staged for deletion within last 48 hours, then use it 

 if ($stime > 0 && $sdiff < $limit) { 
  if ($stime > $ctime && $stime > $vtime) {
   $result=['message' => $values[5], 'status' => $status[5]];
   return $result;
  }
 }
  
// if confirmed file newer than vetted then use it

 if ($ctime > 0. && $ctime > $vtime) {
 $result=['message' => $values[1], 'status' => $status[1]];
 return $result;
 }
 
// if vetted then use it

 if ($vtime > 0.) {
  $result=['message' => $values[2], 'status' => $status[2]];
  return $result;
 } 
	
// check if expired

if ($utime > 0 && $udiff >= $limit) {
  $result=['message' => $values[3], 'status' => $status[3]];
  return $result;
}

// check if deleted

 if ($dtime > 0.) {
  $result=['message' => $values[4], 'status' => $status[4]];
  return $result;
 } 

 return $result;
}


/*
* CORS
* written by: Kevin Addison
* date: 2018-10-24
*/
function cors() {
	// Allow from any origin
	if (isset($_SERVER['HTTP_ORIGIN'])) {
        	// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        	// you want to allow, and if so:
        	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        	header('Access-Control-Allow-Credentials: true');
        	header('Access-Control-Max-Age: 86400');    // cache for 1 day
	}

	// Access-Control headers are received during OPTIONS requests
	if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        		// may also be using PUT, PATCH, HEAD etc
        		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		}

		if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
		}
	}
}


///////////////////////////////////////////////////////////////////////////
// Validate URL

function valid_url($url){
 $headers = @get_headers($url);
 preg_match("/ [4|5][0-9]{2} /", (string)$headers[0] , $match);
 return count($match) === 0;
}
     
////////////////////////////////////////////////////////////////////////////////////
// PHP alert function

function php_alert($message) {
 if (!is_string($message)) exit;
 if (empty($message)) exit;
 echo "<script language = 'javascript'>alert('$message');</script>";
}

////////////////////////////////////////////////////////////////////////////////////
// PHP dialog function

function php_dialog($message,$modal=false,$timeout=0) {
 if (!is_string($message)) exit;
 if (empty($message)) exit;
 $dmodal='false';
 if ($modal) $dmodal='true';
 echo <<<_END

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>

$(document).ready(function () {
	
	var height= Math.min( $(parent).height(), 400);
    var width=Math.min( $(parent).width(), 800);
	
	$("#mydiv").height = 800;
	$("#mydiv").width = 400;
    $("#mydiv").dialog({
	 modal:$dmodal
    });		
    $("#mydiv").html("$message");
	//setTimeout(function(){ alert("Hello"); }, 5000);
	//alert('hello');
	timeout="$timeout";
	if (timeout > 0) setTimeout(function(){ $("#mydiv").dialog("close"); }, "$timeout");
	 
});

</script>

</head>
<body>

<div id="mydiv" style="display:none;"></div>

</body>
</html>
	   
_END;

}

////////////////////////////////////////////////////////////////////////////////////
// PHP echo string array

function php_echo($mess) {
 if (!empty($mess)) {
  if (is_scalar($mess)) {
   echo $mess;
  } else {
   $nc=count($mess); 
   for($i=0; $i<$nc; $i++) {
    echo $mess[$i]."<br>";
   }
  }
 }
}

///////////////////////////////////////////////////////////////////////////////////
// Standardize PI name

function aws_parse($pi_name) {
 if (!valid_str($pi_name)) return '';	
 $name = preg_replace('/\s+/','',strtolower($pi_name));
 $name=str_replace(',','_',$name);
 $name=str_replace('.','',$name);
 return $name;
}

////////////////////////////////////////////////////////////////////////////////////
// PHP download

function php_download(&$file,&$result) {
 $result=['message' => '','status' => 0];
 if (!valid_str($file)) { 
  $result['message']='File name required.';
  return false;
  }

 if (!is_file($file)) {
  $result['message']='File not found.';
  return false;
 } 
 
 $filename=basename($file);
 $fsize=@filesize($file);  
 header('Content-Description: File Transfer');
 header('Content-Type: application/octet-stream');
 header("Content-Disposition: attachment; filename=\"$filename\"");
 header('Expires: -1');
 header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
 header('Pragma: public');
 if ($fsize !== 0 && $fsize !== false) header('Content-Length: ' .$fsize);
 ob_clean();
 flush();
 readfile($file);
 exit(0);
}

////////////////////////////////////////////////////////////////////////////////////
// Validate inputs

function aws_check(&$file,&$roses_id,&$pi_name,&$result,$method='GET') {

 $result=['message' => '','status'=> 0];
 if ($_SERVER['REQUEST_METHOD'] != $method) {
  $result['message'] = 'Invalid request';
  return false;	
 }

 $file="";
 if (isset($_REQUEST['file'])) {
  $file=trim($_REQUEST['file']);
  $file = filter_var($file, FILTER_SANITIZE_STRING);
 }

 if (!valid_str($file) ) { 
  $result['message']='File name required';
  return false;
 }

 $roses_id="";
 $pi_name="";
 if (isset($_REQUEST['pi_name'])) {$pi_name=trim($_REQUEST['pi_name']);}
 if (isset($_REQUEST['roses_id'])) {$roses_id=trim($_REQUEST['roses_id']);}
 $roses_id = filter_var($roses_id, FILTER_SANITIZE_STRING);
 $pi_name = filter_var($pi_name, FILTER_SANITIZE_STRING);
 

 if (!valid_str($roses_id)){ 
  $result['message']='ROSES ID required';
  return false;
 }

 if (!valid_str($pi_name)){ 
  $result['message']='PI name required';
  return false;
 }

// Parse PI name
 
 $pi_name=aws_parse($pi_name);
 $result['message'] = 'Valid input';
 $result['status']=1;
 
 return true;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Execute URL in HTML object

function php_ajax($url) {
	
 if (!is_string($url)) {
  return false;
 } 
 
 
 echo <<<_END
<!DOCTYPE html>
<html>
<head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>

$(document).ready(function () {
 

 $.ajax({
			  
              url : "$url",
			  cache: false,
      //        dataType:"jsonp",
	  //        jsonp:"callback",
	          success:function(data) {
			   $('#content').html('Success');
			  }, 
			  
			  error: function (xhr, ajaxOptions, thrownError) {
               var errorMsg = 'Ajax request failed: ' + xhr.responseText;
               $('#content').html(errorMsg);
              }
             });
	
 });

</script>

</head>

<body>
<div id="content"></div>
</body>

</html>
	   
_END;

return true;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Download file using Ajax

function ajax_download($file) {
	
 if (!valid_str($file)) {
  return false;
 } 
 if (!valid_url($file)) {
  php_dialog('Invalid file URL.');
  return false;
 }
 
 $bname=basename($file);
 
 echo <<<_END
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>
$(document).ready(function () {
    $("#mydiv").height = 800;
    $("#mydiv").width = 400;
    $("#mydiv").dialog({
     modal:true
    });		
    $("#mydiv").text("Downloading...");
	 
    $.ajax({
        url: "$file",
        method: 'GET',
        xhrFields: { responseType: 'blob' },
        success: function (data) {
            var a = document.createElement('a');
            var url = window.URL.createObjectURL(data);
            a.href = url;
            a.download = "$bname";
            a.click();
            window.URL.revokeObjectURL(url);
		    setTimeout(function(){ $("#mydiv").dialog("close"); }, 3000);
        }
	});
});

</script>
</head>
<body>
<div id="mydiv" style="display:none;"></div>
</body>
</html>
	   
_END;

return true;
}


?>
