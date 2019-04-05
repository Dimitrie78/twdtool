<?php

include "verify.php";

if (!isset($_POST['deluid']) && !is_numeric($_POST['deluid'])) { exit; }

if(isset($_POST['suredelete'])){
	$statement = $pdo->prepare("DELETE FROM ".$config->db_pre."stats WHERE uid = :uid");
	$result = $statement->execute(array('uid' => $_POST['deluid']));
	
	$statement = $pdo->prepare("DELETE FROM ".$config->db_pre."users WHERE id = :id");
	$result = $statement->execute(array('id' => $_POST['deluid']));
	
	echo '<br><div class="alert alert-success">
	  <strong>Erledigt!</strong> '.$_POST['delign'].' wurde entfernt!
	</div>';
	header('Refresh: 3; URL=?action=usrmgr');
}
else
{
	echo '<div class="alert alert-info">
  <strong>Sicherheitshaken nicht gesetzt!</strong> Es erfolgte keine Änderung!
</div>';
}
?>