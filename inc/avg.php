<?php
include "verify.php";
//to be moved in inc/functions

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

$and_grouplimit = '';
if (!isdev())
{
    $and_grouplimit = ' AND U.gid = ' . $_SESSION['gid'];
}

if (isdev() && isset($_POST['gid']))
{
    if (is_numeric($_POST['gid']))
    {
        $and_grouplimit = ' AND U.gid = ' . $_POST['gid'];
    }
    
    if ($_POST['gid'] == "uc")
    {
        $and_grouplimit = ' AND U.gid = 0';
    }
}


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
					4 => '4 Wochen',
					8 => '8 Wochen',
					12 => '12 Wochen');

$tbody = '';
$thead = '
<div class="table-responsive"> <table id="avg" class="table table-striped table-condensed datatable table-bordered nowrap table-hover table-fixed" style="width:auto">
  <thead>
    <tr>
	  <th scope="col">Grp</th>
	  <th scope="col" style="min-width: 120px;">Name</th>
	  <th scope="col" style="text-align: right;">&Delta; Messung</th>
	  <th scope="col" style="text-align: right;">&Delta; Woche</th>
	  <th scope="col" style="text-align: right;">Datum1</th>
	  <th scope="col" style="text-align: right;">Datum2</th>
	  <th scope="col" style="text-align: right;">#Tage</th>
      	  <th scope="col" style="text-align: right;">Start</th>
          <th scope="col" style="text-align: right;">Ende</th>
	  <th scope="col" style="text-align: right;">#Stats</th>
    </tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';


echo 'Anzahl der durchschnittlichen '.$abfragen[$abfrage].' pro Woche in letzten '.$wochen.' Wochen<br>';

echo '<form class="form-vertical" role="form" method="POST" action="?action=avg">';

if (isdev())
{
?>
<label for="inputGroup" class = "control-label">Gruppe wählen: <span class="fas fa-arrow-right"></span></label>
      <select onchange="this.form.submit()" id="inputGroup" name = "gid" class="form-control" style="width:auto;min-width:200px;">
	 <option value="allgrp" <?php
    if (isset($_POST['gid']) && $_POST['gid'] == 'allgrp')
    {
        echo ' selected';
    }
?>>--Alle--</option>
	 <option value="uc" <?php
    if (isset($_POST['gid']) && $_POST['gid'] == 'uc')
    {
        echo ' selected';
    }
?>>--Ohne Gruppe--</option>
<?php
    $sql = 'SELECT id, tag, name FROM `' . $config->db_pre . 'groups` ORDER BY sort ASC';
    foreach ($pdo->query($sql) as $row)
    {
        if ($_POST['gid'] == $row['id'])
        {
            $gidselected = ' selected';
        }
        echo '<option value="' . $row['id'] . '" ' . $gidselected . '>[' . $row['tag'] . '] ' . $row['name'] . '</option>';
        $gidselected = '';
    }
    
?>
     </select>
<?php
}	

#<div class="row"><div class="form-group col-xs-6" style="width:auto;max-width:200px;">test</div></div>';
	
echo'<div class="row"><div class="form-group col-xs-6" style="width:auto;max-width:200px;">
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


$sqlgetuser = "SELECT G.tag, U.id AS uid, U.ign AS ign, count( S.uid ) AS anzstats, min( date ) AS mindate, max( date ) AS maxdate, min( ".$abfrage." ) AS minval, max( ".$abfrage." ) AS maxval
	FROM ".$config->db_pre."stats AS S
	INNER JOIN ".$config->db_pre."users AS U ON ( S.uid = U.id )
	INNER JOIN ".$config->db_pre."groups G ON G.id = U.gid
	WHERE U.active =1
	AND DATE(date) >= DATE(NOW( ) - INTERVAL ".$wochen." WEEK) ".$and_grouplimit."
	GROUP BY S.uid
	HAVING COUNT( S.uid ) >1
	ORDER BY U.ign";


$i = 0;
$difftotal = 0;
$diffWochetotal = 0;
foreach ($pdo->query($sqlgetuser) as $user) {
	$i++;  
	$diff = $pdo->query(
        "SELECT 
	TIMESTAMPDIFF(HOUR,min(date), max(date)) AS HourDiff,
	TIMESTAMPDIFF(HOUR,min(date), max(date)) / 24 AS TageDiff,
	7 * ".$wochen." AS WochenTageDiff,
        ceil( (max( ".$abfrage." ) - min( ".$abfrage." ) ) / ".$wochen.") AS diff,
        ceil( (max( ".$abfrage." ) - min( ".$abfrage." ) ) / (TIMESTAMPDIFF(HOUR,min(date), max(date)) / 24) * (7 * ".$wochen.") / ".$wochen.") AS diffWoche
	FROM ".$config->db_pre."stats
	WHERE DATE(date) >= DATE(NOW( ) - INTERVAL ".$wochen." WEEK)
	AND uid = ".$user['uid']."")->fetch();  
	
	$data[] = array('tag' => $user['tag'],
					'uid' => $user['uid'],
					'ign' => $user['ign'],
					'diff' => $diff['diff'],
					'diffWoche' => $diff['diffWoche'],
					'mindate' =>$user['mindate'],
					'maxdate' => $user['maxdate'],
					'TageDiff' => ceil($diff['TageDiff']),
					'minval' => $user['minval'],
					'maxval' => $user['maxval'],
					'anzstats' => $user['anzstats']);

	$difftotal += $diff['diff'];
        $diffWochetotal += $diff['diffWoche'];


	$style = "";
	if ($_SESSION['userid'] == $user['uid']){$style = ' style="font-weight: bold;"';}
}

if ($i > 0){
$sorted = array_orderby($data, 'tag', SORT_ASC, 'diff', SORT_DESC);

        foreach ($sorted as $usr)
        { 
         	$mindt = new DateTime($usr['mindate']);
	$maxdt = new DateTime($usr['maxdate']);
	$tbody .=  '<tr'.$style.'>
	  <td text-align: left;">'.$usr['tag'].'</td>
	  <td style="min-width: 120px; text-align: left;"><a href="?action=stats&uid='.$usr['uid'].'">'.$usr['ign'].'</a></td>
	  <td style="text-align: right;">'.$usr['diff'].'</td>
	  <td style="text-align: right;">'.$usr['diffWoche'].'</td>
	  <td style="text-align: right;">'.$mindt->format('d.m.Y').'</td>
	  <td style="text-align: right;">'.$maxdt->format('d.m.Y').'</td>
	  <td style="text-align: right;">'.$usr['TageDiff'].'</td>
	  <td style="text-align: right;">'.$usr['minval'].'</td>
	  <td style="text-align: right;">'.$usr['maxval'].'</td>					
	  <td style="text-align: right;">'.$usr['anzstats'].'</td>
	   </tr>';
	}

echo $thead.$tbody.$tfoot.'<br>Clandurchschnitt (Messung): '. ceil($difftotal/$i);
echo '<br>Clandurchschnitt (Woche): '. ceil($diffWochetotal/$i);
}
else
{
echo 'Es sind mindestens 2 Datensätze im gewählten Zeitraum für die Auswertung erforderlich. Dieses Kriterium wird in der momentanen Auswahl nicht erfüllt.';	
}
?>
<script>$(document).ready(function(){ $('#avg').tablesorter(); });</script>
