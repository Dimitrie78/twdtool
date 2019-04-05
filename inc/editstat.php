<?php

include "verify.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$id = preg_replace('/[^0-9]/','',$_GET['id']);
	$uid = preg_replace('/[^0-9]/','',$_GET['uid']);

	if (!stat_exists($id)){
		echo '<div class="alert alert-danger">
	  <strong>Abbruch</strong> Gewählte Statistik-ID <b>'.$uid.'</b> existiert nicht. Aufrufen nicht möglich</div>	<a href="?action=stats" name="back" class="btn btn-info" role="button">Zurück</a>';
		exit();
	}

	$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."stats WHERE id = :id");
	$result = $statement->execute(array('id' => $_GET['id']));
	$stat = $statement->fetch();
	$datetime = new DateTime($stat['date']);

?>
Statistik Nr <?= $id ?>  <?php if($uid > 0){echo 'von '.getuname($uid); }?> <br>
<form action="?action=doeditstat&id=<?php echo $id;?>" method = "POST" autocomplete="no">
  <input type="hidden" name="editid" type="text" value="<?php echo ($id);?>">
  <input type="hidden" name="uid" type="text" value="<?php echo ($uid);?>">
  <div class="form-group">
    <label for="date">Datum / Zeit:</label>
    <input type="text" class="form-control" id="date" name = "date"  value = "<?php echo $datetime->format('d.m.Y H:i:s'); ?>">
  </div>
   
   <div class="form-group">
    <label for="exp">Erfahrungspunkte (####/####):</label>
    <input type="text" class="form-control" id="exp" name = "exp"  value = "<?php echo $stat['exp']; ?>">
  </div>
  
   <div class="form-group">
    <label for="streuner">Streuner:</label>
    <input type="number" class="form-control" id="streuner" name = "streuner"  value = "<?php echo $stat['streuner']; ?>">
  </div>
  
   <div class="form-group">
    <label for="menschen">Menschen:</label>
    <input type="number" class="form-control" id="menschen" name = "menschen"  value = "<?php echo $stat['menschen']; ?>">
  </div>
  
   <div class="form-group">
    <label for="gespielte_missionen">Gespielte Missionen:</label>
    <input type="number" class="form-control" id="gespielte_missionen" name = "gespielte_missionen"  value = "<?php echo $stat['gespielte_missionen']; ?>">
  </div>
  
   <div class="form-group">
    <label for="abgeschlossene_missonen">Abgeschlossene Missionen:</label>
    <input type="number" class="form-control" id="abgeschlossene_missonen" name = "abgeschlossene_missonen"  value = "<?php echo $stat['abgeschlossene_missonen']; ?>">
  </div>
  
  <div class="form-group">
    <label for="gefeuerte_schuesse">Gefeuerte Schüsse:</label>
    <input type="number" class="form-control" id="gefeuerte_schuesse" name = "gefeuerte_schuesse"  value = "<?php echo $stat['gefeuerte_schuesse']; ?>">
  </div>

  <div class="form-group">
    <label for="haufen">Kisten (Haufen):</label>
    <input type="number" class="form-control" id="haufen" name = "haufen"  value = "<?php echo $stat['haufen']; ?>">
  </div>
  
  <div class="form-group">
    <label for="heldenpower">Heldenstärke:</label>
    <input type="number" class="form-control" id="heldenpower" name = "heldenpower"  value = "<?php echo $stat['heldenpower']; ?>">
  </div>
  
  <div class="form-group">
    <label for="waffenpower">Waffenstärke:</label>
    <input type="number" class="form-control" id="waffenpower" name = "waffenpower"  value = "<?php echo $stat['waffenpower']; ?>">
  </div>
  
  <div class="form-group">
    <label for="karten">Anzahl Karten:</label>
    <input type="number" class="form-control" id="karten" name = "karten"  value = "<?php echo $stat['karten']; ?>">
  </div>
  
  <div class="form-group">
    <label for="gerettete">Gerettete Menschen:</label>
    <input type="number" class="form-control" id="gerettete" name = "gerettete"  value = "<?php echo $stat['gerettete']; ?>">
  </div>

  
  <div class="clearfix">
	  <div class="pull-left">
		<button type="submit" name = "updatestat" class="btn btn-success">Update</button>
	  </div>
	  <div class="pull-right">
		<a href="?action=removestat&id=<?php echo ($id);?>&uid=<?= $uid;?>" name="removestat" class="btn btn-danger" role="button">Stat entfernen</a>
	  </div>
  </div>
</form>

<?php
} // End IF
?>