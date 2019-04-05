<?php
include "verify.php";

if($_GET['msg'] == "deletesuccess") {
	okmsg('Der Eintrag wurde entfernt!');
}
if($_GET['msg'] == "deletefail"){
	failmsg('Die Eintrag konnte nicht entfernt werden!');
}
	
$thead = '
<div class="table-responsive"><table class="table table-hover" style="width:auto">
  <thead class="thead-dark" style="font-size: 16px;">
    <tr >
	  <th scope="col">Datum</th>
      <th scope="col">ID</th>
	  <th scope="col">OCR-Name</th>
	  <th scope="col">Edit</th>
    </tr>
  </thead>
  <tbody>';	
  
$tfoot = '</tbody>
</table></div>';

$qry = 'SELECT id,date,name FROM `stats` WHERE fail = 1 ORDER BY date ASC';
$tbody = '';

foreach ($pdo->query($qry) as $row){
	$datetime = new DateTime($row['date']);

	$tbody .= '<tr style="font-size: 16px;">
      <th scope="row" style="text-align: right;">'.$datetime->format('d.m.Y H:i:s').'</th>
      <td>'.htmlentities($row['id']).'</td>
	  <td>'.htmlentities($row['name']).'</td>
      <td><a href="?action=editfail&id='.$row['id'].'"  class="btn btn-success btn-sm">Edit</a></td>
 
    </tr>';
}

if(!$row){
	echo 'Alles ok. Keine Fehler.';
} else {
	echo $thead.$tbody.$tfoot;
}
?>