<?php
include "verify.php";

msgbox($_GET['msg']);

if (!isset($_GET['do'])){
$grpqry = $pdo->query('SELECT id,tag,name FROM '.$config->db_pre.'groups ORDER BY name');
$grpqry->execute();
?>
<a href="?action=groupedit&do=add" style = "margin-bottom: 10px" class="btn btn-success" role="button"><span class = "fas fa-plus-square"></span> Gruppe erstellen</a>
<div class="table-responsive">
<table class="table table-hover table-fixed datatable table-bordered" id="sortTable" style="width:auto">
  <thead class="thead-dark">
    <tr>
	  <th scope="col" style="min-width: 41px;">ID</th>
      <th scope="col" style="min-width: 50px;">Tag</th>
      <th scope="col" style="min-width: 200px;">Name</th>
	  <th scope="col" style="min-width: 50px;">Edit</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($grpqry as $group) {
?>
	<tr>
	  <td><?php echo $group['id']; ?></td>
	  <td><?php echo $group['tag']; ?></td>
      <td><?php echo $group['name']; ?></td>
	  <td><a href = "?action=groupedit&do=update&id=<?php echo $group['id']; ?>" class="btn btn-primary btn-xs"><span class = "fas fa-edit"></span> EDIT</a></td>
	</tr>
<?php
}
?>
 </tbody>
</table>
</div>
<?
}
	
if($_GET['do'] == "update" && is_numeric($_GET['id'])){
	$grpqry = $pdo->prepare("SELECT id,tag,name FROM ".$config->db_pre."groups WHERE id = :id");
	$grpqry->execute(array('id' => $_GET['id']));
	if($group = $grpqry->fetch()){?>
<span style = "display: inline-block; margin-bottom:10px; font-weight:bold;">Gruppen-ID: <?php echo $group['id'];?></span>
		<form action="index.php?action=groupedit&do=update2db" method="POST" autocomplete="no">
         <input type="hidden" name="editid" value = "<?php echo $group['id'];?>">
		  <div class="form-group">
			<label for="grouptag">Tag/Kürzel:</label>
			<input type="text" class="form-control" id="grouptag" name="grouptag" 
			value = "<?php echo $group['tag'];?>" maxlength = "3" required>
		  </div>
		  <div class="form-group">
			<label for="groupname">Gruppen-Name:</label>
			<input type="text" class="form-control" id="groupname" name = "groupname"
			value = "<?php echo $group['name'];?>" maxlength = "16" required> 
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
}

if($_POST['update'] == "UpdateGroup" && is_numeric($_POST['editid'])){
##todo: validiation
	$query = $pdo->prepare('UPDATE '.$config->db_pre.'groups SET tag = :tag, name = :name WHERE id = :id');
	if($query->execute(array(':tag' => $_POST['grouptag'],
						 ':name' => $_POST['groupname'],
						 ':id' => $_POST['editid']))){
		header("Location: ?action=groupedit&do=update&id=".$_POST['editid']."&msg=updatesuccess");  
	} else {
		header("Location: ?action=groupedit&do=update&id=".$_POST['editid']."&msg=updatefail");  
	}	
	
}

if($_GET['do'] == "add"){?>
<span style = "display: inline-block; margin-bottom:10px; font-weight:bold;">Gruppe erstellen</span>
<form action="index.php?action=groupedit&do=add2db" method="POST" autocomplete="no">
  <div class="form-group">
    <label for="grouptag">Tag/Kürzel:</label>
    <input type="text" class="form-control" id="grouptag" name="grouptag" maxlength = "3" required>
  </div>
  <div class="form-group">
    <label for="groupname">Gruppen-Name:</label>
    <input type="text" class="form-control" id="groupname" name = "groupname" maxlength = "16" required> 
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
<?
	}

if($_POST['creategroup'] == "AddGroup")
{
		$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."groups(tag, name)
			VALUES(:tag, :name)");


		$statement->execute(array(
			"tag" => $_POST['grouptag'],
			"name" => $_POST['groupname']
		));
		
		$count = $statement->rowCount();
		if($count =='1'){
		header("Location: ?action=groupedit&msg=addsuccess");  
	} else {
		header("Location: ?action=groupedit&msg=addfail");  
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

</script>