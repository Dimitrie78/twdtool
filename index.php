<?php
session_start();
ob_start();
header("Content-Type: text/html; charset=utf-8");
if (!file_exists("conf/config.php")){exit('Config Datei fehlt. Bitte installieren.');}
$config = include("conf/config.php");
$errmode = 'EXCEPTION'; // SILENT im Produktivbetrieb, EXCEPTION oder WARNING beim Debuggen
include("inc/functions.php");
include("inc/header.php");
define('TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(TIMEZONE);

try {  
  $options = array(
        PDO::ATTR_ERRMODE => 'PDO::ERRMODE_'.$errmode, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );  
  $pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword,$options);


  $vers = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
  if (stripos($vers, 'MariaDB')===false)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

}catch(PDOException $e){
    echo "Datenbankverbindung fehlgeschlagen: " . $e->getMessage();
  exit;
}

$rights = array("3" => "User",
               "2" => "Mod",
               "1" => "Admin",
			   "99" => "Dev");
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <title>TWD Stattool</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="inc/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="inc/js/functions.js"></script>
<script src="inc/js/jquery.ui.touch-punch.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/<?=$config->theme;?>/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link href="inc/css/style.css" rel="stylesheet">
<link type="text/css" rel="stylesheet" media="only screen and (max-width: 720px)" href="inc/css/resp.css" />
</head>
<body>

<?php
if (isset($_POST["loginname"]) && isset($_POST["loginpasswort"])){
	$ign = $_POST["loginname"];
	$passwd = $_POST['loginpasswort'];

	$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."users WHERE ign = :ign");
	$result = $statement->execute(array('ign' => $ign));
	$user = $statement->fetch();
	//gruppen die sich immer einloggen dürfen - egal ob aktiv oder inaktiv (dev und admin)
	$alwaysallowed_grp = array(1, 99);
	  //Überprüfung des Passworts
	if ($user !== false && password_verify($passwd, $user['passwd'])) {
		if ($user['active'] == 0 AND !in_array($user['role'],$alwaysallowed_grp))
		{
			 $fail = '<div class="modal-heading">
				<h2 class="text-center">Dein Account ist inaktiv</h2>
			</div>';
			unset($_SESSION["login"]); 
		}
		else
		{
			$_SESSION['userid'] = $user['id'];
			$_SESSION['role'] = $user['role'];
			$_SESSION['ign'] = $user['ign'];
			$_SESSION['gid'] = $user['gid'];
			$_SESSION["login"] = 1;
			$fail = "";
			$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET lastlogin = NOW() WHERE id = :id');
			$query->execute(array(':id' => $_SESSION['userid']));
		}
    } else {
		$fail = '<div class="modal-heading">
			<h2 class="text-center">Login fehlgeschlagen</h2>
		</div>';
		unset($_SESSION["login"]);
    }
}

if (!isset($_SESSION["login"])||($_SESSION["login"] != 1)){
	include("login.php");
	exit;
} 
	
if (isset($_GET["action"]) && ($_GET["action"] == "logout")){
	unset($_SESSION["login"]);
	session_destroy();
	header('Location: index.php');
	exit;
}

$fails = $pdo->query("SELECT count(id) as anz FROM ".$config->db_pre."stats WHERE fail = 1")->fetch();
?>

    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">TWD Stats</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
		    <li><a href="index.php"><span style = "margin-right:5px;" class="fas fa-home"></span>Home</a></li>
			<li><a href="?action=stats&uid=<?php echo $_SESSION['userid'];?>"><span style = "margin-right:5px;" class="fas fa-chart-line"></span>Stats</a></li>	
			<li><a href="?action=top"><span style = "margin-right:5px;" class="fas fa-award"></span>Topliste</a></li>	           
			<li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" ><span class="fas fa-flag-checkered"></span>  Auswertung <span class="caret"></span></a>
              <ul class="dropdown-menu">
		<li><a href="?action=levelingnumbers"><span style = "margin-right:5px;" class="fas fa-sort-numeric-up"></span> Anstieg in Zahlen</a></li>
		<li><a href="?action=currentstats"><span style = "margin-right:5px;" class="fas fa-star"></span> Aktuelle Statistik</a></li>
		<li><a href="?action=avg"><span style = "margin-right:5px;" class="fas fa-stopwatch"></span> Durchschnitt</a></li>
		<li><a href="?action=custom_stat"><span style = "margin-right:5px;" class="fas fa-stopwatch"></span> Custom</a></li>
		<?php if (isadminormod()){ ?>								
		<li><a href="?action=alldata"><span style = "margin-right:5px;" class="fas fa-list-alt"></span> Komplettab.</a></li>
		<?php } ?>
              </ul>
            </li>
		<?php if (isadminormod()){ ?>
		<li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fas fa-users-cog"></span>  User  <span class="caret"></span></a>
              <ul class="dropdown-menu">
				<li><a href="?action=usrmgr"><span style = "margin-right:5px;" class="fas fa-edit"></span>Ändern / Löschen</a></li>
					<li><a href="?action=createnewuser"><span style = "margin-right:5px;" class="fas fa-user-plus"></span>Neu anlegen</a></li>
              </ul>
            </li>
		<?php } ?>
		 <?php if (isadmin()){ ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fa fa-cog"></span>  ADMIN <span class="caret"></span></a>
              <ul class="dropdown-menu">
				<li><a href="?action=uploadimg"><span style="margin-right:5px;" class="fas fa-upload"></span> 1. Bilder hochladen</a></li>
				<li><a href="?action=prepimg"><span style="margin-right:5px;" class="fas fa-bolt"></span> 2. Bilder konvertieren</a></li>
				<li><a href="2ocr/" target="_new"><span style="margin-right:5px;" class="fas fa-search"></span> 3. Konvertierte aufrufen</a></li>
				<li><a href="?action=import"><span style="margin-right:5px;" class="fas fa-save"></span> 4. Auslesen und speichern</a></li> 
				<li><a href="?action=fails"><span style="margin-right:5px;" class="fas fa-bug"></span> 5. Auslesefehler fixen &nbsp;&nbsp;<span class="badge badge-warning"><?php echo $fails['anz']; ?></span></a></li> 
				<li><a href="?action=ocrfix"><span style="margin-right:5px;" class="fas fa-rocket"></span> 6. OCR-Verbessern</a></li> 
				<li><a href="?action=frontpageedit"><span style="margin-right:5px;" class="fas fa-edit"></span> 7. Startseiten Editor</a></li> 
				<li><a href="?action=setHandyType"><span style="margin-right:5px;" class="fas fa-edit"></span> 8. Handy Screen Editor</a></li>
				<?php if (isdev()){ ?>
				  <li><a href="?action=groupedit"><span style="margin-right:5px;" class="fas fa-edit"></span> 9. Gruppen Editor</a></li> 
				<?php } ?>
					
              </ul>
            </li>
			<?php } ?>
          </ul>
          <ul class="nav navbar-nav navbar-right">
		  <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="fas fa-user"></span> <?php echo $_SESSION['ign'] . " [".$rights[$_SESSION['role']] . "]"; ?>  <span class="caret"></span></a>
			  <ul class="dropdown-menu">
				<li><a href="index.php?action=myprofile"><span style = "margin-right:5px;" class="fas fa-cog"></span> Einstellungen</a></li>
				<li><a href="index.php?action=logout" target = "_new"><span style = "margin-right:5px;" class="fas fa-sign-out-alt"></span> Ausloggen</a></li>
			  </ul>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
	
<div class="container">

    <div class="panel panel-primary">
	
      <div class="panel-heading"><?php echo (isset($panelhead)?$panelhead:"TWD-Stats"); ?></div>
      <div class="panel-body">
	  
<?php
## NUR FÜR DEV

if (isdev()){
  if (isset($_GET["action"]))
switch ($_GET['action']) {
	case "groupedit":
    $file = "inc/".$_GET['action'].".php";
    if(is_file($file)) {
		include($file);
	}
	break;
  }
} ## Ende DEV


# Nur für ADMIN
if (isadmin()){
  if (isset($_GET["action"]))
switch ($_GET['action']) {
	case "setHandyType":
	case "uploadimg":
	case "prepimg":
    case "import":
    case "ocrfix":
    case "frontpageedit":
    $file = "inc/".$_GET['action'].".php";
    if(is_file($file)) {
		include($file);
	}
	break;
  }
} ## Ende Admin



# FÜR ADMIN+MOD
if (isadminormod()){
  if (isset($_GET["action"]))
	switch ($_GET['action']) {	
	case "alldata":
	case "createnewuser":
	case "usrmgr":
	case "doremovestat":
	case "notes":
	case "editstat":
	case "doeditstat":
	case "removestat":
	case "addstat":
	case "doaddstat":
	case "removeusr":
	case 'doremoveuser':
	#Auslesefehler beheben
	case "fails":
	case "editfail":
	$file = "inc/".$_GET['action'].".php";
	if(is_file($file)) {
		include($file);
	}
	break;
  }
} 

#FÜR die User
if (isset($_GET["action"]))
switch ($_GET['action']) {
    case "myprofile":
    case "stats":
    case "top":
    case "levelingnumbers":
    case "currentstats":
    case "avg":
    case "custom_stat":
	$file = "inc/".$_GET['action'].".php";
	if(is_file($file)) {
		include($file);
	}
	break;
} 

if (!isset($_GET["action"])){
	$statement = $pdo->prepare("SELECT ".$config->db_pre."groups.name as name, ".$config->db_pre."groups.tag as tag, ".$config->db_pre."groups.id as id FROM ".$config->db_pre."groups
	INNER JOIN ".$config->db_pre."users ON ".$config->db_pre."users.gid = ".$config->db_pre."groups.id where ".$config->db_pre."users.id = :uid");

	$result = $statement->execute(array('uid' => $_SESSION['userid']));
	$group = $statement->fetch();
	
	
	$user = $pdo->query("SELECT COUNT(id) as anz FROM ".$config->db_pre."users WHERE active > 0 AND gid = ".$_SESSION['gid']."")->fetch();
	
	##todo: joinen, brauchen die gid für das korrekte datum!
	$stats = $pdo->query("SELECT max(date) as statupdate from ".$config->db_pre."stats")->fetch();
	if($stats['statupdate'] != "0000-00-00 00:00:00" AND $stats['statupdate'] != ""){
		$datetime = new DateTime($stats['statupdate']);
		$lstatupdate = "<br>Die letzten Statistiken wurden am: " .$datetime->format('d.m.Y H:i:s') ." übertragen.";
		unset($datetime);
	}
	else{
		$lstatupdate = '<br>Es wurden noch keine Statistiken übertragen.';
	}
		
	$news = $pdo->query("SELECT text, ndate FROM ".$config->db_pre."news WHERE id = 1 AND active = 1")->fetch();
	
?>

	<p>Willkommen <b><?php echo $_SESSION['ign']; ?></b>, bei den <?=$group['name'];?> [<?=$group['tag'];?>]
	<br><br>Wir haben derzeit: <?php echo $user['anz']; ?> Spieler in der Gruppe.<?php echo $lstatupdate; ?><br>
	<?=nl2br($news['text']);?>
	
<?php } ?>
	</div>
</div> 
<div class="well">
V 1.8.3 Multigroup Beta
</div>
</div>
<script src="inc/js/bootstrap.min.js"></script>
<script src="inc/js/bootstrap-tabs.js"></script>
<script src="inc/js/highcharts.js"></script>
<script src="inc/js/twd.js"></script>
  </body>
</html>