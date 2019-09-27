<?php
error_reporting(E_ALL);
ob_start();
header("Content-Type: text/html; charset=utf-8");
$configfile = 'conf/config.php';
if (!file_exists($configfile)){exit('Config Datei fehlt. Bitte installieren.');}
$config = include($configfile);
$errmode = 'EXCEPTION'; 
define('TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(TIMEZONE);

?>

<!DOCTYPE html>
<html lang="de">
<head>
  <title>TWD Stattool Upgrader </title>
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
		<h2 class="text-center">TWD Stats: UPGRADE von 1.8.4 Multigroup Beta auf 1.8.5</h2>
		<h4 class="text-center">Vorherige Sicherung von Datenbank und Dateien empfohlen</h3>
	  </div>

	  <div class="modal-body">
<?php
if (isset($_POST["do"]) == "upgrade") {
		
try {  
  $options = array(
        PDO::ATTR_ERRMODE => 'PDO::ERRMODE_'.$errmode, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );  
$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword,$options);


$sql = "ALTER TABLE `".$config->db_pre."groups` ADD `sort` INT(4) NOT NULL AFTER `name`;";
	
	if ($pdo->query($sql)) {		
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
					
		if ($errdel == false)
		{
			echo '<div class="alert alert-success">Das Upgrade wurde erfolgreich durchgeführt.</div><br>
			<a href="index.php" class="btn btn-success btn-sm" role="button" >Login</a>';
		}
	}
	else {
		echo '<div class="alert alert-warning">Fehler beim schreiben in die Datenbank. Bitte prüfen Sie die Zugangsdaten.</div>';
		 }
	} 
	catch(PDOException $e){
    echo "Datenbankverbindung fehlgeschlagen: " . $e->getMessage();
    exit;
		}
	}
	if(!$_POST AND !$_GET) {
	?>
	<form action="upgrade.php" method = "POST" autocomplete="no" name="install" id="install">	  
	  <div class="form-group text-center">
	  <button type="submit" name="do" value="upgrade" class="btn btn-success">Upgrade starten</button>
	 </div>
	</form>		
	<?php	
	}
?>	
	  </div>
	</div>
	</div>
  </body>
</html>