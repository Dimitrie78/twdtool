<?php
include "verify.php";

if(!$_GET['do']){
	if($_GET['msg'] == "success")
	{
		okmsg('Die News wurden aktualisiert!');
	}
	if($_GET['msg'] == "fail")
	{
		failmsg('Die News konnten nicht aktualisiert werden!');
	}
	
	$news = $pdo->query("SELECT text, ndate FROM news WHERE id = 1 AND active = 1")->fetch();


	if (!$news){exit('Gewählter Eintrag nicht existent oder bereits fehlerfrei!');}

	echo 'Zeilenschaltungen werden automatisch registriert.<br> Spezielle Formatierungen müssen in HTML-Code erfasst werden.
	<form action="?action=frontpageedit&do=update" method = "POST" autocomplete="no">
	<div class="form-group">
	<label for="fpe">Startseitentext editieren</label>
	<textarea class="form-control" id="fpd" rows="15" name = "text" required>'.br2nl($news['text']).'</textarea>
	</div>
	<button type="submit" name = "updatenews" class="btn btn-success">Update</button>
	</form>';
}

if($_GET['do'] == "update" && $_POST['text']>"" && isset($_POST['updatenews'])){

	#vorbereitet für news aktivieren / deaktivieren / mehrere news
	$query = $pdo->prepare('UPDATE news SET text = :text, ndate = NOW()');

	if ($query->execute(array(':text' => $_POST['text'])))
	{
		header("Location: ?action=frontpageedit&msg=success");
	}
	else
	{
		header("Location: ?action=frontpageedit&msg=fail");
	}
}
?>