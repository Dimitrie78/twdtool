<?php
include "verify.php";

if(isset($_GET['msg'])){msgbox($_GET['msg']);}

if (!isset($_GET['do'])){

$grpqry = $pdo->query('SELECT g.id, g.tag, g.name, g.sort, COUNT(u.id ) AS  "anz"
FROM '.$config->db_pre.'groups g
LEFT JOIN '.$config->db_pre.'users u ON g.id = u.gid
GROUP BY g.id
order by g.sort asc, g.tag asc;');

$grpqry->execute();
?>
<a href="?action=groupedit&do=add" style = "margin-bottom: 10px" class="btn btn-success" role="button"><span class = "fas fa-plus-square"></span> Gruppe erstellen</a>
<div class="table-responsive">
<table class="table table-hover table-fixed datatable table-bordered" id="sortTable" style="width:auto">
  <thead class="thead-dark">
    <tr>
	  <th scope="col" style="min-width: 41px;">DBID</th>
	  <th scope="col" style="min-width: 41px;">SORT</th>
      <th scope="col" style="min-width: 50px;">Tag</th>
      <th scope="col" style="min-width: 200px;">Name</th>
	  <th scope="col" style="min-width: 50px;">Nutzer</th>
	  <th scope="col" style="min-width: 50px;">Edit</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($grpqry as $group) {
?>
	<tr>
	  <td><?php echo $group['id']; ?></td>
	  <td><?php echo $group['sort']; ?></td>
	  <td><?php echo $group['tag']; ?></td>
      <td><?php echo $group['name']; ?></td>
	  <td><?php echo $group['anz']; ?></td>
	  <td><a href = "?action=groupedit&do=update&id=<?php echo $group['id']; ?>" class="btn btn-primary btn-xs"><span class = "fas fa-edit"></span> EDIT</a></td>
	</tr>
<?php
}
?>
 </tbody>
</table>
</div>
<?php
}
	
$pdo->query('CREATE TABLE IF NOT EXISTS `'.$config->db_pre.'minNumbers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT \'1\',
  `Spalte` varchar(50) DEFAULT NULL,
  `Min` int(11) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	
if($_GET['do'] == "insMin" && is_numeric($_GET['id'])){
  $insMinQuery = $pdo->query('INSERT INTO '.$config->db_pre.'minNumbers (gid, Spalte, Min) VALUES ('.$_GET['id'].', "", "")');
  $_GET['do'] = 'update';
	// $insMinQuery->execute([]);
}
if(isset($_GET['do']) && $_GET['do'] == "update" && is_numeric($_GET['id'])){
	$grpqry = $pdo->prepare("SELECT id,tag,name,sort FROM ".$config->db_pre."groups WHERE id = :id");

	$grpqry->execute(array('id' => $_GET['id']));
	if($group = $grpqry->fetch()){ ?>
<span style = "display: inline-block; margin-bottom:10px; font-weight:bold;">Gruppen-ID: <?php echo $group['id'];?></span>
		<form action="index.php?action=groupedit&do=update2db" method="POST" autocomplete="no">
         <input type="hidden" name="editid" value = "<?php echo $group['id'];?>">
		  <div class="form-group">
			<label for="grouptag">Tag/Kürzel:</label>
			<input type="text" class="form-control" id="grouptag" name="grouptag" 
			value = "<?php echo $group['tag'];?>" maxlength = "5" required>
		  </div>
		  <div class="form-group">
			<label for="groupname">Gruppen-Name:</label>
			<input type="text" class="form-control" id="groupname" name = "groupname"
			value = "<?php echo $group['name'];?>" maxlength = "20" required> 
		  </div>
		  <div class="form-group">
			<label for="groupsort">Gruppen-SortNR:</label>
			<input type="number" class="form-control" id="groupname" name = "groupsort"
			value = "<?php echo $group['sort'];?>" min="1" max="999" required> 
		  </div>
		  <div class="clearfix">
		   <div class="pull-left">
		    <button type="submit" name = "update" value="UpdateGroup" class="btn btn-success">
		    <span class = "fas fa-edit"></span> Gruppe updaten</button>
	       </div>
		   <div class="pull-right">
		  <a href="?action=groupedit&do=delete&id=<?php echo $group['id'];?>" class="btn btn-danger" role="button">
            <span class = "fas fa-trash-alt"></span> Gruppe löschen</a>
		    <a href="?action=groupedit" class="btn btn-warning" role="button">
		    <span class = "fas fa-step-backward"></span> zurück</a><br>
		   </div>
		  </div>
		</form>
<?php
	}
	else {
		failmsg('Der Eintrag konnte nicht geladen werden!');
	}
// }
$min_Numbers = array();
	$minQuery = $pdo->prepare('SELECT `ID`, '.$_GET['id'].' as `gid`, `Spalte`, `Min` FROM '.$config->db_pre.'minNumbers WHERE gid = '.$_GET['id']);
	$minQuery->execute([]);
		// $min_Numbers = $minQuery->fetchAll();
	?>
	<h2>Minimum-Anforderungen</h2>

	<table id="data_table" class="table table-striped">
	<thead>
	<tr>
	<th style="display:none;">ID</th>
	<th style="display:none;">gid</th>
	<th>Spalte</th>
	<th>Min-Wert</th>
	</tr>
	</thead>
	<tbody>
	<?php
	while( $min_Numbers = $minQuery->fetch()) {
	?>
	<tr id="<?php echo $min_Numbers['ID']; ?>">
	<td style="display:none;"><?php echo $min_Numbers['ID']; ?></td>
	<td style="display:none;"><?php echo $min_Numbers['gid']; ?></td>
	<td><?php echo $min_Numbers['Spalte']; ?></td>
	<td><?php echo $min_Numbers['Min']; ?></td>
	</tr>
	<?php } ?>
	</tbody>
	</table>

	<a href="?action=groupedit&do=insMin&id=<?php echo $_GET['id']; ?>" style = "margin-bottom: 10px" class="btn btn-success" role="button"><span class = "fas fa-plus-square"></span> Minimum hinzufügen</a>
	<?php
		
}

if(isSet($_POST['update'])&&($_POST['update'] == "UpdateGroup") &&isSet($_POST['editid'])&& is_numeric($_POST['editid'])){
	$query = $pdo->prepare('UPDATE '.$config->db_pre.'groups SET tag = :tag, name = :name WHERE id = :id');
	if($query->execute(array(':tag' => $_POST['grouptag'],
						 ':name' => $_POST['groupname'],
						 ':sort' => $_POST['groupsort'],
						 ':id' => $_POST['editid']))){
		header("Location: ?action=groupedit&do=update&id=".$_POST['editid']."&msg=updatesuccess");  
	} else {
		header("Location: ?action=groupedit&do=update&id=".$_POST['editid']."&msg=updatefail");  
	}	
	
}

if(isset($_GET['do']) && $_GET['do'] == "add"){ ?>
<span style = "display: inline-block; margin-bottom:10px; font-weight:bold;">Gruppe erstellen</span>
<form action="index.php?action=groupedit&do=add2db" method="POST" autocomplete="no">
  <div class="form-group">
    <label for="grouptag">Tag/Kürzel:</label>
    <input type="text" class="form-control" id="grouptag" name="grouptag" maxlength = "5" required>
  </div>
  <div class="form-group">
    <label for="groupname">Gruppen-Name:</label>
    <input type="text" class="form-control" id="groupname" name = "groupname" maxlength = "20" required> 
  </div>
    <div class="form-group">
    <label for="groupname">Gruppen-SortNR:</label>
    <input type="text" class="form-control" id="groupsort" name = "groupsort" min="1" max="999" required> 
  </div>
  <div class="clearfix">
    <div class="pull-left">
      <button type="submit" name = "creategroup" value="AddGroup" class="btn btn-success">
	  <span class = "fas fa-plus-square"></span> Gruppe anlegen</button>
    </div>
    <div class="pull-right">
      <a href="?action=groupedit" class="btn btn-warning" role="button">
      <span class = "fas fa-step-backward"></span> zurück</a><br>
    </div>
  </div>
</form>
<?php
}

if(isset($_POST['creategroup']) && $_POST['creategroup'] == "AddGroup")
{
		$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."groups(tag, name, sort)
			VALUES(:tag, :name, :sort)");


		$statement->execute(array(
			"tag" => $_POST['grouptag'],
			"name" => $_POST['groupname'],
			"sort" => $_POST['groupsort']
		));
		
		$count = $statement->rowCount();
		if($count =='1'){
		header("Location: ?action=groupedit&msg=addsuccess");  
       } else {
		header("Location: ?action=groupedit&msg=addfail");  
	}	
}


if(isset($_GET['do']) && $_GET['do'] == "delete" && is_numeric($_GET['id'])){
$grpqry = $pdo->prepare('SELECT g.id, g.tag, g.name, COUNT(u.id ) AS  "anz"
FROM '.$config->db_pre.'groups g
LEFT JOIN '.$config->db_pre.'users u ON g.id = u.gid
WHERE g.id = :id');
$grpqry->execute(array('id' => $_GET['id']));
	if($group = $grpqry->fetch()){
		
	if($group['anz'] > 0){
	$uncat = 1;
	?>
	<span style = "display: inline-block; margin-bottom:10px; font-weight:bold;">Löschbestätigung</span>
	<?php
	warnmsg('Die Gruppe "<b>'.$group['name'].'</b>" hat '.$group['anz'].' Spieler/innen.<br>
	Wenn die Gruppe entfernt wird, werden die Mitglieder in die Gruppe <b>"unzugeordnet"</b> verschoben und müssen danach neu zugewiesen werden.');
    }
	else{
	$uncat = 0;
    infomsg('<b>Löschbestätigung.</b><br>Die Gruppe "<b>'.$group['name'].'</b>" hat kein/e Spieler/innen und kann jetzt entfernt werden!');
	}
		
	}
	else{
  	echo 'Daten konnten nicht geladen werden...';
	}
	
?>	
<form action="index.php?action=groupedit&do=rmgrp" method="POST">
<input type="hidden" name="uncategorize" value = "<?php echo $uncat;?>">
<input type="hidden" name="gid" value = "<?php echo $group['id'];?>">
  <div class="form-group">
      <button type="submit" name = "delete" value="DeleteGroup" class="btn btn-danger">
	  <span class = "fas fa-fa-trash-alt"></span> Gruppe löschen</button>
  </div>
</form>
<?php
}

if(isset($_POST['delete']) && $_POST['delete'] == "DeleteGroup" && is_numeric($_POST['gid'])){
	if ($_POST['uncategorize'] == 1){ 
		$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET gid = 0 WHERE gid = ?');
		$query->execute([$_POST['gid']]);
		}
		
		$query = $pdo->prepare('DELETE FROM '.$config->db_pre.'news WHERE gid = ?');
		$query->execute([$_POST['gid']]);
		
		$query = $pdo->prepare('DELETE FROM '.$config->db_pre.'groups WHERE id = ?');
		$query->execute([$_POST['gid']]);
		if($query->rowCount()){
		header("Location: ?action=groupedit&msg=deletesuccess");  
		}
		else{
		header("Location: ?action=groupedit&msg=deletefail");  
		}
}
	
?>
<script>

	// $(document).ready(function(){ $('#sortTable').tablesorter(); });

	 $(document).ready(function () {
        jQuery.tablesorter.addParser({
            id: "fancyNumber",
            is: function (s) {
                return /^[0-9]?[0-9,\.]*$/.test(s);
            },
            format: function (s) {
                return jQuery.tablesorter.formatFloat(s.replace(/\./g, ''));
            },
            type: "numeric"
        });

        $("#sortTable").tablesorter({
            headers: { 0: { sorter: 'fancyNumber'} },
            widgets: ['zebra']
        });
    }); 

 $(document).ready(function(){
$('#data_table').Tabledit({
deleteButton: true,
editButton: true,
restoreButton:false,
columns: {
identifier: [0, 'ID'],
editable: [[1, 'gid'], [2, 'Spalte'], [3, 'Min']]
},
hideIdentifier: true,
url: 'inc/groupedit_update.php',
onSuccess:function(data, textStatus, jqXHR)
      {
       if(data.action == 'delete')
       {
        $('#'+data.id).remove();
       }
      }
});
});
</script>