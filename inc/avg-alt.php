<?php
include "verify.php";

$wochen = 4;
$abfrage = 'streuner';

$tbody = '';
$thead = '
<div class="table-responsive"> <table class="table table-hover table-fixed datatable table-bordered" style="width:auto">
  <thead>
    <tr>
	  <th scope="col">Name</th>
	  <th scope="col">Diff</th>
	  <th scope="col">DT-Start</th>
	  <th scope="col">DT-Ende</th>
      <th scope="col">Start</th>
      <th scope="col">Ende</th>
	  <th scope="col">#Stats</th>
    </tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';


echo 'Anzahl der durchschnittlichen '.ucfirst($abfrage).' pro Woche in letzten '.$wochen.' Wochen<br>';
#echo  'name - diff - anz_stats - startdatum - endatum - startwert - endwert<br>';


$sqlgetuser = "SELECT U.id AS uid, U.ign AS ign, count( S.uid ) AS anzstats, min( date ) AS mindate, max( date ) AS maxdate, min( ".$abfrage." ) AS minval, max( ".$abfrage." ) AS maxval
	FROM ".$config->db_pre."stats AS S
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
	FROM ".$config->db_pre."stats
	WHERE date > NOW( ) - INTERVAL ".$wochen." WEEK
	AND uid = ".$user['uid']."")->fetch();  

	$difftotal += $diff['diff'];

	$mindt = new DateTime($user['mindate']);
	$maxdt = new DateTime($user['maxdate']);
	$style = "";
	if ($_SESSION['userid'] == $user['uid']){$style = "font-weight: bold;";}
	$tbody .=  '<tr>
	  <td style = "'.$style.'"><a href = "?action=stats&uid='.$user['uid'].'">'.$user['ign'].'</a></td>
	  <td style="text-align: right;">'.$diff['diff'].'</td>
	  <td style="text-align: right;">'.$mindt->format('d.m.Y H:i').'</td>
	  <td style="text-align: right;">'.$maxdt->format('d.m.Y H:i').'</td>
	  <td style="text-align: right;">'.$user['minval'].'</td>
	  <td style="text-align: right;">'.$user['maxval'].'</td>					
	  <td style="text-align: right;">'.$user['anzstats'].'</td>
	</tr>';
}
	
echo $thead.$tbody.$tfoot.'<br>Clandurchschnitt: '. ceil($difftotal/$i);
?>