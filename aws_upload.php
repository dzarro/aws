<?php

// Form interface to aws_post.php
// 17-Mar-2018, Zarro (ADNET) - written 

// Gather and sanitize client inputs

require_once './aws_lib.php';

$pi_name="";
if (isset($_REQUEST['pi_name'])) $pi_name=trim($_REQUEST['pi_name']);
$seed="";
if (isset($_REQUEST['seed'])) $seed=trim($_REQUEST['seed']);
$ptype="0";
if (isset($_REQUEST['ptype'])) $ptype=trim($_REQUEST['ptype']);
$roses_id="";
if (isset($_REQUEST['roses_id'])) $roses_id=trim($_REQUEST['roses_id']);
$index="0";
if (isset($_REQUEST['index'])) $index=trim($_REQUEST['index']);
$clobber="0";
if (isset($_REQUEST['clobber'])) $clobber=trim($_REQUEST['clobber']);
$list="";
if (isset($_REQUEST['list'])) $list=trim($_REQUEST['list']);

$roses_id = filter_var($roses_id, FILTER_SANITIZE_STRING);
$pi_name = filter_var($pi_name, FILTER_SANITIZE_STRING);
$pi_name=aws_parse($pi_name);
$seed=filter_var($seed, FILTER_SANITIZE_STRING);
$index=filter_var($index, FILTER_SANITIZE_STRING);
$clobber=filter_var($clobber, FILTER_SANITIZE_STRING);
$list=filter_var($list, FILTER_SANITIZE_STRING);
$maxsize="200000000";

// $pytpe=0 = > files supporting publication
// $ptype=1 = > presentation file
// $ptype=2 = > files supporting presentation
// $ptype=3 = > files supporting project

$ptype = filter_var($ptype, FILTER_SANITIZE_STRING);
$labels=['Upload supplementary files for this publication','Upload presentation file','Upload supplementary files for this presentation','Upload supplementary files for this project'];
$label=$labels[intval($ptype)];

$input='<input type="file" id="files" name="files[]" multiple="multiple" />';
if ($ptype === "1") {
 $input='<input type="file" id="files" name="files[]"/>';
}

// spit out upload form

echo <<<_END

<!DOCTYPE html>
<html>
    <head>
	<meta charset="UTF-8">
<style type="text/css">

    body {
	 background-color: #fff;
	 margin: 20px;
	 font: 13px/20px normal Helvetica, Arial, sans-serif;
	 color: #4F5155;
    }

    #body {
	 margin: 0 10px 0 10px;
    }
	
	#container {
	 margin:0;
	 width:100%;
	 padding: 0;
    }

    fieldset {
	 border: none;
	 padding: 20px;
	 margin: 0;
    }

    legend {
	 font-size: 21px;
    }

    #listing {
	 margin-left: 20px;
    }

    #label {
	 font-size: 15px;
    }


    #upload {
	 color: #fff;
	 background-color: #337ab7;
	 border: 1px solid #2e6da4;
	 font-size: 13px;
	 cursor: pointer;
	 text-shadow: none;
	 height: 28px;
     border-radius: 4px;
    }
</style>
	
        <title>File Upload</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="./aws_lib.js"></script>
	
        <script>
		
$(document).ready(function (e) {
				 
                var nofiles = "<i>No files selected.</i>";	
				var roses_id="$roses_id" , seed="$seed", pi_name="$pi_name", ptype="$ptype" ,index="$index", maxsize="$maxsize",clobber="$clobber", list="$list";
			    var pattern=/\.(pdf|gif|jpe?g|pptx?|docx?|bmp|mpe?g4?|png|txt|tiff?|sav|xlsx?|dat)$/i;
	            var spattern=pattern.toString();
				
                $('#upload').on('click', function () {
                    var form_data = new FormData();
					var x =document.getElementById('files');
		//			var y = $.extend(true,{},x);
                    var ins = x.files.length;
					if (ins === 0) {
					 $('#console').html(nofiles);
					 $("#console").delay(100).fadeOut().fadeIn('slow');
					 return;
					}
					 
// upload checked files
				
					var checkboxes = document.getElementsByName('selection');
					var ufiles=[];
				    var flag=false;
                    for (var i = 0; i < ins; i++) {
				     if (checkboxes[i].checked) {
				      flag=true;
					  var file=x.files[i];
					  form_data.append("files[]",file); 
					  ufiles.push(file.name);
					 }
                    }
					
					if (!flag) {
					 $('#msg').html(nofiles);
					 $("#msg").delay(100).fadeOut().fadeIn('slow');
					 return;
					}
					
					form_data.append("pi_name",pi_name);
					form_data.append("ptype",ptype);
					form_data.append("seed",seed);
					form_data.append("index",index);
					form_data.append("roses_id",roses_id);
			    	form_data.append("maxsize",maxsize.toString());
			    	form_data.append("pattern",spattern);
					form_data.append("clobber",clobber);
     				form_data.append("list",list);
					
                    $.ajax({
                        url: 'aws_post.php', 
                        dataType: 'text', 
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: form_data,
                        method: 'post',
                        success: function (response) {
							var output=JSON.parse(response);
							var count=ufiles.length;
							var result="...Upload failed";
							if (typeof output == 'string') {
						     result="..."+output;
							}
							files=getFields(output,'file');
							results=getFields(output,'result');
							for (var i = 0; i < count; i++) {
							 var sindex = files.indexOf(ufiles[i]);
							 if (sindex > -1) {
							  result='...'+results[sindex];
							 }
							 uid=document.getElementById(ufiles[i]);
							 $(uid).html(result);
							 $(uid).delay(100).fadeOut().fadeIn('slow');
							}
							
							// display response from the PHP script
							
						    $("#msg").html("<i>Selected files uploaded.</i>"); 
						    $("#msg").delay(100).fadeOut().fadeIn('slow');
							aws_set(seed,index);
						
                        },
                        error: function (response) {
							alert('error');
                            $('#msg').html(response); // display error response from the PHP script
                        }
                    });
					
                });
	
// execute before new files are selected
	
   $('#files').on('click', function (event) {
	sdata=-1;
    $.ajax({
     async:false,
	 cache:false,
     url :'aws_get.php',
	 data: {seed:seed,index:index},
     dataType:"jsonp",
	 jsonp:"callback",
	 success:function(data) {	
	  sdata=parseFloat(data);
	  if (data != "0") {
	   var mess="There are "+data+" selected file(s) that have not yet been uploaded.\\nPress OK to continue without uploading, or Cancel to return to uploading selected files.";
       var ans=confirm(mess);
       if (ans == false) {
   	    event.preventDefault();
       }
	  }
	 }
    });

   });
    
// execute after new files are selected

   $('#files').on('change', function (event) {
	var txt = "", dsize=-1, fsize="";
	var checked="<input onclick='aws_set($seed,$index)' type='checkbox' name='selection' checked ";
	var disabled="<input onclick='aws_set($seed,$index)' type='checkbox' name='selection' disabled ";
	var toobig=" ...Exceeds maximum individual file upload size ("+maxsize.toString()+" bytes)";
	var toosmall=" ...Zero size file";
	var nofit=" ...Unsupported file type";
	
// extract selected file names

	var x = document.getElementById("files");	
	if (x.value == "") {txt = nofiles;}	
	if ('files' in x) {
	 var files=x.files;
	 if (files.length == 0) {txt = nofiles;}
	} else { 
	 txt = nofiles;
	}
	
    if (txt == nofiles ) { $('#label').html(""); }
	if (txt == "") {
	 $('#label').html("Selected Files. Uncheck to skip uploading:");
     for (var i = 0; i < files.length; i++) {
	  var label="";
      var file = files[i];
	  var button=checked;
      if ('name' in file) {		   
	   fname=file.name;
	   if ('size' in file) {
		dsize=file.size;
		fsize=" ("+dsize+" bytes)";
	   }	  
	   if (!fname.match(pattern)) { button=disabled; label=nofit;} 
	   if (dsize > maxsize) { button=disabled; label=toobig; }			
	   if (dsize == 0) { button=disabled; label=toosmall;}
	   output=button+'value="'+fname+'"/>'+fname+fsize+'<i id="'+fname+'"> '+label+' </i><div></div>';
	  }
      txt += output;
     }    
    }	 
    
	$('#console').html(txt);
	$('#msg').html("");
	
	aws_set(seed,index);
   });	

});
    
</script>
</head>
<body>
	<div id="container">
        <fieldset>
		<legend>$label</legend>
        $input
		</fieldset>
		<div id="listing">
		<p id="label"</p>
		<p id="console"></p>
		<p id="msg"></p>
		<footer>
        <button id="upload">Upload</button>
		</footer>
		</div>
	</div>
</body>
</html>

_END;

?>
