<?PHP
/*================================================================================
Image Functions
=================================================================================*/

/**
 * @return void
 * @param $img object Image
 * @param $dest object Destination Image
 * @param $dest_w int Width
 * @param $dest_h int Height
 * @desc Erstellt ein Thumbnail eine GD Bildes
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
 * @return int
 * @param $filename string Filename
 * @param $targetwidth int Zielbreite
 * @desc Gibt die Korrekte Breite (Seitenverhältnis) eines Bildes zurück
*/
function getthumbnailheight ($filename, $targetwidth) {
   $size = getimagesize($filename);
   $targetheight = $targetwidth * ($size[0] / $size[1]);
   return $targetheight;
}
         
/**
 * @return int
 * @param $filename string Filename
 * @param $targetheight int Zielhöhe
 * @desc Gibt die Korrekte Höhe (Seitenverhältnis) eines Bildes zurück
 */
function getthumbnailwidth ($filename, $targetheight) {
   $size = getimagesize($filename);
   $targetwidth = $targetheight * ($size[1] / $size[0]);
   return $targetwidth;
}

?>