<?php
include "verify.php";

echo "Aktuelle Statistik</b>";

if (!isdev()){
$and_grouplimit = ' AND U.gid = '.$_SESSION['gid'];
}

if (isdev() && $_POST['gid']){
if (is_numeric($_POST['gid'])){
$and_grouplimit = ' AND U.gid = '.$_POST['gid'];
}

if ($_POST['gid'] == "uc"){
$and_grouplimit =  ' AND U.gid = 0';
}
}

?>  
<?php if (isdev()){?>
<div class ="form-group">
<form class="form-vertical" role="form" method = "POST" action = "?action=currentstats" >
<label for="inputGroup" class = "control-label">Gruppe wählen: <span class="fas fa-arrow-right"></span></label>
      <select onchange="this.form.submit()" id="inputGroup" name = "gid" class = "form-control" style="width:auto;min-width:200px;">
	 <option value="allgrp" <?php if ($_POST['gid'] == 'allgrp'){echo ' selected';} ?>>--Alle--</option>
	 <option value="uc" <?php if ($_POST['gid'] == 'uc'){echo ' selected';} ?>>--Ohne Gruppe--</option>
<?php
	$sql = 'SELECT id, tag, name FROM `'.$config->db_pre.'groups` ORDER BY name ASC';
	
    foreach ($pdo->query($sql) as $row) {
		if ($_POST['gid'] == $row['id'])
	{
	$gidselected = ' selected';
	}
       echo '<option value="'.$row['id'].'" '.$gidselected.'>['.$row['tag'].'] '.$row['name'].'</option>';
	    $gidselected = '';
    }

?>
     </select>
	 </div>
<?php 
}

$out = 'SELECT s1. * , U.gid, G.tag, U.ign, U.id as uid
FROM '.$config->db_pre.'stats AS s1
RIGHT JOIN '.$config->db_pre.'users U ON s1.uid = U.id
RIGHT JOIN '.$config->db_pre.'groups G ON G.id = U.gid
LEFT JOIN (
SELECT uid, MAX( DATE ) AS m
FROM '.$config->db_pre.'stats
GROUP BY uid
) AS s2 ON s1.uid = s2.uid
WHERE s1.date = s2.m
AND s1.uid = s2.uid
'.$and_grouplimit.'
AND U.active =1
ORDER BY G.tag ASC, U.ign ASC';


$query = $pdo->prepare($out);
$query->execute();
$count = $query->rowCount();
if ($count === 0){
echo 'Es wurden noch keine Statistiken hochgeladen!';
}
else{
?>   
  <div class="table-responsive">
  <table id="currentstats" class="table table-striped table-condensed datatable table-bordered nowrap table-hover table-fixed" style="width:100%">
        <thead>
			<tr>
			<?php if (isdev()){ ?>
				<th style="min-width: 50px;">GRP</th>
			<?php }?>
			    <th style="min-width: 120px;">IGN</th>
                <th style="min-width: 100px;">DAT</th>
                <th>STR</th>
				<th>MEN</th>
                <th>GMIS</th>
                <th>AMIS</th>
                <th>SCHS</th>
                <th>KST</th>
				<th>HLD</th>
				<th>WAF</th>
				<th>KRT</th>
			    <th>GRT</th>
				<th>Edit</th>
            </tr>
        </thead>
        <tbody>
<?php
foreach ($query as $row) {
	echo '<tr>';
				if (isdev()){
				echo'
				<td style="min-width: 50px; text-align: left;">'. $row['tag'].'</td>';
				 }echo'
		        <td style="min-width: 120px; text-align: left;"><a href = "?action=stats&uid='.$row['uid'].'">'.$row['ign'].'</a></td>
                <td style="min-width: 100px; text-align: right;">'.date( 'd.m.y H:i:s', strtotime($row['date'])).'</td>
				<td style="text-align: right;">'.$row['streuner'].'</td>
                <td style="text-align: right;">'.$row['menschen'].'</td>
				<td style="text-align: right;">'.$row['gespielte_missionen'].'</td>
                <td style="text-align: right;">'.$row['abgeschlossene_missonen'].'</td>
				<td style="text-align: right;">'.$row['gefeuerte_schuesse'].'</td>
                <td style="text-align: right;">'.$row['haufen'].'</td>
				<td style="text-align: right;">'.$row['heldenpower'].'</td>
                <td style="text-align: right;">'.$row['waffenpower'].'</td>
				<td style="text-align: right;">'.$row['karten'].'</td>	
				<td style="text-align: right;">'.$row['gerettete'].'</td>
				<td style="text-align: center;">
				<a href="?action=editstat&id='.$row['id'].'" role="button"><span class="fas fa-edit"></a>
				</td>
        </tr>';
}
?>
	</tbody>
	</table></div>
<script>$(document).ready(function(){ $('#currentstats').tablesorter(); });</script>
<?php
}
?>