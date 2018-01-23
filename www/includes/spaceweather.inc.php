<?php
require_once( __DIR__ .'/mysql.inc.php');

function get_spaceweather() {
	global $db;
	$source = "http://www.spaceweather.com/";
	$file = @file($source);
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
			foreach($pha as $key => $value) {
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
			}
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
	$add['magnetfield_btotal'][0] = "Magnetfeldst&auml;rke";
	$add['magnetfield_btotal'][1] = "nT";
	$add['magnet_bz_value'][0] = "Magnetfeldrichtungsst&auml;rke";
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
	$add['PHA'][0] = "Potenziell gef&auml;hrliche Asteroiden";
	
	try {
		$sql = "SELECT * FROM spaceweather";
		$result = $db->query($sql,__LINE__,__FILE__);
		while($rs = $db->fetch($result)) {
			if($rs['wert'] == "") {
				$rs['wert'] = "unbekannt";
			}
			if(strlen($add[$rs['name']][0]) > 2) {
				$sw[] = [ 'type' => $add[$rs['name']][0], 'value' => $rs['wert']." ".$add[$rs['name']][1] ];
			}
		}

		shuffle($sw); // Randomize Speachweather infos
		return $sw;
	}
	catch(Exception $e) {
		user_error($e->getMessage(), E_USER_NOTICE);
		return $e->getMessage();
	}
}
