<?php
/**
 * class OCR
 * schrifterkennung aus bildern, welche mit php generiert wurden. 
 * requires: gdlib
 *
 * @author [z]biko
 * @date 11.10.2004
 */
class OCR {
	var $font = array();
	var $_font_height;
	var $_font_width;
	
	/*
	 * @params int $fonttype
	 * @desc Klassenkonstruktor - $fonttype als int-Kennung der gdlib
	 *
	 */
	function OCR ($fonttype, $fontset = array()) {
		$this->_font_height = imagefontheight($fonttype);
		$this->_font_width = imagefontwidth($fonttype);
		
		// create font-bitmaps
		$fontchars = $fontset;
		if (sizeof($fontchars) == 0) for ($c=32; $c<=255; $c++) $fontchars[] = chr($c);
				
		foreach ($fontchars as $c) {
			$im = imagecreate($this->_font_width, $this->_font_height);
			$white = imagecolorallocate($im, 255, 255, 255);
			$black = imagecolorallocate($im, 0, 0, 0);
			imagestring($im, $fonttype, 0, 0, $c, $black);
						
			$this->font[$c] = $this->_font_bitmap($im, imagecolorexact($im, 0, 0, 0));
		}
	}
	
	function scan_url ($url, $mime, $fontcolor, $x=0, $y=0, $wdt=0, $hgt=0, $tolerance=0) {
		$mime = explode('/', $mime);
		$mime = $mime[sizeof($mime)-1];
		
		switch ($mime) {
			case 'gif': $img = imagecreatefromgif($url); break;
			case 'jpeg': case 'jpg': $img = imagecreatefromjpeg($url); break;
			case 'png': $img = imagecreatefrompng($url); break;
			default: return;
		}
		
		return $this->scan_image($img, $fontcolor, $x, $y, $wdt, $hgt, $tolerance);
	}
	
	function scan_image ($img, $fontcolor, $x=0, $y=0, $wdt=0, $hgt=0, $tolerance=0) {
		if (!$hgt) $hgt = imagesy($img);
		if (!$wdt) $wdt = imagesx($img);
		
		// append border to image that its size is at least the font size
		$im = imagecreate($wdt + $this->_font_width, $hgt + $this->_font_height);
		imagecopy($im, $img, $this->_font_width/2, $this->_font_height/2, $x, $y, $wdt, $hgt);
		$wdt += $this->_font_width;
		$hgt += $this->_font_height;
		$x = 0;
		$y = 0;
		$img = $im;
				
		$fontcolor = imagecolorexact($img, $fontcolor[0], $fontcolor[1], $fontcolor[2]);
		unset($ret);
		
		// find first character
		for ($j=0; $j<=$wdt-$this->_font_width; $j++) {
			for ($i=0; $i<=$hgt-$this->_font_height; $i++) {
				if ($this->_scan($img, $fontcolor, $char, $j, $i) && $char!=' ') {
					$ret = $char;
					break;
				}
			}
			if (isset($ret)) break;
		}
		
		if ($ret) {
			for (; $i<=$hgt-$this->_font_height; $i+=$this->_font_height) {
				for ($j=$j+$this->_font_width; $j<=$wdt-$this->_font_width; $j+=$this->_font_width) {
					if ($this->_scan($img, $fontcolor, $char, $j, $i, $tolerance)) {
						$ret .= $char;
					}else{
						break;
					}
				}
			}
		}
		
		return trim($ret);
	}
	
	function _scan ($img, $fontcolor, &$char, $x=0, $y=0, $tolerance=0) {
		
		// creating bitmap
		$bitmap = $this->_font_bitmap($img, $fontcolor, $x, $y);
		
		foreach ($this->font as $c => $val) {
			if ($this->_bitmap_eq($val, $bitmap, $tolerance)) {
				$char = $c;
				return true;
			}
		}
		return false;
	}
	
	function _bitmap_eq ($bitmap1, $bitmap2, $tolerance = 0) {
		if (!is_array($bitmap1)) user_error("Invalid argument type for \$bitmap1", E_USER_ERROR);
		if (!is_array($bitmap2)) user_error("Invalid argument type for \$bitmap2", E_USER_ERROR);
		
		if (sizeof($bitmap1) != sizeof($bitmap2)) return false;
		
		if (!$tolerance) {
			foreach ($bitmap1 as $key => $val) {
				if ($val != $bitmap2[$key]) return false;
			}
			return true;		
		}else{
			if (!$this->_bitmap_eq($bitmap1, $bitmap2)) {
				$wrong = 0;
				foreach ($bitmap1 as $key => $val) {
					$b1 = decbin($val);
					$b2 = decbin($bitmap2[$key]);
					$dif = abs(strlen($b1) - strlen($b2));
					if (strlen($b1) > strlen($b2)) {
						for ($i=0; $i<$abs; $i++) $b2 = '0'.$b2;
					}elseif (strlen($b2) > strlen($b1)) {
						for ($i=0; $i<$abs; $i++) $b1 = '0'.$b1;
					}
					
					for ($i=0; $i<strlen($b1); $i++) {
						if ($b1[$i] != $b2[$i]) $wrong++;
						if ($wrong > $tolerance) return false;
					}
				}
			}
			return true;
		}
	}
	
	function _font_bitmap ($img, $fontcolor, $x=0, $y=0) {
		$bitmap = array();
		for ($i=$x; $i<$x+$this->_font_width; $i++) {
			$bin = '';
			
			for ($j=$y; $j<$y+$this->_font_height; $j++) {
				if (imagecolorat($img, $i, $j) == $fontcolor) $bin .= '1';
				else $bin .= '0';
			}
			$bitmap[] = bindec($bin);
		}
		return $bitmap;
	}
}
