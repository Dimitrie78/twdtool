<?php

include "verify.php";

if (isset($_GET['uid']) && !empty($_GET['uid'])) {
	echo '<a href="?action=stats&uid='.$_GET['uid'].'" class="btn btn-info" role="button">Zurück zur Statistik</a><hr>';

	$statement = $pdo->prepare("SELECT id,ign,notetime,notes FROM users WHERE id = :id");
	$result = $statement->execute(array('id' => $_GET['uid']));
	$user = $statement->fetch();
	
	echo "<b>Notizen von ".$user['ign'].":</b><br><br>";
	
	if($user['notes'] > "") {
		echo "Eintrag vom: ". date("d.m.Y H:i:s", strtotime($user['notetime'])) . "<br><br>";
		echo nl2br(htmlentities($user['notes']));
	} else {
		echo "Keine Notizen vorhanden!";
	}
}
?>