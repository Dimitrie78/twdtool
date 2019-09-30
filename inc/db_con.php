<?php 

$errmode = 'EXCEPTION'; // SILENT im Produktivbetrieb, EXCEPTION oder WARNING beim Debuggen
try {  
  $options = array(
        PDO::ATTR_ERRMODE => 'PDO::ERRMODE_'.$errmode, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );  
  $pdo = new PDO("mysql:host=".$config->dbhost.";dbname=".$config->dbname.";charset=utf8", $config->dbusername, $config->dbpassword,$options);


  $vers = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
  if (stripos($vers, 'MariaDB')===false)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

}catch(PDOException $e){
    echo "Datenbankverbindung fehlgeschlagen: " . $e->getMessage();
  exit;
}
?>