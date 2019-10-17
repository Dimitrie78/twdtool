<?php
// Multi-Dimensionaler In-Array Checker
function in_array_r($item , $array){
    return preg_match('/"'.preg_quote($item, '/').'"/i' , json_encode($array));
}

// Ermittlung des aktuellen Levels und des Prozentsatzes
// der Komplettierung zum nächsten Level - Rückgabe als Array (lvl,prozent)
function leveldata($exp){
  $lvls = array(
  '12150300' => '34',
  '10705300' => '33',
  '9380300' => '32',
    '8155300' => '31',
    '7030300' => '30',
    '6005300' => '29',
  '5080300' => '28',
    '4255300' => '27',
    '3530300' => '26',
    '2885300' => '25',
  '2320300' => '24',
    '1835300' => '23',
    '1430300' => '22',
    '1105300' => '21',
    '830300' => '20',
    '605300' => '19',
    '430300' => '18',
    '305300' => '17',
    '205300' => '16',
    '130300' => '15',
    '80300' => '14',
    '45300' => '13',
    '25300' => '12',
    '15300' => '11',
    '9300' => '10',
    '5300' => '9',
    '2300' => '8',
    '1100' => '7',
    '750' => '6',
    '500' => '5',
    '300' => '4',
    '50' => '3',
    '3' => '2'
  );
	//Entfernung aller ungültigen Zeichen
	$clean = preg_replace("/[^0-9\\/]+/i", "",trim($exp));
	$exp = explode('/',$clean);
	// Der erste Wert muss kleiner als der zweite sein, und die exp wert muss in Levelarray stehen
	if($exp[0] < $exp[1] AND in_array_r($exp[1],$lvls)){
		$currlvl = $lvls[$exp[1]]-1;
		$p = ($exp[0]*100)/$exp[1];
		return $currlvl.'.'.sprintf ("%02d",$p);
	}
	else
	{
		return '-';
	}
}

//helper für upload2api
#Bereinigung exp
#prüft ob ein / Fehlt, wenn ja füge es vor dem letzten vorkommen einer levelgrenze ein
#geht davon aus das davor eine 1 erkannt wurde, die wird mit dem / ersetzt
function cleanexp($exp)
{
$replaceoarr = array("o" => "0","O" => "0","I" => "1");	
$exp = preg_replace("/[^0-9\\/]+/i", "",strtr($exp,$replaceoarr));

$lvls = array('12150300','10705300','9380300','8155300','7030300','6005300','5080300','4255300','3530300','2885300','2320300','1835300','1430300','1105300','830300','605300','430300','305300','205300','130300','80300','45300','25300','15300','9300','5300','2300','1100','750','500','300','50','3');
if (strpos($exp, "/") == false)
	{
	foreach($lvls as $searchfor)
		{
		$pos = strripos($exp, $searchfor);
		if (!$pos === false)
			{
			return substr($exp, 0, $pos - 1) . '/' . substr($exp, $pos, strlen($exp));
			break;
			}
		}
	}
  else
	{
	return $exp;
	}
}

function getCurrentPath() {
	return dirname(((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}
	
function getCurrentURL() {
	#request_uri statt php_self = mit query string
	return ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
}
	
function getuid($uid){
	global $pdo;
	global $config;
	if(isSet($uid)&&$uid!=''){
		$statement = $pdo->prepare("SELECT id FROM ".$config->db_pre."users WHERE `ign` = :ign");
		$result = $statement->execute(array('ign' => $uid));
		$usr = $statement->fetch();
		if (!$usr) {
			return 0;
		}else{
			return $usr['id'];
		}
  }else return 0;
}


function geturole($id){
	global $pdo;
	global $config;
	$statement = $pdo->prepare("SELECT role FROM ".$config->db_pre."users WHERE id = :id");
	$result = $statement->execute(array('id' => $id));
	$usr = $statement->fetch();
	if (!$usr) {
		return 0;
	}else{
		return $usr['role'];
	}
}


function getuname($uid){
	global $pdo;
	global $config;
	$statement = $pdo->prepare("SELECT ign FROM ".$config->db_pre."users WHERE id = :id");
	$result = $statement->execute(array('id' => $uid));
	$usr = $statement->fetch();
	if (!$usr) {
		return 0;
	}else{
		return $usr['ign'];
	}
}

function getugroupname($uid){
	global $pdo;
	global $config;
	$statement = $pdo->prepare("SELECT ".$config->db_pre."groups.name as groupname
	FROM ".$config->db_pre."groups
	INNER JOIN ".$config->db_pre."users ON ".$config->db_pre."users.gid = ".$config->db_pre."groups.id where ".$config->db_pre."users.id = :uid");
	$result = $statement->execute(array('uid' => $uid));
	$usr = $statement->fetch();
	if (!$usr) {
		return 0;
	}else{
		return $usr['groupname'];
	}
}


function getugrouptag($uid){
	global $pdo;
	global $config;
	$statement = $pdo->prepare("SELECT ".$config->db_pre."groups.tag as grouptag
	FROM ".$config->db_pre."groups
	INNER JOIN ".$config->db_pre."users ON ".$config->db_pre."users.gid = ".$config->db_pre."groups.id where ".$config->db_pre."users.id = :uid");
	$result = $statement->execute(array('uid' => $uid));
	$usr = $statement->fetch();
	if (!$usr) {
		return 0;
	}else{
		return $usr['grouptag'];
	}
}

function utf8_converter($array) {
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
        }
    });
    return $array;
}



function uploadToApi($target_file){
	global $pdo;
	global $config;	
	global $vision;
	# echo 'uploadToApi:'.$target_file.'<br />';
	list($width, $height, $type) = getimagesize($target_file);
	$imageurl = getCurrentPath().'/'.$target_file;
	#exit();
	if($config->apiprovider != 'google'){
	 if($config->apiprovider == 'ocrspacepro'){
		 $apiurl = 'https://apipro2.ocr.space/parse/image';
	 }
	 else
	 {
		 $apiurl = 'https://api.ocr.space/parse/image';
	 }
	 
	 
		//2 is jpg, 3 is png
		if($type == 2){
			$data = array(
				"language" => "cht",
				"isOverlayRequired" => "false",
				"detectOrientation" => "false",
				"scale" => "false",
				"filetype" => 'JPG',
				"url" => $imageurl,
			);
		}
		elseif($type == 3){
			$data = array(
				"language" => "cht",
				"isOverlayRequired" => "false",
				"detectOrientation" => "false",
				"scale" => "false",
				"filetype" => 'PNG',
				"url" => $imageurl,
			);
		}
		$ch = curl_init();
		curl_setopt_array($ch, array(
		    CURLOPT_URL => $apiurl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => 1,
			CURLOPT_HTTPHEADER => array('Content-Type:multipart/form-data', 'apikey:'.$config->ocrspace_apikey.''),
			CURLOPT_POSTFIELDS => $data,
		));

		$result = curl_exec($ch);
		curl_close($ch);
			
	$result_array = json_decode($result);


	#if(!empty($result_array->ErrorMessage))  //for ocrspace
	#{
	#	echo 'OCR-Problem: '.$result_array->ErrorMessage[0];
	#}
	#else 
	#{
		$ocrresult = $result_array->ParsedResults[0]->ParsedText;
	}
	else
	{
		echo 'Google-OCR läuft...';
		$imageResource = fopen($target_file, 'r'); #<- images from upload2api image param
		$image = $vision->image($imageResource, ['DOCUMENT_TEXT_DETECTION']);
		$result = $vision->annotate($image);
		$document = $result->fullText();
		$ocrresult = $document->text();
		#echo '<pre>';
		#echo print_r($ocrresult);
		#echo '</pre>';
		#$ocrresult = str_replace(' ','',$ocrresult);
		#$ocrresult = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/","\n", $ocrresult);
		#$array = preg_split("/\r\n|\n|\r/", $ocrresult); //<- Extracted Imagetext in Array Pieces, Name, Exp, etc...
		
	}

		 // echo 'Ergebnis:'.$ocrresult.'<br />-------|||||---------<br />';
		//Entfernt überflüssige Zeichen
		
		#$ocrresult = str_replace(' ','',$ocrresult);
		//Entfernt doppelte Zeilenschaltungen durch eine einzige
		$ocrresult = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/","\n", $ocrresult);
		
		//Erstellt das Array
		$array = preg_split("/\r\n|\n|\r/", $ocrresult);

		//Geht nochmal über die einzelnen Postionen um wirklich alles Leerzeichen innerhalb der Elemente zu entfernen
		$array=array_map('trim',$array);
		// $time = filemtime($target_file);
		if(count($array)>15){ //BigFile?
			//check&fix Double-Page-Effekt of OCR.Space
			$fLine = $array[0];
			// echo $fLine.'<br />';
			if(!is_numeric($fLine))
				for($i=1;$i<count($array);$i++){

					if($array[$i]===$fLine){

						array_splice($array, 0, $i);
						break;
					}
				}
			return readOCRBigArray($array);
		}else
		  return readOCRArray($array, $target_file);		
  #}
}

function readOCRBigArray($array){
	echo 'Zeilen: '.count($array).'<br />';
  $little = array();
  $act_line = '';
  $prior_line = '';
  $c = 0;
  $string_data = file_get_contents("../2ocr/bigfile/bigfile.txt");
	$filelist = unserialize($string_data);

  for($i = 0; $i < count($array); $i++){
    $act_line = trim($array[$i]);

    // echo $i.'|'.$act_line.'<br />';
    if($i > 3)
	    if((((stripos($act_line, 'ep')===0) || (stripos($act_line, 'xp')===0)) &&
	    	 (is_numeric(substr($act_line, 2, 1))) && 
	    	 (is_numeric(substr($act_line, strlen($act_line)-1, 1)))) || ($i == (count($array)-1)))
	    {
	    
	    	if ($i == (count($array)-1)){
	    	  $little[] = $prior_line;
	    	  $little[] = $act_line;
	    	}
	    	// echo ' - EP found';
	     	if(count($little)>20){
	     		echo ' - Fehler!1';
	     		exit;
	     	}else if (count($little)<1&&$i>3){
	     		echo ' - Fehler!2';
	     		exit;
	     	}
		   	if(count($little)>12){
	    		//BUG OCR.Space --> doppelte Nummern filtern
	    		$last = '';
	    		for($h = count($little)-1; $h >= 0; $h--){
	    			if($last!=''&&$last==$little[$h]){
	    				array_splice($little, $h, 1);
				   		echo '[Doppelte Zahlen] entfernt (OCR.Space):';
	    				$last = '';
	    				break;
	    			}
	    			$last = $little[$h];
	    		}
	    	}
	     	$filename = $filelist[$c];
	     	readOCRArray($little, '../'.$filename); 
	     	$c++;
	     	/*
	     	
	     	echo '------ '.$c.'<br />';
	      for($k=0;$k<count($little);$k++){
	      	echo $little[$k].'<br />';
	      }
				/**/

	     	$little = array();
	    }
    if($prior_line!=''){
    	$little[] = $prior_line;     
  	}
    $prior_line = $act_line;
    // echo '<br />';
  }
	unlink("../2ocr/bigfile/bigfile.jpg");
	unlink("../2ocr/bigfile/bigfile.txt");
}

function readOCRArray($array, $target_file){
	#echo '<hr><b>'.getuid($array[0]).'</b><hr>';
	#exit();
	// echo 'readOCRArray:'.$target_file.'<br />';
	// echo 'fm:'.date("Y-m-d", filemtime($target_file)).'<br />';
	global $pdo;
	global $config;
	if(count($array) > 10){
		$q = $pdo->query("SELECT `searchfor` as s, `replacement` as r FROM `".$config->db_pre."namefix` WHERE `searchfor` like '".$array[0]."' ORDER BY ID DESC LIMIT 1 ;");
		// $r = $q->fetchAll(PDO::FETCH_KEY_PAIR);
		$r = $q->fetch();
	
    
    #$exp = cleanexp($array[0]);
    #$x = explode('/',$exp);
    
    #$name = '';

    #if(is_numeric($x[0])) {
      /*if($r)
        $name = strtr($array[0],$r);
      else $name = $array[0];*/
    #  $y = 1;
    #} else {
/*      if($r)
        $name = strtr($array[0],$r);
      else $name = $array[0];
      */
    #  $y = 0;
    #  $name = $array[0];
	#	}
      
    #if($y==0)
    	if ($r){
    		// echo 'NameFix found!<br />'.'OCR:|'.$array[0].'|<br />'.'Sea:|'.$r['s'].'|<br />'.'Rep:|'.$r['r'].'|<br />';
     	    $name = $r['r'];
    	}
		else
		{
			$name = $array[0];
		}
    
  #echo 'Name: '.$name.'<br>';
		#$exp = cleanexp($array[1-$y]);
		$exp = cleanexp($array[1]);
  #echo 'EXP '.$exp.'<br>';
  #echo '<br>';
  
		$streuner =  str_replace("o","0",$array[2-$y]);
		$menschen = str_replace("o","0",$array[3-$y]);
		$gespielte_missionen =  str_replace("o","0",$array[4-$y]);
		$abgeschlossene_missonen =  str_replace("o","0",$array[5-$y]);
		$gefeuerte_schuesse =  str_replace("o","0",$array[6-$y]);
		$haufen =  str_replace("o","0",$array[7-$y]);
		$heldenpower =  str_replace("o","0",$array[8-$y]);
		$waffenpower =  str_replace("o","0",$array[9-$y]);
		$karten =  str_replace("o","0",$array[10-$y]);
		$gerettete =  str_replace("o","0",$array[11-$y]);

		#nur die datei ohne weitere Verzeichnisse daran bereitstellen
		$onlyfile = substr(strrchr($target_file, "/"), 1);
		$fileext = substr(strrchr($target_file, "."), 1);
		$usrid = getuid($name);

		$notizen = '';
		unset($errlog);
		$errlog	= array();
		$fail = 0;

		if($usrid == 0) {
			$fail = 1;
			$errlog[] = "Name";
			if($exp=='') $errlog[] = "EP";
			if($streuner==0) $errlog[] = "Streuner";
			if($menschen==0) $errlog[] = "Menschen";
			if($gespielte_missionen==0) $errlog[] = "gespielte Missionen";
			if($abgeschlossene_missonen==0) $errlog[] = "Abgeschl. Missonen";
			if($gefeuerte_schuesse==0) $errlog[] = "Schüsse";
			if($haufen==0) $errlog[] = "Kisten";
			if($heldenpower==0) $errlog[] = "Weldenpower";
			if($waffenpower==0) $errlog[] = "Waffenpower";
			if($karten==0) $errlog[] = "Karten";
			if($gerettete==0) $errlog[] = "Gerettete";
		}
		else
		{
			$fail = 0;
			$duplicate = False;
			#wenn die uid gefunden wurde, prüfe ob die werte plausibel sind

			//$rst = $pdo->prepare("SELECT * FROM ".$config->db_pre."stats WHERE uid = :uid order by id desc limit 0,1");
			//$result = $rst->execute(array('uid' => $usrid));
			$rst = $pdo->prepare("SELECT * FROM ".$config->db_pre."stats WHERE uid = :uid and date <= :date order by date desc limit 0,1");
			$result = $rst->execute(array(	'uid' => $usrid,
											'date' => date("Y-m-d H:i:s", filemtime($target_file))));
			$usr = $rst->fetch();
			$error_row = -1;
			$fail_count = 0; $save_fail_count = 0;
			#wenn überhaupt was gefunden wurde

			$arr[] = $exp; //0
			$arr[] = $streuner; //1
			$arr[] = $menschen; //2
			$arr[] = $gespielte_missionen;//3
			$arr[] = $abgeschlossene_missonen;//4
			$arr[] = $gefeuerte_schuesse;//5
			$arr[] = $haufen;//6
			$arr[] = $heldenpower;//7
			$arr[] = $waffenpower;//8
			$arr[] = $karten;//9
			$arr[] = $gerettete;//10

			if($usr){ 
				for($i = 0; $i < 2; $i++){
					//Abstand Tage berechnen --> könnte ggfls. benutzt werden, um die Werte besser zu validieren. z.B. $haufen > ($user['haufen']+(1000*$datediff)) sprich Fehler bei mehr als 1k Kisten am Tag.
					 $error_row = -1;
					 $datediff = round(abs(strtotime(date("Y-m-d", filemtime($target_file)))-strtotime(substr($usr['date'],0,10)))/86400);
					 if ($datediff<2) $datediff = 2;

					if ($usr['date'] == date("Y-m-d H:i:s", filemtime($target_file))) {$duplicate = True;}
					#wenn in der exp kein schrägstrich ist
					if(!strstr($exp, '/')){$fail = 1; $errlog[] = "Exp"; $error_row = $error_row<0?0:$error_row; $fail_count++;}
					// else if(!strstr($exp, 'EP')){$fail = 1; $errlog[] = "Exp"; $error_row = $error_row<0?0:$error_row;  $fail_count++;}
					#wenn die anzahl der ausgelesenen werte geringer ist als die zuvor ausgelesene dann fail..
					if ($streuner < $usr['streuner']) {$fail = 1; $errlog[] = "Streuner"; $error_row = $error_row<0?1:$error_row; $fail_count++;}
					if ($menschen < $usr['menschen']) {$fail = 1; $errlog[] = "Menschen"; $error_row = $error_row<0?2:$error_row; $fail_count++;}
					if ($gespielte_missionen < $usr['gespielte_missionen']) {$fail = 1; $errlog[] = "Gespielte Missionen"; $error_row = $error_row<0?3:$error_row; $fail_count++;}
					if (($abgeschlossene_missonen < $usr['abgeschlossene_missonen'])||($abgeschlossene_missonen > ($usr['abgeschlossene_missonen']+(2000*$datediff)))) {$fail = 1; $errlog[] = "Abgeschlossene Missionen"; $error_row = $error_row<0?4:$error_row; $fail_count++;}

					if ($gefeuerte_schuesse < $usr['gefeuerte_schuesse']) {$fail = 1; $errlog[] = "Gefeuerte Schüsse"; $error_row = $error_row<0?5:$error_row; $fail_count++;}
					if ($haufen < $usr['haufen']||$haufen > ($usr['haufen']+(5000*$datediff))) {$fail = 1; $errlog[] = "Haufen"; $error_row = $error_row<0?6:$error_row; $fail_count++;}
					if ($heldenpower < $usr['heldenpower']||$heldenpower > ($usr['heldenpower']+5000)) {$fail = 1; $errlog[] = "Heldenpower"; $error_row = $error_row<0?7:$error_row; $fail_count++;}
					if ($waffenpower < $usr['waffenpower'] || $waffenpower > ($usr['waffenpower']+5000)) {$fail = 1; $errlog[] = "Waffenpower"; $error_row = $error_row<0?8:$error_row; $fail_count++;}
					if ($karten < $usr['karten']) {$fail = 1; $errlog[] = "Karten"; $error_row = $error_row<0?9:$error_row; $fail_count++;}
					if ($gerettete < $usr['gerettete']) {$fail = 1; $errlog[] = "Gerettete"; $error_row = $error_row<0?10:$error_row; $fail_count++;}

					if($fail!=1 || $i > 0) break;
					else{
					  $save_fail_count = $fail_count;
					  if($error_row > -1){
						  if(isset($arr[count($arr)-1])&&$arr[count($arr)-1]==0){
						  	for($h=count($arr)-1;$h>$error_row;$h--){
						  	  $arr[$h] = $arr[$h-1];
						  	}
						  	$arr[$error_row] = 0;
						  }
						  $errlog = array();
			$exp = $arr[0];
			$streuner = $arr[1];
			$menschen = $arr[2];
			$gespielte_missionen = $arr[3];
			$abgeschlossene_missonen = $arr[4];
			$gefeuerte_schuesse = $arr[5];
			$haufen = $arr[6];
			$heldenpower = $arr[7];
			$waffenpower = $arr[8];
			$karten = $arr[9];
			$gerettete = $arr[10];

					  }

					}
				}
			}
		}
		
		if (isset($duplicate)&&($duplicate===True)) {
			unlink($target_file);
			echo 'Duplikate gelöscht<br>';
			return;
		}
		if (!empty($errlog)) {
			$notizen = implode(",", $errlog);
		}
		else{
			$notizen = "";
		}


			
		#Werte in eine Datenbank schreiben
		$statement = $pdo->prepare("INSERT INTO ".$config->db_pre."stats(uid, name, date, exp, streuner, menschen, gespielte_missionen, abgeschlossene_missonen, gefeuerte_schuesse, haufen, heldenpower, waffenpower, karten, gerettete, notizen, fail)
			VALUES(:uid, :name, :date,:exp, :streuner, :menschen, :gespielte_missionen, :abgeschlossene_missonen, :gefeuerte_schuesse, :haufen, :heldenpower, :waffenpower, :karten, :gerettete, :notizen, :fail)");

// $fail  = 1;
		//$curr_datetime = date("Y-m-d H:i:s");
		$curr_datetime = date("Y-m-d H:i:s", filemtime($target_file));
		$statement->execute(array(
			"uid" => getuid($name),
			"name" => $name,
			"date" =>  $curr_datetime,
			"exp" => $exp,
			"streuner" => $streuner,
			"menschen" => $menschen,
			"gespielte_missionen" => $gespielte_missionen,	
			"abgeschlossene_missonen" => $abgeschlossene_missonen,
			"gefeuerte_schuesse" => $gefeuerte_schuesse,
			"haufen" => $haufen,
			"heldenpower" => $heldenpower,
			"waffenpower" => $waffenpower,
			"karten" => $karten,
			"gerettete" => $gerettete,
			"notizen" => $notizen,
			"fail" => $fail
		));
			
		if($statement) {     
			if($name == ""){$name = "Nicht ermittelbar";}
			echo $onlyfile . " - LID: " . $pdo->lastInsertId() . " - IGN: ". $name;
			echo ' verarbeitet.';

			#fehler provoziern zum test
			if ($fail == 1){
				echo ' <b>[FAIL]:</b> '.$notizen;
				rename($target_file,'../2ocr/fail/'.$pdo->lastInsertId().'.'.$fileext);
			}else{
				unlink($target_file);
			}
			echo '<br>';
		} else {
			echo 'Beim Datenbankeintrag ist ein Fehler aufgetreten<br>';
		}

	} #Ende Prüfung ob was im Return-Array von ocr-space steht
}

#Screenshots für die OCR vorbereiten
#Hier noch etwas nachjustieren - manches erkennt die api nicht, muss es der api recht machen...
function preparescreenshots() {
		$screens = glob("screens/*.{jpg,png}", GLOB_BRACE);
		foreach ($screens as $filename) {
		if (strlen(trim($filename)) > 0){
			$hasfiles = True;
			$rfilename = substr($filename, strrpos($filename, '/')+1, strlen($filename));
			echo $rfilename." konvertiert";

			// Get new dimensions
			list($width, $height, $type) = getimagesize($filename);
			$poz = 0.8;
					
			$b1 = $width*0.3;
			$h1 = 60; // Breite und Höhe des Auschnitts
			$c1 = $width*0.36;
			$p1 = $height*0.17;
			
			$b2 = $width*0.32;
			$h2 = 70; // Breite und Höhe des Auschnitts
			$c2 = $width*0.04;
			$p2 = $height*0.265;
			
			$b3 = $width*0.21;
			$h3 = 950; // Breite und Höhe des Auschnitts
			$c3 = $width*0.186;
			$p3 = $height*0.62; // Koordinaten, ab wo kopiert werden soll (erst X, dann Y).
			
			$new_width = $b1*$poz;
			$new_width2 = $b2*$poz;
			$new_width3 = $b3*$poz;
			$new_height = (($h1+$h2+$h3)*$poz)-100;
			
			// Resample
			$new = imagecreatetruecolor($new_width , $new_height);  // true color for best quality
			//2 is jpg, 3 is png
			if($type == 2){
				$image = imagecreatefromjpeg($filename);
			}
			elseif($type == 3){
				$image = imagecreatefrompng($filename);
			}

			#imagecopyresampled($new, $image, 0, 0, 0, 100,$new_width , $h1*$poz, $width, $h1);
			imagecopyresampled($new, $image, 0, 0, $c1, $p1, $new_width, $h1*$poz, $b1, $h1);
			imagecopyresampled($new, $image, 0, ($h1)*$poz, $c2, $p2, $new_width2, $h2*$poz, $b2, $h2);
			imagecopyresampled($new, $image, 0, ($h1+$h2)*$poz, $c3, $p3, $new_width3 , $h3*$poz, $b3, $h3);
			
			imagedestroy($image);
			#bild schärfen (evtl. erst danach - testen!)
			$sharpen = array(
				array(-1, -1,  -1),
				array(-1, 16, -1),
				array(-1, -1,  -1),
			);

			$divisor = array_sum(array_map('array_sum',  $sharpen));
			imageconvolution($new, $sharpen, $divisor, 0);
			#ende bild schärfen	


			
			// Output
			imagejpeg($new,"2ocr/".$rfilename,100); // Neues Bild speichern
			
			if ( strpos(strtolower($rfilename), 'screenshot') !== false ) {
				$str = strtolower($rfilename);
				$w = array('screenshot','-','_');
				$str = str_replace($w,'',$str);
				$str = substr($str, 0, 14);
				$str = strtotime($str);
				touch("2ocr/".$rfilename,$str);
			}

			imagedestroy($new);
			unlink($filename);
			echo " - Original entfernt. <br>";
		}
	}
	if ($hasfiles == True){
		echo '<br><div class="alert alert-success">
		  <strong>Fertig!</strong> Konvertierung abgeschossen.
		</div>';
	}
	else
	{
		echo '<br><div class="alert alert-warning">
		  <strong>Keine Dateien vorhanden!</strong> Bitte zuerst Bilder in das screens Verzechnis laden!
		</div>';
	}
}


function generatePassword ( $passwordlength = 8, 
                            $numNonAlpha = 0, 
                            $numNumberChars = 0, 
                            $useCapitalLetter = true ) { 
     
    $numberChars = '123456789'; 
    $specialChars = '!$%&?*-+@'; 
    $secureChars = 'abcdefghjkmnpqrstuvwxyz'; 
    $stack = ''; 
         
    // Stack für Password-Erzeugung füllen 
    $stack = $secureChars; 
     
    if ( $useCapitalLetter == true ) 
        $stack .= strtoupper ( $secureChars ); 
         
    $count = $passwordlength - $numNonAlpha - $numNumberChars; 
    $temp = str_shuffle ( $stack ); 
    $stack = substr ( $temp , 0 , $count ); 
     
    if ( $numNonAlpha > 0 ) { 
        $temp = str_shuffle ( $specialChars ); 
        $stack .= substr ( $temp , 0 , $numNonAlpha ); 
    } 
         
    if ( $numNumberChars > 0 ) { 
        $temp = str_shuffle ( $numberChars ); 
        $stack .= substr ( $temp , 0 , $numNumberChars ); 
    } 
             
         
    // Stack durchwürfeln 
    $stack = str_shuffle ( $stack ); 
         
    // Rückgabe des erzeugten Passwort 
    return $stack; 
     
} 


function user_exists($id){
	global $pdo;
	global $config;
	$statement = $pdo->prepare("SELECT ign FROM ".$config->db_pre."users WHERE id = :id");
	$result = $statement->execute(array('id' => $id));
	$dusr = $statement->fetch();
	if(!empty($dusr)){
		return true;
	}
}

function stat_exists($id){
	global $pdo;
	global $config;
	$statement = $pdo->prepare("SELECT id FROM ".$config->db_pre."stats WHERE id = :id");
	$result = $statement->execute(array('id' => $id));
	$stat = $statement->fetch();
	if(!empty($stat)){
		return true;
	}
}

function msgbox($msg){
		if (isset($msg)) {
	  switch ($_GET["msg"]) {
		case "updatesuccess":
			okmsg('Der Eintrag wurde aktualisiert!');
		break;
		case "updatefail":
			failmsg('Der Eintrag konnten nicht aktualisiert werden!');
		break;
		case "addsuccess":
			okmsg('Der Eintrag wurde hinzugefügt!');
		break;
	    case "addfail":
			failmsg('Der Eintrag konnte nicht hinzugefügt werden!');
		break;
	    case "deletesuccess":
			okmsg('Der Eintrag wurde entfernt!');
		break;
	    case "deletefail":
			failmsg('Der Eintrag konnte nicht entfernt werden!');
		break;
	  }
	}
}

function isdev(){
	if ($_SESSION['role'] == 99){
		return true;
	}
}

function isadminormod(){
	if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2 OR $_SESSION['role'] == 99){
		return true;
	}
}

function isadmin(){
	if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 99){
		return true;
	}
}
function isuser(){
	if ($_SESSION['role'] == 3){
		return true;
	}
}

function ismod(){
	if ($_SESSION['role'] == 2){
		return true;
	}
}

function failmsg($msg){
	echo '<div class="alert alert-danger"><span class = "fas fa-info-circle"></span> '.$msg.'</div><p> </p>';
}

function okmsg($msg){
	echo '<div class="alert alert-success"><span class = "fas fa-info-circle"></span> '.$msg.'</div><p> </p>';
}

function infomsg($msg){
	echo '<div class="alert alert-info"><span class = "fas fa-info-circle"></span> '.$msg.'</div><p> </p>';
}

function warnmsg($msg){
	echo '<div class="alert alert-warning"><span class = "fas fa-info-circle"></span> '.$msg.'</div><p> </p>';
}


function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

?>
