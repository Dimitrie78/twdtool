<?php

class prepimage {
	
	private $rfile	= '';
	private $filename = '';
	
	private $width	= 0;
	private $height	= 0;
	private $type	= 2;
	
	private $poz 	= 1.5; //0.9;
	private $poz_ep = 1.6;
	private $vars	= array();
	private $out 	= false;
	
	public function __construct(array $vars) {
		$this->vars = $vars;
	}
		
	public function run($filename='') {
		if (strlen(trim($filename)) > 0){
			list($this->width, $this->height, $this->type) = getimagesize($filename);
			$this->rfile = substr($filename, strrpos($filename, '/')+1, strlen($filename));
			$this->filename = $filename;
			$this->render();
		}
	}
	
	public function set(bool $set) {
		$this->out = $set;
	}
	
	private function render() {
		$poz = $this->poz;
		$poz_ep = $this->poz_ep;
		
		// Name
		$b1 = $this->vars['playerW'];
		$h1 = $this->vars['playerH']; // Breite und Höhe des Auschnitts
		$c1 = $this->vars['playerX'];
		$p1 = $this->vars['playerY'];
		
		// EP
		$b2 = $this->vars['epW'];
		$h2 = $this->vars['epH'];
		$c2 = $this->vars['epX'];
		$p2 = $this->vars['epY'];
		
		// Werte
		$b3 = $this->vars['werteW'];
		$h3 = $this->vars['werteH'];
		$c3 = $this->vars['werteX'];
		$p3 = $this->vars['werteY'];
		
		$new_width = $b1*$poz;
		$new_width2 = $b2*$poz*$poz_ep;
		$new_width3 = $b3*$poz;
		$new_height = (($h1+$h3)*$poz+($h2*$poz*$poz_ep));
		
		$new_width_ges = max($new_width, $new_width2, $new_width3);
		// Resample
		$new = imagecreatetruecolor($new_width_ges , $new_height);  // true color for best quality
		//2 is jpg, 3 is png
		if($this->type == 2){
			$image = imagecreatefromjpeg($this->filename);
		}
		elseif($this->type == 3){
			$image = imagecreatefrompng($this->filename);
		}

		#imagecopyresampled($new, $image, 0, 0, 0, 100,$new_width , $h1*$poz, $width, $h1);
		imagecopyresampled($new, $image, 0, 0, $c1, $p1, $new_width, $h1*$poz, $b1, $h1);
		imagecopyresampled($new, $image, 0, ($h1)*$poz, $c2, $p2, $new_width2, $h2*$poz*$poz_ep, $b2, $h2);
		imagecopyresampled($new, $image, 0, ($h1*$poz+$h2*$poz*$poz_ep), $c3, $p3, $new_width3 , $h3*$poz, $b3, $h3);
		
		imagedestroy($image);
		
		#bild schärfen (evtl. erst danach - testen!)
		$this->sharpen($new);
		
		// Output
		if($this->out){
			imagejpeg($new, null, 100);
		} else {
			imagejpeg($new,"2ocr/".$this->rfile,100); // Neues Bild speichern
		}
		imagedestroy($new);
		
		$this->filetimebyname();
	}
	
	private function sharpen($new) {
		$sharpen = array(
			array(-1, -1,  -1),
			array(-1, 16, -1),
			array(-1, -1,  -1),
		);

		$divisor = array_sum(array_map('array_sum',  $sharpen));
		imageconvolution($new, $sharpen, $divisor, 0);
	}
	
	private function filetimebyname() {
		if ( strpos(strtolower($this->rfile), 'screenshot') !== false ) {
			$str = strtolower($this->rfile);
			$w = array('screenshot','-','_',$_SESSION['userid']);
			$str = str_replace($w,'',$str);
			$str = substr($str, 0, 14);
			if (($str = strtotime($str)) !== false) {
        touch("2ocr/".$this->rfile,$str);
      }
		}
	}
}

?>