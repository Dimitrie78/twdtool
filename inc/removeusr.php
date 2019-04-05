<?php

include "verify.php";

if (!isset($_GET['uid']) && !is_numeric($_GET['uid'])) { exit; }

$uid = preg_replace('/[^0-9]/','',$_GET['uid']);

if ((ismod() & geturole($uid) <> 3)){
	echo 'Nice try...';
	exit;
}

if (isset($_GET['uid']) & !user_exists($uid)){
	echo '<div class="alert alert-danger">
  <strong>Abbruch</strong> Gewählte User-ID: <b>'.intval($_GET['uid']).'</b> existiert nicht. Funktion nicht ausführbar</div>	<a href="?action=usrmgr" name="back" class="btn btn-info" role="button">Zurück</a>';
	exit();
}
	
$delinfo = "Es wird der Account im TWD-Tool sowie die Statistik entfernt!";	
	
echo '<br><div class="alert alert-warning">
  <strong>Achtung!</strong> Löschvorgang bei Spieler/in: <b>'.getuname($uid).'</b> ausführen?<br>'.$delinfo.'<br>
  Beachte, das diese Aktion ggf. die Rücksprache mit dem Team erfordert!
</div><p><hr>
<form action = "?action=doremoveuser" method = "POST">
  <div class="form-check">
   <input name = "deluid" type = "hidden" type="text" value = "'.$_GET['uid'].'">
   <input name = "delign" type = "hidden" type="text" value = "'.getuname($uid).'">
   
    <input type="checkbox" class="form-check-input" id="suredelete" name = "suredelete">
    <label class="form-check-label" for="suredelete">Ja wirklich löschen</label>
  </div>
  <button type="submit" class="btn btn-danger btn-block">JA - '.getuname($uid).' endgültig löschen!</button>
</form><br><a href="?action=stats&uid='.$dusr['id'].'" class="btn btn-info btn-block" role="button">'.getuname($uid).' ´s Statistik anschauen</a></p>';
?>