
// count # files pending AWS upload

  function aws_set(seed,index) {
	  
	 
	var checkboxes = document.getElementsByName('selection');
    var len = checkboxes.length;
	var nup=0;
	for (var i = 0; i < len; i++) {
	 if (checkboxes[i].checked) {
	  var value=checkboxes[i].value;
	  var uid=document.getElementById(value);
	  var uval = $(uid).text();
	  uval=uval.trim();
	  if (uval.length == 0) {
	   nup++;
	  }
	 }
	} 
	
// log number on server

    dseed='';
	dindex="0";
	if (typeof index == 'string') {dindex=index.trim();}
	if (typeof index == 'number') {dindex=index.toString();}
	if (typeof seed == 'string') {dseed=seed.trim();}
	if (typeof seed == 'number') {dseed=seed.toString();}
	 
	if (dseed.length > 0) {
     $.ajax({
      async:false,
	  cache: false,
	  method:'post',
      url :'aws_set.php',
	  data: {seed:dseed,index:dindex,value:nup.toString()},
	  error: function (response) { 
       console.log(response);
      }
	 });
	}
   }
   
   
// extract object property value as array

   function getFields(input, field) {
    var output = [];
    for (var i=0; i < input.length ; ++i)
        output.push(input[i][field]);
    return output;
  }
