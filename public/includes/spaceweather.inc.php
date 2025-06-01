<?php
/**
 * Spaceweather V2
 *
 * @package zorg\Spaceweather
 *
 * @TODO Work in Progress! Finish implementing new NASA APIs...
 */

/**
 * File includes
 * @include config.inc.php
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/**
 * Define various Asteroid related constants (for Spaceweather)
 * NeoWs (Near Earth Object Web Service) is a RESTful web service for near earth Asteroid information. Data-set: All the data is from the NASA JPL Asteroid team (http://neo.jpl.nasa.gov/).
 * @const SPACEWEATHER_SOURCE (DEPRECATED) Source-URL von wo die Daten für das Spaceweather abgefragt werden
 * @const NEO_API NASA Space Weather Database Of Notifications, Knowledge, Information (DONKI) API-URL von wo das aktuelle Spaceweather mit dem NASA_API_KEY geholt werden kann
 */
define('SPACEWEATHER_SOURCE', (isset($_ENV['SPACEWEATHER_SOURCE']) ? $_ENV['SPACEWEATHER_SOURCE'] : null));
define('NEO_API', (isset($_ENV['NASA_NEO_API']) ? $_ENV['NASA_NEO_API'] : null));

/**
 * Define various Spaceweather related constants
 * The Space Weather Database Of Notifications, Knowledge, Information (DONKI) is a comprehensive on-line tool for space weather forecasters, scientists, and the general space science community
 * @const DONKI_API_CME	Coronal Mass Ejection (CME)	https://api.nasa.gov/DONKI/CME?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_CMEA	Coronal Mass Ejection (CME) Analysis	https://api.nasa.gov/DONKI/CMEAnalysis?startDate=2016-09-01&endDate=2016-09-30&mostAccurateOnly=true&speed=500&halfAngle=30&catalog=ALL&api_key=DEMO_KEY
 * @const DONKI_API_GST	Geomagnetic Storm (GST)	https://api.nasa.gov/DONKI/GST?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_IPS	Interplanetary Shock (IPS)	https://api.nasa.gov/DONKI/IPS?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&location=LOCATION&catalog=CATALOG&api_key=DEMO_KEY
 * @const DONKI_API_FLR	Solar Flare (FLR)	https://api.nasa.gov/DONKI/FLR?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_SEP	Solar Energetic Particle (SEP)	https://api.nasa.gov/DONKI/SEP?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_MPC	Magnetopause Crossing (MPC)	https://api.nasa.gov/DONKI/MPC?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_RBE	Radiation Belt Enhancement (RBE)	https://api.nasa.gov/DONKI/RBE?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_HSS	Hight Speed Stream (HSS)	https://api.nasa.gov/DONKI/HSS?startDate=yyyy-MM-dd&endDate=yyyy-MM-dd&api_key=DEMO_KEY
 * @const DONKI_API_WSA	WSA+EnlilSimulation	https://api.nasa.gov/DONKI/WSAEnlilSimulations?startDate=2016-01-06&endDate=2016-01-06&api_key=DEMO_KEY
 * @const DONKI_API_Notifications	Notifications	https://api.nasa.gov/DONKI/notifications?startDate=2014-05-01&endDate=2014-05-08&type=all&api_key=DEMO_KEY
 */
define('DONKI_API_CME', (isset($_ENV['NASA_DONKI_API_CME']) ? $_ENV['NASA_DONKI_API_CME'] : null));
define('DONKI_API_CMEA', (isset($_ENV['NASA_DONKI_API_CMEA']) ? $_ENV['NASA_DONKI_API_CMEA'] : null));
define('DONKI_API_GST', (isset($_ENV['NASA_DONKI_API_GST']) ? $_ENV['NASA_DONKI_API_GST'] : null));
define('DONKI_API_IPS', (isset($_ENV['NASA_DONKI_API_IPS']) ? $_ENV['NASA_DONKI_API_IPS'] : null));
define('DONKI_API_FLR', (isset($_ENV['NASA_DONKI_API_FLR']) ? $_ENV['NASA_DONKI_API_FLR'] : null));
define('DONKI_API_SEP', (isset($_ENV['NASA_DONKI_API_SEP']) ? $_ENV['NASA_DONKI_API_SEP'] : null));
define('DONKI_API_MPC', (isset($_ENV['NASA_DONKI_API_MPC']) ? $_ENV['NASA_DONKI_API_MPC'] : null));
define('DONKI_API_RBE', (isset($_ENV['NASA_DONKI_API_RBE']) ? $_ENV['NASA_DONKI_API_RBE'] : null));
define('DONKI_API_HSS', (isset($_ENV['NASA_DONKI_API_HSS']) ? $_ENV['NASA_DONKI_API_HSS'] : null));
define('DONKI_API_WSA', (isset($_ENV['NASA_DONKI_API_WSA']) ? $_ENV['NASA_DONKI_API_WSA'] : null));
define('DONKI_API_Notifications', (isset($_ENV['NASA_DONKI_API_Notifications']) ? $_ENV['NASA_DONKI_API_Notifications'] : null));


function get_spaceweather()
{
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
		// @FIXME Breaks with 'Undefined index: solarflares_percent_48hr_[]_percent', 'file' => '/www/includes/spaceweather.inc.php', 'line' => 231
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
				$sql = 'REPLACE into spaceweather_pha (asteroid,datum,distance,mag) VALUES (?, ?, ?, ?)';
				$db->query($sql, __LINE__, __FILE__, __FUNCTION__, [$ps[0], date("Y-m-d",strtotime(str_replace(".","",$ps[1]))), $ps[2], $ps[3]]);
			}
		}

		//write spaceweather
		foreach($space as $key => $val) {
			$sql = 'REPLACE into spaceweather (name, wert, datum) VALUES (?, ?, ?)';
			$db->query($sql, __LINE__, __FILE__, __FUNCTION__, [$key, $val, timestamp(true)]);
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

	$sql = 'SELECT * FROM spaceweather';
	$query = $db->query($sql,__LINE__,__FILE__,__FUNCTION__);
	if ($db->num($query) > 0)
	{
		while($rs = $db->fetch($query)) {
			if(empty($rs['wert']) || $rs['wert'] === '') {
				$rs['wert'] = 'unbekannt';
			}
			if(isset($add[$rs['name']]) && !empty($add[$rs['name']][0]))
			{
				zorgDebugger::log()->debug('$rs[name]=%s exists, value: %s', [$add[$rs['name']][0], (isset($add[$rs['name']][1]) ? $add[$rs['name']][1] : 'null')]);
				$sw[] = [ 'type' => $add[$rs['name']][0], 'value' => $rs['wert'].(isset($add[$rs['name']][1]) ? " ".$add[$rs['name']][1] : '') ];
			}
		}

		if (is_array($sw)) {
			shuffle($sw); // Randomize Speachweather infos
		}
		return $sw;
	} else {
		return null;
	}
}
