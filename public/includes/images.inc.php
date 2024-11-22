<?php
/*================================================================================
Image Functions
=================================================================================*/

/**
 * Erstellt ein Thumbnail eine GD Bildes
 *
 * @return void
 * @param object $img object Image
 * @param object $dest object Destination Image
 * @param int $dest_w int Width
 * @param int $dest_h int Height
*/
function thumbnail($img,$dest,$dest_w,$dest_h) {

	$img_src = imagecreatefromjpeg($img);
	$img_dest = imagecreate($dest_h,$dest_w);
	imagecopyresized($img_dest,$img_src,0,0,0,0,$dest_h,$dest_w,imagesx($img_src),imagesy($img_src));

	imagejpeg($img_dest,$dest,100);
	imagedestroy($img_dest);
}

/*================================================================================
MISC
=================================================================================*/

/**
 * Gibt die Korrekte Breite (Seitenverhältnis) eines Bildes zurück
 *
 * @return int
 * @param $string filename string Filename
 * @param int $targetwidth int Zielbreite
*/
function getthumbnailheight ($filename, $targetwidth) {
   $size = getimagesize($filename);
   $targetheight = $targetwidth * ($size[0] / $size[1]);
   return $targetheight;
}

/**
 * Gibt die Korrekte Höhe (Seitenverhältnis) eines Bildes zurück
 *
 * @return int
 * @param string $filename string Filename
 * @param int $targetheight int Zielhöhe
 */
function getthumbnailwidth ($filename, $targetheight) {
   $size = getimagesize($filename);
   $targetwidth = $targetheight * ($size[1] / $size[0]);
   return $targetwidth;
}
