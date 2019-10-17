<?php
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
		<h2 class="text-center">TWD Stattool: UPGRADE von<br>1.8.5 auf 1.8.6</h2>
		<h4 class="text-center">Vorherige Sicherung von Datenbank und Dateien empfohlen</h3>
	  </div>

	  <div class="modal-body">
<?php
if (isset($_POST["do"]) == "upgrade") {
	
//Evtl. vorhandene Information aus alter Config auslesen, um Beta-Tester-Settings beizubehalten.
$cstatnumber = (empty($_POST['useClassicStat'])) ? '0' : '1';

$cclassicstats = (isset($config->useClassicStat)) ? $config->useClassicStat : $cstatnumber;
$capiprovider = (isset($config->apiprovider)) ? $config->apiprovider : $_POST['apiprovider'];

//Config neu schreiben
if (is_writable($configfile)) {
$string = '<?php 
return (object) array(
	\'dbhost\' => \''. $config->dbhost. '\',
	\'dbusername\' => \''.$config->dbusername.'\',
	\'dbpassword\' => \''.$config->dbpassword.'\',
	\'dbname\' => \''.$config->dbname.'\',
	\'apiprovider\' => \''.$_POST['apiprovider'].'\',
	\'ocrspace_apikey\' => \''.$config->ocrspace_apikey.'\',
	\'theme\' => \''.$config->theme.'\',
	\'statlimit\' => \''.$config->statlimit.'\',
	\'customstats\' => \''.$config->customstats.'\',
	\'db_pre\' => \''.$config->db_pre.'\',
	\'useClassicStat\' => \''.$cclassicstats.'\');
?>';


$fp = FOPEN($configfile, "w");
FWRITE($fp, $string);
FCLOSE($fp);
}
else
{
echo "<br>Das bereinigen der Datei ".$configfile." ist fehlgeschlagen. Das Tool wird <b>dennoch funktionieren</b>. Optional können Sie die Zeilen mit clanname und clantag manuell entfernen. Sie werden nicht weiter benötigt.<br>";
}
		
try {  
  $options = array(
        PDO::ATTR_ERRMODE => 'PDO::ERRMODE_'.$errmode, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );  
$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword,$options);


$sql = "CREATE TABLE IF NOT EXISTS `".$config->db_pre."minNumbers` (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`gid` int(11) NOT NULL DEFAULT '1',
			`Spalte` varchar(50) DEFAULT NULL,
			 `Min` int(11) NOT NULL DEFAULT '0',
			 PRIMARY KEY (`ID`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
	
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
		<div class="form-group">
			<label for="apiprovider">OCR Anbieter wählen:</label>
			<select class="form-control" id="apiprovider" name = "apiprovider">
				<option value = "google">Google CloudVision API</option>
				<option value = "ocrspacefree" selected>OCR.Space FREE API</option>
				<option value = "ocrspacepro">OCR.Space PRO API</option>
			</select>
		</div>
		<div class="form-group">
			<label for="useClassicStat">Standardansicht Statistik:</label>
			<select class="form-control" id="useClassicStat" name="useClassicStat">
				<option value = "1" selected>Klassische Statistik</option>
				<option value = "0">Neue Statistik</option>
			</select>
		</div>	
		Wenn die <b>Google CloudVision API</b> gewünscht ist:<br>Entpacke bitte inc/googleapi/gglapi_unpack_if_used.zip.<br>Schalte dann die CloudVision API in einem neuen Google Projekt an. Erzeuge die json Datei (welche für die Anmeldung des tools benötigt wird), durch Anlage eines neuen Dienstkontos in deinem neuen Projekt und lege diese als key.json in inc/googleapi. Die Datei kannst du beliebig umbenennen, wenn du auch inc/ajaxUploadToApi.php anpasst.<br>Die Google API ist mit bis zu 1000 Aufrufen pro Monat kostenlos und schneller/präzisier als ocr space.
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