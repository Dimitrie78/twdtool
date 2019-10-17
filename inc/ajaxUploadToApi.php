<?php
// ajaxUploadToApi.php

session_start();
use Google\Cloud\Vision\VisionClient;
$config = include("../conf/config.php");

	if($config->apiprovider == 'google'){
		require_once("googleapi/vendor/autoload.php"); 
		putenv('GOOGLE_APPLICATION_CREDENTIALS=googleapi/key.json');
		$vision = new VisionClient();
	}
	
	
$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

include("../inc/functions.php");

if(isset($_POST['data'])) {
	foreach ($_POST['data'] as $target_file) {
		if (strlen(trim($target_file)) > 0){
			$hasfiles = true;
//			echo $target_file." - ";
			uploadToApi('../'.$target_file);
		}
	}
}
?>