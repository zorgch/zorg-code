<?php
/**
 * zorg Site Helper Functions
 * @package Zorg
 * @subpackage Utils
 */
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
 * @author IneX
 * @date 25.08.03
 * @version 2.0
 * @since 1.0 25.08.2003 function added
 * @since 2.0 09.08.2018 added timestamp validation, string for text, added time-check
 *
 * @param string $timestamp
 * @return string Formatted $timestamp or empty string ''
 */
function datename($timestamp)
{
	/** Leer */
	if($timestamp == 0) return '';

	/** Heute */
	if(date('d.m.y', time()) == date('d.m.y', $timestamp)) {
		return strtolower((date('s', $timestamp) == '00' && date('i', $timestamp) == '00' ? t('datetime-today') : date('H:i', $timestamp)));

	/** Gestern */
	} else if(date('d.m.y', time()-86400) == date('d.m.y', $timestamp)) {
		return strtolower((date('s', $timestamp) == '00' && date('i', $timestamp) == '00' ? t('datetime-yesterday') : t('datetime-yesterday').' '.date('H:i', $timestamp)));

	/** Diesen Monat */
	} else if (date('m.y', time()) == date('m.y', $timestamp)) {
		return (date('s', $timestamp) == '00' && date('i', $timestamp) == '00' ? strftime('%e. %B', $timestamp) : strftime('%e. %B %H:%M', $timestamp));

	/** Dieses Jahr */
	} else if(date('Y',time()) == date('Y', $timestamp)) {
		return (date('s', $timestamp) == '00' && date('i', $timestamp) == '00' ? strftime('%e. %B', $timestamp) : strftime('%e. %B %H:%M', $timestamp));

	/** Letztes Jahr und älter */
	} else {
		//return date('j.m.y', $timestamp); // "altes" Format
		return (date('s', $timestamp) == '00' && date('i', $timestamp) == '00' ? date('d.m.Y', $timestamp) : date('d.m.Y H:i', $timestamp));
	}
}

function timename($timestamp)
{
	/** Leer */
	if(empty($timestamp)) return '';

	try {
		/** Jetzt */
		$currTime = time();

		/** Vergangen oder in der Zukunft? */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Comparing timestamps %s vs %s', __FUNCTION__, __LINE__, $timestamp, $currTime));
		$prefix = ($timestamp >= $currTime ? 'in ' : 'vor ');
		$timeDiff = ($timestamp >= $currTime ? $timestamp - $currTime : $currTime - $timestamp);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps time difference: %s %d', __FUNCTION__, __LINE__, $prefix, $timeDiff));

		/** Zeitperioden */
		$timeLengths = array('s' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400, 'w' => 604800, 'mt' => 2592000, 'y' => 31536000);
	
		if ($timeDiff <= 10) { /** Gerade eben */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d seconds apart', __FUNCTION__, __LINE__, $timeDiff));
			return t('datetime-recently');

		} elseif ($timeDiff < $timeLengths['m']) { /** Sekunden */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d seconds apart', __FUNCTION__, __LINE__, $timeDiff));
			//$timeSuffix = (floor($timeDiff/$timeLengths['m']) > 1 ? 'n' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['m']) > 1 ? 'datetime-seconds' : 'datetime-second' ), 'global', $timeDiff) . $timeSuffix;

		} elseif ($timeDiff < $timeLengths['h']) { /** Minuten */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d minutes apart', __FUNCTION__, __LINE__, floor($timeDiff/$timeLengths['m'])));
			//$timeSuffix = (floor($timeDiff/$timeLengths['m']) > 1 ? 'n' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['m']) > 1 ? 'datetime-minutes' : 'datetime-minute'), 'global', floor($timeDiff/$timeLengths['m'])) . $timeSuffix;

		} elseif ($timeDiff < $timeLengths['d']) { /** Stunden */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d hours apart', __FUNCTION__, __LINE__, floor($timeDiff/$timeLengths['h'])));
			//$timeSuffix = (floor($timeDiff/$timeLengths['m']) > 1 ? 'n' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['m']) > 1 ? 'datetime-hours' : 'datetime-hour'), 'global', floor($timeDiff/$timeLengths['h'])) . $timeSuffix;

		} elseif ($timeDiff < $timeLengths['w']) { /** Tage */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d days apart', __FUNCTION__, __LINE__, floor($timeDiff/$timeLengths['d'])));
			//$timeSuffix = (floor($timeDiff/$timeLengths['m']) > 1 ? 'en' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['m']) > 1 ? 'datetime-days' : 'datetime-day'), 'global', floor($timeDiff/$timeLengths['d'])) . $timeSuffix;

		} elseif ($timeDiff < $timeLengths['mt']) { /** Wochen */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d weeks apart', __FUNCTION__, __LINE__, floor($timeDiff/$timeLengths['w'])));
			//$timeSuffix = (floor($timeDiff/$timeLengths['m']) > 1 ? 'n' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['m']) > 1 ? 'datetime-weeks' : 'datetime-week'), 'global', floor($timeDiff/$timeLengths['w'])) . $timeSuffix;

		} elseif ($timeDiff < $timeLengths['y']) { /** Monate */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d months apart', __FUNCTION__, __LINE__, floor($timeDiff/$timeLengths['mt'])));
			//$timeSuffix = (floor($timeDiff/$timeLengths['mt']) > 1 ? 'en' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['mt']) > 1 ? 'datetime-months' : 'datetime-month'), 'global', floor($timeDiff/$timeLengths['mt'])) . $timeSuffix;

		} elseif ($timeDiff >= $timeLengths['y']) { /** Jahre oder mehr */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Timestamps are %d years apart', __FUNCTION__, __LINE__, floor($timeDiff/$timeLengths['y'])));
			//$timeSuffix = (floor($timeDiff/$timeLengths['y']) > 1 ? 'en' : '' );
			return $prefix . t((floor($timeDiff/$timeLengths['y']) > 1 ? 'datetime-years' : 'datetime-year'), 'global', floor($timeDiff/$timeLengths['y'])) . $timeSuffix;
		}
	} catch (Exception $e) {
		error_log($e->getMessage());
	}
}

/**
 * Funktion um ein Datum-Zeit String in einen Timestamp umzuweandeln
 * @author IneX
 * @date 04.02.2018
 * @see main.inc.php DateTime will take default Timezone as in date_default_timezone_set()
 * @param $datetime Must be valid full Date-Time String, e.g. 2016-03-11 11:00:00
 * @return string
 */
function datetimeToTimestamp($datetime)
{
	$d = new DateTime($datetime);
	return $d->getTimestamp();
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
 * @TODO Move this Method to the Quotes-Class
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
 * @TODO Move this Method to the Quotes-Class
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


/** URL Funktionen */
/**
 * Get & return current Script's URL & Parameters
 * @FIXME Add 2nd Function-Parameter: $base64_encode=true - to enable/disable base64_encoding
 * @param boolean $preserve_query_string Whether or not to keep & return the QUERY_STRING with the URL, or not
 */
function getURL($preserve_query_string=true)
{
	return base64_encode(rawurldecode($_SERVER['PHP_SELF'].($preserve_query_string === true ? '?'.$_SERVER['QUERY_STRING'] : '')));
}

function glue_url($parsed)
{
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

function getChangedURL($newquerystring)
{
	return(
		str_replace('?&', '?', $_SERVER['PHP_SELF'].'?'.changeQueryString($_SERVER['QUERY_STRING'], $newquerystring))
	);
}

function changeURL($url, $querystringchanges)
{
	$urlarray = parse_url($url);
	$urlarray['query'] = changeQueryString($urlarray['query'], $querystringchanges);
	return glue_url($urlarray);
}

function changeQueryString($querystring, $changes)
{
	// der 2. Wert überschreibt den 1.
	parse_str($querystring.'&'.$changes, $querystringarray);

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

function url_params()
{
	$ret = '';
	foreach ($_GET as $key => $val) {
		$ret .= $key.'='.$val.'&';
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
		for ($i=0; $i<$indent; $i++) $ret .= '	';
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
 * Entfernt HTML-Tags aus einem String
 *
 * @author IneX
 * @date 16.03.2008
 * @version 2.0
 * @since 1.0 initial release
 * @since 2.0 changed preg_replace("@</?[^>]*>*@") => strip_tags()
 *
 * @link http://php.net/manual/de/function.strip-tags.php
 * @link https://www.reddit.com/r/PHP/comments/nj5t0/what_everyone_should_know_about_strip_tags/
 * @see Messagesystem::sendMessage()
 * @param string $html HTML-String input to strip tags from
 * @param string $allowable_tags Whitelist of HTML-Tags which should NOT be removed
 * @return string Returns clean $html as string
 */
function remove_html($html, $allowable_tags=NULL)
{
	//$s = preg_replace ("@</?[^>]*>*@", "", $html);
	$s = strip_tags($html, $allowable_tags);
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
 * @return string Returns escaped $string as string
 */
function escape_text($string) {
	$s = addslashes(stripslashes($string));
	return $s;
}


/**
 * Entferne in einem vom User eingegebenen String alle nicht sicheren Zeichen
 *
 * @author IneX
 * @date 24.04.2018
 * @see bugtracker.inc.php
 *
 * @param string	$string String Input which shall be sanitized
 * @param string	$allowable_tags Whitelist of HTML-Tags which should NOT be removed
 * @return string	Returns sanitized $string as string
 */
function sanitize_userinput($string, $allowable_tags=NULL) {
	$s = mysql_real_escape_string(remove_html($string, $allowable_tags));
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
 * @DEPRECATED
 * @TODO Funktion entfernen, wird via JavaScript erledigt
 * @link https://deviceatlas.com/blog/mobile-browser-user-agent-strings
 * @author IneX
 * @date 23.04.2009
 * @version 2.0
 * @since 1.0 23.04.2009 function added
 * @since 2.0 19.07.2018 Array foreach-loop replaced with faster array_filter-search, updated identifiers
 *
 * @see usersystem::usersystem()
 * @param string $userAgent
 * @return int|bool Gibt die numerische Position des ersten Vorkommens zurück (vergleichbar mit 'true') - oder false
 */
function isMobileClient($userAgent)
{
	/** Validate & format $userAgent param */
	if (empty($userAgent) || is_numeric($userAgent)) return false;
	$userAgent = strtolower($userAgent);
	
	/**
	* Liste von Mobile-Clients
	*
	* @var array
	*/
	static $_mobileClients = array(
								 'midp'
								,'240x320'
								,'blackberry'
								,'netfront'
								,'nokia'
								,'panasonic'
								,'portalmmm'
								,'sharp'
								,'sie-'
								,'sonyericsson'
								,'symbian'
								,'windows ce'
								,'benq'
								,'mda'
								,'mot-'
								,'opera mini'
								,'philips'
								,'pocket pc'
								,'sagem'
								,'samsung'
								,'sda'
								,'sgh-'
								,'vodafone'
								,'xda'
								,'iphone'
								,'android'
								,'iemobile'
								,'windows phone'
								,'mobile safari'
						);
	return array_filter($_mobileClients, function($match) use ($userAgent) {
		return ( strpos($userAgent, $match) !== false);
	});
}


/**
 * Array String-Search
 * Searches for a matching String in a given Array
 *
 * @author IneX
 * @date 13.09.2018
 * @version 1.0
 * @since 13.09.2018 function added
 *
 * @param string $searchFor
 * @param array $inArray A valid Array to $searchFor string, if multidimensional also provide $arrayCoulmn!
 * @param string $arrayColumn
 * @param boolean $caseSensitive If false, $searchFor will be compared using all lowercase
 * @return int|bool Gibt die numerische Position des ersten Vorkommens zurück (vergleichbar mit 'true') - oder false
 */
function findStringInArray($searchFor, $inArray, $arrayColumn=null, $caseSensitive=false)
{
	/** Validate & format passed parameters */
	if (empty($searchFor) || is_numeric($searchFor)) return false;
	if (!is_array($inArray) && !empty($inArray)) $inArray = [ $inArray ];
	if ($caseSensitive) $searchFor = strtolower($searchFor);

	/** Search through $inArray by $searchFor */
	If (empty($arrayColumn))
	{
		/** $searchFor in a regular Array */
		return array_filter($inArray, function($match) use ($searchFor) {
			return ( strpos($searchFor, $match) !== false);
		});

	} else {
		/** $searchFor in a multimensional Array $arrayColumn */
		//return array_search($searchFor, array_column($inArray, $arrayColumn));
		//return array_keys(array_combine(array_keys($inArray), array_column($inArray,$arrayColumn)), $searchFor);
		return array_search($searchFor, array_combine(array_keys($inArray), array_column($inArray, $arrayColumn)));
	}
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
	if (@file_get_contents($url,false,NULL,0,1)) return true;
	return false;
}


/**
 * Get Code information from Git
 * Usage: echo getGitVersion();
 * Result: MyApplication v1.2.3-dev.474a1d0 (2016-11-02 14:11:22)
 * @link https://stackoverflow.com/a/33986403/5750030
 *
 * @author IneX
 * @date 04.02.2018
 * @version 2.0
 * @since 1.0 04.02.2018 function added
 * @since 2.0 20.08.2018 fixed error when running from PHP CLI: "fatal: Not a git repository (or any of the parent directories): .git"
 * @see SITE_ROOT
 *
 * @return array|boolean Returns PHP-Array containing the current GIT-Version info, or false if exec() failed
 */
function getGitCodeVersion()
{
	try {
		static $codeVersion = array();

		$codeVersion['version'] = trim(exec('git -C '.SITE_ROOT.' describe --tags --abbrev=0'));
		$codeVersion['last_commit'] = trim(exec('git -C '.SITE_ROOT.' log --pretty="%h" -n1 HEAD'));
		$lastCommitDatetime = trim(exec('git -C '.SITE_ROOT.' log -n1 --pretty=%ci HEAD'));
		
		$codeVersion['last_update'] = datetimeToTimestamp($lastCommitDatetime);
		
		return $codeVersion;

	} catch (Exception $e) {
		error_log($e->getMessage());
		return false;
	}
}


/**
 * Wraps String with a specified HTML-Tag
 * e.g. 'text', 'b' => returns <b>text</b>
 *
 * @author IneX
 * @date 18.06.2018
 * @version 1.0
 * @since 1.0 initial release
 *
 * @param string $text String input to wrap into HTML-tag $htmlTag
 * @param string $htmlTag HTML-Tag to use for warpping $text inside. Use only "b", "pre", "code", etc.
 * @return string Returns $text as wrapped HTML-String
 */
function html_tag($text, $htmlTag)
{
	/** Validate the $text (not empty, null, or alike) */
	if ( empty($text) ) return false;
	/** Validate the $htmlTag (not whitespaces allowed) */
	if ( $htmlTag !== str_replace(' ','',$htmlTag) ) return false;

	/** If $text & $htmlTag are OK - wrap it to get a HTML-Tag as return */
	return sprintf('<%1$s>%2$s</%1$s>', $htmlTag, $text);
}


/**
 * HTTP file download using cURL
 * Starts a cURL instance to download a passed URL to the defined file path, if the URL status is 200 OK
 *
 * @author IneX
 * @date 17.07.2018
 * @version 1.0
 * @since 1.0 added function
 *
 * @param string $url String input containing a URL
 * @param string $save_as_file String input containing a valid local file path to save the $url to
 * @return bool Returns true/false depening on if a successful execution was possible, or not
 */
function cURLfetchUrl($url, $save_as_file)
{
	/** Validate the $url & $filepath (not empty, null, or alike) */
	if ( empty($url) || is_numeric($url) ) return false;
	if ( empty($save_as_file) || is_numeric($save_as_file) ) return false;

	/** Disable PHP timelimit, because this could take a while... */
	set_time_limit(0);

	try {
		/**
		 * Initialize cURL process for handling the HTTP request
		 *
		 * cURL request options:
		 *	 CURLOPT_HEADER	yes/no if to retrieve HTTP-Header with request
		 *   CURLOPT_BODY	yes/no if to retrieve Resource Body with request
		 *	 CURLOPT_FOLLOWLOCATION	yes/no if to follow 3xx HTTP-redirects
		 *		 only if Redirects enabled:
		 *			 CURLOPT_AUTOREFERER	yes/no if passing Referer header field to HTTP requests
		 *			 CURLOPT_MAXREDIRS	maximum amount of redirects
		 *	 CURLOPT_TIMEOUT	maximum request timeout (in seconds)
		 *	 CURLOPT_USERAGENT	Useragent to identify the request
		 *	 CURLOPT_FILE			return the resource as a file (needs an open fopen())
		 *	 CURLOPT_RETURNTRANSFER return the data instead of outputting it
		 *	 CURLOPT_VERBOSE yes/no if to print everything on screen (no!)
		 */
		$curl_request_options = [
									 CURLOPT_USERAGENT => 'Zorg/1.0 (+https://zorg.ch/)'
									,CURLOPT_TIMEOUT => 5
									,CURLOPT_FOLLOWLOCATION => true
									,CURLOPT_RETURNTRANSFER => true
								];

		/** Initialize & execute cURL-Request */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> curl_exec() START: %s', __FUNCTION__, __LINE__, $url));
		$curl_instance = curl_init($url);
		curl_setopt_array($curl_instance, $curl_request_options);
		$curl_data = curl_exec($curl_instance);
		$curl_done = curl_getinfo($curl_instance);

		/** cURL request successful */
		if ($curl_done['http_code'] == 200)
		{
			/** Open a new file handle */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> curl_getinfo(%d): %s', __FUNCTION__, __LINE__, $curl_done['http_code'], $curl_done['url']));
			if (file_put_contents($save_as_file, $curl_data) !== false) {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> file_put_contents() OK: %s', __FUNCTION__, __LINE__, $save_as_file));
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> file_put_contents() ERROR: %s', __FUNCTION__, __LINE__, $save_as_file));
			}
		}

		/** Close the $curl_instance */
		curl_close($curl_instance);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> curl_close(): DONE', __FUNCTION__, __LINE__));

		return true;

	} catch (Exception $e) {
		error_log($e->getMessage());
		return false;
	}
}


/**
 * GET request using cURL to retrieve JSON object
 * Starts a cURL instance to retrieve a JSON data object from the passed $url, and return it as a PHP array if the JSON response status is 200 OK
 *
 * @author IneX
 * @date 06.08.2018
 * @version 1.0
 * @since 1.0 added function
 *
 * @param string $url String input containing a REST API URL
 * @return array|bool Returns a JSON object converted to a PHP array containing the JSON data, or false, depening on if a successful execution was possible
 */
function cURLfetchJSON($url)
{
	/** Validate the $url (not empty, null, or alike) */
	if ( empty($url) || is_numeric($url) ) return false;

	/** Initialize cURL process for handling the HTTP request */
	$curl_request_options = [
								 CURLOPT_USERAGENT => 'Zorg/1.0 (+https://zorg.ch/)'
								,CURLOPT_TIMEOUT => 5
								,CURLOPT_FOLLOWLOCATION => true
								,CURLOPT_RETURNTRANSFER => true
								,CURLOPT_HTTPHEADER => ['Content-type: application/json']
							];

	/** Initialize & execute cURL-Request */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> curl_exec() START: %s', __FUNCTION__, __LINE__, $url));
	$curl_instance = curl_init($url);
	curl_setopt_array($curl_instance, $curl_request_options);
	$curl_data = curl_exec($curl_instance);
	$curl_done = curl_getinfo($curl_instance);

	/** cURL request successful */
	if ($curl_done['http_code'] == 200)
	{
		/** Retrieve & decode JSON object */
		$json_decoded_array = json_decode($curl_data, true);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $json_decoded: %s', __FUNCTION__, __LINE__, print_r($json_decoded_array, true)));
	}
	
	/** If cURL request is ERROR */
	else {
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> curl_getinfo() ERROR: %d', __FUNCTION__, __LINE__, $curl_done['http_code']));
		return false;
	}

	/** Close the $curl_instance */
	curl_close($curl_instance);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> curl_close(): DONE', __FUNCTION__, __LINE__));

	/** JSON response is OK */
	return $json_decoded_array;
}


/**
 * Test if a File exists on the Server
 *
 * @author IneX
 * @version 1.0
 * @date 06.08.2018
 *
 * @param string $filepath 	The filepath to validate
 * @return string|boolean	Returns the passed $filepath if it exists, or false if not found
 */ 
function fileExists($filepath)
{
	return (stream_resolve_include_path($filepath) !== false ? $filepath : false);
}


/**
 * Calculate a unique md5-Hash of a File or URL - or compare to another File/URL
 * Either the file only, or by adding it's last modification datetime (for comparing file changes)
 * Pass a second file, in order to do a comparison of the two
 *
 * @author IneX
 * @date 13.08.2018
 * @version 2.0
 * @since 1.0 08.08.2018 added function
 * @since 2.0 13.08.2018 added $filepath_to_compare & comaprison functionality, added file_exists() before filemtime()
 *
 * @param string $filepath 	The filepath to a file for creating the hash
 * @param boolean $use_last_modification_datetime	(Optional) Whether or not to md5-hash with $filepath AND filemtime(), default: false
 * @param string $filepath_to_compare 	(Optional) A 2nd filepath to a file for comparing md5-hash against $filepath
 * @return string|boolean	Returns the calculated md5-Hash or false if file doesn't exist - or, if $filepath_to_compare given, true/false depening if comparison matched
 */
function fileHash($filepath, $use_last_modification_datetime=false, $filepath_to_compare=NULL)
{
	/** Hash 1st $filepath (required) */
	if (md5_file($filepath) !== false)
	{
		$file_hash = md5_file($filepath);

		if ($use_last_modification_datetime)
		{
			/** filemtime() requires a LOCAL file (no URL) */
			if (fileExists($filepath) !== false)
			{
				$file_lastmodified = filemtime($filepath);
				$file_hash = md5($file_lastmodified.$file_hash);
			} else {
				error_log(sprintf('[WARN] <%s:%d> filemtime() requires a LOCAL file (no URL), given: %s', __FILE__, __LINE__, $filepath));
				return false;
			}
		}
	} else {
		//error_log(sprintf('[WARN] <%s:%d> %s Non-existent $filepath: %s', __FILE__, __LINE__, __FUNCTION__, $filepath));
		return false;
	}

	/** Hash 2nd $filepath_to_compare (optional) */
	if (!empty($filepath_to_compare) && $filepath_to_compare != null)
	{
		if (md5_file($filepath_to_compare) !== false)
		{
			$file_to_compare_hash = md5_file($filepath_to_compare);
	
			if ($use_last_modification_datetime)
			{
				/** filemtime() requires a LOCAL file (no URL) */
				if (fileExists($filepath_to_compare) !== false)
				{
					$file_to_compare_lastmodified = filemtime($filepath_to_compare);
					$file_to_compare_hash = md5($file_to_compare_lastmodified.$file_to_compare_hash);
				} else {
					error_log(sprintf('[WARN] <%s:%d> filemtime() requires a LOCAL file (no URL), given: %s', __FILE__, __LINE__, $filepath_to_compare));
					return false;
				}
			}

			/** Compare the two Hashes & return true on match (false will be handled later) */
			if (!empty($file_to_compare_hash) && $file_to_compare_hash != null && $file_to_compare_hash === $file_hash) return true;

		} else {
			//error_log(sprintf('[WARN] <%s:%d> %s Non-existent $filepath_to_compare: %s', __FILE__, __LINE__, __FUNCTION__, $filepath_to_compare));
			return false;
		}
	}

	/** Check if $filepath was hashed - and return it. In case $file_to_compare_hash was also hashed, return false (otherwise a matching Hash would have been true already) */
	return (!empty($file_hash) && $file_hash != null && empty($file_to_compare_hash) ? $file_hash : false);
}
