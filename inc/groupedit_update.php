<?php
$config = include("../conf/config.php");
include("../inc/db_con.php");

$input = filter_input_array(INPUT_POST);
if ($input['action'] === 'edit') {
	$update_field='';

	if(isset($input['Spalte'])) {
		$update_field.= "Spalte='".$input['Spalte']."'";
	} 
	if(isset($input['Min'])) {
		$update_field.= ($update_field!=''?', ':'')."Min='".$input['Min']."'";
	}
	if($update_field && $input['ID']) {
		$q = 'UPDATE '.$config->db_pre.'minNumbers SET '.$update_field.' WHERE ID = '.$input['ID'];
	  $minQuery = $pdo->query($q);
		// $minQuery->execute();
	}
}
if ($input['action'] === 'delete') {
		$q = 'DELETE FROM '.$config->db_pre.'minNumbers WHERE ID = '.$input['ID'];
	  $minQuery = $pdo->query($q);
}
?>