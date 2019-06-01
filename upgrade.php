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
		<h2 class="text-center">TWD Stattool: UPGRADE von 1.7</h2>
		<h4 class="text-center">Vorherige Sicherung von Datenbank und Dateien empfohlen</h3>
	  </div>

	  <div class="modal-body">
<?php
if ($_POST["do"] == "upgrade") {
//Evtl. vorhandene Clan-Information aus alter Config auslesen
$clantag = (isset($config->clantag)) ? $config->clantag : 'TWD';
$clanname = (isset($config->clanname)) ? $config->clanname : 'twdclan';
$customstats = (isset($config->customstats)) ? $config->customstats : 'streuner+menschen as kills,round((streuner+menschen)/$days) as day, abgeschlossene_missonen as missions, waffenpower+heldenpower as upgrades, haufen as crates, gerettete as survivors';

//Config neu schreiben
if (is_writable($configfile)) {
$string = '<?php 
return (object) array(
	\'dbhost\' => \''. $config->dbhost. '\',
	\'dbusername\' => \''. $config->dbusername. '\',
	\'dbpassword\' => \''. $config->dbpassword. '\',
	\'dbname\' => \''. $config->dbname. '\',
	\'ocrspace_apikey\' => \''. $config->ocrspace_apikey. '\',
	\'theme\' => \''. $config->theme. '\',
	\'statlimit\' => \''. $config->statlimit. '\',
	\'customstats\' => \''. $customstats. '\',
	\'db_pre\' => \''. $config->db_pre. '\');
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


$sql = "CREATE TABLE IF NOT EXISTS `".$config->db_pre."groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tag` varchar(5) NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `".$config->db_pre."ocr` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `".$config->db_pre."openStats` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `Query` varchar(2000) NULL,
				  `gid` int(11) NULL,
				  `DateVon` date NULL,
				  `DateBis` date NULL,
				  `DateDisable` date NULL,
				  `active` bit(1) NULL DEFAULT b'1',
				PRIMARY KEY (`Id`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `".$config->db_pre."ocr` ENGINE = MYISAM;

ALTER TABLE  `".$config->db_pre."namefix` ADD  `gid` INT( 10 ) NOT NULL AFTER  `id` ;

ALTER TABLE  `".$config->db_pre."news` ADD `gid` INT( 10 ) NOT NULL AFTER  `id` ;

ALTER TABLE  `".$config->db_pre."news` DROP `validuntil`;

ALTER TABLE  `".$config->db_pre."news` CHANGE `active` `active` TINYINT( 1 ) NOT NULL;

ALTER TABLE  `".$config->db_pre."users` ADD `gid` INT( 10 ) NOT NULL AFTER  `id` ;

INSERT INTO `".$config->db_pre."groups` (`tag`, `name`) VALUES
('".$clantag."', '".$clanname."');

UPDATE `".$config->db_pre."users` SET  `gid` =  '1';";
	
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