<?php
/**
 * GO Board Funktionen
 *
 * ...
 * ...
 * ...
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package zorg\Games\Go
 */
/**
 * File Includes
 */
        include_once dirname(__FILE__).'/go_game.inc.php';

	/**
	 * Defines
	 */
  
//        define("LINEWIDTH", 2);


function draw_go_base ($size) {
        define("GOIMGPATH", PHP_IMAGES_DIR.'go/');
        define("LINE", imagecreatefrompng(GOIMGPATH."line.png"));
        define("DOT", imagecreatefrompng(GOIMGPATH."dot.png"));
        define("BLACKSTONE", imagecreatefrompng(GOIMGPATH."go_black.png"));
        define("WHITESTONE", imagecreatefrompng(GOIMGPATH."go_white.png"));
    
    
        $imgsize = ($size+1)*FIELDSIZE;
        $im = @ImageCreate ($imgsize,$imgsize);
//        if (!$im) return array("error"=>__LINE__);
    
        $bg = htmlcolor2array(HZ_BG_COLOR);
        $background_color = ImageColorAllocate ($im, $bg['r'], $bg['g'], $bg['b']);
    define("COLOR_TEXT", imagecolorallocate($im, 0,0,0));
    
//        imagesetbrush($im, LINE);
//        imagerectangle($im, 0, 0, $imgsize-1, $imgsize-1, COLOR_BORDER);
    
 //       draw_grid($im, $size);
    imagecopy($im, imagecreatefrompng($_SERVER['DOCUMENT_ROOT']."/images/go/go_black.png"), 0, 0, 0, 0, 20, 20);
//ImageString ($im, 3, 0,0, GOIMGPATH, COLOR_TEXT);
    
    //    $im=  
    return $im;
}

function draw_grid(&$im, $size){
    $offset = FIELDSIZE - ceil(LINEWIDTH / 2);
    imagesetbrush($im, LINE);
    for ($i = 0; $i < $size; $i++){
	imageline($im, $offset, $offset + $i*FIELDSIZE, $offset + ($size-1)*FIELDSIZE,
		  $offset + $i*FIELDSIZE, IMG_COLOR_BRUSHED);
	imageline($im, $offset + $i*FIELDSIZE, $offset, $offset + $i*FIELDSIZE,
		  $offset + ($size-1)*FIELDSIZE, IMG_COLOR_BRUSHED);
    }
}
	    
function draw_go_stone(&$im, $x, $y, $which){
    
    if ($which == 1) $stone = WHITESTONE;
    else if ($which == 2) $stone = BLACKSTONE;
    else return;
		
    $offset = FIELDSIZE / 2;
    imagecopy($im, $stone, $offset + $x * FIELDSIZE, $offset + $y * FIELDSIZE, 0, 0, imagesx($stone), imagesy($stone));
}
