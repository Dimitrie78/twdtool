<?php
$mode = 'icons'; // wähle icons oder noicons für die Gestaltung des Tabellenkopfs

include "verify.php";
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
if (isdev())
{
?>
<div class ="form-group">
<form class="form-vertical" role="form" method = "POST" action = "?action=levelingnumbers" >
<label for="inputGroup" class = "control-label">Gruppe wählen: <span class="fas fa-arrow-right"></span></label>
      <select onchange="this.form.submit()" id="inputGroup" name = "gid" class = "form-control" style="width:auto;min-width:200px;">
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
	$sql = 'SELECT `id`, `tag`, `name` FROM `'.$config->db_pre.'groups` ORDER BY `sort` ASC, `tag` ASC';
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
	 </div>
<?php
}
$usrqry = $pdo->query('
SELECT U.id AS uid, U.ign, G.tag FROM ' . $config->db_pre . 'users U 
INNER JOIN ' . $config->db_pre . 'groups G ON G.id = U.gid
WHERE active > 0 ' . $and_grouplimit . ' ORDER BY ign');
$usrqry->execute();

$tbody = '';
foreach ($usrqry as $usr)
{
    $uid   = $usr['uid'];
    $uname = $usr['ign'];
    
    $sql1 = 'SELECT date, streuner, menschen, gespielte_missionen, abgeschlossene_missonen, gefeuerte_schuesse, haufen, heldenpower, waffenpower, karten, gerettete 
	FROM  `' . $config->db_pre . 'stats`
	WHERE uid =' . $uid . '
	ORDER BY date DESC 
	LIMIT 0 , 1';
    
    $sql2 = 'SELECT date, streuner, menschen, gespielte_missionen, abgeschlossene_missonen, gefeuerte_schuesse, haufen, heldenpower, waffenpower, karten, gerettete
	FROM  `' . $config->db_pre . 'stats` 
	WHERE uid =' . $uid . '
	ORDER BY date DESC
	LIMIT 1 , 1';
	
    
    //reset variables
    $date1                    = '';
    $streuner1                = '';
    $menschen1                = '';
    $gespielte_missionen1     = '';
    $abgeschlossene_missonen1 = '';
    $gefeuerte_schuesse1      = '';
    $haufen1                  = '';
    $heldenpower1             = '';
    $waffenpower1             = '';
    $karten1                  = '';
    $gerettete1               = '';
    
    foreach ($pdo->query($sql1) as $row1)
    {
        $date1                    = $row1['date'];
        $streuner1                = $row1['streuner'];
        $menschen1                = $row1['menschen'];
        $gespielte_missionen1     = $row1['gespielte_missionen'];
        $abgeschlossene_missonen1 = $row1['abgeschlossene_missonen'];
        $gefeuerte_schuesse1      = $row1['gefeuerte_schuesse'];
        $haufen1                  = $row1['haufen'];
        $heldenpower1             = $row1['heldenpower'];
        $waffenpower1             = $row1['waffenpower'];
        $karten1                  = $row1['karten'];
        $gerettete1               = $row1['gerettete'];
    }
    
    //reset variables
    $date2                    = '';
    $streuner2                = '';
    $menschen2                = '';
    $gespielte_missionen2     = '';
    $abgeschlossene_missonen2 = '';
    $gefeuerte_schuesse2      = '';
    $haufen2                  = '';
    $heldenpower2             = '';
    $waffenpower2             = '';
    $karten2                  = '';
    $gerettete2               = '';
    
    foreach ($pdo->query($sql2) as $row2)
    {
        $date2                    = $row2['date'];
        $streuner2                = $row2['streuner'];
        $menschen2                = $row2['menschen'];
        $gespielte_missionen2     = $row2['gespielte_missionen'];
        $abgeschlossene_missonen2 = $row2['abgeschlossene_missonen'];
        $gefeuerte_schuesse2      = $row2['gefeuerte_schuesse'];
        $haufen2                  = $row2['haufen'];
        $heldenpower2             = $row2['heldenpower'];
        $waffenpower2             = $row2['waffenpower'];
        $karten2                  = $row2['karten'];
        $gerettete2               = $row2['gerettete'];
    }
	
    $tage                    = ($date1 && $date2) ? ceil((strtotime($date1) - strtotime($date2)) / 86400) : 0;
    $streuner                = ($streuner1 && $streuner2) ? $streuner1 - $streuner2 : 0;
    $menschen                = ($menschen1 && $menschen2) ? $menschen1 - $menschen2 : 0;
    $gespielte_missionen     = ($gespielte_missionen1 && $gespielte_missionen2) ? $gespielte_missionen1 - $gespielte_missionen2 : 0;
    $abgeschlossene_missonen = ($abgeschlossene_missonen1 && $abgeschlossene_missonen2) ? $abgeschlossene_missonen1 - 
	                            $abgeschlossene_missonen2 : 0;
    $gefeuerte_schuesse      = ($gefeuerte_schuesse1 && $gefeuerte_schuesse2) ? $gefeuerte_schuesse1 - $gefeuerte_schuesse2 : 0;
    $haufen                  = ($haufen1 && $haufen2) ? $haufen1 - $haufen2 : 0;
    $heldenpower             = ($heldenpower1 && $heldenpower2) ? $heldenpower1 - $heldenpower2 : 0;
    $waffenpower             = ($waffenpower1 && $waffenpower2) ? $waffenpower1 - $waffenpower2 : 0;
    $karten                  = ($karten1 && $waffenpower2) ? $karten1 - $karten2 : 0;
    $gerettete               = ($gerettete1 && $gerettete2) ? $gerettete1 - $gerettete2 : 0;
    $streuner_pro_mission    = number_format((float) $streuner / ($abgeschlossene_missonen ?: 1), 2, '.', '');
    $karten_pro_mission      = number_format((float) $karten / ($abgeschlossene_missonen ?: 1), 2, '.', '');
    $schuesse_pro_mission    = number_format((float) $gefeuerte_schuesse / ($abgeschlossene_missonen ?: 1), 2, '.', '');
    
    $tbody .= '<tr>';
    if (isdev())
    {
        $tbody .= '<td style="text-align: left;">' . $usr['tag'] . '</td>';
    }

    $tbody .= '<td style="text-align: left; min-width: 120px;"><a href = "?action=stats&uid=' . $uid . '">' . $uname . '</a></td>
          <td style="text-align: right;">' . $tage . '</td>
		  <td style="text-align: right;">' . $streuner . '</td>
		  <td style="text-align: right;">' . $menschen . '</td>
		  <td style="text-align: right;">' . $gespielte_missionen . '</td>
		  <td style="text-align: right;">' . $abgeschlossene_missonen . '</td>
		  <td style="text-align: right;">' . $gefeuerte_schuesse . '</td>
		  <td style="text-align: right;">' . $haufen . '</td>					
		  <td style="text-align: right;">' . $heldenpower . '</td>		
		  <td style="text-align: right;">' . $waffenpower . '</td>	
		  <td style="text-align: right;">' . $karten . '</td>	
		  <td style="text-align: right;">' . $gerettete . '</td>	
		  <td style="text-align: right;">' . $streuner_pro_mission . '</td>	
		  <td style="text-align: right;">' . $karten_pro_mission . '</td>	
		  <td style="text-align: right;">' . $schuesse_pro_mission . '</td>	
		</tr>';
}

$thead = '<div class="table-responsive">
 <table id="levelingnumbers" class="table table-striped table-condensed datatable table-bordered nowrap table-hover table-fixed" style="width:100%">
  <thead>
    <tr>';
	
if (isdev())
{
    $thead .= '<th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Gruppe"><i class="gi gi-users"></i> GRP</a></th>';
}

if ($mode == 'noicons')
{
    $thead .= '
      <th>IGN</th>
	  <th>TG</th>
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
	  <th>STR/Mis</th>
	  <th>KRT/Mis</th>
	  <th>SCH/Mis</th>
    </tr>
  </thead>
  <tbody>';
}
elseif ($mode == 'icons')
{
	
    $thead .= '
      <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Ingame Name"><i class="gi gi-id-card"></i> IGN</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Anzahl Tage"><span class="glyphicon glyphicon-calendar"></span> TG</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Streuner"><i class="gi gi-poison"></i> STR</a></th>
      <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Menschen"><i class="gi gi-heartbeat"></i> MEN</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Gespielte Missionen"><i class="gi gi-caution"></i> GMIS</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Abgeschlossene Missionen"><i class="gi gi-check-square-o"></i> AMIS</a></th>
      <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Schüsse"><i class="gi gi-ammo"></i> SCHS</a></th>
      <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Kisten"><i class="gi gi-briefcase"></i> KST</a></th>
      <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Helden"><i class="gi gi-user-military"></i> HLD</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Waffen"><i class="gi gi-gun"></i> WAF</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Karten"><i class="gi gi-dogtags"></i> KRT</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Gerettet"><i class="fab fa-creative-commons-by"></i> GRT</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Streuner pro Mission"><i class="gi gi-poison"></i>/M</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Karten pro Mission"><i class="gi gi-briefcase"></i>/M</a></th>
	  <th nowrap><a href="#" data-toggle="tooltip" data-placement="top" title="Schüsse pro Mission"><i class="gi gi-ammo"></i>/M</a></th>
    </tr>
  </thead>
  <tbody>';
}

$tfoot = '</tbody>
 </table>
</div>';
echo $thead . $tbody . $tfoot;
?>
<script>$(document).ready(function(){ $('#levelingnumbers').tablesorter(); });</script>