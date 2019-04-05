<?php

include "verify.php";

if (!isset($_GET['id']) && !is_numeric($_GET['id'])) { exit; }

if((!isset($_GET['do'])) || (!$_GET['do'])){
	$id = preg_replace('/[^0-9]/','',$_GET['id']);
	$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."stats WHERE id = :id AND fail = 1");
	$result = $statement->execute(array('id' => $id));
	$stats = $statement->fetch();
	if (!$stats){exit('Gewählter Eintrag nicht existent oder bereits fehlerfrei!');}

	#todo: checken ob der fail korrekt is
	
	$file = '2ocr/fail/'.$_GET['id'];
	
	if (file_exists($file.'.jpg')){
		$img = '<img class="card-img-bottom" src="'.$file.'.jpg" style="max-height:800px;" alt="Failimg" title="Failimg">';
		$ftype = 'jpg';
	}
	elseif (file_exists($file.'.png')){
		$img = '<img class="card-img-bottom" src="'.$file.'.png" style="max-height:800px;" alt="Failimg" title="Failimg">';
		$ftype = 'png';
	} else {		
		$img = 'Keine Bilddatei gefunden';	
	}

	$datetime = new DateTime($stats['date']);	
	
?>

<div class="container">
	<a href="?action=fails" class="btn btn-info btn-sm" role="button" title = "Zur Fehlerauswahlliste zurück">< Zurück</a>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-block">
            <h4 class="card-title">Fehlerbehebung für ID <?php echo $stats['id'].' vom '.$datetime->format('d.m.Y H:i:s'); ?></h4>
            <p class="card-text">Erkannter Name: <?php echo $stats['name']; ?> <br>Übertrage die Werte vom Bild in das Formular.<br>Fehler: <?php echo $stats['notizen']; ?></p>
			<form action="?action=editfail&id=<?=$_GET['id']?>" method="POST" autocomplete="no">
			  <input  type = "hidden" name="editid" type="text" value="<?php echo $stats['id'];?>">
			  <input  type = "hidden" name="ftype" type="text" value="<?php echo $ftype;?>">
			  
			  <div class="form-group">
				<label for="name">Name:</label>
				      <select id="inputUser" id="name" name = "name" class = "form-control" style="width:auto;min-width:200px;" onchange="var mysel = document.getElementById('correct'); if (mysel!=null) if(this.options[this.selectedIndex].value == '-NEW-') mysel.style.display='block'; else mysel.style.display='none';">

<?php	    
	$sql = 'SELECT id,ign,telegram,notes FROM `'.$config->db_pre.'users` ORDER BY ign ASC';
	
	echo '<option value="">--Wähle--</option>';
    foreach ($pdo->query($sql) as $row) {
		if ($stats['uid'] == $row['id']){
			$selected = ' selected';
		}
		echo '<option value="'.$row['ign'].'" '.$selected.'>'.$row['ign'].'</option>';
		$selected = '';
    }
	echo '<option value="-NEW-">Benutzer anlegen</option>';
	echo '<input type="text" name="correction" value="'.$stats['name'].'" style="display:none;" id="correct" />';
?>
					</select>
			  </div>

			  <div class="form-group">
				<label for="exp">#1 Erfahrung (####<strong>/</strong>####):</label>
				<input type="text" class="form-control" id="exp" name ="exp" value = "<?php echo $stats['exp']; ?>">
			  </div>
			  <div class="form-group">
				<label for="1">#2 Streuner:</label>
				<input type="text" class="form-control" id="1" name ="streuner" value = "<?php echo $stats['streuner']; ?>">
			  </div>
				<div class="form-group">
				<label for="2">#3 Menschen:</label>
				<input type="text" class="form-control" id="2" name ="menschen" value = "<?php echo $stats['menschen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="3">#4 Missionen:</label>
				<input type="text" class="form-control" id="3" name ="gespielte_missionen" value = "<?php echo $stats['gespielte_missionen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="4">#5 Abgeschlossene Missionen:</label>
				<input type="text" class="form-control" id="4" name ="abgeschlossene_missonen" value = "<?php echo $stats['abgeschlossene_missonen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="5">#6 Gefeuerte Schüsse:</label>
				<input type="text" class="form-control" id="5" name ="gefeuerte_schuesse" value = "<?php echo $stats['gefeuerte_schuesse']; ?>">
			  </div>
			  <div class="form-group">
				<label for="6">#7 Kisten (Haufen):</label>
				<input type="text" class="form-control" id="6" name ="haufen" value = "<?php echo $stats['haufen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="7">#8 Heldenstärke:</label>
				<input type="text" class="form-control" id="7" name ="heldenpower" value = "<?php echo $stats['heldenpower']; ?>">
			  </div>
			  <div class="form-group">
				<label for="8">#9 Waffenstärke:</label>
				<input type="text" class="form-control" id="8" name ="waffenpower" value = "<?php echo $stats['waffenpower']; ?>">
			  </div>
			  <div class="form-group">
				<label for="9">#10 Gesammelte Karten::</label>
				<input type="text" class="form-control" id="9" name ="karten" value = "<?php echo $stats['karten']; ?>">
			  </div>
			  <div class="form-group">
				<label for="10">#11 Gerettete Menschen:</label>
				<input type="text" class="form-control" id="10" name ="gerettete" value = "<?php echo $stats['gerettete']; ?>">
			  </div>
			  <div class="clearfix">
			<div class="pull-left">
				<button type="submit" name="updatefailid" class="btn btn-success">Update</button><input type="checkbox" name="setFix" value="1" checked="checked" /> ggfls. OCR-Verbesserung anlegen
				<?php echo '<input type="hidden" name="OCRResult" value="'.$stats['name'].'" />'; ?>
			</div>
			<div class="pull-right">
			<a href="?action=editfail&do=failremove&id=<?=$id;?>" name="remove" class="btn btn-danger" role="button"><span class = "fas fa-minus-square"></span> Entfernen</a>
		</div>
  </div>
  
			</form>
           </div>
        </div>
      </div>
      <div class="col-md-6">
        <?php echo $img; ?>
      </div>
    </div>
</div>

<?php
}
if(isset($_GET['do']) && $_GET['do'] == "failremove" && is_numeric($_GET['id'])){
	$id = preg_replace('/[^0-9]/','',$_GET['id']);
	
	$statement = $pdo->prepare("DELETE FROM ".$config->db_pre."stats WHERE id = :id AND fail = 1");
	if($result = $statement->execute(array('id' => $id))) {
		header("Location: ?action=fails&msg=deletesuccess");
	} else {
		header("Location: ?action=fails&msg=deletefail");
	}
}

if(isset($_POST['updatefailid'])){
	
	if  ($_POST['name'] == "")
	{
		echo '<div class="alert alert-warning"><strong>Keine Speicherung. Bitte den Nutzer auswählen.</strong>. <a href = "?action=editfail&id='.$_POST['editid'].'">Rückkehr ins Bearbeitungsmenü</a></div>';
		header('Refresh: 2; URL=?action=editfail&id='.$_POST['editid']);
	} 
	else
	{
		$dbid = preg_replace('/[^0-9]/','',$_POST['editid']);

		$igname = $_POST['name'];
		if ($igname == '-NEW-'){
			$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."users(telegram, notes, notetime, ign, passwd, role, created_at, active)
				VALUES(:telegram, :notes, :notetime, :ign, :passwd, :role, :created_at, :active)");


			$statement->execute(array(
				"telegram" => '',
				"notes" => '',
				"notetime" => date("Y-m-d H:i:s"),
				"ign" =>  $_POST['correction'],
				"passwd" => password_hash('123456', PASSWORD_DEFAULT),
				"role" => 3,
				"created_at" => date("Y-m-d H:i:s"),
				"active" => 1
			));
			$igname = $_POST['correction'];
		  $uid = $pdo->lastInsertId();

		}else $uid = getuid($_POST['name']);


		if ($uid > 0){
			
			$query = $pdo->prepare('UPDATE '.$config->db_pre.'stats SET uid = :uid, name = :name, exp = :exp, streuner = :streuner, menschen = :menschen,
									gespielte_missionen = :gespielte_missionen, abgeschlossene_missonen = :abgeschlossene_missonen,
									gefeuerte_schuesse = :gefeuerte_schuesse, haufen = :haufen, heldenpower = :heldenpower,
									waffenpower = :waffenpower, karten = :karten, gerettete = :gerettete, notizen = :notizen, fail = :fail WHERE id = :id');

								
			$query->execute(array(':uid' => $uid,
								  ':name' => $igname,	
								  ':exp' => $_POST['exp'],
								  ':streuner' => $_POST['streuner'],
								  ':menschen' => $_POST['menschen'],
								  ':gespielte_missionen' => $_POST['gespielte_missionen'],
								  ':abgeschlossene_missonen' => $_POST['abgeschlossene_missonen'],
								  ':gefeuerte_schuesse' => $_POST['gefeuerte_schuesse'],
								  ':haufen' => $_POST['haufen'],
								  ':heldenpower' => $_POST['heldenpower'],
								  ':waffenpower' => $_POST['waffenpower'],
								  ':karten' => $_POST['karten'],
								  ':gerettete' => $_POST['gerettete'],
								  ':fail' => "0",
								  ':notizen' => "",
								  ':id' => $dbid));

			if(isset($_POST['setFix'])&&$_POST['setFix']==1){
				if($igname != $_POST['OCRResult']){
					$query = $pdo->prepare('INSERT INTO '.$config->db_pre.'namefix SET searchfor = :s, replacement = :r');

										
					$query->execute(array(':s' => $_POST['OCRResult'],
										  ':r' => $igname));
			  }

			}
							  
			if($query) {       
				##sicherheit: alles ausser zahlen entfernen, sicheres entfernen
				if($_POST['ftype'] == 'jpg' && file_exists('2ocr/fail/'.$dbid.'.jpg')){
					unlink('2ocr/fail/'.$dbid.'.jpg');
				}
				elseif($_POST['ftype'] == 'png' && file_exists('2ocr/fail/'.$dbid.'.png')){
					unlink('2ocr/fail/'.$dbid.'.png');
				}

				echo '<div class="alert alert-success"><strong>DB-ID '.$_POST['editid'].' erfolgreich aktualisiert!</strong> <a href = "?action=fails">Weiterleitung</a></div>';
				header('Refresh: 2; URL=?action=fails');
			}
		}
    }
}
?>