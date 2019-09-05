<?php
include "verify.php";

if (isset($_POST['uid'])) {
	$sUser = $_POST['uid'];
} else {
	$sUser = $_GET['uid'];
}


if (isset($_GET['mode'])) {
	$mode = $_GET['mode'];
} else {
  if (isSet($config->useClassicStat)&&($config->useClassicStat==1))
	$mode = 'classic';
  else
	$mode = '';
}

if(!$sUser) {
	$sUser = $_POST['suid'];
}
$sUser = preg_replace('/[^0-9]/','',$sUser);


if (isset($_GET['uid']) & !user_exists($sUser)){echo '<div class="alert alert-danger">
  <strong>Abbruch</strong> Gewählte User-ID <b>'.$sUser.'</b> existiert nicht. Funktion nicht ausführbar</div>	<a href="?action=stats" name="back" class="btn btn-info" role="button">Zurück</a>'; exit();}


if (isdev()){
$query = $pdo->prepare('SELECT gid FROM '.$config->db_pre.'users WHERE id = :uid');
$query->execute(array(':uid' => $sUser));
$user_gid = $query->fetchColumn();
$target_gid = ($_POST['gid'] ? $_POST['gid'] : $user_gid);
}


if ($sUser == $_SESSION['userid'])
{
	$stattype = 'Deine Statistik';	
}
else
{
	$stattype = 'Spieler/in:';	
}

$and_grouplimit = "";
if (!isdev()){
$gidfilter = 'gid = '.$_SESSION['gid'];
$and_grouplimit = ' AND '.$gidfilter;
}

if (isdev() && isSet($_POST['gid'])){
if (is_numeric($_POST['gid'])){
$and_grouplimit = ' AND gid = '.$_POST['gid'];
}

if ($_POST['gid'] == "uc"){
$and_grouplimit =  ' AND gid = 0';
}
}

if (isdev() && is_numeric($_GET['uid'])){
$and_grouplimit = ' AND gid = '.$user_gid;
}


?>  
<form class="form-vertical" role="form" method = "POST" action = "?action=stats" >
<input  type = "hidden" name = "suid" type="text" value = "<? echo $sUser; ?>">

<?php if (isdev()){?>
<div class ="form-group">
<label for="inputGroup" class = "control-label">Gruppe wählen: <span class="fas fa-arrow-right"></span></label>
      <select onchange="this.form.submit()" id="inputGroup" name = "gid" class = "form-control" style="width:auto;min-width:200px;">
	 <option value="allgrp" <?php if ($target_gid  == 'allgrp'){echo ' selected';} ?>>--Alle--</option>
	 <option value="uc" <?php if ($target_gid  == 'uc'){echo ' selected';} ?>>--Ohne Gruppe--</option>
<?php
	$sql = 'SELECT id, tag, name FROM `'.$config->db_pre.'groups` ORDER BY name ASC';

    foreach ($pdo->query($sql) as $row) {
		if ($target_gid == $row['id'])
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
?>
	<label for="inputUser" class = "control-label"><?php echo $stattype; ?></label> 
    <div class="form-group">
		<div class="clearfix">
	  <div class="pull-left">   
      <select onchange="this.form.submit()" id="inputUser" name = "uid" class = "form-control" style="width:auto;min-width:200px;">

<?php


$sql = 'SELECT id,ign,telegram,notes FROM `'.$config->db_pre.'users` WHERE active = 1 '.$and_grouplimit.' ORDER BY ign ASC';
		
	       echo '<option value="">--Wähle--</option>';
foreach ($pdo->query($sql) as $row) {
	$selected = '';
	if ($sUser == $row['id']) {
		$selected = ' selected';
	
		if ($row['notes'] > ""){
			$btnnotes = '<a href="?action=notes&uid='.$sUser.'" class="btn btn-success" role="button"><span class = "fas fa-paperclip"></span> Notizen</a> ';
		}
		
		if ($row['telegram'] > ""){
			$telegram = '<a href="https://t.me/'.$row['telegram'].'" target = "_new" class="btn btn-info" role="button"><span class = "fab fa-telegram-plane"></span> Telegram</a> ';
		}

$editusr .= '<a href="?action=stats&uid='.$sUser.'&mode='.(isSet($mode)&&$mode=='classic'?'':'classic').'" class="btn btn-info" role="button"><span class = "fas fa-chart-line"></span> '.(isSet($mode)&&$mode=='classic'?'Standard Stats':'Classic Stats').'</a>&nbsp;';								  
		if (isadminormod()){
			$editusr .= '<a href="?action=usrmgr&uid='.$sUser.'" class="btn btn-warning" role="button"><span class = "fas fa-edit"></span> Edit User </a>
			<a href="?action=addstat&uid='.$sUser.'" class="btn btn-success" role="button"><span class = "fas fa-plus-square"></span> Stat hinzu</a> ';
		}

	}
		
    echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['ign'].'</option>';
}
echo '</select></div> 
	  <div class="pull-right">'.$btnnotes.$telegram.$editusr.'</div></div></div>';
	  
$query1 = $pdo->prepare('SELECT count(id) as anz FROM '.$config->db_pre.'stats WHERE uid = :uid and `fail` = 0');
$query1->execute(array(':uid' => $sUser));
$total_stats = $query1->fetchColumn();

if($total_stats > 0){

$limit = '';
if ($config->statlimit){
if (isset($_POST['limit']) && $_POST['limit']>""){
	$sel_limit = preg_replace('/[^0-9]/','',$_POST['limit']);
}
else{
	$sel_limit = $config->statlimit;  //Wert aus config
}	

if (is_numeric($sel_limit)){$limit = ' LIMIT 0,'.$sel_limit;}
}


$q_str = "	SET @lastKills = 0, @lastGespielt = 0, @lastAbgeschl = 0, @lastGerett = 0, @lastDate = '2000-01-01', @actTage = 0, @actKills = 0, @actGerett = 0;";
$query_stat2 = $pdo->prepare($q_str);
$query_stat2->execute();
if(!(isSet($mode)&&$mode=='classic')){
$q_str = "
			SELECT * FROM 
(			    SELECT 
			      `id` as _id
 				, `date` as _date
			    , DATE_FORMAT(`date`, '%d.%m.%Y') as Datum
			    , (@actTage := CASE WHEN IFNULL(@lastDate, '2000-01-01') = '2000-01-01' THEN 0 ELSE DATEDIFF(`date`, @lastDate) END) as Tage
			    , `exp` as LVL, `streuner` as Streuner, `menschen` as Menschen
			    , (@actKills := CASE WHEN IFNULL(@lastKills, 0) = 0 THEN 0 ELSE ((`streuner`+`menschen`)-@lastKills) END) as Diff_Kills
			    , (CASE WHEN @actTage < 1 THEN 0 ELSE round(@actKills/@actTage, 0) END) as ProTag
			    , (CASE WHEN @actKills < 1 THEN 0 ELSE round(`gefeuerte_schuesse`/(`streuner`+`menschen`), 0) END) as SchKill
				,  `gespielte_missionen` as GespMis
			    , CASE WHEN IFNULL(@lastGespielt, 0) = 0 THEN 0 ELSE (`gespielte_missionen`-@lastGespielt) END as Diff_GM
			    , `abgeschlossene_missonen` as AbgeMis
			    , CASE WHEN IFNULL(@lastAbgeschl, 0) = 0 THEN 0 ELSE (`abgeschlossene_missonen`-@lastAbgeschl) END as Diff_AM
			    , `gefeuerte_schuesse` as Schüsse, `haufen` as Haufen, `waffenpower` as Waffen, `heldenpower` as Helden, `karten` as Karten, `gerettete` as Gerettete
			    , CASE WHEN IFNULL(@lastGerett, 0) = 0 THEN 0 ELSE (`gerettete`-@lastGerett) END as Diff_Gerettet
			    , (@actGerett := CASE WHEN IFNULL(@lastGerett, 0) = 0 THEN 0 ELSE (`gerettete`-@lastGerett) END) as _Diff_Gerett
			    , (`streuner`) as chart_Streuner
			    , (`menschen`) as chart_Menschen
			    , (`gerettete`) as chart_Gerettete
			    , (`streuner`+`menschen`) as chart_Kills
			    , CASE WHEN IFNULL(@lastKills, 0) = 0 THEN 0 ELSE(round(@actKills/@actTage, 0)*7) END as chart_Kills_pro_Woche
			    , CASE WHEN IFNULL(@lastGerett, 0) = 0 THEN 0 ELSE (round(@actGerett/@actTage, 0)*7) END as chart_Gerettete_pro_Woche

			    , (@lastKills := (`streuner`+`menschen`)) as _calc2
			    , (@lastGespielt := `gespielte_missionen`) as _calc3
			    , (@lastAbgeschl := `abgeschlossene_missonen`) as _calc4
			    , (@lastGerett := `gerettete`) as _calc5
			    , (@lastDate := `date`) as _calc6
			    FROM ".$config->db_pre."stats WHERE uid = ".$sUser." AND fail = 0 ORDER BY `date` ASC 
			) as e ORDER BY `_date` DESC ".$limit."
		";
}else{
$q_str = "
			SELECT * FROM 
(			    SELECT 
			      `id` as _id
 				, `date` as _date
			    , DATE_FORMAT(`date`, '%d.%m.%Y') as Datum
			    , (@actTage := CASE WHEN IFNULL(@lastDate, '2000-01-01') = '2000-01-01' THEN 0 ELSE DATEDIFF(`date`, @lastDate) END) as _Tage
			    ,  `exp` as LVL,`exp` as EP, `streuner` as Streuner, `menschen` as Menschen
			    , (round(`gefeuerte_schuesse`/`streuner`, 0)) as Schü_pro_Str
				,  `gespielte_missionen` as GespMis
			    , CASE WHEN IFNULL(@lastGespielt, 0) = 0 THEN 0 ELSE (`gespielte_missionen`-@lastGespielt) END as Diff_GM
			    , `abgeschlossene_missonen` as AbgeMis
			    , CASE WHEN IFNULL(@lastAbgeschl, 0) = 0 THEN 0 ELSE (`abgeschlossene_missonen`-@lastAbgeschl) END as Diff_AM
			    , `gefeuerte_schuesse` as Schüsse, `haufen` as Haufen, `waffenpower` as Waffen, `heldenpower` as Helden, `karten` as Karten, `gerettete` as Gerettete
			    , (`streuner`) as chart_Streunerkills
			    , (`menschen`) as chart_Menschen
			    , (`heldenpower`) as chart_Heldenstärke
			    , (`waffenpower`) as chart_Waffenstärke
			    , (`gerettete`) as chart_Überlebende
			    , (@lastKills := (`streuner`+`menschen`)) as _calc2
			    , (@lastGespielt := `gespielte_missionen`) as _calc3
			    , (@lastAbgeschl := `abgeschlossene_missonen`) as _calc4
			    , (@lastGerett := `gerettete`) as _calc5
			    , (@lastDate := `date`) as _calc6
			    FROM ".$config->db_pre."stats WHERE uid = ".$sUser." AND fail = 0 ORDER BY `date` ASC 
			) as e ORDER BY `_date` DESC ".$limit."
		";
}
$query_stat = $pdo->prepare($q_str);
$query_stat->execute();
// echo $query_stat->rowCount().'zeilen<br />';

// $cq = $pdo->query($q_str);
// $cq->execute();
$c = array();
$total_column = 0;
// if (count($c)<1)
{
  $total_column = 0;
  $total_column = $query_stat->columnCount();
  // echo 'TEST:'.$total_column;
  for ($counter = 0; $counter < $total_column; $counter ++) {
    $meta = $query_stat->getColumnMeta($counter);
    // echo $meta['name'].'TEST<br />';
    if((strlen($meta['name'])>0)&&(substr($meta['name'],0, 1)!='_'))
      $c[] = $meta['name'];
  }
}

if ($config->statlimit){
	echo'<div class="slidecontainer">
		   Die letzten <label for="inputUser" class = "control-label"> <span id="demo"></span> von '.$total_stats.' </label> Einträgen
	  <input onchange="this.form.submit()" type="range" min="1" max="'.$total_stats.'" value="'.$sel_limit.'" step="1" class="slider" id="myRange" name="limit">
	</div>
	</form>';
}


		
	if ($sUser > ""){
   
?>
<div id="container"></div>
<small> <span class = "fas fa-info-circle"></span>  Durch Klick auf einen Wert unter Legende können einzelne Werte ein- oder ausgeblendet werden.</small>
<div class="table-responsive">
   <table id="stats" class="table table-striped table-bordered nowrap table-hover table-condensed datatable" style="width:100%">
        <thead class="thead-dark">
            <tr>
                <?php 
			  		for($h=0; $h<count($c);$h++){
					  if(!(strpos($c[$h], 'chart_')!==false))
			  		    echo '<th>'.str_replace('_pro_', '\\', $c[$h]).'</th>';
			  		}

                ?>
<!--                <th>Datum</th>
				<th>LVL</th>
                <th>EP</th>
                <th>Streuner</th>
				<th>Menschen</th>
				<th>Schü/Str</th>
                <th>GespMis</th>
                <th>Diff_GM</th>
                <th>AbgeMis</th>
				<th>Diff_AM</th>
                <th>Schüsse</th>
                <th>Haufen</th>
				<th>Helden</th>
				<th>Waffen</th>
				<th>Karten</th>
			    <th>Gerettet</th>
			-->
				<?php if (isadminormod()){ ?>
				<th>Edit</th>
				<?php } ?>
            </tr>
        </thead>
        <tbody>
	
<?php
#iterator für das differenzarray initialisieren
$i = 0;
$chart = array();
foreach ($query_stat as $row) {
	// $datetime = new DateTime($row['date']);
	// $year = $datetime->format('Y');
	// $month = $datetime->format('m')-1; #highcharts monat fängt bei 0 an zu zählen!
	// $day = $datetime->format('j');
	// $fulldate = $datetime->format('d.m.Y H:i');
	// $streuner[] = '[Date.UTC('.$year.', '.$month.', '.$day.'), '.$row['streuner'].'],'; 
	// $menschen[] =  '[Date.UTC('.$year.', '.$month.', '.$day.'), '.$row['menschen'].'],'; 
	// $heldenpower[] =  '[Date.UTC('.$year.', '.$month.', '.$day.'), '.$row['heldenpower'].'],'; 
	// $waffenpower[] =  '[Date.UTC('.$year.', '.$month.', '.$day.'), '.$row['waffenpower'].'],'; 
	// $gerettete[] =  '[Date.UTC('.$year.', '.$month.', '.$day.'), '.$row['gerettete'].'],'; 
	// unset($datetime);
	#$schuestr = $row['gefeuerte_schuesse']/$row['streuner']
	echo '<tr>';
	for($h=0; $h<count($c);$h++){
		if ($c[$h] == 'LVL')
		    echo '<td style="text-align: right;">'.leveldata($row[$c[$h]]).'</td>';
		else {
			if((strpos($c[$h], 'chart_')!==false) && $row[$c[$h]]<>0 ) {
				$datetime = new DateTime($row['_date']);
				$year = $datetime->format('Y');
				$month = $datetime->format('m')-1; #highcharts monat fängt bei 0 an zu zählen!
				$day = $datetime->format('j');
				if (!isSet($chart[$h])) $chart[$h] = array();
				$chart[$h][$i] = '[Date.UTC('.$year.', '.$month.', '.$day.'), '.$row[$c[$h]].']'; 
				unset($datetime);
			}else
  		    	echo '<td style="text-align: right;">'.$row[$c[$h]]./*'|'.$chart[$h][$i].'|'.$i.*/'</td>';
		}
	}

	if (isadminormod()){ 
		echo '<td style="text-align: center;"><a href="?action=editstat&id='.$row['_id'].'&uid='.$sUser.'" role="button" title="Diese Statistik bearbeiten"><span class="fas fa-edit"></span></a></td>';
	} 
					
	// echo "<tr>";
	$i++;
}

?>
	

<script type="text/javascript">
$(function () { 

    $('#container').highcharts({
        chart: {
            type: 'spline'
        },
        title: {
            text: 'Aktivität'
        },
		 yAxis: {
		title: {
           text: 'Werte'
        },
		 },
   xAxis: {
        type: 'datetime',
        dateTimeLabelFormats: { 
            month: '%e. %b',
            year: '%b'
        },
        title: {
            text: 'Legende'
        }
    },

    <?php
      echo 'series: [';
	  for($h=0; $h<count($c);$h++){
	  	if(isSet($chart[$h])&&count($chart[$h])>0)
	  	{
		  	echo ($h>0?'':'').'{
	        name: "'.str_replace('_pro_', '\\\\', substr($c[$h], 6)).'",
	        data: [';
			for ($x = $i; $x >= 0; $x--)
	    	  if(isSet($chart[$h][$x]))	
	    		echo $chart[$h][$x].',';
  	  	echo ']},';
    	}
	  }
	?>

	
	]
});
});

var slider = document.getElementById("myRange");
var output = document.getElementById("demo");
output.innerHTML = slider.value;

slider.oninput = function() {
  output.innerHTML = this.value;
}

</script>
   
	</tbody>
	</table>

	</div>
<?php
}
}
else {echo 'Du hast noch keine Statistiken die angezeigt werden könnten.<br>Bitte habe noch etwas Geduld.';}
?>