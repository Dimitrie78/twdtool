<?php
include "verify.php";
if(!isset($_POST["domove"])){
?>
    <form class="form-vertical" role="form" method = "POST" action = "?action=groupmove" >
    <div class="row">
	<div class="form-group col-xs-6" style="width:auto;">
	<label for="startGroup" class = "control-label">Aus Gruppe: <span class="fas fa-arrow-right"></span></label>
     <select onchange="this.form.submit()" id="startGroup" name = "startGroup" class = "form-control" style="width:auto;min-width:200px;">
	 <option value="allgrp" <?php if (isset($_POST['startGroup']) && $_POST['startGroup'] == 'allgrp'){echo ' selected';} ?>>--Alle--</option>
	 <option value="uc" <?php if (isset($_POST['startGroup']) && $_POST['startGroup'] == 'uc'){echo ' selected';} ?>>--Ohne Gruppe--</option>
	<?php
	$sql = 'SELECT id, tag, name FROM `'.$config->db_pre.'groups` ORDER BY sort ASC';
	

    foreach ($pdo->query($sql) as $row) {
	
	if ($_POST['startGroup'] == $row['id'])
	{
	$gidselected = ' selected';
	}
	else
	{
	$out .= '<option value="'.$row['id'].'" >['.$row['tag'].'] '.$row['name'].'</option>';
	}
       echo '<option value="'.$row['id'].'" '.$gidselected.'>['.$row['tag'].'] '.$row['name'].'</option>';
	    $gidselected = '';
    }
		if ($_POST['startGroup'] != 'uc'){
	$out .= '<option value="uc" >--Ohne Gruppe--</option>';
		}
	?>
	
	 </select> </div>

 
<div class="form-group col-xs-6" style="width:auto;">
<label for="targetGroup" class = "control-label">In Gruppe: </label>     
      <select  id="targetGroup" name = "targetGroup" class = "form-control" style="width:auto;min-width:200px">	    
<?php
echo $out;
?>
   </select>   
  </div>
 </div>

  <input type="hidden" name="moveform" value="1">
 <table class="table table-hover datatable table-bordered" style="width:auto;min-width:200px">
  <thead>
    <tr>
      <th scope="col">Wahl</th>
	  <th scope="col">Gruppe</th> 
      <th scope="col">Name</th>
    </tr>
  </thead>
  <tbody>
<?php


if (isset($_POST['startGroup']) && $_POST['startGroup'] == 'allgrp' || !isset($_POST['startGroup'])){
$uqry = $pdo->prepare('SELECT U.id, G.id AS gid, G.tag, U.ign
FROM  `'.$config->db_pre.'users` U
LEFT JOIN `'.$config->db_pre.'groups` G ON G.id = U.gid ORDER BY tag, ign');	
$uqry->execute();
}

if (isset($_POST['startGroup']) && $_POST['startGroup'] == 'uc'){
$uqry = $pdo->prepare('SELECT U.id, G.id AS gid, G.tag, U.ign
FROM  `'.$config->db_pre.'users` U
LEFT JOIN `'.$config->db_pre.'groups` G ON G.id = U.gid
WHERE `gid` = 0 ORDER BY ign');	
$uqry->execute();
}

if (isset($_POST['startGroup']) && is_numeric($_POST['startGroup'])){
$uqry = $pdo->prepare('SELECT U.id, G.id AS gid, G.tag, U.ign
FROM  `'.$config->db_pre.'users` U
LEFT JOIN `'.$config->db_pre.'groups` G ON G.id = U.gid
WHERE `gid` = :id ORDER BY ign');
$uqry->execute(array('id' => $_POST['startGroup'] ));
}


$count = $uqry->rowCount();

#$uqry = $statement->fetch();

if ($count === 0){
echo 'Es wurden noch keine Statistiken hochgeladen!';
}


foreach ($uqry as $row) {
	echo '<tr>
			  <th style="text-align: center;"><input id = "id'.$row['id'].'" type="checkbox" class="form-check-input" name="mvid[]" value = "'.$row['id'].'">
			  <label for="id'.$row['id'].'"><span></span></label>
</th>
			  <td>'.($row['tag'] ? $row['tag'] : 'Ohne').'</td>
			  <td>'.$row['ign'].'</td>
         </tr>';
	}
?>
   </tbody>
</table>
  <button type="submit" class="btn btn-success" name = "domove" value = "1">Verschieben</button>
</form>

<?php
}
else{
	if(isset($_POST["mvid"])){
   $qryids = implode(',', $_POST["mvid"]);
   $tgid = ($_POST['targetGroup'] == 'uc') ? '0' : $_POST['targetGroup'];
   $uqry = $pdo->query('UPDATE `'.$config->db_pre.'users` SET gid = '.$tgid.' WHERE id IN ('.$qryids.')');
   
	   if ($uqry->execute()){
		?> Ausführung abgeschlossen!<br>
		 <a href="?action=groupmove" class="btn btn-success btn-lg active" role="button" >Zurück - OK</a>
		<?php
	   }
	   else {
		    ?><a href="?action=groupmove" class="btn btn-warning btn-lg active" role="button" >Zurück - FAIL</a><?php
	   }
  }
  else{
	  ?>Es gibt nichts zu updaten, denn es wurde nichts ausgewählt.
		 <a href="?action=groupmove" class="btn btn-primary btn-lg active" role="button" >Zurück</a>
		<?php
  }
}
?>