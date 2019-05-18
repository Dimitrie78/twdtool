<?php
include "verify.php";

if(!isset($_GET['do'])) {
	msgbox($_GET['msg']);
	
	$news = $pdo->query("SELECT text, ndate FROM ".$config->db_pre."news WHERE gid = ".$_SESSION['gid']."  AND active = 1")->fetch();
	
	echo 'Zeilenschaltungen werden automatisch registriert.<br> Spezielle Formatierungen müssen in HTML-Code erfasst werden.
	<form action="?action=frontpageedit&do=update" method = "POST" autocomplete="no">
	<div class="form-group">
	<label for="fpe">Startseitentext editieren</label>
	<textarea class="form-control" id="fpd" rows="15" name = "text" required>'.br2nl($news['text']).'</textarea>
	</div>
	<button type="submit" name = "updatenews" class="btn btn-success">Update</button>
	</form>';
}

if(isset($_GET['do']) && $_GET['do'] == "update" && $_POST['text']>"" && isset($_POST['updatenews'])){

$stmt = $pdo->query("SELECT id FROM ".$config->db_pre."news WHERE gid = ".$_SESSION['gid']." AND active = 1"); 
$stmt->execute(); 
$ncount = $stmt->rowCount();


if($ncount === 0){ 
echo 'adding news...';
$date = date('Y-m-d H:i:s');
	$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."news(gid, ndate, text, active)
	VALUES(:gid, :ndate, :text, :active)");
	
	$statement->execute(array(
		"gid" => $_SESSION['gid'],
		"ndate" => $date,
		"text" => $_POST['text'],
		"active" => 1
	));

	
	$count = $statement->rowCount();
	if($count =='1'){    
		header("Location: ?action=frontpageedit&msg=addsuccess");
	}
	else
	{
		header("Location: ?action=frontpageedit&msg=addfail");
	}

}
else{

	#vorbereitet für news aktivieren / deaktivieren / 1 news per gid
	$query = $pdo->prepare('UPDATE '.$config->db_pre.'news SET text = :text, ndate = NOW() WHERE gid = '.$_SESSION['gid'].'');

	if ($query->execute(array(':text' => $_POST['text'])))
	{
		header("Location: ?action=frontpageedit&msg=updatesuccess");
	}
	else
	{
		header("Location: ?action=frontpageedit&msg=updatefail");
	}
}
}
?>