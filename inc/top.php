<?php
include "verify.php";

if (!isdev()){
$and_grouplimit_nojoin = ' AND gid = '.$_SESSION['gid'];
$and_grouplimit = ' AND U.gid = '.$_SESSION['gid'];
}

if (isdev() && $_POST['gid']){
if (is_numeric($_POST['gid'])){
$and_grouplimit_nojoin = ' AND gid = '.$_POST['gid'];
$and_grouplimit = ' AND U.gid = '.$_POST['gid'];
}

if ($_POST['gid'] == "uc"){
$and_grouplimit_nojoin =  ' AND gid = 0';
$and_grouplimit =  ' AND U.gid = 0';
}
}

$top['lastlogin'] = "SELECT ign, lastlogin as top FROM ".$config->db_pre."users  where active = 1 ".$and_grouplimit_nojoin." ORDER BY lastlogin DESC";

$top['streuner'] = "SELECT U.ign, MAX( S.streuner ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.streuner ) DESC";

$avg['streuner'] = "SELECT round(avg(top)) as avg
FROM ({$top['streuner']}) as avgtab";

$top['menschen'] = "SELECT U.ign, MAX( S.menschen ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.menschen ) DESC";

$avg['menschen'] = "SELECT round(avg(top)) as avg
FROM ({$top['menschen']}) as avgtab";

$top['gespielte_missionen'] = "SELECT U.ign, MAX( S.gespielte_missionen ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.gespielte_missionen ) DESC";

$avg['gespielte_missionen'] = "SELECT round(avg(top)) as avg
FROM ({$top['gespielte_missionen']}) as avgtab";

$top['abgeschlossene_missonen'] = "SELECT U.ign, MAX( S.abgeschlossene_missonen ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.abgeschlossene_missonen ) DESC";

$avg['abgeschlossene_missonen'] = "SELECT round(avg(top)) as avg
FROM ({$top['abgeschlossene_missonen']}) as avgtab";

$top['gefeuerte_schuesse'] = "SELECT U.ign, MAX( S.gefeuerte_schuesse ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.gefeuerte_schuesse ) DESC";

$avg['gefeuerte_schuesse'] = "SELECT round(avg(top)) as avg
FROM ({$top['gefeuerte_schuesse']}) as avgtab";

$top['haufen'] = "SELECT U.ign, MAX( S.haufen ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.haufen ) DESC";

$avg['haufen'] = "SELECT round(avg(top)) as avg
FROM ({$top['haufen']}) as avgtab";

$top['heldenpower'] = "SELECT U.ign, MAX( S.heldenpower ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.heldenpower ) DESC";

$avg['heldenpower'] = "SELECT round(avg(top)) as avg
FROM ({$top['heldenpower']}) as avgtab";

$top['waffenpower'] = "SELECT U.ign, MAX( S.waffenpower ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.waffenpower ) DESC";

$avg['waffenpower'] = "SELECT round(avg(top)) as avg
FROM ({$top['waffenpower']}) as avgtab";

$top['karten'] = "SELECT U.ign, MAX( S.karten ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1	".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.karten ) DESC";

$avg['karten'] = "SELECT round(avg(top)) as avg
FROM ({$top['karten']}) as avgtab";

$top['gerettete'] = "SELECT U.ign, MAX( S.gerettete ) AS top
FROM ".$config->db_pre."stats AS S
INNER JOIN ".$config->db_pre."users AS U 
       ON  (S.uid = U.id)
WHERE U.active = 1 ".$and_grouplimit."
GROUP BY S.uid
ORDER BY MAX( S.gerettete ) DESC";

$avg['gerettete'] = "SELECT round(avg(top)) as avg
FROM ({$top['gerettete']}) as avgtab";


?>
    <form class="form-vertical" role="form" method = "POST" action = "?action=top" >
 <div class="row">
<?php if (isdev()) {

 ?>
	<div class="form-group col-xs-6" style="width:auto;">
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
	
	 </select> </div>
 <?php } ?>
 
<div class="form-group col-xs-6" style="width:auto;">
<label for="inputUser" class = "control-label">Liste:</label>     
      <select onchange="this.form.submit()" id="inputUser" name = "mode" class = "form-control" style="width:auto;min-width:200px">	    
      <option value="">--Wählen--</option>
<?php
foreach($top as $key => $value)
{
	if ($_POST['mode'] == $key)
	{
	$selected = ' selected';
	}
	
 echo '<option value="'.$key.'" '.$selected.'>'.ucfirst($key).'</option>';
 $selected = '';
}
?>
   </select>   
  </div>
 </div>
</form>

<?php
if (isset($_POST['mode'])){
switch ($_POST['mode']) {
	
	case "lastlogin":
     $qry = $top['lastlogin'];
     break;
		
    case "streuner":
        $qry = $top['streuner'];
		$avgqry = $avg['streuner'];
        break;
    case "gerettete":
        $qry = $top['gerettete'];
		$avgqry = $avg['gerettete'];
        break;		
    case "menschen":
         $qry = $top['menschen'];
		 $avgqry = $avg['menschen'];
        break;
    case "gespielte_missionen":
         $qry = $top['gespielte_missionen'];
		 $avgqry = $avg['gespielte_missionen'];
        break;
    case "abgeschlossene_missonen":
         $qry = $top['abgeschlossene_missonen'];
		 $avgqry = $avg['abgeschlossene_missonen'];
        break;
    case "gefeuerte_schuesse":
         $qry = $top['gefeuerte_schuesse'];
		 $avgqry = $avg['gefeuerte_schuesse'];
        break;
    case "haufen":
         $qry = $top['haufen'];
		 $avgqry = $avg['haufen'];
        break;
    case "heldenpower":
         $qry = $top['heldenpower'];
		$avgqry = $avg['heldenpower'];
        break;
    case "waffenpower":
         $qry = $top['waffenpower'];
		$avgqry = $avg['waffenpower'];
        break;
    case "karten":
         $qry = $top['karten'];
		 $avgqry = $avg['karten'];
        break;
    default:
        $qry = $top['streuner'];
		$avgqry = $avg['streuner'];
						}

if($_POST['mode']!="lastlogin"){
$avg = $pdo->query($avgqry);
$avg->execute(); 
$row = $avg->fetch();
?>
<p><span style = "margin-left:1em;"> Ø <?php echo $row['avg']; ?></span></p>
<?php
}
?>
 <table class="table table-hover datatable table-bordered" style="width:auto;min-width:200px">
  <thead>
    <tr>
      <th scope="col" style="text-align: right;">#</th>
      <th scope="col">Name</th>
      <th scope="col">Wert</th>
    </tr>
  </thead>
  <tbody>
<?php
$i = 1;
    foreach ($pdo->query($qry) as $row)
	{
	echo '<tr>
			  <th scope="row" style="text-align: right;">'.$i.'</th>
			  <td>'.$row['ign'].'</td>';
			if($_POST['mode']=="lastlogin"){
				$lastlogin = (($row['top']) <>  "" ? date("d.m.Y H:i:s", strtotime($row['top'])) : "Kein Login");
			  echo '<td>'.$lastlogin.'</td>';
			}
			else
			{
				echo '<td>'.$row['top'].'</td>';
			}
		  echo '</tr>';
	$i++;
	}
?>
   </tbody>
</table>
<?php
}
?>