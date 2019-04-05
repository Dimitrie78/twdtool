<?php
include "verify.php";
$uid =0;
$uid = ((isset($_POST['edituid'])&&$_POST['edituid'] > "") ?$_POST['edituid'] : (isset($_GET['uid'])?$_GET['uid']:0)); 

if (isset($_GET['uid']) & !user_exists($uid)){
	echo '<div class="alert alert-danger">
  <strong>Abbruch</strong> Gewählte User-ID <b>'.$uid.'</b> existiert nicht. Funktion nicht ausführbar</div>	<a href="?action=usrmgr" name="back" class="btn btn-info" role="button">Zurück</a>'; exit();

}

$active = '';
$optall = '';
$optactive = '';
$optinactive ="";
if (isset($_POST['optactive'])&&$_POST['optactive'] == "all"){
$active = '';
$optall = "checked";
}
elseif (isset($_POST['optactive'])&&($_POST['optactive'] == "active" OR !isset($_POST['optactive']))){
$active = 'WHERE active = 1';
$optactive = "checked";
}
elseif (isset($_POST['optactive'])&&$_POST['optactive'] == "inactive"){
$active = 'WHERE active = 0';
$optinactive = "checked";
}
$cqry = $pdo->query('SELECT count(id) as cnt FROM `'.$config->db_pre.'users` '.$active.' ORDER BY ign ASC');
$anz = $cqry->fetchColumn();


?>

<div style="padding-top: 5px;padding-left: 5px; margin-bottom: 2px; border-style: solid; border-color: darkgrey; border-width: thin;">
<form class="form-vertical" role="form" method = "POST" action = "?action=usrmgr" >
    <div class="form-group">
<label class="radio-inline"><input type="radio" value = "all" name="optactive" <?=$optall;?> onchange="this.form.submit()">Alle</label>
<label class="radio-inline"><input type="radio" value = "active" name="optactive" <?=$optactive;?> onchange="this.form.submit()">Aktive</label>
<label class="radio-inline"><input type="radio" value = "inactive" name="optactive" <?=$optinactive;?> onchange="this.form.submit()">Inaktive</label>
<span style="padding-left:5px" >[<?=$anz;?> User]</span>
</div>

    <div class="form-group">
<label for="inputUser" class = "control-label">Mitglied <?php if($uid > 0){ echo 'Nr: '.$uid.' - '.getuname($uid).'  - ';} ?>bearbeiten:</label>
      <select onchange="this.form.submit()" id="inputUser" name = "edituid" class = "form-control" style="width:auto;min-width:200px;">
	 <option value="">--Wählen--</option>
	<?php
	$sql = 'SELECT id, ign FROM `'.$config->db_pre.'users` '.$active.' ORDER BY ign ASC';
		
    foreach ($pdo->query($sql) as $row) {
		if ($uid == $row['id'])
	{
	$selected = ' selected';
	}
       echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['ign'].'</option>';
	    $selected = '';
    }
	
	echo'</select> 
    </div>
</form></div>';


if($uid >""){
	
$statement = $pdo->prepare("SELECT id,ign,notes,telegram,role,created_at,updated_at,active FROM ".$config->db_pre."users WHERE id = :id");
$result = $statement->execute(array('id' => $uid));
$user = $statement->fetch();
#edituser
	
?>
     Erstellt: <?php echo date("d.m.Y", strtotime($user['created_at'])); ?>
 | Update: <?php echo ($user['updated_at'] > "0" ? date("d.m.Y", strtotime($user['updated_at'])) : "keines"); ?>
 
<form action="?action=usrmgr" method = "POST" autocomplete="no">
  <input  type = "hidden" name = "edituid" type="text" value = "<?php echo ($uid);?>">
  <div class="form-group">
    <label for="telegram">Ingame-Name:</label>
    <input type="ign" class="form-control" id="ign" name = "ign"  value = "<?php echo ((isset($_POST['ign'])&&$_POST['ign'])? $_POST['ign'] : htmlentities(isset($user['ign'])?$user['ign']:"")); ?>">
  </div>
    <div class="form-group">
	<input type="password" style="display:none"> <!-- Prevent Password-Autofill -->
    <label for="pwd">Passwort:</label>
	<input autocomplete="new-password" type="password" class="form-control" id="pwd" name="pwd" value = "">
  </div>
  
  <?php if (isadmin()){  ?>
	<div class="form-group">
	 <label for="inputUser" class = "control-label">Stattool-Rechte:</label>
     <select  id="inputUser" name = "role" class = "form-control">	    
<?php 
$role = ($_POST['role'] ? $_POST['role'] : $user['role']);

foreach($rights as $key => $value)
{
	if ($role == $key)
	{
		$selected = ' selected';
    }
	
 echo '<option value="'.$key.'" '.$selected.'>'.ucfirst($value).'</option>';
 $selected = '';
}
?>
	 </select>   
    </div>
  <?php } ?>
	

    <div class="form-group">
    <label for="telegram">Telegram:</label>
    <input type="telegram" class="form-control" id="telegram" name = "telegram"  value = "<?php echo ((isset($_POST['telegram'])&&$_POST['telegram'])? $_POST['telegram'] : htmlentities(isset($user['telegram'])?$user['telegram']:"")); ?>"> 
  </div>
  <div class="form-group">
      <label for="notes">Notizen:</label>
	<textarea class="form-control input-md"  rows="9" name = "notes"><?php echo ((isset($_POST['notes'])&&$_POST['notes'])? $_POST['notes'] : htmlentities(isset($user['notes'])?$user['notes']:"")); ?></textarea>
  </div>
  
  
      <div class="funkyradio">
        <div class="funkyradio-success">
            <input type="checkbox" name="active" id="active" <?php echo ($user['active'] == 1 ? "checked" : ""); ?>/>
            <label for="active">Aktiv</label>
        </div>
    </div>

	


	
  <div class="clearfix">
	  <div class="pull-left">
		<button type="submit" name = "updateuser" class="btn btn-success">Update</button>
	  </div>
<?php 
if ((ismod() & $user['role'] == 3) OR isadmin()){
?>
	  <div class="pull-right">
		<a href="?action=removeusr&uid=<?php echo ($uid);?>" name="removeuser" class="btn btn-danger" role="button">Nutzer entfernen</a>
	  </div>
<?php } ?>
  </div>
</form>
<?php

if (isset($_GET['doneedit']) && $_GET['doneedit'] == 'yes')
{
?>	<hr><div class="alert alert-success">
<strong>Update abgeschlossen!</strong>
</div>
<?php
	
}

}

if(isset($_POST['updateuser']) & (isset($_POST['edituid'])&&is_numeric($_POST['edituid']))){
## iif rein und update hier
$curr_datetime =date("Y-m-d H:i:s");

	if (isadmin()){
if($_POST['pwd'] > "")
{

	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET ign = :ign, role = :role, telegram = :telegram, notes = :notes, notetime = NOW(), updated_at = :updated_at,
							passwd = :passwd, active = :active WHERE id = :id');
	$query->execute(array(':ign' => $_POST['ign'],
						 ':role' => $_POST['role'],
						 ':telegram' => $_POST['telegram'],
						 ':notes' => $_POST['notes'],
						 ':updated_at' => $curr_datetime,
						 ':passwd' => password_hash($_POST['pwd'], PASSWORD_DEFAULT),
						 ':active' => ($_POST['active'] ? 1 : 0),
						 ':id' => $_POST['edituid']));
}
	
else
{
	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET ign = :ign, role = :role, telegram = :telegram, notes = :notes, notetime = NOW(), updated_at = :updated_at, active = :active WHERE id = :id');
	$query->execute(array(':ign' => $_POST['ign'],
						  ':role' => $_POST['role'],
						  ':telegram' => $_POST['telegram'],
						  ':notes' => $_POST['notes'],
						  ':updated_at' => $curr_datetime,
						  ':active' => ($_POST['active'] ? 1 : 0),
						  ':id' => $_POST['edituid']));
}
}

	if (ismod()){
if($_POST['pwd'] > "")
{

	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET ign = :ign, telegram = :telegram, notes = :notes, notetime = NOW(), updated_at = :updated_at,
							passwd = :passwd, active = :active WHERE id = :id');
	$query->execute(array(':ign' => $_POST['ign'],
						 ':telegram' => $_POST['telegram'],
						 ':notes' => $_POST['notes'],
						 ':updated_at' => $curr_datetime,
						 ':passwd' => password_hash($_POST['pwd'], PASSWORD_DEFAULT),
						 ':active' => ($_POST['active'] ? 1 : 0),
						 ':id' => $_POST['edituid']));
}
	
else
{
	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET ign = :ign, telegram = :telegram, notes = :notes, notetime = NOW(), updated_at = :updated_at, active = :active WHERE id = :id');
	$query->execute(array(':ign' => $_POST['ign'],
						  ':telegram' => $_POST['telegram'],
						  ':notes' => $_POST['notes'],
						  ':updated_at' => $curr_datetime,
						  ':active' => ($_POST['active'] ? 1 : 0),
						  ':id' => $_POST['edituid']));
}
}

header('Refresh: 0; URL=?action=usrmgr&uid='.$_POST['edituid'].'&doneedit=yes');
}
?>