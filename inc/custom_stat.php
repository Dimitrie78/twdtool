<?php
// error_reporting(E_ALL);
extract($_GET);
extract($_POST);
// $group = '';
if(isSet($createOpenKey)&&$createOpenKey==1){
	$sq = "INSERT ".$config->db_pre."openStats (gid, Query, DateVon, DateBis, DateDisable) VALUES (".(isSet($cgroup)&&$cgroup!=""?$cgroup:0).", '".(isSet($cfelder)?($cfelder):"")."', '".$cdate1."', '".$cdate2."', DATE_ADD(CURDATE(), INTERVAL 10 DAY))";
	// print $sq;
	$cKeyqry = $pdo->query($sq);

	// $cKeyqry->execute();
	$openKey = $pdo->lastInsertId();
}

$dateDisable  = date('');
if(isSet($openKey)&&$openKey>0){
	$oKeyqry = $pdo->query('SELECT gid, Query, DateVon, DateBis, (active=1 AND DateDisable > CURRENT_DATE()) as Aktiv, DateDisable FROM '.$config->db_pre.'openStats WHERE ID = '.$openKey);
	$oKeyqry->execute();
	$c = 0;
	if($oKeyqry){
	foreach ($oKeyqry as $oKey) {
	  if ($oKey["Aktiv"]==0){
	  	echo "Link ist abgelaufen!";
	  	$c = 1;
	  }else{
	  	$felder = $oKey["Query"];
	  	$date1	= $oKey["DateVon"];
	  	$date2	= $oKey["DateBis"];
	  	$group  = $oKey["gid"];
	  	$dateDisable = $oKey["DateDisable"];
	  	$c = $felder!=""?1:0;
	  }
	}
}else echo "Link ungültig!";

	if($c==0) echo "Link ungültig!";

}else{
include "verify.php";

$and_grouplimit = "";
if (!isdev()){
  $group = $_SESSION['gid'];
}



$felder = (isSet($config->customstats)&&$config->customstats?$config->customstats:' (`streuner`+`menschen`) as `Kills (Streuner+Menschen)`, round((`streuner`+`menschen`)/$days) as `pro Tag (Kills)`, `streuner` as `_Streuner`, `menschen` as `_Menschen`, `gespielte_missionen` as `_gesp. Mis.`, `abgeschlossene_missonen` as `abg. Mis.`, `gefeuerte_schuesse` as `_Schüsse`, `haufen` as `Kisten`, `heldenpower` as `_Helden`, `waffenpower` as `_Waffen`, `waffenpower`+`heldenpower` as `Upgrades (Helden+Waffen)`, `karten` as `_Karten` , `gerettete` as `Gerettete`');

}

$min_Numbers = array();

if (isSet($group)&&$group>0){
  $minQuery = $pdo->query('SELECT `Spalte`, `Min` FROM '.$config->db_pre.'minNumbers WHERE gid = '.$group);
	$minQuery->execute();
	if($minQuery){
	  foreach ($minQuery as $oMin) {
		  if($oMin["Spalte"]!=''){
  			$min_Numbers[$oMin["Spalte"]] = $oMin["Min"];
  		}
		}
	}
}
// }


$tbody = '';
$c = array(); //columns

$usrqry = $pdo->query('SELECT id,ign, gid FROM '.$config->db_pre.'users WHERE active > 0 '.(isSet($group)&&$group>0?' AND gid='.$group.' ':'').' ORDER BY ign');
$usrqry->execute();
$datediff = 0;
if (isSet($date1)&&isSet($date2)){
	if($date1<$date2){
	  $save = $date2;
	  $date2 = $date1;
	  $date1 = $save;
	}

	$datediff = round(abs(strtotime($date1)-strtotime($date2))/86400);

}
$felder = str_replace('$days', $datediff, $felder);

$s1Missed = '';
$s2Missed = '';

if(!(isSet($openKey)&&$openKey>0)){
	$groupqry = $pdo->query('SELECT u.gid gid, c.name Name, count(u.ID) Anzahl FROM '.$config->db_pre.'users u left join '.$config->db_pre.'groups c on u.gid = c.id WHERE u.active = 1 Group BY u.gid ORDER BY c.sort ASC, c.tag ASC');
	$groupqry->execute();

	$grouppicker = '';
	foreach ($groupqry as $gro) {
	 $grouppicker .= '<option value="'.$gro['gid'].'">'.$gro['Name'].' ('.$gro['Anzahl'].')</option>';
	}

	$dateqry = $pdo->query('SELECT (CASE WHEN WEEKDAY(s.date) = 0 THEN \'Mo\' WHEN WEEKDAY(s.date) = 1 THEN \'Di\' WHEN WEEKDAY(s.date) = 2 THEN \'Mi\' WHEN WEEKDAY(s.date) = 3 THEN \'Do\' WHEN WEEKDAY(s.date) = 4 THEN \'Fr\' WHEN WEEKDAY(s.date) = 5 THEN \'Sa\' WHEN WEEKDAY(s.date) = 6 THEN \'So\' END) Tag, DATE_FORMAT(s.date, "%Y-%m-%d") Datum , count(s.uid) Anzahl FROM '.$config->db_pre.'stats  s left join '.$config->db_pre.'users  u on s.uid = u.id WHERE u.active = 1 Group BY Datum ORDER BY Datum DESC ');
	$dateqry->execute();

	$datepicker = '';
	$dcounter = 0;
	foreach ($dateqry as $dat) {
	  $dcounter++;
	  if($dcounter==1&&!isSet($date1)) $date1 = $dat['Datum'];
	  if($dcounter==2&&!isSet($date2)) $date2 = $dat['Datum'];
	 $datepicker .= '<option value="'.$dat['Datum'].'">'.$dat['Tag'].' '.((new DateTime($dat['Datum']))->format('d.m.Y')).' ('.$dat['Anzahl'].')</option>';
	}
}
$count = 0;
$missed = 0;
$group_color_picker = array();
foreach ($usrqry as $usr) {
	$missed = 0;
	$count++;
	$uid = $usr['id'];
	$uname = $usr['ign'];
		   
	$sql1 = 'SELECT '.$felder.'
		FROM  `'.$config->db_pre.'stats`
		WHERE uid ='.$uid.' '.(isSet($date1)&&$date1!=""?' AND Date like \''.$date1.'%\'  ':'').'
		ORDER BY date DESC 
		LIMIT 0 , 1';
 //echo $sql1.'<br/>';
	$sql2 = 'SELECT '.$felder.'
		FROM  `'.$config->db_pre.'stats` 
		WHERE uid ='.$uid.' '.(isSet($date2)&&$date2!=""?' AND Date like \''.$date2.'%\'  ':'').'
		ORDER BY date DESC
		LIMIT '.(isSet($date2)&&$date2!=""?'0':'1').' , 1';
 // echo $sql2.'<br/>';
	#reset variables

	$s1 = array();
	$s2 = array();
	$e = array();
	$q1 = $pdo->query($sql1);


	if (count($c)<1){
		$total_column = 0;
	    $total_column = $q1->columnCount();
		// var_dump($total_column);
		for ($counter = 0; $counter < $total_column; $counter ++) {
		    $meta = $q1->getColumnMeta($counter);
		    $c[] = $meta['name'];
		}
	}


	if ($q1->rowCount()<1){
	  $s1Missed .= ($s1Missed?', ':'').$usr['ign'];
	  $missed = 1;
	}else{ foreach ($q1 as $row1) {
		for($h=0; $h<count($c);$h++){
			$s1[] = $row1[$c[$h]];
		}
	}
}

	$q2 = $pdo->query($sql2);
	if ($q2->rowCount()<1){
	  $s2Missed .= ($s2Missed?', ':'').$usr['ign'];
	  $missed = 1;
	}else{  foreach ($q2 as $row2) {
		for($h=0; $h<count($c);$h++){
			$s2[] = $row2[$c[$h]];
		}
	}
}

	

	// if((!$streuner1)||(!$streuner2)) continue;
		
	for($h=0; $h<count($c);$h++){
    			$e[] = (isSet($s1[$h])&&$s1[$h]&&isSet($s2[$h])&&$s2[$h])?$s1[$h]-$s2[$h]:0;
	}
	if(!in_array($usr['gid'], $group_color_picker)) $group_color_picker[] = $usr['gid'];
	$tbody .=  '<tr '.($missed==1?'style="color:#ff4000;"':'').'>
		  <td style="text-align: right; min-width: 40px;">&nbsp;</td>
		  <td style="text-align: left; min-width: 120px;"><a href = "?action=stats&uid='.$uid.'" target="_new" class="group'.(array_search($usr['gid'], $group_color_picker)+1).'">'.$uname.'</a></td>';
	
	for($h=0; $h<count($c);$h++){
  	$_h = substr($c[$h], 0, (strpos($c[$h], '(', 0)===false)?strlen($c[$h]):strpos($c[$h], '(', 0));
  	$_h = trim(($_h[0]=='_'?substr($_h, 1, strlen($_h)):$_h));
	  $minValue = ($missed!=1&&isSet($min_Numbers[$_h]))&&($min_Numbers[$_h]>0)&&$datediff>0&&(floor($min_Numbers[$_h]/7)>floor($e[$h]/$datediff))?1:0;
  		  // echo 'TEST:'.$min_Numbers[$c[$h]].'|'.$c[$h];
	  // if($minValue==1) echo 'TEST';
    	$tbody .= '<td style="text-align: right; '.($minValue?'color:#ff6666;':'').'" class="'.($c[$h][0]=='_'?'hidden':'').' col'.$h.'">'.
number_format(round((isSet($e[$h])?$e[$h]:0)/(isSet($avgTage)&&$avgTage?$datediff:1)),0, ",", ".")
    			.'</td>';

	}
	$tbody .= '</tr>';
		
}
$thead = '';	
if(!(isSet($openKey)&&$openKey>0)){
	$thead .= '
	<div class="table-responsive"> 
	<form action="" method="get"><input type="hidden" name="action" value="custom_stat" />'.(isdev()?'Gruppe: <select id="group" name="group"><option value="-1" selected=selected></option>'.$grouppicker.'</select>&nbsp;&nbsp;':'').'Von: <select id="date2" name="date2"><option value="" selected=selected></option>'.$datepicker.'</select>
	&nbsp;&nbsp;&nbsp;Bis: <select id="date1" name="date1"><option value="" selected=selected></option>'.$datepicker.'</select> <button type="submit" class="btn btn-success">Laden</button></form>';
$thead .= ($datediff?$datediff.' Tage<br/>':'<br />');
}else{
	$thead .= '<span style="font-weight:bold">';
	if($group > 0){
		$gqry = $pdo->query('SELECT c.name Name FROM '.$config->db_pre.'groups c WHERE c.id = '.$group);
		$gqry->execute();

		foreach ($gqry as $_gro) {
		 $thead .= $_gro['Name'].'<br />';
		}
	}

	$thead .= str_replace('Monday', 'Mo',
			  str_replace('Tuesday', 'Di',
			  str_replace('Wednesday', 'Mi',
			  str_replace('Thursday', 'Do',
			  str_replace('Friday', 'Fr',
			  str_replace('Saturday', 'Sa',
			  str_replace('Sunday', 'So',
			   ''.date('l d.m.', strtotime($date2)).' - '.date('l d.m.Y', strtotime($date1)).($datediff?' ( '.$datediff.' Tage ) ':'').'<br/><br />')))))));
	$thead .= '</span>';
}
//table-fixed
$thead .=	'<table class="tablesorter table table-hover  datatable table-bordered" id="sortTable" style="width:auto">
	  <thead>
	    <tr>
	    <th scope="col" style="min-width: 40px;" class="">&nbsp;</th>
		  <th scope="col" style="min-width: 120px;" class="">Spieler/in</th>
	';


for($h=0; $h<count($c);$h++){
  if(isSet($c[$h])&&$c[$h]!=''){
  	$_h = substr($c[$h], 0, (strpos($c[$h], '(', 0)===false)?strlen($c[$h]):strpos($c[$h], '(', 0));
  	$_h = trim(($_h[0]=='_'?substr($_h, 1, strlen($_h)):$_h));
    $thead .= '<th scope="col" id="col'.$h.'" class="'.($c[$h][0]=='_'?'hidden':'').' col'.$h.'">'.$_h.'</th>';
  }
}
$thead .= '</tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';

$tfoot .= '<div style="margin-left: auto; margin-right: auto; padding-left:30px;"><div style="float:left; width:33%;">';
$half = ceil(count($c)/3);
for($h=0; $h<count($c);$h++){
	if ($half == $h || ($half*2) == $h) $tfoot .= '</div><div style="float:left; width:33%;">';
	$tfoot .= '<label><input type="checkbox" id="chk_col_'.$h.'" onchange="if(this.checked) $(\'.col'.$h.'\').removeClass(\'hidden\'); else $(\'.col'.$h.'\').addClass(\'hidden\');" '.($c[$h][0]!='_'?'checked=checked':'').' /> '.($c[$h][0]=='_'?substr($c[$h], 1, strlen($c[$h])):$c[$h]).'</label><br />';
}

$tfoot .= '</div></div>';
$tfoot .= '<div style="clear:both;">&nbsp;</div>';

if($s1Missed||$s2Missed)
  echo '<br /><div style="border:2px dashed silver; padding:10px; background-color:#111111; font-weight:bold;">Folgende User-Stats fehlen:<br />'.($s1Missed?'Datum 1: <span style="color:red;">'.$s1Missed.'</span><br />':'').($s2Missed?'Datum2: <span style="color:red;">'.$s2Missed.'</span><br />':'').'</div><br />';

if(isSet($openKey)&&$openKey>0){
  $l = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'], 2)[0];	
//  echo $l.'<br />';
  $tfoot .= '<br /><br /><center><a href="'.$l.'?openKey='.$openKey.'" target="_new">öffentlicher Link zu dieser Statistik</a> <input type="hidden" name="oKeyLink" id="oKeyLink" value="'.$l.'?openKey='.$openKey.'" /><!--<button onclick="var o = document.getElementById(\"oKeyLink\"); if(o!=null) window.clipboardData.setData(\"Text\", o.value);"> kopieren </button>--><br />';
  $dateDisdiff = round(abs(strtotime($dateDisable)-strtotime(date('Y-m-d')))/86400);
  $tfoot .= '<small>(Der Link läuft in '.$dateDisdiff.' Tagen ab.)</small></center>';
// $tfoot .= ''.date('Y-m-d');
}else{
  if($date1&&$date2)
  $tfoot .= '<br /><br /><form target="_blank" method="post" action="index.php?action=custom_stat">
  				<input type="hidden" name="cfelder" value="'.$felder.'" />
  				<input type="hidden" name="cdate1" value="'.$date1.'" />
  				<input type="hidden" name="cdate2" value="'.$date2.'" />
  				<input type="hidden" name="cgroup" value="'.$group.'" />
  				<input type="hidden" name="createOpenKey" value="1" />
  				<input type="hidden" name="action" value="custom_stat" />
  				<button type="submit" class="btn btn-success">öffentlichen Link erstellen</button>
  			</form><br />';
}

echo $thead.$tbody.$tfoot;

?>
<script>
	//$(document).ready(function(){ $('#sortTable').tablesorter(); });

	<?php 
		echo '$("#group").val("'.$group.'"); $("#date1").val("'.$date1.'"); $("#date2").val("'.$date2.'"); ';   
		/*
		if(isSet($openKey)&&$openKey>0){
	      echo 'var o = document.getElementById("oKeyLink"); if(o!=null) window.clipboardData.setData("Text", o.value);';
	    }
	    */
	?>
var table = $("#sortTable");
table.bind("sortEnd",function() { 
    var i = 1;
    table.find("tr:gt(0)").each(function(){
        $(this).find("td:eq(0)").text(i);
        i++;
    });
});

$(function() {

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
  $("#sortTable").tablesorter({ headers: { 1: { sorter: 'fancyNumber'} }, sortInitialOrder: 'desc', sortList: [[2,1]], widgets: ['zebra'] });
});
 

</script>