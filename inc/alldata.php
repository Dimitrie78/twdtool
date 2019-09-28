<?php
include "verify.php";

echo "Statistik von allen</b>";
$and_grouplimit = '';
if (!isdev()){
$gidfilter = 'gid = '.$_SESSION['gid'];
$and_grouplimit = ' AND '.$gidfilter;
}

if (isdev() && isset($_POST['gid'])){
if (is_numeric($_POST['gid'])){
$and_grouplimit = ' AND gid = '.$_POST['gid'];
}

if ($_POST['gid'] == "uc"){
$and_grouplimit =  ' AND gid = 0';
}
}

?>  
<?php if (isdev()){?>
<div class ="form-group">
<form class="form-vertical" role="form" method = "POST" action = "?action=alldata" >
<label for="inputGroup" class = "control-label">Gruppe wählen: <span class="fas fa-arrow-right"></span></label>
      <select onchange="this.form.submit()" id="inputGroup" name = "gid" class = "form-control" style="width:auto;min-width:200px;">
	 <option value="allgrp" <?php if (isSet($_POST['gid'])&& $_POST['gid'] == 'allgrp'){echo ' selected';} ?>>--Alle--</option>
	 <option value="uc" <?php if (isSet($_POST['gid']) && $_POST['gid'] == 'uc'){echo ' selected';} ?>>--Ohne Gruppe--</option>
<?php
	$sql = 'SELECT id, tag, name FROM `'.$config->db_pre.'groups` ORDER BY sort ASC';
	
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

$out = 'SELECT G.tag,U.ign, DATE_FORMAT( S.date,  "%d.%m.%Y" ) AS datum, S.id, S.exp, S.streuner, S.menschen, S.gespielte_missionen, S.abgeschlossene_missonen, S.gefeuerte_schuesse, S.haufen, S.heldenpower, S.waffenpower, S.karten, S.gerettete
FROM  `'.$config->db_pre.'stats` S
INNER JOIN `'.$config->db_pre.'users` U ON S.uid = U.id
INNER JOIN `'.$config->db_pre.'groups` G ON G.id = U.gid
WHERE U.active = 1 '.$and_grouplimit.'
ORDER BY U.ign, S.date DESC';

$query = $pdo->prepare($out);
$query->execute();
$count = $query->rowCount();
if ($count === 0){
echo 'Es wurden noch keine Statistiken hochgeladen!';
}
else{
?>   
  <div class="table-responsive">
  <table id="allstats" class="table table-striped table-condensed datatable table-bordered nowrap table-hover table-fixed" style="width:100%">
        <thead>
			<tr>
			<?php if (isdev()){ ?>
				<th style="min-width: 50px;">GRP</th>
			<?php }?>
			    <th style="min-width: 120px;">IGN</th>
                <th style="min-width: 100px;">DAT</th>
                <th style="min-width: 120px;">EP</th>
				<th>LVL</th>
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
		        <td style="min-width: 120px; text-align: left;">'.$row['ign'].'</td>
                <td style="min-width: 100px; text-align: right;">'.$row['datum'].'</td>
				<td style="min-width: 120px; text-align: right;">'.$row['exp'].'</td>
				<td style="text-align: right;">'. leveldata($row['exp']) .'</td>
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
<script>$(document).ready(function(){ $('#allstats').tablesorter(); });</script>
<?php
}
?>