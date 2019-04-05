<?php
include "verify.php";

if (!isset($_GET['uid']) && !is_numeric($_GET['uid'])) { exit; }

$uid = preg_replace('/[^0-9]/','',$_GET['uid']);

echo 'Statistik für '.getuname($uid).' hinzufügen<br>';
if (!user_exists($uid)){
	echo '<div class="alert alert-danger">
  <strong>Abbruch</strong> Gewählte User-ID <b>'.$uid.'</b> existiert nicht. Eintragen nicht möglich</div>	<a href="?action=stats" name="back" class="btn btn-info" role="button">Zurück</a>';
	exit();
}

?>
<form action="?action=doaddstat" method = "POST" autocomplete="no">
  <input  type = "hidden" name = "uid" type="text" value = "<?php echo ($uid);?>">
  <div class="form-group">
    <label for="date">Datum / Zeit: (tt:mm:jjjj ##:##)</label>
    <input type="text" class="form-control" id="date" name = "date" required value = "<?php echo date("d.m.Y H:i:s"); ?>">
  </div>
   
   <div class="form-group">
    <label for="exp">Erfahrungspunkte (####/####):</label>
    <input type="text" class="form-control" id="exp" name = "exp" required>
  </div>
  
   <div class="form-group">
    <label for="streuner">Streuner:</label>
    <input type="number" class="form-control" id="streuner" name = "streuner" required>
  </div>
  
   <div class="form-group">
    <label for="menschen">Menschen:</label>
    <input type="number" class="form-control" id="menschen" name = "menschen" required>
  </div>
  
   <div class="form-group">
    <label for="gespielte_missionen">Gespielte Missionen:</label>
    <input type="number" class="form-control" id="gespielte_missionen" name = "gespielte_missionen" required>
  </div>
  
   <div class="form-group">
    <label for="abgeschlossene_missonen">Abgeschlossene Missionen:</label>
    <input type="number" class="form-control" id="abgeschlossene_missonen" name = "abgeschlossene_missonen" required>
  </div>
  
  <div class="form-group">
    <label for="gefeuerte_schuesse">Gefeuerte Schüsse:</label>
    <input type="number" class="form-control" id="gefeuerte_schuesse" name = "gefeuerte_schuesse" required>
  </div>

  <div class="form-group">
    <label for="haufen">Kisten (Haufen):</label>
    <input type="number" class="form-control" id="haufen" name = "haufen" required>
  </div>
  
  <div class="form-group">
    <label for="heldenpower">Heldenstärke:</label>
    <input type="number" class="form-control" id="heldenpower" name = "heldenpower" required>
  </div>
  
  <div class="form-group">
    <label for="waffenpower">Waffenstärke:</label>
    <input type="number" class="form-control" id="waffenpower" name = "waffenpower" required>
  </div>
  
  <div class="form-group">
    <label for="karten">Anzahl Karten:</label>
    <input type="number" class="form-control" id="karten" name = "karten" required>
  </div>
  
  <div class="form-group">
    <label for="gerettete">Gerettete Menschen:</label>
    <input type="number" class="form-control" id="gerettete" name = "gerettete" required>
  </div>

  
  <div class="clearfix">
	  <div class="pull-left">
		<button type="submit" name = "addstat" class="btn btn-success">Anlegen</button>
	  </div>
	  <div class="pull-right">
		<a href="?action=stats&uid=<?php echo ($uid);?>" name="back" class="btn btn-info" role="button">Zurück</a>
	  </div>
  </div>
</form>