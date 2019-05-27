<?php
include "verify.php";
include "prepimage.class.php";


$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'ocr WHERE uid = :uid AND aktiv = 1');
$query->execute(array(':uid' => $_SESSION['userid']));

$data = $query->fetchAll(PDO::FETCH_ASSOC); #is maybe faster
$hasfiles = False;

if(empty($data)) {
	echo 'Sie müssen erst ein Handy Profile erstellen oder Aktiv setzen! <a href="index.php?action=setHandyType"> Link </a>';
} else {
	
	$vars = $data[0];
	$img = new prepimage($vars);

	$screens = glob("screens/*.{jpg,png}", GLOB_BRACE);
	foreach ($screens as $filename) {
		if (strlen(trim($filename)) > 0){
      $file   = substr($filename, strrpos($filename, '/')+1, strlen($filename));
      $fileid = explode("_", $file);
      if($_SESSION['userid']==$fileid[0]){
        $hasfiles = True;
        $img->run($filename);
        unlink($filename);
        echo " - Original entfernt. <br>";
      }
		}
	}
	if ($hasfiles == True){
		echo '<br><div class="alert alert-success">
		  <strong>Fertig!</strong> Konvertierung abgeschossen.
		</div>';
		echo '<form action="" method="GET" autocomplete="no">	  
			  <div class="form-group text-center">
			   <button type="submit" name="action" value="import" class="btn btn-success">Auslesen und Speichern</button>
			  </div>
			</form>';
	}
	else
	{
		echo '<br><div class="alert alert-warning">
		  <strong>Keine Dateien vorhanden!</strong> Bitte zuerst Bilder in das screens Verzechnis laden!
		</div>';
	}
}
?>