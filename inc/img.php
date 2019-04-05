<?php
session_start();

$config = include("../conf/config.php");
include "prepimage.class.php";

$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$screens = glob("../screens/*.{jpg,png}", GLOB_BRACE);
foreach ($screens as $filename) {
	if (strlen(trim($filename)) > 0){
		$hasfiles = True;
		break;
	}
}
$file = $filename;
set_header_content_type($file);
//readfile($file);

if(isset($_GET['id'])) {
	$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'ocr WHERE uid = :uid AND id = :id');
	$query->execute(array(':uid' => $_SESSION['userid'], ':id' => $_GET['id']));
} else {
	$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'ocr WHERE uid = :uid');
	$query->execute(array(':uid' => $_SESSION['userid']));
}
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
$vars = $data[0];

$a = new prepimage($vars);
$a->set(true);
$a->run($file);

function set_header_content_type($file) {
    //Number to Content Type
    $ntct = Array( "1" => "image/gif",
                   "2" => "image/jpeg", #Thanks to "Swiss Mister" for noting that 'jpg' mime-type is jpeg.
                   "3" => "image/png",
                   "6" => "image/bmp",
                   "17" => "image/ico");

    header('Content-type: ' . $ntct[exif_imagetype($file)]);
}

?>