<?php
// error_reporting(E_ALL);
extract($_GET);
include "verify.php";

$felder = (isSet($config->customstats)&&$config->customstats?$config->customstats:' streuner, menschen, gespielte_missionen, abgeschlossene_missonen, gefeuerte_schuesse, haufen, heldenpower, waffenpower, karten, gerettete ');

$tbody = '';
$c = array(); //columns

$usrqry = $pdo->query('SELECT id,ign FROM '.$config->db_pre.'users WHERE active > 0 '.(isSet($clan)&&$clan>0?' AND clanid='.$clan.' ':'').' ORDER BY ign');
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

$groupqry = $pdo->query('SELECT u.clanid ClanID, c.name Name, count(u.ID) Anzahl FROM '.$config->db_pre.'users u left join '.$config->db_pre.'clans c on u.clanid = c.id WHERE u.active = 1 Group BY u.clanid ORDER BY c.name ');
$groupqry->execute();

$grouppicker = '';
foreach ($groupqry as $gro) {
 $grouppicker .= '<option value="'.$gro['ClanID'].'">'.$gro['Name'].' ('.$gro['Anzahl'].')</option>';
}

$dateqry = $pdo->query('SELECT (CASE WHEN WEEKDAY(s.date) = 0 THEN \'Mo\' WHEN WEEKDAY(s.date) = 1 THEN \'Di\' WHEN WEEKDAY(s.date) = 2 THEN \'Mi\' WHEN WEEKDAY(s.date) = 3 THEN \'Do\' WHEN WEEKDAY(s.date) = 4 THEN \'Fr\' WHEN WEEKDAY(s.date) = 5 THEN \'Sa\' WHEN WEEKDAY(s.date) = 6 THEN \'So\' END) Tag, DATE_FORMAT(s.date, "%Y-%m-%d") Datum , count(s.uid) Anzahl FROM '.$config->db_pre.'stats  s left join '.$config->db_pre.'users  u on s.uid = u.id WHERE u.active = 1 Group BY Datum ORDER BY Datum DESC ');
$dateqry->execute();

$datepicker = '';
foreach ($dateqry as $dat) {
 $datepicker .= '<option value="'.$dat['Datum'].'">'.$dat['Tag'].' '.((new DateTime($dat['Datum']))->format('d.m.Y')).' ('.$dat['Anzahl'].')</option>';
}
$count = 0;
foreach ($usrqry as $usr) {
	$count++;
	$uid = $usr['id'];
	$uname = $usr['ign'];
		   
	$sql1 = 'SELECT '.$felder.'
		FROM  `'.$config->db_pre.'stats`
		WHERE uid ='.$uid.' '.(isSet($date1)&&$date1!=""?' AND Date like \''.$date1.'%\'  ':'').'
		ORDER BY date DESC 
		LIMIT 0 , 1';
 // echo $sql1.'<br/>';
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

	foreach ($q1 as $row1) {
		for($h=0; $h<count($c);$h++){
			$s1[] = $row1[$c[$h]];
		}
	}

	foreach ($pdo->query($sql2) as $row2) {
		for($h=0; $h<count($c);$h++){
			$s2[] = $row2[$c[$h]];
		}
	}
	// if((!$streuner1)||(!$streuner2)) continue;

	for($h=0; $h<count($c);$h++){
		$e[] = ($s1[$h]&&$s2[$h])?$s1[$h]-$s2[$h]:0;
	}

	$tbody .=  '<tr>
		  <td style="text-align: left; min-width: 120px;"><a href = "?action=stats&uid='.$uid.'">'.$uname.'</a></td>';

	for($h=0; $h<count($c);$h++){
    	$tbody .= '<td style="text-align: right;">'.number_format(round($e[$h]/(isSet($avgTage)&&$avgTage?$datediff:1)),0, ",", ".").'</td>';
	}
	$tbody .= '</tr>';
		
}
	
$thead = '
<div class="table-responsive"> 
<form action="" method="get"><input type="hidden" name="action" value="custom_stat" />Gruppe: <select id="clan" name="clan"><option value="-1" selected=selected></option>'.$grouppicker.'</select>&nbsp;&nbsp;Von: <select id="date1" name="date1"><option value="" selected=selected></option>'.$datepicker.'</select>
&nbsp;&nbsp;&nbsp;Bis: <select id="date2" name="date2"><option value="" selected=selected></option>'.$datepicker.'</select><button type="submit">Laden</button></form>'.($datediff?$datediff.' Tage<br/>':'<br />').'
<table class="table table-hover table-fixed datatable table-bordered" id="sortTable" style="width:auto">
  <thead>
    <tr>
	  <th scope="col" style="min-width: 120px;">Spieler/in</th>
';

for($h=0; $h<count($c);$h++){
  $thead .= '<th scope="col">'.$c[$h].'</th>';
}
$thead .= '</tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';

echo $thead.$tbody.$tfoot;

?>
<script>
	//$(document).ready(function(){ $('#sortTable').tablesorter(); });
	<?php echo '$("#clan").val("'.$clan.'"); $("#date1").val("'.$date1.'"); $("#date2").val("'.$date2.'"); ';   ?>
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
            headers: { 1: { sorter: 'fancyNumber'} },
            widgets: ['zebra'],
            sortInitialOrder: "desc"

        });

    }); 

</script>