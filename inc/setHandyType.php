<?php
include "verify.php";

$hasfiles = False;

$screens = glob("screens/*.{jpg,png}", GLOB_BRACE);
foreach ($screens as $filename) {
	if (strlen(trim($filename)) > 0){
		$hasfiles = True;
		break;
	}
}

$query = $pdo->prepare('SELECT * FROM ocr WHERE uid = :uid');
$query->execute(array(':uid' => $_SESSION['userid']));
$data = $query->fetchAll(PDO::FETCH_ASSOC); #is maybe faster
if(empty($data)) {
	$data[0] = array(
		'name' => 'new',
		
		'playerW' => 372,
		'playerH' => 60,
		'playerX' => 706,
		'playerY' => 173,
		
		'epW' => 397,
		'epH' => 70,
		'epX' => 50,
		'epY' => 532,
		
		'werteW' => 260,
		'werteH' => 800,
		'werteX' => 205,
		'werteY' => 1350,
	);
}

if($hasfiles) { 

?>

<link rel="stylesheet" href="inc/css/ocr.css">
<script language="javascript">var vars = <?=json_encode($data)?>;</script>
<script type="text/javascript" src="inc/js/ocr.js"></script>
<form>
 <div class="row">
  <div class="form-group col-xs-6">
    <label for="name">Handy-Name:</label>
    <input type="text" class="form-control" id="name" name="name" value=""/>
  </div>
  <div class="form-group col-xs-6">
	<label for="selName" class="control-label">Handy-Namen:</label>
    <select id="selName" name="selName" class="form-control">
	</select>
  </div>
 </div>
<div class="clearfix">
  <div class="funkyradio pull-left">
	<div class="funkyradio-success">
		<input type="checkbox" name="active" id="active" />
		<label for="active">Aktiv</label>
	</div>
  </div>
  <div class="pull-right">
	 <input type="button" name="remove" id="remove" class="btn btn-danger" role="button" value="Type entfernen" />
  </div>
</div>
</form>

<div class="row">

	<div id="pic" class="col-md-6">
		<div id="player" class="drag">Player Name</div>
		<div id="ep" class="drag">Erfahrung</div>
		<div id="werte" class="drag">Werte</div>
		<img id="origin" src="<?=$filename?>">
	</div>
	<div id="pic" class="col-md-6">
		<img id="ocrtest" src="inc/img.php">
	</div>

</div>
<div class="result"></div>
<div id="pos"><p><strong>X-Position: </strong> | <strong>Y-Position: </strong></p></div>
<div id="size"><p><strong>width: </strong> | <strong>height: </strong></p></div>

<?php } else { ?>

<p>Sie müssen erst ein Screenshot hochladen!</p>

<?php } ?>