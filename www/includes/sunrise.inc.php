<?php
/**
 * Sunrise & Sunset Times
 *
 * This code will give you the sunrise and sunset times for any
 * latitude and longitude in the world. You just need to supply
 * the latitude, longitude and difference from GMT.<br><br>
 * This script includes code translated from the perl module
 * Astro-SunTime-0.01.<br><br>
 * PHP code mattf@mail.com - please use this code in any way you wish
 * and if you want to, let me know how you are using it.<br><br>
 * Made into a class by <bbolli@ewanet.ch>, 2003-12-14
 * 
 * @author Matt <mattf@mail.com>, Bolli <bbolli@ewanet.ch>
 * @version $Id: sunrise.inc.php 208 2004-05-08 17:12:27Z bb $
 * @date 08.05.2004
 * @link http://www.zend.com/codex.php?id=135&single=1
 * @package Zorg
 * @subpackage Sunrise
 *
 * @global array $user
 * @global integer $suncalc
 * @global integer $cur_time
 * @global string $sun
 * @global string $sunset
 * @global string $sunrise
 * @global string $country
 * @global string $image_code
 * @global integer $layouttype
 */
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');

/**
 * Globals
 */
global $user, $suncalc, $cur_time, $sun, $sunset, $sunrise, $country, $image_code, $layouttype;


/**
 * Astro Sunrise Class
 * 
 * @author Bolli <bbolli@ewanet.ch>
 * @version $Id: sunrise.inc.php 208 2004-05-08 17:12:27Z bb $
 * @date 14.12.2003
 * @package Zorg
 * @subpackage Sunrise
 */
class Astro_Sunrise {

  /**
   * coordinates to calculate sunrise/sunset for
   * @var integer -90..+90; > 0 is north of the equator
   */
  var $lat = 47.0452;    // -90..+90; > 0 is north of the equator
  /**
   * coordinates to calculate sunrise/sunset for
   * @var integer -180..+180; > 0 is east of Greenwich
   */
  var $lon =  7.2715;    // -180..+180; > 0 is east of Greenwich

  /**
   * date
   * @var integer 4 digits, please
   */
  var $year;        // 4 digits, please
  /**
   * date
   * @var integer
   */
  var $month;
  /**
   * date
   * @var integer day of the month
   */
  var $mday;        // day of the month
  /**
   * date
   * @var integer timezone offset in hours, > 0 is east of GMT, < 0 is west
   */
  var $tz;        // timezone offset in hours, > 0 is east of GMT, < 0 is west
  /**
   * date
   * @var integer day of the year
   */
  var $yday;        // day of the year
  
  /**
   * Twilight values
   * @var array Array containing sunrise/sunset, civil twilight, nautical twilight, astronomical twilight
   */
  var $twilight = array(
    'effective' => -.0145439,    // sunrise/sunset
    'civil' => -.104528,    // civil twilight
    'nautical' => -.207912,    // nautical twilight
    'astronomical' => -.309017    // astronomical twilight
  );
  
  /**
   * Twilight calculation
   * @var integer radius used for twilight calculation
   */
  var $R;        // radius used for twilight calculation
  
  /**
   * time calculation
   * @var integer UNIX timestamp of last calculation
   */
  var $last_utc;    // UNIX timestamp of last calculation
  
  /**
   * Astro Sunrise Twilight setzen
   */
  function Astro_Sunrise() {
    $this->setTwilight('effective');
  }

  /**
   * Set coordinates
   * @param integer $lat
   * @param integer $lon
   * @see $lat, $lon
   */
  function setCoords($lat, $lon) {
    if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180)
      return null;
    $this->lat = $lat;
    $this->lon = $lon;
  }

  /**
   * Get coordinates
   * @see setCoords()
   */
  function getCoords() {
    return sprintf('%1.4f %s %1.4f %s',
      abs($this->lat), $this->lan < 0 ? 'S' : 'N',
      abs($this->lon), $this->lon < 0 ? 'W' : 'E'
    );
  }

  /**
   * Set date
   * @param integer $year
   * @param integer $month
   * @param integer $mday
   * @see $year, $month, $mday
   */
  function setDate($year, $month, $mday) {
    if ($year < 100)
      $year += 1900;
    if ($year < 1600 || !checkdate($month, $mday, $year))
      return null;

    $this->year = $year;
    $this->month = $month;
    $this->mday = $mday;

    $daysinmonth = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
    $this->yday = $daysinmonth[$month - 1] + $mday - 1;
    if ($month > 2 && ($year % 4) == 0 && (($year % 100) != 0 || ($year % 400) == 0))
      $this->yday++;
  }

  /**
   * Get date
   * @see setDate()
   */
  function getDate() {
    return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->mday);
  }

  /**
   * Set timestamp
   * @param integer $time
   * @see setDate()
   */
  function setTimestamp($time) {
    list($this->year, $this->month, $this->mday, $this->yday) =
      explode(':', date('Y:n:j:z', $time));
  }

  function setTimezone($tz=0) {
    if ($tz < -13 || $tz > 13)
      return null;
    $this->tz = $tz;
  }

  function getTimezone() {
    $tz = abs($this->tz);
    if ($tz == 0)
      return 'UTC';
    $hours = intval($tz);
    $mins = intval(($tz - $hours) * 60 + 0.5);
    return sprintf('%s%02d%02d', $this->tz < 0 ? '-' : '+', $hours, $mins);
  }

  function setTwilight($type) {
    if (!array_key_exists($type, $this->twilight))
      return null;
    $this->R = $this->twilight[$type];
  }


  function getSunrise() {
    return $this->calcSunrise(true);
  }

  function getSunset() {
    return $this->calcSunrise(false);
  }

  function getLastSwatchBeat() {
    $tm = ($this->last_utc + 3600) % 86400;    // MEZ
    return sprintf("@%03d", 1000 * $tm / 86400);
  }

  function calcSunrise($isRise) {

    // multiples of pi
    $A = 0.5 * M_PI;            // Quarter circle
    $B =       M_PI;            // Half circle
    $C = 1.5 * M_PI;            // 3/4 circle
    $D = 2   * M_PI;            // Full circle

    // convert coordinates and time zone to radians
    $E = $this->lat * $B / 180;
    $F = $this->lon * $B / 180;
    $G = $this->tz * $D / 24;

    $J = $isRise ? $A : $C;

    $K = $this->yday + ($J - $F) / $D;
    $L = $K * .017202 - .0574039;    // Solar Mean Anomoly
    $M = $L + .0334405 * sin($L);    // Solar True Longitude
    $M += 4.93289 + 3.49066E-4 * sin(2 * $L);

    // Quadrant Determination
    $M = norm($M, $D);

    if (($M / $A) - intval($M / $A) == 0)
      $M += 4.84814E-6;
    $P = sin($M) / cos($M);        // Solar Right Ascension
    $P = atan2(.91746 * $P, 1);

    // Quadrant Adjustment
    if ($M > $C)
      $P += $D;
    elseif ($M > $A)
      $P += $B;

    $Q = .39782 * sin($M);        // Solar Declination
    $Q /= sqrt(-$Q * $Q + 1);
    $Q = atan2($Q, 1);

    $S = $this->R - sin($Q) * sin($E);
    $S /= cos($Q) * cos($E);

    if (abs($S) > 1)
      return "(Mitternachtssonne/Dauernacht)";

    $S /= sqrt(-$S * $S + 1);
    $S = $A - atan2($S, 1);

    if ($isRise)
      $S = $D - $S;

    $T = $S + $P - 0.0172028 * $K - 1.73364;    // Local apparent time
    $U = $T - $F;            // Universal time
    $V = $U + $G;            // Wall clock time

    // Quadrant Determination
    $U = norm($U, $D);
    $V = norm($V, $D);

    // Scale from radians to hours
    $U *= 24 / $D;
    $V *= 24 / $D;

    // Universal time
    $hour = intval($U);
    $U    = ($U - $hour) * 60;
    $min  = intval($U);
    $U    = ($U - $min) * 60;
    $sec  = intval($U);
    $this->last_utc = gmmktime($hour, $min, $sec, $this->month, $this->mday, $this->year);

    // Local time
    $hour = intval($V);
    $min  = intval(($V - $hour) * 60 + 0.5);

    return sprintf('%02d:%02d', $hour, $min);

  }    // function calcSunrise

}    // class Astro_SunTime

function norm($a, $b) {        // normalize $a to be in [0, $b)
  while ($a < 0)
    $a += $b;
  while ($a >= $b)
    $a -= $b;
  return $a;
}    // function norm




//Position vom user bestimmen
$user_ip = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
//$user_ip = sprintf("%u", ip2long($user->last_ip)); --> tut noed! schickt alle in die USA :-(
$sql = "
SELECT 
	ci.country_code as code, 
	ci.country as country,
	ci.country_code2 as image_code,
	co.lat as lat,
	co.lon as lon
FROM country_ip ci
INNER JOIN country_coords co
	ON co.country_code = ci.country_code
WHERE
	ci.ip_from <= $user_ip and ci.ip_to >= $user_ip";
$result = $db->query($sql,__FILE__,__LINE__);
$rs = $db->fetch($result);
$lat = $rs['lat'];
$lon = $rs['lon'];
$country = (!empty($rs['country']) ? strtolower($rs['country']) : 'che'); // Wenn Land nicht ermittelt werden kann, Fallback zu CHE
$image_code = (!empty($rs['image_code']) ? strtoupper($rs['image_code']) : 'che'); // Wenn Land nicht ermittelt werden kann, Fallback zu CHE

$suncalc = new Astro_Sunrise();
$suncalc->setCoords($lat, $lon);
$suncalc->setTimezone(round($lon/15.0)+date("I"));
$suncalc->setTimestamp(time()+(3600*round($lon/15.0)+date("I")));
$sunrise = $suncalc->getSunrise();
$sunset = $suncalc->getSunset();

$cur_time = time()+(3600*round($lon/15.0)+date("I")) - 3600;

if($cur_time > strtotime($suncalc->getSunrise())) {
	$sun = "up";
	$layouttype = "day";
} 
if($cur_time > strtotime($suncalc->getSunset()) || $cur_time < strtotime($suncalc->getSunrise())) {
	$sun = "down";
	$layouttype = "night";
}
if(isset($_GET['tschau'])) {
	$_SESSION['tschau'] = $_GET['tschau'];
	header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?".session_name()."=".session_id());
}
if(isset($_SESSION) && $_SESSION['tschau'] == "day") {
	$sun = "up";
}
if(isset($_SESSION) && $_SESSION['tschau'] == "night") {
	$sun = "down";
}


?>
