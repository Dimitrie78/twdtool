<?php
session_start();
ob_start();
header("Content-Type: text/html; charset=utf-8");
if (!file_exists("conf/config.php")){exit('Config Datei fehlt. Bitte installieren.');}
$config = include("conf/config.php");
$errmode = 'EXCEPTION'; // SILENT im Produktivbetrieb, EXCEPTION oder WARNING beim Debuggen
$cookie_name = 'twdtool';
$cookie_time = (3600 * 24 * 365); // 365 Tage
$alwaysallowed_grp = array(1, 99); //gruppen die sich immer einloggen dürfen - egal ob aktiv oder inaktiv (dev und admin)
include("inc/functions.php");
include("inc/header.php");
define('TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(TIMEZONE);

include("inc/db_con.php");

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
<script type="text/javascript" src="inc/js/jquery.tabledit.js"></script>
<script type="text/javascript" src="inc/js/functions.js"></script>
<script src="inc/js/jquery.ui.touch-punch.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/<?=$config->theme;?>/bootstrap.min.css">
<link rel="stylesheet" href="inc/css/govicons/css/govicons.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link href="inc/css/style.css" rel="stylesheet">
<link type="text/css" rel="stylesheet" media="only screen and (max-width: 720px)" href="inc/css/resp.css" />
</head>
<body>

<?php
if ((isset($_GET["openKey"]) && $_GET["openKey"]<>"")||(isset($_POST["createOpenKey"]) && $_POST["createOpenKey"]<>"")){
  print '<div class="container">

    <div class="panel panel-primary">
	
      <div class="panel-heading"><?php echo (isset($panelhead)?$panelhead:"TWD-Stats"); ?></div>
      <div class="panel-body">';
      include("inc/custom_stat.php");
  
}else{

if (isset($_POST["loginname"]) && isset($_POST["loginpasswort"])){
	$ign = $_POST["loginname"];
	$passwd = $_POST['loginpasswort'];

	$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."users WHERE ign = :ign");
	$result = $statement->execute(array('ign' => $ign));
	$user = $statement->fetch();
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
			session_regenerate_id();
			$_SESSION['userid'] = $user['id'];
			$_SESSION['role'] = $user['role'];
			$_SESSION['ign'] = $user['ign'];
			$_SESSION['gid'] = $user['gid'];
			$_SESSION["login"] = 1;
			$fail = "";
			$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET lastlogin = NOW() WHERE id = :id');
			$query->execute(array(':id' => $_SESSION['userid']));
			
			if(isset($_POST['autologin']) == 1)
				{
					$password_hash = password_hash($passwd, PASSWORD_DEFAULT); 
					setcookie ($cookie_name, 'usr='.$ign.'&hash='.$password_hash, time() + $cookie_time);
				}
		}
    } else {
		$fail = '<div class="modal-heading">
			<h2 class="text-center">Login fehlgeschlagen</h2>
		</div>';
		unset($_SESSION["login"]);
    }
}

if (!isset($_SESSION["login"])||($_SESSION["login"] != 1) || !isset($_SESSION["gid"])){
	
	if(isSet($_COOKIE[$cookie_name])){
		parse_str($_COOKIE[$cookie_name],$ck);
		#$ck['usr'];
		#$ck['hash'];
		#check stuff and...
		
		$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."users WHERE ign = :ign");
		$result = $statement->execute(array('ign' => $ck['usr']));
		$user = $statement->fetch();
		  //Überprüfung des Passworts
		if ($user !== false && password_verify($ck['hash'], $user['passwd'])) {
			if ($user['active'] == 0 AND !in_array($user['role'],$alwaysallowed_grp))
			{
				 $fail = '<div class="modal-heading">
					<h2 class="text-center">Dein Account ist ungültig</h2>
				</div>';
				unset($_SESSION["login"]); 
			}						
		}
		else{
			session_regenerate_id();
			$_SESSION['userid'] = $user['id'];
			$_SESSION['role'] = $user['role'];
			$_SESSION['ign'] = $user['ign'];
			$_SESSION['gid'] = $user['gid'];
			$_SESSION["login"] = 1;
			$fail = "";
			$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET lastlogin = NOW() WHERE id = :id');
			$query->execute(array(':id' => $_SESSION['userid']));
			header('Location: index.php');
		}
	}
	else{
	include("login.php");
}	
	exit;
} 
	
if (isset($_GET["action"]) && ($_GET["action"] == "logout")){
	setcookie($cookie_name, "", time()-3600);
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
				<li><a href="?action=uploadimg"><span style="margin-right:5px;" class="fas fa-upload"></span> Bilder hochladen</a></li>
				<li><a href="?action=prepimg"><span style="margin-right:5px;" class="fas fa-bolt"></span> Bilder konvertieren</a></li>
				<li><a href="2ocr/" target="_new"><span style="margin-right:5px;" class="fas fa-search"></span> Konvertierte aufrufen</a></li>
				<li><a href="?action=import"><span style="margin-right:5px;" class="fas fa-save"></span> Auslesen und speichern</a></li> 
				<li><a href="?action=fails"><span style="margin-right:5px;" class="fas fa-bug"></span> Auslesefehler beheben &nbsp;&nbsp;<span class="badge badge-warning"><?php echo $fails['anz']; ?></span></a></li> 
				<li><a href="?action=ocrfix"><span style="margin-right:5px;" class="fas fa-rocket"></span> OCR-Verbessern</a></li> 
				<li><a href="?action=frontpageedit"><span style="margin-right:5px;" class="fas fa-edit"></span> Startseiten Editor</a></li> 
				<li><a href="?action=setHandyType"><span style="margin-right:5px;" class="fas fa-edit"></span> Handy Screen Editor</a></li>
				<?php if (isdev()){ ?>
				  <li><a href="?action=groupedit"><span style="margin-right:5px;" class="fas fa-edit"></span> Gruppen Editor</a></li> 
				  <li><a href="?action=groupmove"><span style="margin-right:5px;" class="fas fa-edit"></span> Nutzer umgruppieren</a></li> 
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
echo getuid('gorb23 MTW');
include ("inc/routing.php");

if (!isset($_GET["action"])){
	
	if (isdev()){
	$totalactiveusers = $pdo->query("SELECT COUNT(id) as anz FROM ".$config->db_pre."users WHERE active > 0")->fetch();
	$totalusers = $pdo->query("SELECT COUNT(id) as anz FROM ".$config->db_pre."users")->fetch();
	$groupcount = $pdo->query("SELECT COUNT(id) as anz FROM ".$config->db_pre."groups")->fetch();
	}
	
	$statement = $pdo->prepare("SELECT ".$config->db_pre."groups.name as name, ".$config->db_pre."groups.tag as tag, ".$config->db_pre."groups.id as id FROM ".$config->db_pre."groups
	INNER JOIN ".$config->db_pre."users ON ".$config->db_pre."users.gid = ".$config->db_pre."groups.id where ".$config->db_pre."users.id = :uid");

	$result = $statement->execute(array('uid' => $_SESSION['userid']));
	$group = $statement->fetch();
	$ugrpcnt = $statement->rowCount();
	if ($ugrpcnt > 0) {
	$grpname = ', bei <b>['.$group['tag'].'] '.$group['name'].'</b>';
	}
	else
	{
	$grpname = '. Dein Account befindet sich momentan in keiner Gruppe.';
	}

	
	$user = $pdo->query("SELECT COUNT(id) as anz FROM ".$config->db_pre."users WHERE active > 0 AND gid = ".$_SESSION['gid']."")->fetch();
	
	$devstats = $pdo->query('SELECT DATE_FORMAT(`date`, "%d.%m.%Y %H:%i:%s") AS statupdate
							FROM  `'.$config->db_pre.'stats`
							ORDER BY `date` DESC 
							LIMIT 1')->fetch();

	$grpstat = $pdo->query('SELECT DATE_FORMAT(S.`date`, "%d.%m.%Y %H:%i:%s") AS statupdate
							FROM  `'.$config->db_pre.'stats` S
							INNER JOIN  `'.$config->db_pre.'users` U ON S.uid = U.id
							WHERE U.gid ='.$_SESSION['gid'].'
							ORDER BY S.date DESC 
							LIMIT 1')->fetch();


	if(isset($grpstat['statupdate'])) {
		$lstatupdate = "Die letzten Statistiken wurden am: ". $grpstat['statupdate'] ." übertragen.";
	}
	else{
	$lstatupdate = 'Es wurden noch keine Statistiken übertragen.';
	}

	$devnews = $pdo->query("SELECT `text`, `ndate` FROM ".$config->db_pre."news
	WHERE `devnews` = 1 AND `active` = 1
	ORDER BY `ndate` DESC LIMIT 1")->fetch();

if($_SESSION['gid'] > 0)
{
	$news = $pdo->query("SELECT text, ndate FROM ".$config->db_pre."news
	WHERE gid = ".$_SESSION['gid']." AND active = 1
	ORDER BY ndate DESC LIMIT 1")->fetch();
}
	
?>

	<p>Willkommen <b><?php echo $_SESSION['ign'];?></b><?php echo $grpname;?>
	<br><br>Es sind derzeit: <b><?php echo $user['anz']; ?></b> Spieler/innen in Deiner Gruppe.<br>
	<?php echo $lstatupdate; ?>
	<?php if (isdev()){ ?>
	<br><br>
    <u>DEV-Stats:</u> <br>User gesamt: <b><?php echo $totalusers['anz'];?></b>
	davon aktiv: <b><?php echo $totalactiveusers['anz'];?></b>
	in <b><?php echo $groupcount['anz'];?></b> Gruppe/n<br>
	Letzter Stat-Eintrag: 	<?php echo (isset($devstats['statupdate']) ? $devstats['statupdate'] : 'Keiner')?><br>
	<?php } if (isset($news['text'])){?>
	<hr><u>News/Claninfo:</u><br>
	<?php echo nl2br($news['text']);
	}
	} if (isset($devnews['text'])){?>
	<hr><u>DEV-News:</u><br>
	<?php echo nl2br($devnews['text']);
	}
	}

?>

	</div>
  </div> 
<div class="well">V 1.8.6</div>
</div>
<script src="inc/js/bootstrap.min.js"></script>
<script src="inc/js/bootstrap-tabs.js"></script>
<script src="inc/js/highcharts.js"></script>
<script src="inc/js/twd.js"></script>
 </body>
</html>
