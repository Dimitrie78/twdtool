<!DOCTYPE html>
<html lang="de">
<head>
  <title>TWD Stattool: UPGRADE von 1.7</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://bootswatch.com/3/slate/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head> 
  <body>
	<div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-heading">
		<h2 class="text-center">TWD Stattool: UPGRADE von 1.7</h2>
	  </div>
	  <hr />
	  <div class="modal-body">
<?php
// 1. Check if file existits and dir is writeable
// 2.1 Check if we CAN use chmod -> then chmod it


if($_GET['action'] == "clean"){
	$errdel = false;

	if(file_exists('install.php')){
		if(unlink('install.php'))
		{
			echo 'install.php entfernt!<br>';
		}
		else
		{
			echo 'install.php konnte nicht entfernt werden, bitte manuell löschen<br>';
			$errdel = true;
		}
	}
	if(file_exists('upgrade.php')){
		if(unlink('upgrade.php'))
		{
			echo 'upgrade.php entfernt!<br>';
		}
		else
		{
			echo 'upgrade.php konnte nicht entfernt werden, bitte manuell löschen<br>';
			$errdel = true;
		}
	}

	if(file_exists('config.php')){
		if(unlink('config.php'))
		{
			echo 'Alte config.php entfernt!<br>';
		}
		else
		{
			echo 'config.php konnte nicht entfernt werden, bitte manuell löschen<br>';
			$errdel = true;
		}
	}

	if ($errdel == false)
	{
		echo '<div class="alert alert-success">Alles OK Sie können sich jetzt einloggen</div><br>
		<a href="index.php" class="btn btn-success btn-sm" role="button" >Login</a>';
	}
}
else
{
	if(file_exists('conf/config.php')){
		$config = include 'conf/config.php';
	}

	$dirs = array(
		"./conf",
		"./screens",
		"./2ocr");
		
	foreach($dirs as $dir) {
		if (file_exists($dir)) {
			if (!is_writable($dir))	{
				echo 'Versuche Schreibrechte für Verzeichnis ' . $dir . ' zu setzen...';
				if (fileowner($dir) === getmyuid())	{
					if (chmod($dir, 0755)) {
						echo 'erfolgreich!<br />';
					} else {
						$notsupporeted = true;
						echo 'fehlgeschlagen<br />';
					}
				} else {
					$notsupporeted = true;
					$notokay[] = $dir;
				}
			}
		} else {
			echo $dir . ' existiert nicht. bitte anlegen und chmod auf 0755 stellen<br />';
			exit;
		}
	}

	if ($notsupporeted) {
		echo 'Ihr Webspace unterstützt das automatische setzen von Schreibrechten nicht.<br />Bitte setzen Sie in Ihrem FTP-Programm das chmod (Verzeichnisberechtigung) auf 0755 für das/die folgende/n Verzeichnis/e:<br />' . implode('<br />', $notokay);
		exit;
	}

	if ($_POST["do"] == "upgrade") {
		try {
			$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword);
			//$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
			$sql = "CREATE TABLE IF NOT EXISTS `".$config->db_pre."ocr` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `uid` int(11) DEFAULT NULL,
					  `aktiv` tinyint(3) DEFAULT '0',
					  `name` varchar(255) DEFAULT NULL,
					  `playerW` int(11) DEFAULT NULL,
					  `playerH` int(11) DEFAULT NULL,
					  `playerX` int(11) DEFAULT NULL,
					  `playerY` int(11) DEFAULT NULL,
					  `epW` int(11) DEFAULT NULL,
					  `epH` int(11) DEFAULT NULL,
					  `epX` int(11) DEFAULT NULL,
					  `epY` int(11) DEFAULT NULL,
					  `werteW` int(11) DEFAULT NULL,
					  `werteH` int(11) DEFAULT NULL,
					  `werteX` int(11) DEFAULT NULL,
					  `werteY` int(11) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
					";
			
			if ($pdo->query($sql)) {
				echo '<div class="alert alert-success">Daten erfolgreich in die Datebank geschrieben</div><br>';
				if(file_exists('install.php') OR file_exists('upgrade.php') OR file_exists('config.php')){
					echo 'Bitte löschen Sie jetzt aus Sicherheitsgründen die Installationsdateien sowie die alte Config-Datei, danach können Sie sich einloggen.<br><a href="upgrade.php?action=clean" class="btn btn-success btn-sm" role="button" >Installer löschen</a>';
				} else {
					echo '<a href="index.php" class="btn btn-success btn-sm" role="button">Login</a>';
				}
			} else {
				echo '<div class="alert alert-warning">Fehler beim Datebank import</div>';
			}
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


	if(!$_POST AND ! $_GET) {
	?>
			<form action="" method = "POST" autocomplete="no" name="install" id="install">	  
			  <div class="form-group text-center">
			   <button type="submit" name="do" value="upgrade" class="btn btn-success">Upgrade starten</button>
			  </div>
			</form>		
	<?php	
	}
}
?>	
	  </div>
	</div>
	</div>
  </body>
</html>