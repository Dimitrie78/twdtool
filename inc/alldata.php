<?php
include "verify.php";

echo "Statistik von allen</b>";
$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'stats ORDER BY name, date desc');
$query->execute();
   
?>   

  <div class="table-responsive">
  <table id="allstats" class="table table-striped table-condensed datatable table-bordered nowrap table-hover table-fixed" style="width:100%">
        <thead>
            <tr>
			    <th style="min-width: 120px;">Name</th>
                <th style="min-width: 100px;">Datum</th>
                <th style="min-width: 120px;">EP</th>
                <th>Streuner</th>
				<th>Menschen</th>
                <th>GespMis</th>
                <th>AbgeMis</th>
                <th>Schüsse</th>
                <th>Haufen</th>
				<th>Helden</th>
				<th>Waffen</th>
				<th>Karten</th>
			    <th>Gerettet</th>
				<th>Edit</th>
            </tr>
        </thead>
        <tbody>
	
<?php

foreach ($query as $row) {
	$datetime = new DateTime($row['date']);
	$fulldate = $datetime->format('d.m.Y');

	echo '<tr>
				<td style="min-width: 120px; text-align: left;">'. $row['name'].'</td>
                <td style="min-width: 100px; text-align: right;">'. $fulldate.'</td>
				<td style="min-width: 120px; text-align: right;">'. $row['exp'].'</td>
				<td style="text-align: right;">'. $row['streuner'].'</td>
                <td style="text-align: right;">'. $row['menschen'].'</td>
				<td style="text-align: right;">'. $row['gespielte_missionen'].'</td>
                <td style="text-align: right;">'. $row['abgeschlossene_missonen'].'</td>
				<td style="text-align: right;">'. $row['gefeuerte_schuesse'].'</td>
                <td style="text-align: right;">'. $row['haufen'].'</td>
				<td style="text-align: right;">'. $row['heldenpower'].'</td>
                <td style="text-align: right;">'. $row['waffenpower'].'</td>
				<td style="text-align: right;">'. $row['karten'].'</td>	
				<td style="text-align: right;">'. $row['gerettete'].'</td>			
				<td style="text-align: center;"><a href="?action=editstat&id='.$row['id'].'&uid='.$row['uid'].'" role="button"><span class="fas fa-edit"></a></td>			
            </tr>';
	unset($datetime);
}

?>
	</tbody>
	</table></div>
<script>$(document).ready(function(){ $('#allstats').tablesorter(); });</script>
