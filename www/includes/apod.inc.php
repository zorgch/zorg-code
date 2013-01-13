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
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/gallery.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');

/** Pfad zum initialen Download des aktuellen APOD-Bildes */
define("APOD_TEMP_IMGPATH",$_SERVER['DOCUMENT_ROOT']."../data/temp/"); 

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

function get_spaceweather() {
	global $db;
	//$source = "http://www.spaceweather.com/";
	$file = @file(SPACEWEATHER_SOURCE);
	if($file) {
		$html_source = join("",$file);
		
		//haupt chind
		$html = str_replace("\n","",(strip_tags($html_source)));
		$html = preg_replace("/\s+/i"," ",$html);
		
		//fuer PHAs
		$html_table = str_replace("\n","",(strip_tags($html_source,"<table> <th> <tr> <td>")));
		$html_table = preg_replace("/\s+/i"," ",$html_table);
		
		//Solar Wind
		$pattern = "(Solar\sWind\sspeed:\s(\d+\.\d+)\skm\/s\sdensity:\s(\d+\.\d+)\sprotons\/cm3)";
		preg_match_all($pattern,$html,$out);
	
		$space['solarwind_speed'] = $out[1][0];
		$space['solarwind_density'] = $out[2][0];
	
		//Solar Flares
		$pattern = "(X-ray\sSolar\sFlares\s6-hr\smax:\s(\w\d)\s(\d+)\sUT\s(.....)\s24-hr:\s(\w\d)\s(\d+)\sUT\s(.....)\sexplanation)";
		preg_match_all($pattern,$html,$out);
		
		$space['solarflares_6hr_typ'] = $out[1][0];
		$space['solarflares_6hr_time'] = $out[2][0];
		$space['solarflares_6hr_date'] = $out[3][0];
		
		$space['solarflares_24hr_typ'] = $out[4][0];
		$space['solarflares_24hr_time'] = $out[5][0];
		$space['solarflares_24hr_date'] = $out[6][0];
		
		//Sunspot Number
		$pattern = "(Sunspot\sNumber:\s(\d+))";
		preg_match_all($pattern,$html,$out);
		
		$space['sunspot_number'] = $out[1][0];
		
		//Magnetfeld
		$pattern = "(Interplanetary\sMag\.\sField\sBtotal:\s(\d+\.\d+)\snT\sBz:\s(\d+\.\d+)\snT\s(.....)\sexplanation)";
		preg_match_all($pattern,$html,$out);
	
		$space['magnetfield_btotal'] = $out[1][0];
		$space['magnet_bz_value'] = $out[2][0];
		$space['magnet_z_unit'] = $out[3][0];
		
		//solarflars
		$pattern = "(FLARE\s0-24\shr\s24-48\shr\sCLASS\s(\w)\s(\d+)%\s(\d+)%\sCLASS\s(\w)\s(\d+)%\s(\w+)%\s)";
		preg_match_all($pattern,$html,$out);
		
		$space['solarflares_percent_24hr_'.$out[1][0].'_percent'] = $out[2][0];
		$space['solarflares_percent_48hr_'.$out[1][0].'_percent'] = $out[3][0];
		$space['solarflares_percent_24hr_'.$out[4][0].'_percent'] = $out[5][0];
		$space['solarflares_percent_48hr_'.$out[4][0].'_percent'] = $out[6][0];
		
		//magnetsturm mid
		$pattern = "(Mid-latitudes\s0-24\shr\s24-48\shr\sACTIVE\s(\d+)%\s(\d+)%\sMINOR\s(\d+)%\s(\d+)%\sSEVERE\s(\d+)%\s(\d+)%\sHigh\slatitudes)";
		preg_match_all($pattern,$html,$out);
		
		$space['magstorm_mid_active_24hr'] = $out[1][0];
		$space['magstorm_mid_active_48hr'] = $out[2][0];
		$space['magstorm_mid_minor_24hr'] = $out[3][0];
		$space['magstorm_mid_minor_48hr'] = $out[4][0];
		$space['magstorm_mid_severe_24hr'] = $out[5][0];
		$space['magstorm_mid_severe_48hr'] = $out[6][0];
	
		//magnetsturm max
		$pattern = "(High\slatitudes\s0-24\shr\s24-48\shr\sACTIVE\s(\d+)%\s(\d+)%\sMINOR\s(\d+)%\s(\d+)%\sSEVERE\s(\d+)%\s(\d+)%\s)";
		preg_match_all($pattern,$html,$out);
		
		$space['magstorm_high_active_24hr'] = $out[1][0];
		$space['magstorm_high_active_48hr'] = $out[2][0];
		$space['magstorm_high_minor_24hr'] = $out[3][0];
		$space['magstorm_high_minor_48hr'] = $out[4][0];
		$space['magstorm_high_severe_24hr'] = $out[5][0];
		$space['magstorm_high_severe_48hr'] = $out[6][0];
		
		//PHAs today
		$pattern = "(\sthere\swere\s(\d+)\sknown\sPotentially\sHazardous\sAsteroids\s)";
		preg_match_all($pattern,$html,$out);
		
		$space['PHA'] = $out[1][0];
		
		//PHAs im detail
		$PHAs = substr($html_table,strpos($html_table,"Earth-asteroid encounters <table"));
		$PHAs = substr($PHAs,0,strpos($PHAs,"</table>"));
	
		$pa = @explode("</td>",$PHAs);
		$anz = @count($pa) - 2;
		if($anz) {
			$x = 0;
			$xs = array("asteroid", "datum", "distance", "mag");
			$inn = 0;
			for($i=4;$i<=$anz;$i++) {
				$pha[$inn][$xs[$x]] = str_replace("&nbsp;","",strip_tags($pa[$i]));
				$x++;
				if($x == 4) {
					$x = 0;
					$inn++;
				}	
			}
			
			//write space Phas
/*			foreach($pha as $key => $value) {
				$ps = array();
				foreach($value as $kk => $vv) {
					$ps[] = trim($vv);	
				}
				$sql = "
				REPLACE into spaceweather_pha
					(asteroid,datum,distance,mag)
				VALUES
					('".$ps[0]."','".date("Y-m-d",strtotime(str_replace(".","",$ps[1])))."','".$ps[2]."','".$ps[3]."')";
				$db->query($sql,__LINE__,__FILE__);
			}*/
		}
		
		//write spaceweather
		foreach($space as $key => $val) {
			$sql = "
			REPLACE into spaceweather
				(name, wert, datum)
			VALUES
				('$key','$val',now())";
			$db->query($sql,__LINE__,__FILE__);
		}	
	}
}

function spaceweather_ticker() {
	global $db;
	
	$add['solarwind_speed'][0] = "Solarwind";
	$add['solarwind_speed'][1] = "km/s";
	$add['solarwind_density'][0] = "Solarwind Dichte";
	$add['solarwind_density'][1] = "Protonen/cm<sup>3</sup>";
	$add['solarflares_6hr_typ'][0] = 0;
	$add['solarflares_6hr_time'][0] = 0;
	$add['solarflares_6hr_date'][0] = 0;
	$add['solarflares_24hr_typ'][0] = 0;
	$add['solarflares_24hr_time'][0] = 0;
	$add['solarflares_24hr_date'][0] = 0;
	$add['sunspot_number'][0] = "relative Anzahl Sonnenflecken";
	$add['magnetfield_btotal'][0] = "Magnetfeldstärke";
	$add['magnetfield_btotal'][1] = "nT";
	$add['magnet_bz_value'][0] = "Magnetfeldrichtungsstärke";
	$add['magnet_bz_value'][1] = "nT";
	$add['magnet_z_unit'][0] = "Magnetfeldrichtung";
	$add['solarflares_percent_24hr_M_percent'][0] = 0;
	$add['solarflares_percent_48hr_M_percent'][0] = 0;
	$add['solarflares_percent_24hr_X_percent'][0] = 0;
	$add['solarflares_percent_48hr_X_percent'][0] = 0;
	$add['magstorm_mid_active_24hr'][0] = 0;
	$add['magstorm_mid_active_48hr'][0] = 0;
	$add['magstorm_mid_minor_24hr'][0] = 0;
	$add['magstorm_mid_minor_48hr'][0] = 0;
	$add['magstorm_mid_severe_24hr'][0] = 0;
	$add['magstorm_mid_severe_48hr'][0] = 0;
	$add['magstorm_high_active_24hr'][0] = 0;
	$add['magstorm_high_active_48hr'][0] = 0;
	$add['magstorm_high_minor_24hr'][0] = 0;
	$add['magstorm_high_minor_48hr'][0] = 0;
	$add['magstorm_high_severe_24hr'][0] = 0;
	$add['magstorm_high_severe_48hr'][0] = 0;
	$add['PHA'][0] = "Potentiel gefährliche Asteroiden";
		
	$sql = "SELECT * FROM spaceweather";
	$result = $db->query($sql,__LINE__,__FILE__);
	while($rs = $db->fetch($result)) {
		if($rs['wert'] == "") {
			$rs['wert'] = "unbekannt";
		}
		if(strlen($add[$rs['name']][0]) > 2) {
			$sw[$rs['name']] = $add[$rs['name']][0].": ".$rs['wert']." ".$add[$rs['name']][1];
		}
	}
	
	shuffle($sw);
	for($i=0;$i<=2;$i++) {
		$spw .= $sw[$i]." | ";	
		
	}
	$spw .= "<a href='spaceweather.php'>more ".htmlentities(">>")."</a>";
	return $spw;
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