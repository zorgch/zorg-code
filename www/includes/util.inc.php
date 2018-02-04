<?php
/**
* Define preferred encryption type for user password encryption
* @const CRYPT_SALT Sets the Salt encryption type to be used
* @see crypt_pw()
* @see exec_newpassword()
* @see UserManagement::login()
* @see usersystem::login()
* @see usersystem::new_pass()
* @see usersystem::create_newuser()
*/
if (!defined('CRYPT_SALT')) define('CRYPT_SALT', 'CRYPT_BLOWFISH');

/**
 * File includes
 * @include mysql.inc.php 		
 * @include activities.inc.php 	
 * @include strings.inc.php 	Strings die im Zorg Code benutzt werden
 */
include_once( __DIR__ .'/mysql.inc.php');
include_once( __DIR__ .'/activities.inc.php');
include_once( __DIR__ .'/strings.inc.php');

/**
* Funktion um ein UNIX_TIMESTAMP schön darzustellen.
* @author Milamber
* @date 25.08.03
*/
function datename ($timestamp) {

	// Leer
	if($timestamp == 0) return '';

	// Heute
	if(date("d.m.y", time()) == date("d.m.y", $timestamp)) {
		return date("H:i", $timestamp);

	// Gestern
	} else if(date("d.m.y", time()-86400) == date("d.m.y", $timestamp)) {
		return 'Gestern '.date("H:i", $timestamp);

	// Diesen Monat
	} else if (date("m.y", time()) == date("m.y", $timestamp)) {
		return date("j. M H:i", $timestamp);

	// Dieses Jahr
	} else if(date("Y",time()) == date("Y", $timestamp)) {
		return date("j. M H:i", $timestamp);

	// Letztes Jahr und älter
	} else {
		//return date("j.m.y", $timestamp); // "altes" Format
		return date("d. M Y H:i", $timestamp);
	}
}

function timename($timestamp) {

	if($timestamp == 0) return '';

	if($timestamp < 60) {
		return $timestamp.' sek.';
	} else if($timestamp < 60*60) {
		return floor($timestamp/60).' min.';
	} else if($timestamp < 60*60*24) {
		return floor($timestamp/(60*60)).' h';
	} else {
		return floor($timestamp/(60*60*24)).' tage';
	}

}

function emailusername($username) {
	$username = strtolower($username);
	$username = str_replace("ä", "ae", $username);
	$username = str_replace("ö", "oe", $username);
	$username = str_replace("ü", "ue", $username);
	$username = preg_replace("/([^[:alnum:]])/sU", "", $username);
	return $username;
}

/**
 * Passwort encryption
 *
 * Verschlüsselt ein Passwort
 *
 * @return string crypted Passwort
 * @param $password string Plaintext Passwort
 */
function crypt_pw($password) {
	return crypt($password, CRYPT_SALT);
}

/**
 * E-Mailadresse prüfen
 *
 * Überprüft eine E-Mail Adresse
 *
 * @return bool
 * @param $email string E-Mail
 */
function check_email($email) {
	if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}$", $email)) return TRUE;
	else return FALSE;

}

/**
* Gibt einen random Quote zurück
* @author keep3r
* @date 22.03.2004
*/
function quote(){
	global $db;

	$sql = "SELECT count(*) as anzahl FROM quotes";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);
	$total = $rs['anzahl'];

	mt_srand((double)microtime()*1000000);
    $rnd = mt_rand(1, $total);
	$sql = "SELECT * FROM quotes";
	$result = $db->query($sql);

	for ($i=0;$i<$rnd;$i++){
		$rs = $db->fetch($result);
	}
	return $rs['text'];
}

/**
* Setzt einmal am Tag einen Quote in die DB daily_quote
* @author keep3r
* @date 22.03.2004
*/
function set_daily_quote(){

	$date = date("Y-m-d");
	$sql = "SELECT * FROM daily_quote WHERE date = '$date'";
	$result = $db->query($sql);
	$rs = $db->fetch($result);

	if (!$rs){
		$quote = quote();
  		$sql = "INSERT INTO daily_quote(
  				date,
  	  			quote

	  			)VALUES(

	  			'$date',
	  			'$quote'
	  			)";
  		$db->query($sql,__FILE__, __LINE__);
  		return 1;
  	} else {
  		return 0;
  	}
}


/**
 * URL Funktionen
 */
function getURL() {
	return rawurldecode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
}

function glue_url($parsed) {
   if (! is_array($parsed)) return false;
       $url = $parsed['scheme'] ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
       $url .= $parsed['user'] ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
       $url .= $parsed['host'] ? $parsed['host'] : '';
       $url .= $parsed['port'] ? ':'.$parsed['port'] : '';
       $url .= $parsed['path'] ? $parsed['path'] : '';
       $url .= $parsed['query'] ? '?'.$parsed['query'] : '';
       $url .= $parsed['fragment'] ? '#'.$parsed['fragment'] : '';
  return $url;
}

function getChangedURL($newquerystring) {

	return(
		str_replace("?&", "?", $_SERVER['PHP_SELF']
		.'?'
		.changeQueryString($_SERVER['QUERY_STRING'], $newquerystring))
	);
}

function changeURL($url, $querystringchanges) {
	$urlarray = parse_url($url);

	$urlarray['query'] = changeQueryString($urlarray['query'], $querystringchanges);

	return glue_url($urlarray);
}

function changeQueryString($querystring, $changes) {

	// der 2. Wert überschreibt den 1.
	parse_str($querystring."&".$changes, $querystringarray);

	foreach ($querystringarray as $key => $value) {
		if(is_array($value)) {
			foreach ($value as $key2 => $value2) {
				if($value2 != '')	$str .= '&'.$key.'[]='.$value2;
			}
		} else {
			if($value != '') $str .= '&'.$key.'='.$value;
		}
	}

	return ltrim($str, '&');
}

function url_params () {
	$ret = "";
	foreach ($_GET as $key => $val) {
		$ret .= "$key=$val&";
	}
	return substr($ret, 0, -1);
}


/**
 * Array auf 2d überprüfen
 * 
 * \$arr muss 2d sein, \$sortcrit enthält in 2d-array die sortierkriterien.
 * \$sortcrit[0] ist das erste kriterium. \$sortcrit[x][0]=row, \$sortcrit[x][1]=Reihenfolge, \$sortcrit[x][2]=Sortiertypen
 * 
 * @return Array
 * @param Array $arr
 * @param Array $sortcrit
 */
function array2d_sort (&$arr, $sortcrit) {
	if (sizeof($arr) == 0) return $arr;
	if (!is_array($sortcrit)) user_error("Invalid Parameter \$sortcrit for array2d_sort", E_USER_ERROR);

	$sortarr = array();
	$fields = array();
	foreach ($arr[0] as $key => $val) {
		if (!is_numeric($key)) {
			$sortarr[$key] = array();
			$fields[$key] = 1;
		}
	}

	foreach ($arr as $it) {
		foreach ($it as $key => $val) {
			$sortarr[$key][] = $val;
		}
	}

	$exec = "\$cmdres = array_multisort (";
	foreach ($sortcrit as $it) {
		if (!is_array($it) && sizeof($it) < 1) user_error(t('array2d_sort-invalid-parameter', 'util', $sortcrit), E_USER_ERROR);

		if (!isset($it[1])) $it[1] = SORT_ASC;
		if (!isset($it[2])) $it[2] = SORT_REGULAR;
		$exec .= "\$sortarr['$it[0]'], $it[1], $it[2], ";
		unset($fields[$it[0]]);
	}
	foreach ($fields as $key => $val) {
		$exec .= "\$sortarr['$key'], ";
	}
	$exec = substr($exec, 0, -2);
	$exec .= ");";

	$cmdres = false;
	eval($exec);

	$ret = array();
	foreach ($sortarr as $key => $values) {
		for ($i=0; $i<sizeof($values); $i++) {
			if (!isset($ret[$i])) $ret[$i] = array();
			$ret[$i][$key] = $values[$i];
		}
	}

	$arr = $ret;

	return $cmdres;

}

function htmlcolor2array ($color) {
	if (substr($color, 0, 1) == '#') $color = substr($color, 1);
	if (strlen($color) != 6) {
		user_error(t('htmlcolor2array-invalid-parameter', 'util', $color), E_USER_WARNING);
		return array('r'=>0, 'g'=>0, 'b'=>0);
	}

	return array(
		'r' => hexdec(strtolower(substr($color, 0, 2))),
		'g' => hexdec(strtolower(substr($color, 2, 2))),
		'b' => hexdec(strtolower(substr($color, 4, 2)))
	);
}

function maxwordlength($text, $max) {
	$words = explode(' ', $text);
   foreach($words as $key => $word)
   {
       $length = strlen($word);
       if($length > $max)
           $word = chunk_split($word, floor($length/ceil($length/$max)), ' ');
       $words[$key] = $word;
   }
   return implode(' ', $words);
}


/**
* Smarty Klammern überprüfen
* 
* Prüft den \$text auf Fehler in der Klammernsetzung von smarty-tags
* 
* @return bool
* @param string $text
* @param string &$error
*/
function smarty_brackets_ok ($text, &$error) {
	$open = false;
	$last_open_tag = 0;

	$text = preg_replace("/\{\*.*\*\}/", '', $text);
	$text = preg_replace("/\{\s*literal\s*\}.*{\s*\/\s*literal\s*\}/", '', $text);

	for ($i=0; $i<strlen($text); $i++) {
		if ($text[$i] == '{') {
			if ($open) break;
			$open = true;
			$last_open_tag = $i;
		}elseif ($text[$i] == '}') {
			if (!$open) break;
			$open = false;
		}
	}

	if ($i != strlen($text) || $open) {
		$error = t('smarty_brackets_ok-invalid-brackets', 'util', substr($text, $last_open_tag, 50) );
		return false;
	}else{
		return true;
	}
}

function print_array ($arr, $indent=0) {
	if (!is_array($arr)) user_error( t('smarty_brackets_ok-invalid-argument', 'util', $arr ), E_USER_ERROR);

	$ret = '';

	if (!$indent) $ret .= '<div align="left"><xmp>';
	foreach ($arr as $key => $val) {
		for ($i=0; $i<$indent; $i++) $ret .= '   ';
		if (is_array($val)) {
			$ret .= "$key => Array: \n";
			$ret .= print_array($val, $indent+1);
		}else{
			$ret .= "$key => $val \n";
		}
	}
	if (!$indent) $ret .= '</xmp></div>';

	return $ret;
}

function text_width ($text, $width, $delimiter='') {
	if (strlen($text) == $width) return $text;
	if (strlen($text) > $width) return substr($text, 0, $width).$delimiter;
	else{
		for ($i=strlen($text); $i<$width; $i++) {
			$text .= ' ';
		}
		return $text;
	}
}


/**
* Funktion entfernt alle HTML-Tags aus einem String
*
* @author IneX
* @date 16.03.2008
*
* @return String
* @param $html
*/
function remove_html($html) {
   $s = preg_replace ("@</?[^>]*>*@", "", $html);
   return $s;
}


/**
 * Escape alle nicht sicheren Zeichen eines Strings
 *
 * @author IneX
 * @date 27.12.2017
 * @see comment_new.php
 * @see comment_edit.php
 * @see Comment:post()
 *
 * @param $string String Input which shall be escaped
 * @return string
 */
function escape_text($string) {
   $s = addslashes(stripslashes($string));
   return $s;
}


/**
* Funktion liefert den Zeitunterschied zur GMT basis
*
* @author IneX
* @verison 1.0
* @date 16.03.2008
*
* @return String
* @param $date
*/
function gmt_diff($date) {
	$diff = ($date - date('Z', $date)) / 3600;
	
	if ($diff < 0) {
			$diff2gmt = $diff;
	} else {
			$diff2gmt = '+' . $diff;
	}
   
	return $diff2gmt;
}



/**
 * Funktion prüft, ob der Client ein Mobile-Client ist (iPhone, BB, etc.)
 *
 * @deprecated
 * @todo Funktion entfernen, wird via JavaScript erledigt
 * @author IneX
 * @version 1.0
 * @date 23.04.2009
 * @see usersystem::usersystem()
 *
 * @param string $userAgent
 * @return string Enthält den Namen des mobilen User Agents oder nichts
 */
function isMobileClient($userAgent)
{
	
	/**
	* Liste von Mobile-Clients
	*
	* @var array
	*/
	$_mobileClients = array(
									"midp",
									"240x320",
									"blackberry",
									"netfront",
									"nokia",
									"panasonic",
									"portalmmm",
									"sharp",
									"sie-",
									"sonyericsson",
									"symbian",
									"windows ce",
									"benq",
									"mda",
									"mot-",
									"opera mini",
									"philips",
									"pocket pc",
									"sagem",
									"samsung",
									"sda",
									"sgh-",
									"vodafone",
									"xda",
									"iphone",
									"android"
								);
	
	$userAgent = strtolower($userAgent);
	
	foreach($_mobileClients as $mobileClient) {
	//foreach($_mobileClients as $mobileClient) {
		if (strstr($userAgent, $mobileClient)) {
			//return true
			return $mobileClient;
		}
	}
	return '';
}


/**
 * Test if a URL returns status code 200 OK
 *
 * @author IneX
 * @version 1.0
 * @date 21.01.2017
 * @link https://stackoverflow.com/a/39811033/5750030
 *
 * @param string $url 	The URL to validate
 * @return boolean		Returns true or false indicating the validity of the given URL
 */ 
function urlExists($url)
{
	if (@file_get_contents($url,false,NULL,0,1))
    {
        return true;
    }
    return false;
}
