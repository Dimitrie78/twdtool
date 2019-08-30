<?php
include "verify.php";

## NUR FÜR DEV
if (isdev()){
  if (isset($_GET["action"]))
switch ($_GET['action']) {
	case "groupedit":
    $file = "inc/".$_GET['action'].".php";
    if(is_file($file)) {
		include($file);
	}
	break;
  }
} 
## Ende DEV


# Nur für ADMIN
if (isadmin()){
  if (isset($_GET["action"]))
switch ($_GET['action']) {
	case "setHandyType":
	case "uploadimg":
	case "prepimg":
    case "import":
    case "ocrfix":
    case "frontpageedit":
    $file = "inc/".$_GET['action'].".php";
    if(is_file($file)) {
		include($file);
	}
	break;
  }
} ## Ende Admin



# FÜR ADMIN+MOD
if (isadminormod()){
  if (isset($_GET["action"]))
	switch ($_GET['action']) {	
	case "alldata":
	case "createnewuser":
	case "usrmgr":
	case "doremovestat":
	case "editstat":
	case "doeditstat":
	case "removestat":
	case "addstat":
	case "doaddstat":
	case "removeusr":
	case "groupmove":
	case 'doremoveuser':
	#Auslesefehler beheben
	case "fails":
	case "editfail":
	$file = "inc/".$_GET['action'].".php";
	if(is_file($file)) {
		include($file);
	}
	break;
  }
} 

#FÜR die User
if (isset($_GET["action"]))
switch ($_GET['action']) {
    case "myprofile":
	case "notes":
	case "classicstats":
    case "stats":
    case "top":
    case "levelingnumbers":
    case "currentstats":
    case "avg":
    case "custom_stat":
	$file = "inc/".$_GET['action'].".php";
	if(is_file($file)) {
		include($file);
	}
	break;
} 
?>