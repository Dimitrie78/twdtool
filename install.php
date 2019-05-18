<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <title>TWD Stattool: Installation</title>
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
		<h2 class="text-center">TWD Stattool: Installation</h2>
	  </div>
	  <hr />
	  <div class="modal-body">
<?php
	if (is_file('conf/config.php')) {
		die('<span style="color:#FF0000">Abbruch. Die config Datei existiert bereits!</span>');
	}
	if (is_file('conf/.gitignore')) {
		unlink('conf/.gitignore');
	}
	if (is_file('2ocr/fail/.gitignore')) {
		unlink('2ocr/fail/.gitignore');
	}
		
$dirs = array(
	"./conf",
	"./screens",
	"./2ocr",
	"./2ocr/fail");
	
foreach($dirs as $dir) {
	if (!file_exists($dir)) {
		echo 'Verzeichniss '.$dir.
		' existiert nicht. Es wird automatisch angelegt...';
		if (!mkdir($dir, 0755, true)) {
			die('<br><span style="color:#FF0000">Erstellung schlug fehl!</span>
				 Bitte manuell anlegen und den Installer
				<a href = "install.php">erneut aufrufen</a>.');
		} else {
			echo '<span style="color:#00FF00">erfolgreich!</span>';
		}
	} else {
		if (!is_writable($dir)) {
			echo 'Versuche Schreibrechte für Verzeichnis '.$dir.
			' zu setzen...';
			if (fileowner($dir) === getmyuid()) {
				if (chmod($dir, 0755)) {
					echo '<span style="color:#00FF00">erfolgreich!</span><br />';
				} else {
					$notsupporeted = true;
					echo 'fehlgeschlagen<br />';
				}
			} else {
				$notsupporeted = true;
				$notokay[] = $dir;
			}
		}
	}
}

if ($notsupporeted) {
	echo 'Ihr Webspace unterstützt das automatische setzen von Schreibrechten nicht.<br />
	Bitte setzen Sie in Ihrem FTP-Programm die chmods auf 0755 für die Verzeichnisse:<br />
	' . implode('<br />', $notokay);
	exit;
	}

if ($_POST["do"] == "createdb") {
	$config['dbhost'] = $_POST["dbhost"];
	$config['dbname'] = $_POST["dbname"];
	$config['dbusername'] = $_POST["dbusername"];
	$config['dbpassword'] = $_POST["dbpassword"];

	$config = array_map('trim', $config);
	foreach ($config as $key => $value) {
		if (empty($value)){
			$notfilled = true;
		}
	}
	if($notfilled){die('Abbruch. Ein für die Datenbankverbindung nötiger Wert wurde nicht eingetragen!');}
		
	$dsn= 'mysql:host='.$config['dbhost'].';dbname='.$config['dbname'].';charset=utf8';
	try{
		$conn = new PDO($dsn, $config['dbusername'], $config['dbpassword']);
		if($conn){
$string = '<?php 
return (object) array(
	\'dbhost\' => \''. $_POST["dbhost"]. '\',
	\'dbusername\' => \''. $_POST["dbusername"]. '\',
	\'dbpassword\' => \''. $_POST["dbpassword"]. '\',
	\'dbname\' => \''. $_POST["dbname"]. '\',
	\'ocrspace_apikey\' => \''. $_POST["ocrspace_apikey"]. '\',
	\'theme\' => \'slate\',
	\'statlimit\' => \'8\',
	\'db_pre\' => \''. $_POST["db_pre"]. '\');
?>';
			$pre = $_POST["db_pre"];
			$fp = FOPEN("conf/config.php", "w");
			FWRITE($fp, $string);
			FCLOSE($fp);
			#chmod('conf/config.php', 0640);
			if (!is_file('conf/config.php')) {
				echo '<div class="alert alert-warning">Fehler beim erzeugen der config.php</div>';
			} else {
				echo '<div class="alert alert-success">Verbindung ok, config.php geschrieben, erstelle Tabellen...</div>';
				$createqry = "CREATE TABLE IF NOT EXISTS `".$pre."namefix` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `searchfor` varchar(255) NOT NULL,
				  `replacement` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

				CREATE TABLE IF NOT EXISTS `".$pre."news` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `gid` int(10) NOT NULL,
				  `ndate` datetime NOT NULL,
				  `text` text NOT NULL,
				  `active` tinyint(1) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

				CREATE TABLE IF NOT EXISTS `".$pre."stats` (
				  `id` int(10) NOT NULL AUTO_INCREMENT,
				  `uid` int(4) NOT NULL,
				  `date` datetime NOT NULL,
				  `name` varchar(40) NOT NULL,
				  `exp` varchar(40) NOT NULL,
				  `streuner` int(10) NOT NULL,
				  `menschen` int(10) NOT NULL,
				  `gespielte_missionen` int(10) NOT NULL,
				  `abgeschlossene_missonen` int(10) NOT NULL,
				  `gefeuerte_schuesse` int(10) NOT NULL,
				  `haufen` int(10) NOT NULL,
				  `heldenpower` int(10) NOT NULL,
				  `waffenpower` int(10) NOT NULL,
				  `karten` int(10) NOT NULL,
				  `gerettete` int(10) NOT NULL,
				  `notizen` text NOT NULL,
				  `fail` tinyint(1) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

				CREATE TABLE IF NOT EXISTS `".$pre."users` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `gid` int(10) NOT NULL,
				  `telegram` varchar(255) NOT NULL,
				  `notes` text NOT NULL,
				  `notetime` datetime DEFAULT NULL,
				  `ign` varchar(12) NOT NULL,
				  `passwd` varchar(255) NOT NULL,
				  `role` tinyint(1) NOT NULL,
				  `lastlogin` datetime NULL,
				  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `updated_at` timestamp NULL DEFAULT NULL,
				  `active` tinyint(1) NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `ign` (`ign`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
				
				CREATE TABLE IF NOT EXISTS `".$pre."groups` (
				  `id` int(10) NOT NULL AUTO_INCREMENT,
				  `tag` varchar(3) NOT NULL,
				  `name` varchar(16) NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `tag` (`tag`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;

				CREATE TABLE IF NOT EXISTS `".$pre."ocr` (
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
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

				if ($conn->query($createqry))
				{
					echo '<div class="alert alert-success">Tabellen erfolgreich erstellt!</div>';	
					
					$statement = $conn->prepare("INSERT INTO ".$pre."groups(tag, name)
					VALUES(:tag, :name)");
					$statement->execute(array(
						"tag" => $_POST["clantag"],
						"name" => $_POST["clanname"]
						));
					
					$count = $statement->rowCount();
				    if($count =='1'){
						echo '<div class="alert alert-success">Gruppe / ersten Clan erfolgreich angelegt.</div>';
					}
					else{
						echo '<div class="alert alert-warning">Fehler bei der Anlage der Gruppe</div>';
					}
					$curr_datetime = date("Y-m-d H:i:s");
					$statement = $conn->prepare("INSERT INTO ".$pre."users(ign, gid, passwd, role, created_at, active)
					VALUES(:ign, :gid, :passwd, :role, :created_at, :active)");
					$statement->execute(array(
						"ign" => $_POST['firstuser'],
						"gid" => 1,
						"passwd" => password_hash($_POST['firstuserpw'], PASSWORD_DEFAULT) ,
						"role" => 1,
						"created_at" => $curr_datetime,
						"active" => 1
						));
					$count = $statement->rowCount();
				    if($count =='1'){
						echo '<div class="alert alert-success"><strong>Admin ' . htmlentities($_POST['firstuser']) . ' angelegt!</strong></div>';
						if(unlink('install.php')){
						echo '<div class="alert alert-success"><strong>Entferne Installer... OK.</strong> <a href ="index.php">Weiterleitung...</a></div>';
						header('Refresh: 2; URL=index.php');
						}
						else{
						echo 'Bitte jetzt die install.php löschen.';
						}
						}
					  else {
						echo '<div class="alert alert-warning">Fehler bei der Anlage des Adminusers</div>';
						   }	
				} else {
					echo '<div class="alert alert-warning">Fehler beim erzeugen der Tabellen</div>';	
					   }
			}
		}
	} catch (PDOException $e) {
		// echo $e->getMessage();
		echo '<div class="alert alert-warning">Es ist ein Fehler aufgetreten, bitte prüfen Sie die SQL-Zugangsdaten und versuchen Sie es erneut.</div><br><form>
  <input type="button" value="Zur&uuml;ck" onClick="history.go(-1);return true;">
</form>';
	 }
} 
if(!$_POST) {
?>
		<form action="install.php" method = "POST" autocomplete="off" name="install" id="install">
		  <div class="form-group">
			<label for="telegram">Datenbank Hostname:</label>
			<input type="text" class="form-control" id="dbhost" name="dbhost" value="localhost" required>
		  </div> 
		  <div class="form-group">
			<label for="dbusername">Datenbank Benutzername:</label>
			<input type="text" class="form-control" id="dbusername" name="dbusername" required> 
		  </div>
		  <div class="form-group">
			<label for="dbpassword">Datenbank Passwort:</label>
			<input type="password" class="form-control" id="dbpassword" name="dbpassword" autocomplete="new-password" required> 
		  </div>
		  <div class="form-group">
			<label for="dbname">Datenbank Name:</label>
			<input type="text" class="form-control" id="dbname" name="dbname" required> 
		  </div>
		  <div class="form-group">
			<label for="dbname">Datenbank Präfix (Optional):</label>
			<input type="text" class="form-control" id="db_pre" name="db_pre" required> 
		  </div>
		  <div class="form-group">
			<label for="ocrspace_apikey"><a href="https://ocr.space" target="_new">OCR.Space API KEY:</a></label>
			<input type="text" class="form-control" id="ocrspace_apikey" name="ocrspace_apikey" required> 
		  </div>
		  <div class="form-group">
			<label for="firstuser">Ingame-Name des Tool-Admins / DEV-User</label>
			<input type="text" class="form-control" id="firstuser" name="firstuser" maxlength = "12" required> 
		  </div>
		  <div class="form-group">
		    <label for="firstuserpw">Gewünschtes Passwort des Tool-Admins / DEV-User</label>
		    <input type="password" class="form-control" id="firstuserpw" name="firstuserpw" minlength="5" required> 
		  </div>
		  <div class="form-group">
		    <label for="clantag">Guppen-Kürzel / Clan-Tag</label>
		    <input type="text" class="form-control" id="clantag" name="clantag" maxlength = "3" required> 
		  </div>	
		  <div class="form-group">
		    <label for="clanname">Gruppen-Name / Clan-Name</label>
		    <input type="text" class="form-control" id="clanname" name="clanname" maxlength = "16" required> 
		  </div>			  
		  <div class="form-group text-center">
		   <button type="submit" name="do" value="createdb" class="btn btn-success">Installieren</button>
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