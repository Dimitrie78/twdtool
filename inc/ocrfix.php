<?php
include "verify.php";

if (!$_GET['do']){
	
	if($_GET['msg'] == "updatesuccess")
	{
		okmsg('Der Eintrag wurde aktualisiert!');
	}
	if($_GET['msg'] == "updatefail")
	{
		failmsg('Die Eintrag konnten nicht aktualisiert werden!');
	}
	if($_GET['msg'] == "addsuccess")
	{
		okmsg('Der Eintrag wurde hinzugefügt!');
	}
	if($_GET['msg'] == "addfail")
	{
		failmsg('Die Eintrag konnten nicht hinzugefügt werden!');
	}
	if($_GET['msg'] == "deletesuccess")
	{
		okmsg('Der Eintrag wurde entfernt!');
	}
	if($_GET['msg'] == "deletefail")
	{
		failmsg('Die Eintrag konnte nicht entfernt werden!');
	}
?>

  <div class="clearfix">
	  <div class="pull-left">
		Ersetzung eines falsch erkannten OCR-Namens
	  </div>
	  <div class="pull-right">
		<a href="?action=ocrfix&do=add" name="add" class="btn btn-success" role="button"><span class = "fas fa-plus-square"></span> Hinzufügen</a>
	  </div>
  </div>
<?php
	$sql = "SELECT id, searchfor, replacement FROM namefix";
?>
<table class="table table-hover">
  <thead>
    <tr>
      <th style="width: 4%" scope="col">ID</th>
      <th  style="width: 10%" scope="col">Suche</th>
      <th style="width: 10%" scope="col">Ersetzung</th>
      <th  style="width:  60%" scope="col">Edit</th>
    </tr>
  </thead>
  <tbody>
<?php
	foreach ($pdo->query($sql) as $row) {
		echo '<tr>
		  <th scope="row">'.$row['id'].'</th>
		  <td>'.$row['searchfor'].'</td>
		  <td>'.$row['replacement'].'</td>
		  <td><a href = "?action=ocrfix&do=edit&id='.$row['id'].'"><span class="fas fa-edit"></span></a></td>
		</tr>';
	}

	echo '</tbody>
	</table>';
}

if ($_GET['do']=="edit"){
	$id = preg_replace('/[^0-9]/','',$_GET['id']);	
	$statement = $pdo->prepare("SELECT id, searchfor, replacement FROM namefix WHERE id = :id");
	$result = $statement->execute(array('id' => $id));
	$res = $statement->fetch();
	if(!$res){echo 'Keine Daten zu dieser ID!'; exit();}
	
	echo 'Suchen nache / Ersetzen durch...';
?>
	<form action="?action=ocrfix&do=update" method = "POST" autocomplete="no">
	<input  type = "hidden" name = "editid" type="text" value = "<?php echo $id;?>">
    <div class="form-group">
        <div class="input-group">
          <span class="input-group-addon "><span class="fa fa-search"></span></span>
	<input type="text" class="form-control" name = "searchfor" placeholder="Suche" value = "<?=$res['searchfor'];?>" required>
        </div>
   </div>

	<div class="form-group">    
        <div class="input-group">
		   <span class="input-group-addon"><span class="fa fa-exchange-alt"></span></span>
           <select name = "replacement" class="form-control"  type="number" min="1" required>
<?php 
	echo '<option value="">--Wähle--</option>';
    $sql = 'SELECT id,ign,telegram,notes FROM `users` ORDER BY ign ASC';
    foreach ($pdo->query($sql) as $row) {
		$selected = '';
		if ($res['replacement'] == $row['ign']){
			$selected = ' selected';
		}
		echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['ign'].'</option>';
    }
?>
			</select>
        </div>
    </div>
	
	<div class="clearfix">
	  <div class="pull-left">
		<button type="submit" name = "updaterepl" class="btn btn-success">Update</button>
	  </div>
	  <div class="pull-right">
		<a href="?action=ocrfix&do=delete&id=<?=$id;?>" name="deleterep" class="btn btn-danger" role="button">Entfernen</a>
	</div>

  </div>

</form>

<?php
}

if (($_GET['do']=="update") && isset($_POST['updaterepl']) && ($_POST['searchfor']>"")){
	$query = $pdo->prepare('UPDATE namefix SET searchfor = :searchfor, replacement = :replacement WHERE id = :id');
	if($query->execute(array(':replacement' => getuname($_POST['replacement']),
			  ':searchfor' => $_POST['searchfor'],
			  ':id' => $_POST['editid']))){
		header("Location: ?action=ocrfix&msg=updatesuccess");  
	} else {
		header("Location: ?action=ocrfix&msg=updatefail");  
	}					  
}

if ($_GET['do']=="add"){
?>
	<form action="?action=ocrfix&do=addentry" method = "POST" autocomplete="no">
    <div class="form-group">
        <div class="input-group">
          <span class="input-group-addon "><span class="fa fa-search"></span></span>
			<input name = "searchfor" type="text" class="form-control" placeholder="Suche" required>
        </div>
	</div>

	<div class="form-group">    
        <div class="input-group">
		   <span class="input-group-addon"><span class="fa fa-exchange-alt"></span></span>
           <select name = "replacement" class="form-control" type="number" min="1" required>
<?php 
	   
	echo '<option value="">--Ersetze durch--</option>';
    $sql = 'SELECT id,ign,telegram,notes FROM `users` ORDER BY ign ASC';
    foreach ($pdo->query($sql) as $row) {
		echo '<option value="'.$row['id'].'">'.$row['ign'].'</option>';
	}
?>
		</select>
        </div>
    </div>
	
	<div class="clearfix">
	  <div class="pull-left">
		<button type="submit" name = "addrepl" class="btn btn-success">Hinzufügen</button>
	  </div>
	  <div class="pull-right">
		<a href="?action=ocrfix"  class="btn btn-danger" role="button">Zurück</a>
	</div>

	</div>

	</form>
<?php
}

if (($_GET['do']=="addentry") && isset($_POST['addrepl']) && ($_POST['searchfor'] > "")){
	$statement = $pdo->prepare("INSERT INTO namefix(searchfor, replacement)
		VALUES(:searchfor, :replacement)");

	if ($statement->execute(array(
		"searchfor" => $_POST['searchfor'],
		"replacement" => getuname($_POST['replacement'])))) {
		header("Location: ?action=ocrfix&msg=addsuccess");
	} else {
		header("Location: ?action=ocrfix&msg=addfail");
	}
}

if (($_GET['do']=="delete") && is_numeric($_GET['id'])){
	$id = preg_replace('/[^0-9]/','',$_GET['id']);
	$statement = $pdo->prepare("DELETE FROM namefix WHERE id = :id");
	if($result = $statement->execute(array('id' => $id))){
		header("Location: ?action=ocrfix&msg=deletesuccess");
	} else {
		header("Location: ?action=ocrfix&msg=deletefail");
	}
}
?>