<?php 
$allowed = "Dev";
include "verify.php";
?>



<?php
/**
* Multi file upload example
* @author Resalat Haque
* @link http://www.w3bees.com/2013/02/multiple-file-upload-with-php.html
**/
$valid_formats = array("jpg", "png");
$max_file_size = 1125*2436; //3 mb
$path = "screens/"; // Upload directory
$count = 0;
if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
	// Loop $_FILES to execute all files
	foreach ($_FILES['files']['name'] as $f => $name) {
	  if ($_FILES['files']['error'][$f] == 4) {
      continue; // Skip file if any error found
		}		   
		if ($_FILES['files']['error'][$f] == 0) {			   
		  if ($_FILES['files']['size'][$f] > $max_file_size) {
				$message[] = "$name is too large!.";
				continue; // Skip large files
			}
			elseif( ! in_array(pathinfo($name, PATHINFO_EXTENSION), $valid_formats) ){
				$message[] = "$name is not a valid format";
				continue; // Skip invalid file formats
			}
			else{ // No error found! Move uploaded files 
				if(move_uploaded_file($_FILES["files"]["tmp_name"][$f], $path.$_SESSION['userid'].'_'.$name)) {
					$count++; // Number of successfully uploaded files
				}
			}
		}
	}
}
?>

<style type="text/css">
.wrap{
	margin: 15px auto;
	padding: 20px 25px;
	border: 1px solid #DBDBDB;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	overflow: hidden;
	text-align: center;
}
.status{
	/*display: none;*/
	padding: 8px 35px 8px 14px;
	margin: 20px 0;
	text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);

	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
}

input[type="submit"] {
	cursor:pointer;
	font-weight: bold;
	margin: 20px 0;
	padding: 10px;
	border-radius:5px;
}

input[type="submit"]:hover {
	-webkit-transition:background 0.3s ease-in-out;
	-moz-transition:background 0.3s ease-in-out;
	transition:background-color 0.3s ease-in-out;
}

input[type="submit"]:active {
	box-shadow:inset 0 1px 3px rgba(0,0,0,0.5);
}

.btn-file {
    position: relative;
    overflow: hidden;
}
.btn-file input[type=file] {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 100%;
    min-height: 100%;
    font-size: 100px;
    text-align: right;
    filter: alpha(opacity=0);
    opacity: 0;
    outline: none;
    background: white;
    cursor: inherit;
    display: block;
}

.inputfile {
	width: 0.1px;
	height: 0.1px;
	opacity: 0;
	overflow: hidden;
	position: absolute;
	z-index: -1;
}


.inputfile + label {
    font-size: 1.25em;
    font-weight: 700;
    color: white;
    background-color: grey;
    display: inline-block;
}

.inputfile:focus + label,
.inputfile + label:hover {
    background-color: white;
}


.js .inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
}

.inputfile + label {
    max-width: 80%;
    font-size: 1.25rem;
    /* 20px */
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
    display: inline-block;
    overflow: hidden;
    padding: 0.625rem 1.25rem;
    /* 10px 20px */
}

.no-js .inputfile + label {
    display: none;
}

.inputfile:focus + label,
.inputfile.has-focus + label {
    outline: 1px dotted #000;
    outline: -webkit-focus-ring-color auto 5px;
}

.inputfile + label * {
    /* pointer-events: none; */
    /* in case of FastClick lib use */
}

.inputfile + label svg {
    width: 1em;
    height: 1em;
    vertical-align: middle;
    fill: currentColor;
    margin-top: -0.25em;
    /* 4px */
    margin-right: 0.25em;
    /* 4px */
}



/* style */

.inputfile-6 + label {
    color: #000000;
}

.inputfile-6 + label {
    background-color: #ffffff;
    padding: 0;
}

	
.inputfile-6:focus + label,
.inputfile-6.has-focus + label,
.inputfile-6 + label:hover {
    border-color: #DBDBDB;
}

.inputfile-6 + label strong:hover {
    background-image: linear-gradient(#000000, #3a3f44 60%, #313539);
}



.inputfile-6 + label span,
.inputfile-6 + label strong {
    padding: 0.625rem 1.25rem;
    /* 10px 20px */
}

.inputfile-6 + label span {
    width: 200px;
    min-height: 2em;
    display: inline-block;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    vertical-align: top;
}

.inputfile-6 + label strong {
    height: 100%;
	
    color: #ffffff;
    background-color: #d3394c;
	
    display: inline-block;
	
	background-image: linear-gradient(#484e55, #3a3f44 60%, #313539);
    background-repeat: no-repeat;
}

.inputfile-6:focus + label strong,
.inputfile-6.has-focus + label strong,
.inputfile-6 + label:hover strong {
    background-color: #722040;
}

@media screen and (max-width: 50em) {
	.inputfile-6 + label strong {
		display: block;
	}
}

</style>
	<div class="wrap">
		
		<?php
		if($config->apiprovider == 'google'){
			gglapicounter ('read', 'inc/counter.txt',1000,900);
		}
		# error messages
		if (isset($message)) {
			foreach ($message as $msg) {
				printf("<p class='status'>%s</p></ br>\n", $msg);
			}
		}
		# success message
		if($count !=0){
			printf("<p class='status'>%d Dateien erfolgreich hochgeladen!</p>\n", $count);
			echo '<form action="" method="GET" autocomplete="no" id="convertIt">	  
			  <div class="form-group text-center">
			  <input type="hidden" name="bigfile" id="bigfile" value="0" />
			  <input type="hidden" name="action" value="prepimg" />
			   <button type="submit" name="b1" class="btn btn-success">Bilder konvertieren</button>';
			if($count>1&&$count<28)
			  echo '&nbsp;&nbsp;&nbsp;<button type="button" name="b2" class="btn btn-success" onclick="document.getElementById(\'bigfile\').value=1; document.getElementById(\'convertIt\').submit();">Bilder mit BigFile konvertieren</button>';
			echo '
			  </div>
			</form>';
		}
		?>
		<p>Maximale Dateigröße 3MB, Erlaubt: jpg, png</p>
		<div class="alert alert-warning"></div>
		<br />
		<!-- Multiple file upload html form-->
		<form action="" method="post" enctype="multipart/form-data">
  
				<div class="box">
					<input type="file" name="files[]" id="file-7" class="inputfile inputfile-6" data-multiple-caption="{count} Dateien gewählt" multiple="multiple" accept="image/*" />
					<label for="file-7"><span></span> <strong> Durchsuchen...</strong></label>
				</div>

			<input type="submit" value="Upload" class="btn btn-success btn-block">
		</form>
		
		
				

</div>
<script>
$(function() {
  var // Define maximum number of files.
      max_file_number = <?=ini_get('max_file_uploads');?>,
      // Define your form id or class or just tag.
      $form = $('form'), 
      // Define your upload field class or id or tag.
      $file_upload = $('#file-7', $form), 
      // Define your submit class or id or tag.
      $button = $("input[type='submit']"); 

  $('div.alert').hide();
  // Disable submit button on page ready.
  $button.prop('disabled', 'disabled');
  
  $file_upload.on('change', function () {
    var number_of_images = $(this)[0].files.length;
    if (number_of_images > max_file_number) {
      //alert(`You can upload maximum ${max_file_number} files.`);
	  $('div.alert').show();
	  $('div.alert').text(`You can upload maximum ${max_file_number} files.`);
      $(this).val('');
      $button.prop('disabled', 'disabled');
    } else {
      $button.prop('disabled', false);
	  $('div.alert').hide();
	  $('div.alert').text();
    }
  });
});
</script>
<script src="inc/js/jquery.custom-file-input.js"></script>