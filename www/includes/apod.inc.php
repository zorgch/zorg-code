<?php
/**
 * APOD & Spaceweather
 * 
 * Holt und speichert die Astronomy Pictures of the Day (APOD)
 * sowie das aktuelle Spaceweather.
 *
 * @author [z]biko
 * @date 01.01.2004
 * @version 1
 * @package Zorg
 * @subpackage APOD
 */
/** File includes */
require_once(SITE_ROOT.'/includes/mysql.inc.php');
require_once(SITE_ROOT.'/includes/forum.inc.php');

/** Pfad zum initialen Download des aktuellen APOD-Bildes */
define("APOD_TEMP_IMGPATH", SITE_ROOT."../data/temp/"); 

/** ID der APOD-Gallery in der Datenbank */
define("APOD_GALLERY_ID", 41);

/** Source-URL von wo die APOD-Bilder heruntergeladen werden */
define("APOD_SOURCE", "http://antwrp.gsfc.nasa.gov/apod/astropix.html");

/** Source-URL von wo die Daten für das Spaceweather abgefragt werden */
define("SPACEWEATHER_SOURCE", "http://www.spaceweather.com/");


/**
 * Astronomy Picture of the Day (APOD)
 * 
 * Holt und speichert das neus Astronomy Pic of the Day (APOD).
 * APOD Bild wird via Funktion createPic() nach /data/gallery/41/ kopiert!
 * (kann also aus dem APOD Temp img-Ordner gelöscht werden danach)
 *
 * @todo Wenn die Funktion createPic() erfolgreich ausgeführt wurde (= APOD Bild kopiert) soll das Original-Bild aus dem /data/temp/ Ordner gelöscht werden.
 */
function get_apod() {
	global $db, $MAX_PIC_SIZE;
	//$source = "http://antwrp.gsfc.nasa.gov/apod/ap041209.html";
	$file = @file(APOD_SOURCE);
	if($file) {
		$html = join("",$file);
		$html = strip_tags($html,"<img> <a> <b>");

		//Image file & BIG Image file
		$pattern = "((<(a|A)\s(HREF|href)=\"image/(.*)\")|(<(IMG|img)\s(SRC|src)=\"(.*)\"))";
		preg_match_all($pattern,$html,$out);
		$image_big = $url."image/".$out[4][0];
		foreach ($out[8] as $key => $val){
			if($val != "") {
				$img = $out[8][$key];
			}	
		}
		$img_url = $url.$img;
		
		//desc
		$pattern = "(<b>(.*)</b>)";
		preg_match_all($pattern,$html,$out);
		$image_desc = ltrim($out[1][0]);
		
		//explanation
		$image_expl = substr($html,strpos($html,"Explanation:"),strrpos($html,"Tomorrow's picture:")-strpos($html,"Explanation:"));
		//externe links bauen
		$image_expl = preg_replace("/\/(\n)/i","/",$image_expl);
		$image_expl = preg_replace("/\"(\n)/i","\"",$image_expl);
		$image_expl = preg_replace("/href=(\n)/i","href=",$image_expl);
		$image_expl = str_replace("<b>","",$image_expl);
		$image_expl = preg_replace("/href=\"([^http].*)\"/i","href=\"".$url."\$1\"",$image_expl);
		
		$fp = @fopen($img_url,"r");
		if($fp) {
			//fetch image
			while(!feof($fp)) {
				$image .= fread($fp,1024);
			}
			fclose($fp);
			
			//write image
			$img_src = APOD_TEMP_IMGPATH.date("Ymd").extension($img_url);
			$fp = fopen($img_src,"w");
			fwrite($fp,$image);
			fclose($fp);
			
			//gallery id
			//$id = $apod_gal_id;
			$picid = $db->insert("gallery_pics", array("album"=>APOD_GALLERY_ID, "extension"=>extension($img_src)), __FILE__, __LINE__);
			 
			 // create pic
			 createPic($img_src, picPath($id, $picid, extension($img_src)), $MAX_PIC_SIZE[picWidth], $MAX_PIC_SIZE[picHeight]);
			
			 // create thumbnail
			 createPic($img_src, tnPath($id, $picid, extension($img_src)), $MAX_PIC_SIZE[tnWidth], $MAX_PIC_SIZE[tnHeight]);
			 
			 $sql = "UPDATE gallery_pics set name = '".addslashes($image_desc)."' WHERE id = '$picid'";
			 $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			
			 $text = "<a href='$image_big'><b>$image_desc</b></a><br>
			 <br>".$image_expl."<br><br><a href='".$url."ap".date("ymd",time()-86400).".html'>Credit &amp; Copyright</a>";
			 
			 Comment::post($picid, 'i', 59, $text); 
		}
	}
}

/**
 * Aktuelleste APOD Bild-ID
 * 
 * Holt das aktuellste APOD Bild aus der Datenbank
 */
function get_apod_id() {
	global $db;
	
	$sql = 'SELECT * FROM gallery_pics WHERE album = '.APOD_GALLERY_ID.' ORDER by id DESC LIMIT 0,1';
	$result = $db->query($sql);
	$rs = $db->fetch($result);

	return $rs;
}
?>