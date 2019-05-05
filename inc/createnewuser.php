<?php 
include "verify.php";
	
if(!isset($_POST['createuser'])) { ?>
<form action="index.php?action=createnewuser" method="POST" autocomplete="no">
  <div class="form-group">
    <label for="telegram">Ingame-Name:</label>
    <input type="ign" class="form-control" id="ign" name="ign" value="" required>
  </div>
  <div class="form-group">
  <input type="password" style="display:none"> <!-- Prevent Password-Autofill -->
    <label for="pwd">Passwort-Vorschlag: <span id = "passsuggest"><?php echo generatePassword ( 8,1,2 ); ?></span>
	
 <br><input type="button" class="btn-info" id = "copypw" onclick="copysuggestedpw()" value="Übernehmen und kopieren" /></label>

	<input autocomplete="new-password" type="password" class="form-control" id="pwd" name="pwd" value="" required>
  </div>
  

<?php
if(isdev()){
	?>
	  <div class="form-group">
		<label for="ugroup" class = "control-label">Gruppe:</label>
		 <select  id="ugroup" name = "ugroup" class = "form-control">	    
	<?php 
	$grpqry = 'SELECT id,tag,name FROM '.$config->db_pre.'groups ORDER BY name';
	foreach ($pdo->query($grpqry) as $group){
		unset($selected);
		if ($group['id'] == $_SESSION['gid']) {
			$selected = ' selected';
		}
		 echo '<option value="'.$group['id'].'" '.$selected.'>['.$group['id'].'] ['.$group['tag'].'] '.$group['name'].'</option>';
	}
	?>
		 </select>   
	</div>
	<?php
}
if (isadmin()){
	if($_SESSION['role'] === 1){
		array_pop($rights);
	}
?>
  <div class="form-group">
    <label for="inputUser" class = "control-label">Stattool-Rechte:</label>
     <select  id="inputUser" name = "role" class = "form-control">	    
<?php 
foreach($rights as $key => $value) {
 echo '<option value="'.$key.'" >'.ucfirst($value).'</option>';
}
?>
	 </select>   
  </div>
<?php } ?>
	
  <div class="form-group">
    <label for="telegram">Telegram:</label>
    <input type="telegram" class="form-control" id="telegram" name = "telegram"  value = ""> 
  </div>
    <div class="form-group">
      <label for="notes">Notizen:</label>
	<textarea class="form-control input-md"  rows="15" name = "notes"></textarea>
  </div>
<button type="submit" name = "createuser" value="AddUser" class="btn btn-success">Nutzer anlegen</button>
</form>
<?php
}

if(isset($_POST['createuser']) && $_POST['createuser'] == "AddUser" & isadminormod()){
	$curr_datetime =date("Y-m-d H:i:s");
	$notetime = ($_POST['notes'] > "" ? $curr_datetime : ''); 
	$groupid = ($_POST['ugroup'] > "" ? intval($_POST['ugroup']) : $_SESSION['gid']); 

	if (isadmin()){
		$urole = $_POST['role'];
	}
	
	if (ismod()){
		$urole = 3;
	}
	
		$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."users(gid, telegram, notes, notetime, ign, passwd, role, created_at, active)
			VALUES(:gid, :telegram, :notes, :notetime, :ign, :passwd, :role, :created_at, :active)");


		$statement->execute(array(
			"gid" => $groupid,
			"telegram" => $_POST['telegram'],
			"notes" => $_POST['notes'],
			"notetime" => $notetime,
			"ign" =>  $_POST['ign'],
			"passwd" => password_hash($_POST['pwd'], PASSWORD_DEFAULT),
			"role" => $urole,
			"created_at" => $curr_datetime,
			"active" => 1
		));

	
		$count = $statement->rowCount();
		if($count =='1'){     
			echo '<div class="alert alert-success"><strong>Nutzer '.$_POST['ign'].' angelegt!</strong> Weiterleitung...</div>';
			header('Refresh: 2; URL=?action=usrmgr&uid='.$pdo->lastInsertId().'');
	}
		}
?>