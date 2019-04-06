<?php

include "verify.php";

if (!isset($_POST['updatestat'])) { exit; }

$id = preg_replace('/[^0-9]/','',$_GET['id']);
$new_date_format = date('Y-m-d H:i:s', strtotime($_POST['date']));


$query = $pdo->prepare('UPDATE stats SET date = :date, exp = :exp, streuner = :streuner, menschen = :menschen,
						gespielte_missionen = :gespielte_missionen, abgeschlossene_missonen = :abgeschlossene_missonen,
						gefeuerte_schuesse = :gefeuerte_schuesse, haufen = :haufen, heldenpower = :heldenpower,
						waffenpower = :waffenpower, karten = :karten, gerettete = :gerettete WHERE id = :id');

					
$query->execute(array(':date' => $new_date_format,
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
					  ':id' => $id));	
if($query) {        
	echo '<div class="alert alert-success"><strong>Statistik-ID '.$id.' editiert!</strong> <a href ="?action=editstat&id='.$id.'">Weiterleitung...</a></div>';
	header('Refresh: 2; URL=?action=editstat&id='.$id.'');
}
?>