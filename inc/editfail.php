<?php
extract($_POST);
extract($_GET);

include "verify.php";

if (!isset($id) && !is_numeric($id)) { exit; }


if(isset($do) && $do == "failremove" && is_numeric($id)){
	$id = preg_replace('/[^0-9]/','',$id);
	
	if(/*$ftype == 'jpg' && */file_exists('2ocr/fail/'.$id.'.jpg')){
	  unlink('2ocr/fail/'.$id.'.jpg');
	}
	elseif(/*$ftype == 'png' && */file_exists('2ocr/fail/'.$id.'.png')){
	  unlink('2ocr/fail/'.$id.'.png');
	}	
	
	$statement = $pdo->prepare("DELETE FROM ".$config->db_pre."stats WHERE id = :id AND fail = 1");
	
	if($result = $statement->execute(array('id' => $id))) {
		header("Location: ?action=fails&msg=deletesuccess");
	} else {
		header("Location: ?action=fails&msg=deletefail");
	}
}

if(isset($updatefailid)){
	
	if  ($name == "")
	{
		echo '<div class="alert alert-warning"><strong>Keine Speicherung. Bitte den Nutzer auswählen.</strong>. <a href = "?action=editfail&id='.$editid.'">Rückkehr ins Bearbeitungsmenü</a></div>';
		header('Refresh: 2; URL=?action=editfail&id='.$editid);
	} 
	else
	{
		$dbid = preg_replace('/[^0-9]/','',$editid);

		$igname = $name;
		if ($igname == '-NEW-'){
			$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."users(telegram, notes, notetime, ign, passwd, role, created_at, active)
				VALUES(:telegram, :notes, :notetime, :ign, :passwd, :role, :created_at, :active)");


			$statement->execute(array(
				"telegram" => '',
				"notes" => '',
				"notetime" => date("Y-m-d H:i:s"),
				"ign" =>  $correction,
				"passwd" => password_hash('123456', PASSWORD_DEFAULT),
				"role" => 3,
				"created_at" => date("Y-m-d H:i:s"),
				"active" => 1
			));
			$igname = $correction;
		  $uid = $pdo->lastInsertId();

		}else $uid = getuid($name);


		if ($uid > 0){
			
			$query = $pdo->prepare('UPDATE '.$config->db_pre.'stats SET uid = :uid, name = :name, exp = :exp, streuner = :streuner, menschen = :menschen,
									gespielte_missionen = :gespielte_missionen, abgeschlossene_missonen = :abgeschlossene_missonen,
									gefeuerte_schuesse = :gefeuerte_schuesse, haufen = :haufen, heldenpower = :heldenpower,
									waffenpower = :waffenpower, karten = :karten, gerettete = :gerettete, notizen = :notizen, fail = :fail WHERE id = :id');

								
			$query->execute(array(':uid' => $uid,
								  ':name' => $igname,	
								  ':exp' => $exp,
								  ':streuner' => $streuner,
								  ':menschen' => $menschen,
								  ':gespielte_missionen' => $gespielte_missionen,
								  ':abgeschlossene_missonen' => $abgeschlossene_missonen,
								  ':gefeuerte_schuesse' => $gefeuerte_schuesse,
								  ':haufen' => $haufen,
								  ':heldenpower' => $heldenpower,
								  ':waffenpower' => $waffenpower,
								  ':karten' => $karten,
								  ':gerettete' => $gerettete,
								  ':fail' => "0",
								  ':notizen' => "",
								  ':id' => $dbid));

			if(isset($setFix)&&$setFix==1){
				if($igname != $OCRResult){
					$query = $pdo->prepare('INSERT INTO '.$config->db_pre.'namefix SET searchfor = :s, replacement = :r');

										
					$query->execute(array(':s' => $OCRResult,
										  ':r' => $igname));
			  }

			}
							  
			if($query) {       
				##sicherheit: alles ausser zahlen entfernen, sicheres entfernen
				if(/*$ftype == 'jpg' && */file_exists('2ocr/fail/'.$dbid.'.jpg')){
					unlink('2ocr/fail/'.$dbid.'.jpg');
				}
				elseif(/*$ftype == 'png' && */file_exists('2ocr/fail/'.$dbid.'.png')){
					unlink('2ocr/fail/'.$dbid.'.png');
				}


				$qry = 'SELECT id,date,name FROM `'.$config->db_pre.'stats` WHERE fail = 1 ORDER BY date ASC LIMIT 0, 1';
				$newid = -1;
				foreach ($pdo->query($qry) as $row){
					$newid = $row['id'];

				}

				if($newid > 0){
					$do = '';
					$editid = $newid;
					$id = $newid;
					unset($updatefailid);
				}else{
				  $do = 'erledigt'; //Formular nicht laden
				  echo '<div class="alert alert-success"><strong>DB-ID '.$editid.' erfolgreich aktualisiert!</strong> <a href = "?action=fails">Weiterleitung</a></div>';
 				  header('Refresh: 2; URL=?action=fails');
				
				}

			}
		}
    }
}



if((!isset($do)) || (!$do)){
	$id = preg_replace('/[^0-9]/','',$id);
	$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."stats WHERE id = :id AND fail = 1");
	$result = $statement->execute(array('id' => $id));
	$stats = $statement->fetch();
	if (!$stats){exit('Gewählter Eintrag nicht existent oder bereits fehlerfrei!');}

	#todo: checken ob der fail korrekt is
	
	$file = '2ocr/fail/'.$id;
	
	if (file_exists($file.'.jpg')){
		$img = '<img class="card-img-bottom" src="'.$file.'.jpg" style="margin-top:200px; max-height:800px;" alt="Failimg" title="Failimg">';
		$ftype = 'jpg';
		$file = $file.'.jpg';
	}
	elseif (file_exists($file.'.png')){
		$img = '<img class="card-img-bottom" src="'.$file.'.png" style="margin-top:200px; max-height:800px;" alt="Failimg" title="Failimg">';
		$ftype = 'png';
		$file = $file.'.png';
	} else {		
		$img = 'Keine Bilddatei gefunden';	
	}

	$datetime = new DateTime($stats['date']);	

	$h_werte = 0;
	$w_werte = 0;
	$s_werte = 0;
	$e_werte = 0;
	$img_sql = 'SELECT epH, epY, werteH, werteW FROM `'.$config->db_pre.'ocr` WHERE uid='.$_SESSION['userid'].' AND aktiv=1';
	foreach ($pdo->query($img_sql) as $irow) {
		$s_werte = ceil($irow['werteH']*1.5);
		$w_werte = ceil($irow['werteW']*1.5);
		$e_werte = ceil($irow['epH']*1.6*1.5);
	}
	$h_werte = ceil($s_werte/10);

	
?>

<div class="container">
	<a href="?action=fails" class="btn btn-info btn-sm" role="button" title = "Zur Fehlerauswahlliste zurück">< Zurück</a>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-block">
            <h4 class="card-title">Fehlerbehebung für ID <?php echo $stats['id'].' vom '.$datetime->format('d.m.Y H:i:s'); ?></h4>
            <p class="card-text">Erkannter Name: <span style="font-weight:bold;"><?php echo $stats['name']; ?></span> <br>Übertrage die Werte vom Bild in das Formular.<br>Fehler: <br /><span class="card-error">&nbsp;&nbsp;&nbsp;<?php echo $stats['notizen']!=''?explode('\n', $stats['notizen'])[0]:''; ?> </span></p>
			<form action="?action=editfail&id=<?=$id?>" method="POST" autocomplete="no">
			  <input  type = "hidden" name="editid" type="text" value="<?php echo $stats['id'];?>">
			  <input  type = "hidden" name="ftype" type="text" value="<?php echo $ftype;?>">
			  
			  <div class="form-group">
				<label for="name">Name:</label><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.ceil($e_werte*0.9).'&s='.$s_werte.'&w='.ceil($w_werte*1.5).'&i=-1" style="width:150px; height:auto;"/>'; ?> <br />
				      <select id="inputUser" id="name" name = "name" class = "form-control" style="width:auto;min-width:200px; display:inline-block;" onchange="var mysel = document.getElementById('correct'); if (mysel!=null) if(this.options[this.selectedIndex].value == '-NEW-') mysel.style.display='block'; else mysel.style.display='none';">

<?php	    
  echo '<option value="">--Wähle--</option>';
  echo '<option value="-NEW-">Benutzer anlegen</option>';
  if ($stats['uid'] < 1){

	$sql2 = 'CREATE TEMPORARY TABLE nearUser SELECT s.uid, u.ign, max(s.streuner) as streuner, max(menschen) as menschen, max(haufen) as haufen FROM `'.$config->db_pre.'stats` as s LEFT JOIN `'.$config->db_pre.'users` as u ON u.id = s.uid WHERE u.active = 1 GROUP BY s.uid;';
	$query_nearUser = $pdo->prepare($sql2);
	$query_nearUser->execute();
	$sql2 = 'SELECT uid, ign, streuner from nearUser WHERE streuner < '.$stats['streuner'].' AND menschen < '.$stats['menschen'].' AND haufen < '.$stats['haufen'].' ORDER BY streuner DESC LIMIT 3';
	$query_nearUser = $pdo->prepare($sql2);
	$query_nearUser->execute();
	echo '<optgroup label="Vorschläge">';
	foreach ($query_nearUser as $row) {
		echo '<option value="'.$row['ign'].'" >'.$row['ign'].'</option>';
	}
	echo '</optgroup>';
}
	
	$sql = 'SELECT id,ign,telegram,notes FROM `'.$config->db_pre.'users` WHERE active = 1 ORDER BY ign ASC';
	
    echo '<optgroup label="Aktive">';
  $selected = '';
	foreach ($pdo->query($sql) as $row) {
		if ($stats['uid'] == $row['id']){
			$selected = ' selected';
		}
		echo '<option value="'.$row['ign'].'" '.$selected.'>'.$row['ign'].'</option>';
		$selected = '';
    }
	echo '</optgroup>';

	$sql = 'SELECT id,ign,telegram,notes FROM `'.$config->db_pre.'users` WHERE active = 0 ORDER BY ign ASC';
	echo '<optgroup label="Inaktiv">';		
    foreach ($pdo->query($sql) as $row) {
		if ($stats['uid'] == $row['id']){
			$selected = ' selected';
		}
		echo '<option value="'.$row['ign'].'" '.$selected.'>'.$row['ign'].'</option>';
		$selected = '';
    }
	echo '</optgroup>';



    echo '</select>';
    echo '<div  name="NewUser" onclick="var mysel = document.getElementById(\'inputUser\'); if (mysel!=null) mysel.value = \'-NEW-\'; var mysel2 = document.getElementById(\'correct\'); if (mysel2!=null) mysel2.style.display=\'block\'; " style="font-size:50px;color:green;font-weight:bold; padding:0px 5px; height:30px; display:inline-block; cursor:pointer; margin:0px 10px;line-height: 30px; text-align: center; vertical-align: middle;">+</div>';
	echo '<input type="text" name="correction" value="'.$stats['name'].'" style="display:none; margin-top:10px;" id="correct" />';
?>
			  </div>

			  <div class="form-group">
				<label for="exp">#1 Erfahrung (####<strong>/</strong>####):</label>
				<div onclick="moveNumbersDown(0);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$e_werte.'&s='.$s_werte.'&w=0&i=0" style="width:150px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="exp" name ="exp" value = "<?php echo $stats['exp']; ?>">
			  </div>
			  <div class="form-group">
				<label for="1">#2 Streuner:</label>
				<div onclick="moveNumbersDown(1);" class="moveDown">&#8595;</div>
				<?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=1" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="1" name ="streuner" value = "<?php echo $stats['streuner']; ?>"> 
			  </div>
				<div class="form-group">
				<label for="2">#3 Menschen:</label>
				<div onclick="moveNumbersDown(2);" class="moveDown">&#8595;</div>
				<?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=2" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="2" name ="menschen" value = "<?php echo $stats['menschen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="3">#4 Missionen:</label>
				<div onclick="moveNumbersDown(3);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=3" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="3" name ="gespielte_missionen" value = "<?php echo $stats['gespielte_missionen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="4">#5 Abgeschlossene Missionen:</label>
				<div onclick="moveNumbersDown(4);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=4" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="4" name ="abgeschlossene_missonen" value = "<?php echo $stats['abgeschlossene_missonen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="5">#6 Gefeuerte Schüsse:</label>
				<div onclick="moveNumbersDown(5);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=5" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="5" name ="gefeuerte_schuesse" value = "<?php echo $stats['gefeuerte_schuesse']; ?>">
			  </div>
			  <div class="form-group">
				<label for="6">#7 Kisten (Haufen):</label>
				<div onclick="moveNumbersDown(6);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=6" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="6" name ="haufen" value = "<?php echo $stats['haufen']; ?>">
			  </div>
			  <div class="form-group">
				<label for="7">#8 Heldenstärke:</label>
				<div onclick="moveNumbersDown(7);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=7" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="7" name ="heldenpower" value = "<?php echo $stats['heldenpower']; ?>">
			  </div>
			  <div class="form-group">
				<label for="8">#9 Waffenstärke:</label>
				<div onclick="moveNumbersDown(8);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=8" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="8" name ="waffenpower" value = "<?php echo $stats['waffenpower']; ?>">
			  </div>
			  <div class="form-group">
				<label for="9">#10 Gesammelte Karten:</label>
				<div onclick="moveNumbersDown(9);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=9" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="9" name ="karten" value = "<?php echo $stats['karten']; ?>">
			  </div>
			  <div class="form-group">
				<label for="10">#11 Gerettete Menschen:</label>
				<div onclick="moveNumbersDown(10);" class="moveDown">&#8595;</div><?php echo '<img src="inc/editfail_CropImg.php?f='.urlencode($file).'&h='.$h_werte.'&s='.$s_werte.'&w='.$w_werte.'&i=10" style="width:80px; height:auto;"/>'; ?> 
				<input type="text" class="form-control" id="10" name ="gerettete" value = "<?php echo $stats['gerettete']; ?>">
			  </div>
			  <div class="clearfix">
			<div class="pull-left">
				<button type="submit" name="updatefailid" class="btn btn-success">Update</button>&nbsp;&nbsp;<input type="checkbox" name="setFix" value="1" checked="checked" /> ggfls. OCR-Verbesserung anlegen
				<?php echo '<input type="hidden" name="OCRResult" value="'.$stats['name'].'" />'; ?>
			</div>
			<div class="pull-right">
			<a href="?action=editfail&do=failremove&id=<?=$id;?>&ftype=<?=$ftype;?>" name="remove" class="btn btn-danger" role="button"><span class = "fas fa-minus-square"></span> Entfernen</a>
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

?>