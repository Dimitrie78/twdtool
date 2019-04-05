<?php
include "verify.php";


if (isset($_POST['abfrage'])) {
	$abfrage = $_POST['abfrage'];
} else {
	$abfrage = 'streuner';
}

if (isset($_POST['wochen'])) {
	$wochen = $_POST['wochen'];
} else {
	$wochen = 4;
}

$abfragen = array('streuner' => 'Streuner',
				  'menschen' => 'Menschen',
				  'gespielte_missionen' => 'Gespielte Missionen',
				  'abgeschlossene_missonen' => 'Abgesch. Missionen',
				  'gefeuerte_schuesse' => 'Gefeuerte Schüsse',
				  'haufen' => 'Haufen gesammelt',
				  'heldenpower' => 'Ges. Helden',
				  'waffenpower' => 'Ges. Waffen',
				  'karten' => 'Ges. Karten',
				  'gerettete' => 'Gerettete Überlebende');

$arr_wochen = array(1 => '1 Woche',
					2 => '2 Wochen',
					3 => '3 Wochen',
					4 => '4 Wochen');

$tbody = '';
$thead = '
<div class="table-responsive"> <table id="avg" class="table table-hover table-fixed datatable table-bordered" style="width:auto">
  <thead>
    <tr>
	  <th scope="col" style="min-width: 120px;">Name</th>
	  <th scope="col">Diff</th>
	  <th scope="col" style="min-width: 140px;">Startdatum</th>
	  <th scope="col" style="min-width: 140px;">Enddatum</th>
      <th scope="col">Start</th>
      <th scope="col">Ende</th>
	  <th scope="col">#Stats</th>
    </tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';


echo 'Anzahl der durchschnittlichen '.$abfragen[$abfrage].' pro Woche in letzten '.$wochen.' Wochen<br>';

echo '<div class="row">
		<form class="form-vertical" role="form" method="POST" action="?action=avg">
		<div class="form-group col-xs-6" style="width:auto;max-width:200px;">
			<label for="abfrage" class="control-label"> </label>
			<select onchange="this.form.submit()" id="abfrage" name="abfrage" class="form-control">';

foreach( $abfragen as $key => $value) {
	if ($abfrage == $key) $selected=' selected'; else $selected='';
	echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
}
echo '</select></div>';

echo '<div class="form-group col-xs-6" style="width:auto;max-width:200px;">
			<label for="wochen" class="control-label"> </label>
			<select onchange="this.form.submit()" id="wochen" name="wochen" class="form-control">';

foreach( $arr_wochen as $key => $value) {
	if ($wochen == $key) $selected=' selected'; else $selected='';
	echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
}
echo '</select></div></form></div>';

#echo  'name - diff - anz_stats - startdatum - endatum - startwert - endwert<br>';


$sqlgetuser = "SELECT U.id AS uid, U.ign AS ign, count( S.uid ) AS anzstats, min( date ) AS mindate, max( date ) AS maxdate, min( ".$abfrage." ) AS minval, max( ".$abfrage." ) AS maxval
	FROM stats AS S
	INNER JOIN users AS U ON ( S.uid = U.id )
	WHERE U.active =1
	AND date > NOW( ) - INTERVAL ".$wochen." WEEK
	GROUP BY S.uid
	HAVING COUNT( S.uid ) >1
	ORDER BY U.ign";


$i = 0;
$difftotal = 0;
foreach ($pdo->query($sqlgetuser) as $user) {
	$i++;  
	$diff = $pdo->query("SELECT ceil( (
	max( ".$abfrage." ) - min( ".$abfrage." ) ) /  ".$wochen."
	) AS diff
	FROM stats
	WHERE date > NOW( ) - INTERVAL ".$wochen." WEEK
	AND uid = ".$user['uid']."")->fetch();  

	$difftotal += $diff['diff'];

	$mindt = new DateTime($user['mindate']);
	$maxdt = new DateTime($user['maxdate']);
	$style = "";
	if ($_SESSION['userid'] == $user['uid']){$style = ' style="font-weight: bold;"';}
	$tbody .=  '<tr'.$style.'>
	  <td style="min-width: 120px; text-align: left;"><a href="?action=stats&uid='.$user['uid'].'">'.$user['ign'].'</a></td>
	  <td style="text-align: right;">'.$diff['diff'].'</td>
	  <td style="min-width: 140px; text-align: right;">'.$mindt->format('d.m.Y H:i').'</td>
	  <td style="min-width: 140px; text-align: right;">'.$maxdt->format('d.m.Y H:i').'</td>
	  <td style="text-align: right;">'.$user['minval'].'</td>
	  <td style="text-align: right;">'.$user['maxval'].'</td>					
	  <td style="text-align: right;">'.$user['anzstats'].'</td>
	</tr>';
}
	
echo $thead.$tbody.$tfoot.'<br>Clandurchschnitt: '. ceil($difftotal/$i);
?>
<script>$(document).ready(function(){ $('#avg').tablesorter(); });</script>