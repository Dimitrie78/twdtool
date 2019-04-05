<?php
if (!isset($_SESSION)) {
	die('Eine Horde Zombies hat diese Seite Übernommen!');
}
if (!$_SESSION["login"] == 1){exit;}

$rtns = array("Dev" => "99",
			  "Admin" => "1,99",
   			  "Mod" => "1,2,99",
			  "User" => "1,2,3,99");

$realrights = explode(",",$rtns[$allowed]);
/*
if (!in_array($_SESSION['role'],$realrights)){
	echo 'No right to open this Site';
	exit;	
}
*/
?>