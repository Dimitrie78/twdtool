<div id="result"></div>
<?php
include "verify.php";

#Todo: Für alle umgewandelten Dateien
#$target_file = "ocr_foo.jpg";
$hasfiles = False;
$data = array();

$screens = glob("2ocr/*.{jpg,png}", GLOB_BRACE);
foreach ($screens as $target_file) {
	if (strlen(trim($target_file)) > 0){
    $file   = substr($target_file, strrpos($target_file, '/')+1, strlen($target_file));
    $fileid = explode("_", $file);
    if($_SESSION['userid']==$fileid[0]){
      $hasfiles = true;
      $data[] = $target_file;
//		echo $target_file." - ";
//		uploadToApi($target_file);
    }
	}
}

if ($hasfiles !== true ) {
	echo '<br><div class="alert alert-warning">
	  <strong>Keine Daten!</strong> Es stehen keine verarbeitbaren Daten zur Verfügung!<br>Bitte zunächst Screenshots hochladen und konvertieren!
	</div>';	
}
?>
<div class="modal"></div>
<script language="javascript">var data = <?=json_encode($data)?>;</script>
<script type="text/javascript" src="inc/js/import.js"></script>