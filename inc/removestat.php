<?php

include "verify.php";

if (!isset($_GET['id']) && !is_numeric($_GET['id'])) { exit; }

$uid 	= preg_replace('/[^0-9]/','',$_GET['uid']);
$id 	= preg_replace('/[^0-9]/','',$_GET['id']);
$uname 	= getuname($uid);	
$delinfo = "Die gewählte Statistikzeile wird entfernt!";	
	
echo '<br><div class="alert alert-warning">
  <strong>Sicherheitsabfrage</strong> <br>Löschvorgang der Statistik Nr. '.$id.' bei Spieler/in: <b>'.$uname .'</b> ausführen?<br>'.$delinfo.'<br>
</div><p><hr>
<form action = "?action=doremovestat" method = "POST">
  <div class="form-check">
   <input name = "delstatid" type = "hidden" type="text" value = "'.$id.'">
    <input name = "delstatuid" type = "hidden" type="text" value = "'.$uid.'">
  </div>
  <button type="submit" class="btn btn-danger btn-block" name = "delstat">JA - Statistik Nr. '.$id.' bei Spieler/in: <b>'.$uname .' endgültig löschen!</button>
</form><br><a href="?action=stats&uid='.$uid.'" class="btn btn-info btn-block" role="button">'.$uname.' ´s Statistik anschauen</a></p>';
?>