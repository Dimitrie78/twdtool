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

	if (file_exists("../2ocr/bigfile/bigfile.jpg"))
	  unlink("../2ocr/bigfile/bigfile.jpg");
	if (file_exists("../2ocr/bigfile/bigfile.txt"))
	  unlink("../2ocr/bigfile/bigfile.txt");

	
	$vars = $data[0];
	$img = new prepimage($vars);
	$counter = 0;
	$screens = glob("screens/*.{jpg,png}", GLOB_BRACE);
	foreach ($screens as $filename) {
		if (strlen(trim($filename)) > 0){
      $file   = substr($filename, strrpos($filename, '/')+1, strlen($filename));
      $fileid = explode("_", $file);
      if($_SESSION['userid']==$fileid[0]){
		$counter++;
        $hasfiles = True;
        $img->run($filename);
        unlink($filename);
        // echo " - Original entfernt. <br>";
      }
		}
	}
  echo $counter." Dateien erfolgreich konvertiert!<br />";

  if(($counter < 28) && (isSet($_GET['bigfile'])&&$_GET['bigfile']==1)){
 	  $screens = glob("2ocr/*.{jpg,png}", GLOB_BRACE);
		$c = count($screens);
		$w = 0; $h = 0; $t = 0;
	  $str = '';
	  $filelist = array();
	  $image;

		if ($c>0){
		  list($w, $h, $t) = getimagesize($screens[0]);
		  $new_w = ceil($w*0.6666);
		  $new_h = ceil($h*0.6666) * $c + 10;
		  $new = imagecreatetruecolor($new_w, $new_h);  // true color for best quality
		  $act_h = 0;

		  $first = 1;
	    foreach ($screens as $filename) {
	    	if ($first == 1) {
	    		$first = 0;
				  $ti = filemtime($filename);

	    	}
	    	$filelist[] = $filename;
	    	// echo 'bfc:'.$filename.'<br />';
		    if (strlen(trim($filename)) > 0){
					if($t == 2){
						$image = imagecreatefromjpeg($filename);
					}
					elseif($t == 3){
						$image = imagecreatefrompng($filename);
					}
					if($image)
					{
						imagecopyresampled($new, $image, 0, $act_h, 0, 0, $new_w, ceil($h*0.6666), $w, $h);
						$act_h = $act_h + ceil($h*0.6666);
			  		imagedestroy($image);
					}
		   	}
		 	}

			if (!file_exists('2ocr/bigfile')) {
	    	mkdir('2ocr/bigfile', 0777, true);
			}

			imagejpeg($new,"2ocr/bigfile/bigfile.jpg",60); // Neues Bild speichern
			imagedestroy($new);

			$string_data = serialize($filelist);
			file_put_contents("2ocr/bigfile/bigfile.txt", $string_data);

			touch("2ocr/bigfile/bigfile.jpg", $ti);

			echo "BigFile erfolgreich erzeugt!<br />";
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