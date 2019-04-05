<?php
session_start();
header('Content-Type: application/json');

$config = include("../conf/config.php");

$pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

if (isset($_POST['ocr'])) {
	if (isset($_POST['ocr']['id'])) {
		$str = 'UPDATE '.$config->db_pre.'ocr SET ';
		$arr = array();
		foreach($_POST['ocr'] as $key => $value) {	
			if ($key != 'id' && $key != 'uid') {
				$str .= $key.'=:'.$key.', ';
				$arr[':'.$key] = $value;
			}
		}
		$arr[':id'] = $_POST['ocr']['id'];
		$str = substr($str, 0, -2);
		$str .= ' WHERE id=:id';
			
		$query = $pdo->prepare($str);    
		$query->execute($arr);
		
		$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'ocr WHERE uid = :uid');
		$query->execute(array(':uid' => $_SESSION['userid']));
		$data = $query->fetchAll(PDO::FETCH_ASSOC);
		
		echo json_encode(array("id"=>$_POST['ocr']['id'], 'data'=>$data));
	} else {
		$str = 'INSERT INTO '.$config->db_pre.'ocr (uid, ';
		$arr = array();
		foreach($_POST['ocr'] as $key => $value) {	
			if ($key != 'id' && $key != 'uid') {
				$str .= $key.', ';
				$arr[':'.$key] = $value;
			}
		}
		$arr[':uid'] = $_SESSION['userid'];
		$str = substr($str, 0, -2);
		$str .= ') VALUES (:uid, ';
		foreach($_POST['ocr'] as $key => $value) {	
			if ($key != 'id' && $key != 'uid') {
				$str .= ':'.$key.', ';
			}
		}
		$str = substr($str, 0, -2);
		$str .= ')';
		
		$query = $pdo->prepare($str);    
		$query->execute($arr);
		$id = $pdo->lastInsertId();
		$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'ocr WHERE uid = :uid');
		$query->execute(array(':uid' => $_SESSION['userid']));
		$data = $query->fetchAll(PDO::FETCH_ASSOC);
		
		echo json_encode(array("id"=>$id, 'data'=>$data));
	}
}

if (isset($_POST['id'])) {
	if($_POST['aktiv']) {
		$str = 'UPDATE '.$config->db_pre.'ocr SET aktiv = 0 WHERE uid=:uid';
		$arr = array(':uid' => $_SESSION['userid']);
		$query = $pdo->prepare($str);
		$query->execute($arr);
		$str = 'UPDATE '.$config->db_pre.'ocr SET aktiv = 1 WHERE id=:id';
		$arr = array(':id' => $_POST['id']);
		$query = $pdo->prepare($str);
		$query->execute($arr);
	} else {
		$str = 'UPDATE '.$config->db_pre.'ocr SET aktiv = 0 WHERE id=:id';
		$arr = array(':id' => $_POST['id']);
		$query = $pdo->prepare($str);
		$query->execute($arr);
	}
}

if (isset($_POST['remove'])) {
	$str = 'DELETE FROM '.$config->db_pre.'ocr WHERE id=:id';
	$arr = array(':id' => $_POST['remove']);
	$query = $pdo->prepare($str);
	$query->execute($arr);
	
	$query = $pdo->prepare('SELECT * FROM '.$config->db_pre.'ocr WHERE uid = :uid');
	$query->execute(array(':uid' => $_SESSION['userid']));
	$data = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode(array("id"=>$_POST['remove'], 'data'=>$data));
}
?>