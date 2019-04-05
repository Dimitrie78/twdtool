<?php
include "verify.php";

$usrqry = $pdo->query('SELECT id,ign FROM '.$config->db_pre.'users WHERE active > 0 ORDER BY ign');
$usrqry->execute();
$tbody = '';

foreach ($usrqry as $usr) {
	$uid = $usr['id'];
	$uname = $usr['ign'];
	
	$sql1 = 'SELECT streuner, menschen, gespielte_missionen, abgeschlossene_missonen, gefeuerte_schuesse, haufen, heldenpower, waffenpower, karten, gerettete 
		FROM  '.$config->db_pre.'stats
		WHERE uid ='.$uid.'
		ORDER BY date DESC 
		LIMIT 0 , 1';

	#reset variables
	$streuner1 = '';
	$menschen1 = '';
	$gespielte_missionen1 = '';
	$abgeschlossene_missonen1 = '';
	$gefeuerte_schuesse1 = '';
	$haufen1 = '';
	$heldenpower1 = '';
	$waffenpower1 = '';
	$karten1 = '';
	$gerettete1 = '';

	$rows = $pdo->query($sql1);
	$rows->execute();
	if (isset($rows)) {
		foreach ($rows as $row1) {
			$streuner1 = $row1['streuner'];
			$menschen1 = $row1['menschen'];
			$gespielte_missionen1 = $row1['gespielte_missionen'];
			$abgeschlossene_missonen1 = $row1['abgeschlossene_missonen'];
			$gefeuerte_schuesse1 = $row1['gefeuerte_schuesse'];
			$haufen1 = $row1['haufen'];
			$heldenpower1 = $row1['heldenpower'];
			$waffenpower1 = $row1['waffenpower'];
			$karten1 = $row1['karten'];
			$gerettete1 = $row1['gerettete'];
		}
	}



	$streuner = ($streuner1) ? $streuner1 : 0;
	$menschen = ($menschen1) ? $menschen1 : 0;
	$gespielte_missionen = ($gespielte_missionen1) ? $gespielte_missionen1  : 0;
	$abgeschlossene_missonen = ($abgeschlossene_missonen1) ? $abgeschlossene_missonen1 : 0;
	$gefeuerte_schuesse = ($gefeuerte_schuesse1) ? $gefeuerte_schuesse1  : 0;
	$haufen = ($haufen1) ? $haufen1 : 0;
	$heldenpower = ($heldenpower1) ? $heldenpower1 : 0;
	$waffenpower = ($waffenpower1) ? $waffenpower1 : 0;
	$karten = ($karten1) ? $karten1 : 0;
	$gerettete = ($gerettete1) ? $gerettete1 : 0;


	$tbody .=  '<tr>
		  <td style="text-align: left; min-width: 120px;"><a href = "?action=stats&uid='.$uid.'">'.$uname.'</a></td>
		  <td style="text-align: right;">'.$streuner.'</td>
		  <td style="text-align: right;">'.$menschen.'</td>
		  <td style="text-align: right;">'.$gespielte_missionen.'</td>
		  <td style="text-align: right;">'.$abgeschlossene_missonen.'</td>
		  <td style="text-align: right;">'.$gefeuerte_schuesse.'</td>
		  <td style="text-align: right;">'.$haufen.'</td>					
		  <td style="text-align: right;">'.$heldenpower.'</td>		
		  <td style="text-align: right;">'.$waffenpower.'</td>	
		  <td style="text-align: right;">'.$karten.'</td>	
		  <td style="text-align: right;">'.$gerettete.'</td>	
		</tr>';
		
}
	
$thead = '
<div class="table-responsive"> <table class="table table-hover table-fixed datatable table-bordered" id="sortTable" style="width:auto">
  <thead class="thead-dark">
    <tr>
	  <th scope="col" style="min-width: 120px;">Spieler/in</th>
      <th scope="col">Streuner</th>
	  <th scope="col">Menschen</th>
	  <th scope="col">Gesp. Mis.</th>
	  <th scope="col">Abge. Mis.</th>
      <th scope="col">Schüsse</th>
      <th scope="col">Haufen</th>
      <th scope="col">Helden</th>
	  <th scope="col">Waffen</th>
	  <th scope="col">Karten</th>
	<th scope="col">Gerettet</th>
    </tr>
  </thead>
  <tbody>';

	
$tfoot = '</tbody>
</table></div>';

echo $thead.$tbody.$tfoot;
	
?>
<script>$(document).ready(function(){ $('#sortTable').tablesorter(); });</script>