<?php
include "verify.php";

$id = preg_replace('/[^0-9]/','',$_POST['delstatid']);
$uid = $_POST['delstatuid'];
$statement = $pdo->prepare("DELETE FROM ".$config->db_pre."stats WHERE id = :id");
$result = $statement->execute(array('id' => $id));
if($result){
	echo '<div class="alert alert-success"><strong>Statistik-ID '.$id.' erfolgreich entfernt!</strong> <a href ="?action=stats&uid='.$uid.'">Weiterleitung...</a></div>';
	header('Refresh: 2; URL=?action=stats&uid='.$uid.'');
} else {
	echo 'Löschen fehlgeschlagen!';
}
?>