<?php
// ajaxUploadToApi.php

session_start();

$config = include("../conf/config.php");

$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

include("../inc/functions.php");

if(isset($_POST['data'])) {
	foreach ($_POST['data'] as $target_file) {
		if (strlen(trim($target_file)) > 0){
			$hasfiles = true;
//			echo $target_file." - ";
			echo uploadToApi('../'.$target_file);
		}
	}
}
?>