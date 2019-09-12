<?php
/*
				header('Content-type: image/jpeg');
				$fi = '../2ocr/fail/853.jpg';
				$image = imagecreatefromjpeg($fi);
				imagejpeg($image);
				imagedestroy($image);

/**/
extract($_GET);
 if(isSet($f)&&$f!='')
{
  $fi = '../'.urldecode($f);
	// if(file_exists($fi)&&(stripos($fi, '.jpg')!==false||stripos($fi, '.png')!==false))
	{
		// echo '1';
	if(isSet($s)&&is_numeric($s)){
		// echo '2';
		if(isSet($h)&&is_numeric($h)){
		if(isSet($w)&&is_numeric($w)){
		// echo '1';
  		if(isSet($i)&&is_numeric($i)){
		// echo '1';
		  	$_w = 0; $_h = 0; $_t = 0;
				list($_w, $_h, $_t) = getimagesize($fi);
				// $h = ceil($_h/14*1.0);
		  	// $h = ceil(($s)/10);
		  	if($w==0) $w = $_w;
		  	$new = imagecreatetruecolor($w, $h);  // true color for best quality
				$image;
				if($_t == 2){
					$image = imagecreatefromjpeg($fi);
				}
				elseif($_t == 3){
					$image = imagecreatefrompng($fi);
				}
				#imagecopyresampled($new, $image, 0, 0, 0, 100,$new_width , $h1*$poz, $width, $h1);
				// imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
				imagecopyresampled($new, $image, 0, 0, 0, $_h-$s+($h*($i-1)), $w, $h, $w, $h);
				// imagecrop($image, ['x' => 0, 'y' => ($_h-$s+($h*($i-1))), 'width' => $_w, 'height' => $h]);
				// if($_t == 2){
				header('Content-type: image/jpeg');
				imagejpeg($new);
				/*}else
				if($_t == 3){}
				header('Content-type: image/png');
				imagepng($new);
				}*/
				
				imagedestroy($image);
				// die;				
				imagedestroy($new);
  		}
		}
	}
}
}

}

?>