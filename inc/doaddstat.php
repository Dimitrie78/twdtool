<?php
include "verify.php";

if (!isset($_POST['addstat']) && !isset($_POST['uid'])) { exit; }

$uid = preg_replace('/[^0-9]/','',$_POST['uid']);
$new_date_format = date('Y-m-d H:i:s', strtotime($_POST['date']));
if (!user_exists($uid)){
	echo '<div class="alert alert-danger">
  <strong>Abbruch</strong> Gewählte User-ID <b>'.$uid.'</b> existiert nicht. Eintragen nicht möglich</div>	<a href="?action=stats" name="back" class="btn btn-info" role="button">Zurück</a>'; 
	exit();
}

$curr_datetime =date("Y-m-d H:i:s");
$notetime = ($_POST['notes'] > "" ? $curr_datetime : ''); 

$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."stats(uid, date, name, exp, streuner, menschen, gespielte_missionen, abgeschlossene_missonen, gefeuerte_schuesse, haufen, heldenpower,  waffenpower, karten, gerettete)
    VALUES(:uid, :date, :name, :exp, :streuner, :menschen, :gespielte_missionen, :abgeschlossene_missonen, :gefeuerte_schuesse, :haufen, :heldenpower, :waffenpower, :karten, :gerettete)");

						
$statement->execute(array(':uid' => $uid,
					  ':date' => $new_date_format,
					  ':name' => getuname($uid),
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
					  ':gerettete' => $_POST['gerettete']));	
if($statement) {        
	echo '<div class="alert alert-success"><strong>Statistik hinzufügt!</strong> <a href ="?action=stats&uid='.$uid.'">Weiterleitung...</a></div>';
	header('Refresh: 2; URL=?action=stats&uid='.$uid.'');
}
?>