<?php
include "verify.php";

msgbox($_GET['msg']);

if (!isset($_GET['do'])){
$grpqry = $pdo->query('SELECT id,tag,name FROM '.$config->db_pre.'groups ORDER BY name');
$grpqry->execute();
$tbody = '';

foreach ($grpqry as $group) {
	$tbody .=  '<tr>
		  <td style="text-align: left;">'.$group['id'].'</td>
		  <td style="text-align: left;">'.$group['tag'].'</td>
 		  <td style="text-align: left;">'.$group['name'].'</td>
		   <td style="text-align: left;"><a href = "?action=groupedit&do='.$group['id'].'">EDIT</a></td>
		</tr>';
}
	
$thead = '
<div class="table-responsive"> <table class="table table-hover table-fixed datatable table-bordered" id="sortTable" style="width:auto">
  <thead class="thead-dark">
    <tr>
	  <th scope="col" style="min-width: 41px;">ID</th>
      <th scope="col" style="min-width: 50px;">Tag</th>
      <th scope="col" style="min-width: 200px;">Name</th>
	  <th scope="col" style="min-width: 50px;">Edit</th>
    </tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';

echo $thead.$tbody.$tfoot;
	}

if($_GET['do'] == "add"){
	
?>	<form action="index.php?action=groupedit&do=add2db" method="POST" autocomplete="no">
  <div class="form-group">
    <label for="grouptag">Tag/Kürzel:</label>
    <input type="grouptag" class="form-control" id="grouptag" name="grouptag" maxlength = "3" required>
  </div>
  <div class="form-group">
    <label for="groupname">Gruppen-Name:</label>
    <input type="groupname" class="form-control" id="groupname" name = "groupname" maxlength = "16" required> 
  </div>
<button type="submit" name = "creategroup" value="AddGroup" class="btn btn-success">Gruppe anlegen</button>
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