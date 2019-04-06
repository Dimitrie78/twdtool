<?php
include "verify.php";

$statement = $pdo->prepare("SELECT * FROM ".$config->db_pre."users WHERE id = :id");
$result = $statement->execute(array('id' => $_SESSION['userid']));
$user = $statement->fetch();
?>
<div class="container">
	<div class="row">
		<h2>Benutzerdaten</h2>
    <ul class="nav nav-tabs">
      <li class="active"><a href="#home" data-toggle="tab">Profil</a></li>
      <li><a href="#passwd" data-toggle="tab">Passwort</a></li>
	        <li><a href="#mynotes" data-toggle="tab">Notizen</a></li>
    </ul>
    <div id="myTabContent" class="tab-content">
	

<div class="tab-pane active in tab-links" id="home">
	<form class="form-horizontal" id = "tab" method = "POST" action = "?action=myprofile#home" >
		<div class="form-group green-border-focus">
		  <label class="col-md-4 control-label" for="textinput">Telegram</label>  
		  <div class="col-md-4">
		  <input id="textinput"  name = "telegram" value= "<?php echo ($_POST['telegram'] ? $_POST['telegram'] : htmlentities($user['telegram'])); ?>"  placeholder="Telegram Benutzername" class="form-control input-md" required="" type="text">
		  </div>
		</div>
		<div class="form-group green-border-focus">
		  <label class="col-md-4 control-label" for="save"></label>
		  <div class="col-md-4">
			<button id="save" name="save" class="btn btn-success">Speichern</button>
			<a href="index.php" name="cancel" class="btn btn-danger" role="button">Abbrechen</a>
			
			<?php if ($_POST['telegram']) {

	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET telegram = "'.$_POST['telegram'].'" WHERE id = :id');
   $query->execute(array(':id' => $_SESSION['userid']));
					echo '<p> </p>
					<div class="alert alert-success"><span class = "fas fa-info-circle"></span> Die Kontaktinfos wurden gespeichert!</div>';
			}
			?>

		  </div>
		</div>
	</form>
</div>


<div class="tab-pane in tab-links" id="passwd">
	<br>
Bitte wähle ein starkes Passwort!<br>Wenn du es vergisst kannst du bei einem Moderator oder Admin ein neues setzen lassen. Sollte aber nicht zu oft vorkommen ;)<br><br>
			<form class="form-horizontal" id = "tab" method = "POST" action = "?action=myprofile#passwd">
		<div class="form-group green-border-focus">
		  <label class="col-md-4 control-label" for="textinput">Passwort</label>  
		  <div class="col-md-4">
		    <input type="password" style="display:none"> <!-- Prevent Password-Autofill -->
		  <input id="textinput"  name = "password" value= ""  class="form-control input-md" required="" type="password">
		  </div>
		</div>
		<div class="form-group green-border-focus">
		  <label class="col-md-4 control-label" for="save"></label>
		  <div class="col-md-4">
			<button id="save" name="save" class="btn btn-success">Speichern</button>
			<a href="index.php" name="cancel" class="btn btn-danger" role="button">Abbrechen</a>
			
						<?php if (trim($_POST['password']) > "") {

	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET passwd = "'.password_hash($_POST['password'], PASSWORD_DEFAULT).'" WHERE id = :id');
   $query->execute(array(':id' => $_SESSION['userid']));
					echo '<p> </p>
					<div class="alert alert-success"><span class = "fas fa-info-circle"></span> Dein neues Passwort wurde gespeichert!<br>Merke es dir gut oder schreibe es auf.</div>';
			}
			?>
			
		  </div>
		</div>
	</form>
</div><div class="tab-pane in tab-links" id="mynotes"><span class="col-md-4" for="comment"><br>Hier kannst du Abwesenheitsnotizen etc. eintragen, alles das die Admins / Mods lesen sollten<br><br> 
<?php

 
$notetime = ($user['notetime'] <>  "0000-00-00 00:00:00" ? date("d.m.Y H:i:s", strtotime($user['notetime'])) : "Noch keins");
echo "Letztes Update:<br>".($_POST['notes'] ? date("d.m.Y H:i:s",time()) : $notetime);

	
?>
</span><br><form class="form-horizontal" id = "tab" method = "POST" action = "?action=myprofile#mynotes" >
		<div class="form-group green-border-focus">
		  <div class="col-md-5">
			    <textarea class="form-control input-md"  rows="15" id="comment" name = "notes"><?php echo ($_POST['notes'] ? $_POST['notes'] : htmlentities($user['notes'])); ?></textarea>
		  </div>
		</div>
		<div class="form-group green-border-focus">
		  <label class="col-md-4 control-label" for="save"></label>
		  <div class="col-md-4">
			<button id="save" name="save" class="btn btn-success">Speichern</button>
			<a href="index.php" name="cancel" class="btn btn-danger" role="button">Abbrechen</a>
			
			<?php if ($_POST['notes']) {


	$query = $pdo->prepare('UPDATE '.$config->db_pre.'users SET notes = "'.$_POST['notes'].'", notetime = NOW() WHERE id = :id');
   $query->execute(array(':id' => $_SESSION['userid']));
   
					echo '<p> </p>
					<div class="alert alert-success"><span class = "fas fa-info-circle"></span> Die Notiz wurde gespeichert!</div>';
			}
			?>
		  </div>
		</div>
	</form>
</div>

	</div>
	
  </div>
</div>
