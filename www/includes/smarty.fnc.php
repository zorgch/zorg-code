<?
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/apod.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/bugtracker.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/events.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/gallery.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/go_game.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/quotes.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/stockbroker.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/poll.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/stl.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/error.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/peter.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/rezepte.inc.php');
//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/chat.inc.php');


/**
 * Smarty Template Function Handler
 *
 * This Class contains various functions/function-mappings between
 * PHP and the Smarty Template engine. Further, it takes care
 * of properly registering/assigning the functions or methods
 * to Smarty as Template Functions, so they can be used within
 * Smarty templates using somethling like {custom_function}
 *
 * @ToDo This stuff here doesn't work in a Class Context yet...
 * @ToDo Probably needs to use $this-> context for everything - Functions, Constants, etc.?
 *
 * @author IneX
 * @date 03.01.2016
 * @version 1.0
 * @package Zorg
 * @subpackage Smarty
 */
//class SmartyZorgFunctions
//{

	/**
	 * PHP Functions, Arrays, Modifiers and Blocks for Smarty
	 *
     * These are all the PHP Functions and Arrays we want to register in Smarty
     * var Array format for Functions: '[PHP Function Name]' => '[Optional*: Smarty TPL Function Name]'
     * var Array format for Arrays: '[Smarty TPL Array Name]' => '$[PHP Array]'
     * *if Smarty TPL Function Name is empty/false, the PHP Function Name will be used!
     *
     * @ToDo Jeder Eintrag sollte wiederum ein Array sein, damit nebst Funktions-Name noch eine Kategorie & Beschreibung mitgegeben werden kann
     *
     * @author IneX
     * @date 03.01.2016
     * @version 1.0
     * @var array
     */
    $zorg_php_arrays = array(
    							'all_users' => array( // Format: [Smarty TPL Array Name] => [PHP Array]

			    								 'color' => $color
			    								,'event_newest' => Events::getEventNewest()
			    								,'nextevents' => Events::getNext()
			    								,'eventyears' => Events::getYears()
			    								,'rezept_newest' => Rezepte::getRezeptNewest()
			    								,'categories' => Rezepte::getCategories()
			    								,'num_errors' => $num_errors
			    								,'sun' => $sun //sunrise
			    								,'sunset' => $sunset //sunrise
			    								,'sunrise' => $sunrise //sunrise
			    								,'country' => $country //sunrise
			    								,'country_image' => "images/country/flags/$image_code.png" //sunrise
			    								,'request' => var_request() //system, associative array:  page = requested page / params = url parameter / url = page+params
			    								,'url' => getURL() //system
			    								,'self' => $_SERVER['PHP_SELF'] //system, Self = Aktuelle Seiten-URL
			    								,'user' => $user
			    								,'usertyp' => array('alle'=>USER_ALLE, 'user'=>USER_USER, 'member'=>USER_MEMBER, 'special'=>USER_SPECIAL)
			    								,'user_mobile' => $user->from_mobile
			    								,'user_ip' => $user->last_ip
			    								,'comments_default_maxdepth' => DEFAULT_MAXDEPTH
			    								,'online_users' => var_online_users()
												
												
											),
    							'members' => array( // Format: [Smarty TPL Array Name] => [PHP Array]

	    										 'num_new_events' => Events::getNumNewEvents()
	    										

											)
								);
	
	/**
	 * PHP Functions as Modifiers for Smarty Functions
	 *
     * @ToDo Jeder Eintrag sollte wiederum ein Array sein, damit nebst Funktions-Name noch eine Beschreibung mitgegeben werden kann
     *
	 * @var array
	 */
    $zorg_php_modifiers = array( // Format: [PHP Function Name] => [Optional: Smarty TPL Function Name]

									 'datename' => 'datename' // {$timestamp|datename}  // konviertiert einen timestamp in ein anst?ndiges datum/zeit
									,'stripslashes' => 'stripslashes' // Modifier für die Funktion stripslashes() wie in PHP
									,'strstr' => 'strstr' // Modifier für die Funktion strstr() wie in PHP
									,'stristr' => 'stristr' // Modifier für die Funktion stristr() wie in PHP (Gross-/Kleinschreibung ignorieren)
									,'smarty_sizebytes' => 'sizebytes' // stellt z.B: ein 'kB' dahinter und konvertiert die zahl.
									,'smarty_quantity' => 'quantity' // {$anz|quantity:Zug:Züge}
									,'smarty_number_quotes' => 'number_quotes' //
									,'htmlentities' => 'htmlentities' // Registriert für Smarty den Modifier htmlentities() aus PHP
									,'base64_encode' => 'base64encode' // Registriert für Smarty den Modifier base64_encode() aus PHP
									,'smarty_concat' => 'concat' // Registriert für Smarty den Modifier concat() aus PHP
									,'smarty_ltrim' => 'ltrim' // Registriert für Smarty den Modifiert ltrim() aus PHP
									,'smarty_maxwordlength' => 'maxwordlength' // Registriert für Smarty den Modifier maxwordlength() aus PHP
									,'smarty_name' => 'name' // usersystem
									,'smarty_username' => 'username' // {$userid|username}  // konvertiert userid zu username
									,'smarty_userpic' => 'userpic' // {$userid|userpic}
									,'smarty_userpic2' => 'userpic2' // {$userid|userpic2:0}
									,'smarty_usergroup' => 'usergroup' // {$id|usergroup}   f?r tpl schreib / lese rechte
									,'smarty_userpage' => 'userpage' // {$userid|userpage:0}  // 1.param = username (0) or userpic (1)
									,'smarty_userismobile' => 'ismobile' // {$userid|ismobile} // ermittelt ob letzter Login eines Users per Mobile war
									,'smarty_strip_anchor' => 'strip_anchor' // link
									,'smarty_change_url' => 'change_url' // newquerystring
									,'smarty_print_r' => 'print_r' // ACHTUNG {$myarray|@print_r} verwenden!
									,'smarty_implode' => 'implode' // String glue
									,'smarty_floor' => 'floor' // util
									,'print_array' => 'print_array' // {print_array arr=$hans}


								);

	/**
	 * PHP Function Output as HTML-Blocks for Smarty Templates
	 *
     * @ToDo Jeder Eintrag sollte wiederum ein Array sein, damit nebst Funktions-Name noch eine Beschreibung mitgegeben werden kann
     *
	 * @var array
	 */
	$zorg_php_blocks 	= array( // Format: [PHP Function Name] => [Optional: Smarty TPL Function Name]

									 'smarty_zorg' => 'zorg' // {zorg title="Titel"}...{/zorg}	displays the zorg layout (including header, menu and footer)
									,'smarty_html_link' => 'link' // {link tpl=x param="urlparams"}text{/a}	default tpl = das aktuelle
									,'smarty_new_tpl_link' => 'new_link' // shows a link to the editor with new tpl.
									,'smarty_html_button' => 'button' // {button tpl=x param="urlparams"}button-text{/button}
									,'smarty_form' => 'form' // {form param="urlparams" formid=23 upload=1}..{/form}
									,'smarty_table' => 'table' // layout, table
									,'smarty_tr' => 'tr' // layout, table > tr
									,'smarty_td' => 'td' // layout, table > tr > td
									,'smarty_menubar' => 'menubar' // menu
									,'smarty_menuitem' => 'menuitem' // menu
									,'smarty_edit_link' => 'edit_link' // {edit_link tpl=x}  link zum tpl-editor, default ist aktuelles tpl
									,'smarty_substr' => 'substr' // {substr from=2 to=-1}text{/substr}  // gleich wie php-fnc substr(text, from, to)
									,'smarty_trim' => 'trim' // text modification
									,'smarty_member' => 'member' // {member}..{/member}   {member noborder=1}..{/member}
									
									
								);

	/**
	 * PHP Functions as Template Functions for Smarty
	 *
     * @ToDo Jeder Eintrag sollte wiederum ein Array sein, damit nebst Funktions-Name noch eine Beschreibung mitgegeben werden kann
     *
	 * @var array
	 */
    $zorg_php_functions = array(
									'all_users' => array( // Format: [PHP Function Name] => [Optional: Smarty TPL Function Name]

													 'smarty_addle_highscore' => 'addle_highscore' // Addle
													,'smarty_apod' => 'apod' // Astronomy Picture of the Day (APOD)
													,'smarty_assign_chatmessages' => 'assign_chatmessages' // Chat
													,'smarty_assign_yearevents' => 'assign_yearevents' // events
													,'smarty_assign_event' => 'assign_event' // events
													,'smarty_assign_visitors' => 'assign_visitors' // events
													,'smarty_assign_rezepte' => 'assign_rezepte' // rezepte
													,'smarty_assign_rezept' => 'assign_rezept' // rezepte
													,'smarty_assign_rezept_score' => 'assign_rezept_score' // rezepte
													,'smarty_link' => 'url' // <a href={link id=x word="x" param="urlparams"}>  default tpl ist das akutelle
													,'smarty_space' => 'spc' // {space i=5}
													,'smarty_error' => 'error' // {error msg="Fehler!"}
													,'smarty_state' => 'state' // {state msg="Update erfolgreich"}
													,'smarty_gettext' => 'gettext' // files / filemanager
													,'smarty_comments' => 'comments' // {comments}  f?gt comments zu diesem tpl an.
													,'smarty_latest_comments' => 'latest_comments' // {latest_comments anzahl=10 board=t title="Tabellen-Titel"}  // letzte comments aus board (optional)
													,'smarty_latest_threads' => 'latest_threads' // {latest_threads}
													,'smarty_unread_comments' => 'unread_comments' // {unread_comments board=t title="Tabellen-Titel"}
													,'smarty_3yearold_threads' => '3yearold_threads' // {3yearold_threads}
													,'smarty_commentingsystem' => 'commentingsystem' // forum, comments
													,'getRandomThumb' => 'random_pic' // {random_pic}  displays a random thumb out of the gallery
													,'getDailyThumb' => 'daily_pic' // {daily_pic}   displays the pic of the day
													,'smarty_get_randomalbumpic' => 'random_albumpic' // gallery
													,'smarty_top_pics' => 'top_pics' // gallery
													,'smarty_user_pics' => 'user_pics' // gallery
													,'smarty_assign_users_on_pic' => 'assign_users_on_pic' // gallery
													,'smarty_getNumNewImap' => 'new_imap' // imap
													,'smarty_menu' => 'menu' // menu
													,'smarty_getrandomquote' => 'random_quote' // {random_quote}  display a random quote
													,'smarty_getdailyquote' => 'daily_quote' // {daily_quote}   display a daily quote
													,'smarty_poll' => 'poll' // {poll id=23}
													,'getOpenSTLLink' => 'open_stl_link' // Shoot the lamber
													,'getLatestUpdates' => 'latest_updates' // {latest_updates}  table mit den letzten smarty-updates
													,'smarty_edit_link_url' => 'edit_url' // {edit_url tpl=x}  tpl ist optional. default: aktuelles tpl.
													,'spaceweather_ticker' => 'spaceweather' // spaceweather
													,'peter_zuege' => 'peter' // peter
													,'smarty_sql_errors' => 'sql_errors' // sql errors
													,'stockbroker_assign_stocklist' => 'assign_stocklist' // Stockbroker
													,'stockbroker_assign_stock' => 'assign_stock' // Stockbroker
													,'stockbroker_assign_searchedstocks' => 'assign_searchedstocks' // Stockbroker
													,'stockbroker_update_kurs' => 'update_kurs' // Stockbroker
													,'stockbroker_getkursbought' => 'getkursbought' // Stockbroker
													,'stockbroker_getkurs' => 'getkurs' // Stockbroker
													,'smarty_num_new_tauschangebote' => 'num_new_tauschangebote' // Tauschbörse
													,'smarty_assign_artikel' => 'assign_artikel' // Tauschbörse
													,'url_params' => 'url_params' // system
													,'smarty_sizeof' => 'sizeof' // system
													,'smarty_get_changed_url' => 'get_changed_url' // system
													,'smarty_htmlentities' => 'htmlentities' // Registriert für Smarty die Funktion htmlentities() aus PHP
													,'base64_encode' => 'base64encode' // Registriert für Smarty die Funktion base64_encode() aus PHP
													,'smarty_onlineusers' => 'onlineusers' // usersystem
													,'loginform' => 'loginform' // usersystem
													,'smarty_FormFieldUserlist' => 'formfielduserlist' // usersystem
													,'smarty_datename' => 'datename' // stellt ein Datum leserlich dar
													,'smarty_rand' => 'rand' // {rand min=2 max=10 assign=var}
													,'smarty_function_assign_array' => 'assign_array' // erlaubt es, mit Smarty Arrays zu erzeugen


												),
									'members' => array( // Format: [PHP Function Name] => [Optional: Smarty TPL Function Name]

		    										 'smarty_assign_event_hasjoined' => 'assign_event_hasjoined' // events
		    										,'smarty_event_hasjoined' => 'event_hasjoined' // events
													,'smarty_assign_rezept_voted' => 'assign_rezept_voted' // rezepte


												),
									'compiler_functions' => array( // Format: [PHP Function Name] => [Optional: Smarty TPL Function Name]
													 'smarty_menuname' => 'menuname' // menu
												)
									);


	/**
	 * Function to register the PHP Arrays
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 * Usage: $smarty->assign([array], [value])
	 *
	 * @author IneX
	 * @since 1.0
	 * @version 1.0
	 *
	 * @global object Smarty Class
	 * @global array User Information Array
	 */
	function register_php_arrays($php_arrays_array)
	{
		// Globals
		global $smarty, $user;

		// Regular Arrays, for all Users
		foreach ($php_arrays_array['all_users'] as $smarty_array_key => $array_value)
		{
		  $smarty->assign($smarty_array_key, $array_value);
		}
		
		// Restricted Functions, for logged in Users only
		if($user != null) // nur für eingeloggte
		{
			foreach ($php_arrays_array['members'] as $smarty_array_key => $array_value)
			{
				$smarty->assign($smarty_array_key, $array_value);
			}
		}
		
		natcasesort($php_arrays_array['all_users']); // Sort the Array from A-Z
		$smarty->assign('smartyarrays_public', $php_arrays_array['all_users']); // {smartyarrays_public} Lists all available Smarty Arrays for all Users
		$smarty->assign('smartyarrays_members', $php_arrays_array['members']); // {smartyarrays_members} Lists all available Smarty Arrays for logged in Users
	}


	/**
	 * Function to register the PHP Functions as Modifiers for Smarty Functions
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 * Usage: $smarty->register_modifier([template modifier], [php function])
	 *
	 * @author IneX
	 * @since 1.0
	 * @version 1.0
	 *
	 * @global object Smarty Class
	 * @global array User Information Array
	 */
	function register_php_modifiers($php_modifiers_array)
	{
		// Globals
		global $smarty, $user;

		foreach ($php_modifiers_array as $php_modifier_function => $smarty_modifier)
		{
			if (empty($smarty_modifier)) $smarty_modifier = $php_modifier_function;
			$smarty->register_modifier($smarty_modifier, $php_modifier_function);
		}
		
		natcasesort($php_modifiers_array); // Sort the Array from A-Z
		$smarty->assign('smartymodifiers', $php_modifiers_array); // {smartyarrays_members} Lists all available Smarty Arrays for logged in Users
	}


	/**
	 * Function to register PHP Function Outputs as HTML-Blocks for Smarty Templates
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 * Usage: $smarty->register_block([template block], [php function])
	 *
	 * @author IneX
	 * @since 1.0
	 * @version 1.0
	 *
	 * @global object Smarty Class
	 * @global array User Information Array
	 */
	function register_php_blocks($php_blocks_array)
	{
		// Globals
		global $smarty, $user;
		
		//console output would be: year 2012
		foreach ($php_blocks_array as $php_block_function => $smarty_block)
		{
			if (empty($smarty_block)) $smarty_block = $php_block_function;
			$smarty->register_block($smarty_block, $php_block_function);
		}
		
		natcasesort($php_blocks_array); // Sort the Array from A-Z
		$smarty->assign('smartyblocks', $php_blocks_array); // {smartyarrays_public} Lists all available Smarty Arrays for all Users
	}


	/**
	 * Function to register the PHP Functions to Smarty Functions
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 * Usage 1: $smarty->register_function([template function], [php function])
	 * Usage 2: $smarty->register_compiler_function([template function], [php function], [cacheable true/false])
	 *
	 * @author IneX
	 * @since 1.0
	 * @version 1.0
	 *
	 * @global object Smarty Class
	 * @global array User Information Array
	 */
	function register_php_functions($php_functions_array)
	{
		// Globals
		global $smarty, $user;

		// Regular Functions, for all Users
		foreach ($php_functions_array['all_users'] as $php_function => $smarty_function)
		{
			if (empty($smarty_function)) $smarty_function = $php_function;
			$smarty->register_function($smarty_function, $php_function);
		}

		// Restricted Functions, for logged in Users only
		if($user != null) // nur für eingeloggte
		{
			foreach ($php_functions_array['members'] as $php_function => $smarty_function)
			{
				if (empty($smarty_function)) $smarty_function = $php_function;
				$smarty->register_function($smarty_function, $php_function);
			}
		}

		// Smarty Compiler Functions
		foreach ($php_functions_array['compiler_functions'] as $php_compiler_function => $smarty_compiler_function)
		{
			if (empty($smarty_compiler_function)) $smarty_compiler_function = $php_compiler_function;
			$smarty->register_compiler_function($smarty_compiler_function, $php_compiler_function, false);
		}
		
		natcasesort($php_functions_array['all_users']); // Sort the Array from A-Z
		natcasesort($php_functions_array['members']); // Sort the Array from A-Z
		$smarty->assign('smartyfunctions_public', $php_functions_array['all_users']); // {smartyarrays_public} Lists all available Smarty Arrays for all Users
		$smarty->assign('smartyfunctions_members', $php_functions_array['members']); // {smartyarrays_members} Lists all available Smarty Arrays for logged in Users
	}


//} Closing Class "SmartyZorgFunctions"

//$ZorgSmarty = new SmartyZorgFunctions;
//$ZorgSmarty->register_php_arrays();

register_php_arrays($zorg_php_arrays);
register_php_blocks($zorg_php_blocks);
register_php_modifiers($zorg_php_modifiers);
register_php_functions($zorg_php_functions);



/**
 * Arrays for Smarty
 *
 * Arrays to be used in Smarty Templates
 */
$color = array(
	'background'		=> "#".BACKGROUNDCOLOR,
	'tablebackground'	=> "#".TABLEBACKGROUNDCOLOR,
	'border'			=> "#".BORDERCOLOR,
	'font' 				=> "#".FONTCOLOR,
	'header'			=> "#".HEADERBACKGROUNDCOLOR,
	'link'				=> "#".LINKCOLOR,
	'newcomment'		=> "#".NEWCOMMENTCOLOR,
	'owncomment'		=> "#".OWNCOMMENTCOLOR,
	'menu1'				=> "#".MENUCOLOR1,
	'menu2'				=> "#".MENUCOLOR2
);

function var_online_users ()
{
	global $db;

	$online_users = array();
	$sql = "
		SELECT id FROM user
		WHERE UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(now()) - ".USER_TIMEOUT.")
		ORDER by activity DESC
	";
	$e = $db->query($sql, __FILE__, __LINE__);

	while ($d = mysql_fetch_row($e)) {
		array_push($online_users, $d[0]);
	}
	return $online_users;
}

function var_request ()
{
   return array("page" => $_SERVER['PHP_SELF'],
               "params" => $_SERVER['QUERY_STRING'],
               "url" => $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'],
               "tpl" => $_GET['tpl'],
               "_tpl" => 'tpl:'.$_GET['tpl'],
               "_word" => 'word:'.$_GET['word']);
}


/**
 * Modifiers for Smarty
 *
 * PHP Functions acting as Smarty Modifiers in Smarty Template Functions
 */
	/**
	 * String, Integer, Date and Array Modifiers
	 */
	function smarty_datename($params) {
		return datename($params['date']);
	}
	function smarty_htmlentities($params) {
		return htmlentities($params['text']);
	}
	function smarty_concat($text1, $text2) {
		return $text1.$text2;
	};
	function smarty_ltrim($text, $chars='') {
		if($chars != '') {
			return ltrim($text, $chars);
		} else {
			return ltrim($text);
		}
	};
	function smarty_print_r($myarray) {
		return print_r($myarray, true);
	}
	function smarty_implode($myarray, $zeichen) {
		return implode($zeichen, $myarray);
	}
	function smarty_floor($zahl) {
		return floor($zahl);
	}
	function smarty_sizebytes ($size) {
	   $units = array("B", "kB", "MB", "GB", "TB");
	   $i = 0;
	   while ($size >= 1000 && $i<5) {
	      $i++;
	      $size /= 1024;
	   }
	   $size = round($size, 2);
	   return "<nobr>$size $units[$i]</nobr>";
	}
	function smarty_quantity ($count, $singular="", $plural="") {
		if ($count == 1) return "$count $singular";
		else return "$count $plural";
	}
	function smarty_number_quotes($number, $num_decimal_places='', $dec_seperator='', $thousands_seperator='') {
		if (!$thousands_seperator) $thousands_seperator = '\'';
		if ($thousands_seperator)
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
		else
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
	}
	function smarty_maxwordlength($text, $maxlength) {
		return maxwordlength($text, $maxlength);
	}


	/**
	 * URL Modifiers
	 */
	function smarty_change_url($url, $querystringchanges) {
		return changeURL($url, $querystringchanges);
	}
	function smarty_strip_anchor($url) {
		return substr($url, 0, strpos($url, "#"));
	}
	
	/**
	 * User Information
	 */
	function smarty_userismobile ($userid)
	{
		global $user;
		return $user->ismobile($userid);
	}
	function smarty_usergroup ($groupid) {
	   switch ($groupid) {
	      case 0: return "Alle"; break;
	      case 1: return "Normale User"; break;
	      case 2: return "Member &amp; Schöne"; break;
	      case 3: return "Nur Besitzer"; break;
	      default: return "unknown_usergroup";
	   }
	}
	function smarty_name ($userid) {
      	global $user;
      	return $user->id2user($userid, false, false);
    }
    function smarty_username ($userid) { // converts id to username
      	global $user;
      	return $user->link_userpage($userid, false);
    }
    function smarty_userpic ($userid) {
      	global $user;
      	return $user->link_userpage($userid, true);
    }
    function smarty_userpage ($userid, $pic=0) {
      	global $user;
      	return $user->link_userpage($userid, $pic);
    }
	function smarty_userpic2 ($userid, $displayName=FALSE)
	{
		global $user;
		return $user->userpic($userid, $displayName);
	}


/**
 * Blocks for Smarty
 *
 * PHP Function Output to be reused in Smarty Templates
 */
	/**
	 * Seitenausgabe ZORG
	 */
	function smarty_zorg ($params, $content, &$smarty, &$repeat) {
		$out = "";
	
		$out .= head(117, $params['page_title'], true);
	
	   	$out .= $content;
	
	  	$out .= foot(0);
	
	   	return $out;
	}
	
	/**
	 * String, Integer, Date und Array HTML-Ausgabe
	 */
	function smarty_substr ($params, $content, &$smarty, &$repeat) {
	   if (isset($params['to'])) {
	      return substr($content, $params['from'], $params['to']);
	   }else{
	      return substr($content, $params['from']);
	   }
	}
	function smarty_trim ($params, $content, &$smarty, &$repeat) {
		if ($content) {
			return trim($content);
		}
	}

	/**
	 * HTML Elemente
	 */
    function smarty_html_link ($params, $content, &$smarty, &$repeat) { // gibt einen link aus
      	if (!$content) $content = "link";
	  	return '<a href="'.smarty_link($params).'">'.$content.'</a>';
    }
    function smarty_html_button ($params, $content, &$smarty, &$repeat) { // gibt einen button als link aus
      	return '<input type="button" class="button" value="'.$content.'" onClick="self.location.href=\''.smarty_link($params).'\'">';
    }
    function smarty_form ($params, $content, &$smarty, &$repeat) {
    	// returns an opening-tag for a html-form. action is always 'smarty.php'
    	// if you set the parameter 'formid', a hidden input with this formid is added.
      if (!$_GET[tpl]) $_GET[tpl] = '0';

      if ($params['url']) {
      	$url = $params['url'];
      }elseif ($params['action']) {
      	$url = "/actions/$params[action]?".url_params();
      }else{
      	$url = "/smarty.php?".url_params();
      	if ($params[param]) {
         	$url .= '&'.$params[param];
      	}
      }
      
      $ret = '<form method="post" action="'.$url.'" ';

      if ($params[upload]) {
         $ret .= 'enctype="multipart/form-data"';
      }
      $ret .= '>';
      if ($params[formid]) {
         $ret .= '<input name="formid" type="hidden" value="'.$params[formid].'">';
      }

      $ret .= $content;
      $ret .= "</form>";

      return $ret;
    }
	
	function smarty_table ($params, $content, &$smarty, &$repeat) {
		global $table_color, $tr_count, $table_align, $table_valign;

		if (!$content) {
			$tr_count = 0;

			if ($params['align']) {
				$table_align = $params['align'];
				unset($params['align']);
			}else{
				$table_align = "";
			}

			if ($params['valign']) {
				$table_valign = $params['valign'];
				unset($params['valign']);
			}else{
				$table_valign = "";
			}

			if ($params['nocolor']) {
				$table_color = 0;
				unset($params['nocolor']);
			}else{
				$table_color = 1;
			}

		}else{

			$out = '<table ';
			foreach ($params as $key => $value) {
				if (!in_array($key, array("align", "valign", "nocolor")))
					$out .= $key.'="'.$value.'" ';
			}
			$out .= '>'.$content.'</table>';

			return $out;
		}
	}

	function smarty_tr ($params, $content, &$smarty, &$repeat) {
		global $tr_count, $tr_title, $tr_align, $tr_valign;

		if (!$content) {
			if ($params['title']) {
				$tr_title = 1;
				unset($params['title']);
			}else{
				$tr_count++;
				$tr_title = 0;
			}
			if ($params['align']) {
				$tr_align = $params['align'];
				unset($params['align']);
			}else{
				$tr_align = "";
			}
			if ($params['valign']) {
				$tr_valign = $params['valign'];
				unset($params['valign']);
			}else{
				$tr_valign = "";
			}
		}else{
			$out = '<tr ';
			foreach ($params as $key => $value) {
				if (!in_array($key, array("align", "valign", "title")))
					$out .= $key.'="'.$value.'" ';
			}
			$out .= '>'.$content.'</tr>';

			return $out;
		}
	}

	function smarty_td ($params, $content, &$smarty, &$repeat) {
		global $table_color, $table_align, $table_valign, $tr_count, $tr_title, $tr_align, $tr_valign, $user;

		if (!$content) {
		}else{
			$out = '<td ';
			if ($params['title'] || $tr_title) {
				$title = 1;
				unset($params['title']);
			}else{
				$title = 0;
			}

			if ($params['nobr']) {
				$nobr = 1;
				unset($params['nobr']);
			}else{
				$nobr = 0;
			}

			if ($tr_align && !$params['align']) {
				$out .= 'align="'.$tr_align.'" ';
			}
			if ($table_align && !$tr_align && !$params['align']) {
				$out .= "align='$table_align' ";
			}
			if ($tr_valign && !$params['valign']) {
				$out .= 'valign="'.$tr_valign.'" ';
			}
			if ($table_valign && !$tr_valign && !$params['valign']) {
				$out .= "valign='$table_valign' ";
			}

			foreach ($params as $key => $value) {
				$out .= $key.'="'.$value.'" ';
			}

			if($params['date'] > $user->lastlogin) {
				$out .= 'bgcolor="#'.NEWCOMMENTCOLOR.'" ';
			} else {
				if (!$params['bgcolor'] && $table_color) {
					if (! ($tr_count % 2)) $out .= 'bgcolor="#'.TABLEBACKGROUNDCOLOR.'" ';
				}
			}

			$out .= '>';
			if ($title) $out .= '<b>';
			if ($nobr) $out .= '<nobr>';
			$out .= $content;
			if ($nobr) $out .= '</nobr>';
			if ($title) $out .= '</b>';
			$out .= '</td>';

			return $out;
		}
	}
	
	function smarty_member ($params, $content, &$smarty, &$repeat) {
		global $user;

		if ($content) {
			if ($user->typ == USER_MEMBER) {

				if ($params['width']) $width = "width='$params[width]'";
				else $width = '';

				if (!$params['noborder']) {
					return
						"<table class='border' cellspacing=0 cellpadding=3 $width>".
							"<tr><td align='left' bgcolor=".TABLEBACKGROUNDCOLOR."><b><font color='red'>Member only:</font></b></td></tr>".
							"<tr><td>$content</td></tr>".
						"</table>"
					;
				}else{
					return $content;
				}
			}else{
				return "";
			}
		}
	}
	
	/**
	 * Menu
	 */
	function smarty_menubar ($params, $content, &$smarty, &$repeat) {
		global $user;

		$vars = $smarty->get_template_vars();

		if (!$repeat) {  // closing tag
			$out = '';
			$out .=
				'</div>'.
				'<div class="menu" align="center" width="100%" ';

					if (tpl_permission($vars['tpl']['write_rights'], $vars['tpl']['owner'])) {
						$out .=
							'onDblClick="document.location.href=\''.edit_link_url($vars['tpl']['id']).'\';"';
					}

			$out .=
				'>'.
					'<a class="left">&nbsp;</a>'.
					preg_replace('/<\/a> *<a/', '</a><a', trim($content)).
					'<a class="right">&nbsp;</a>'.
				'</div>'.
				'<div '.BODYSETTINGS.'>'
			;

			return $out;
		}
	}
		function smarty_menuitem ($params, $content, &$smarty, &$repeat) {
		global $user;

		if (!$repeat) {  // closing tag
			if (!isset($params['group'])) $params['group'] = "all";
			if(
				$params['group'] == "all"
				|| $params['group'] == "guest" && !$user->id
				|| $params['group'] == "user" && $user->id
				|| $params['group'] == "member" && $user->typ==USER_MEMBER
			) {

				if (!$content) $content = "???";
				return '<a href="'.smarty_link($params).'">'.trim($content).'</a>';
			}
		}
	}
	
	/**
	 * Smarty Templates
	 */
	function smarty_new_tpl_link ($params, $content, &$smarty, &$repeat) {
		global $smarty;

		$vars = $smarty->get_template_vars();

		return '<a href="/smarty.php?tpleditor=1&tplupd=new&location='.base64_encode($_SERVER['PHP_SELF'].'?'.url_params()).'">'.$content.'</a>';
	}
	function smarty_edit_link ($params, $content, &$smarty, &$repeat) {

		if (!$repeat) {  // closing tag
			if ($params['tpl']) {
				$tpl = $params['tpl'];
			}else{
				$vars = $smarty->get_template_vars();
				$tpl = $vars['tpl']['id'];
				$rights = $vars['tpl']['write_rights'];
				$owner = $vars['tpl']['owner'];
			}

			return edit_link($content, $tpl, $rights, $owner);
		}
	}
		function edit_link ($text='', $tpl=0, $rights=0, $owner=0) {
			global $db;
	
			if (!$text) $text = '[edit]';
			if (!$tpl) $tpl = $_GET['tpl'];
	
			if ($tpl && (!$rights || !$owner)) {
				$d = $db->fetch($db->query("SELECT * FROM templates WHERE id='$tpl'", __FILE__, __LINE__));
				$rights = $d['write_rights'];
				$owner = $d['owner'];
			}
	
		   if ($tpl && tpl_permission($rights, $owner)) {
				return "<a href='".edit_link_url($tpl)."'>$text</a>";
		   }else{
		   	return "";
		   }
		}


/**
 * PHP Functions for Smarty
 *
 * PHP Functions to be used in Smarty Templates
 */
	/**
	 * Stockbroker
	 */
	function stockbroker_assign_stocklist($params, &$smarty) {
		$smarty->assign("stocklist", Stockbroker::getStocklist($params['anzahl'], $params['page']));
		//{assign_stocklist anzahl=100 page=$smarty.get.page}
	}
	function stockbroker_assign_stock($params, &$smarty) {
		$smarty->assign("stock", Stockbroker::getSymbol($params['symbol']));
		//{assign_kurs symbol=$kurs.symbol}
	}
	function stockbroker_assign_searchedstocks($params, &$smarty) {
		$smarty->assign("searchedstocks", Stockbroker::searchstocks($params['search']));
	}
	function stockbroker_update_kurs($params, &$smarty) {
		Stockbroker::updateKurs($params['symbol']);
	}
	function stockbroker_getkursbought($params) {
		global $user;
		return Stockbroker::getKursBought($user->id, $params['symbol']);
	}
	function stockbroker_getkurs($params) {
		return Stockbroker::getKurs($params['symbol']);
	}

	/**
	 * Tauschbörse
	 */
	function smarty_num_new_tauschangebote ($params, &$smarty) {
		if (isset($user->lastlogin)) {
			$result = $db->query(
				"
				SELECT COUNT(*) AS num
				FROM tauschboerse
				WHERE
					UNIX_TIMESTAMP(datum) > ".$user->lastlogin."
				",
				__FILE__,
				__LINE__
			);
			$rs = $db->fetch($result);
			//$smarty->assign("artikel", $rs);
			$smarty->assign("num_new_tauschangebote", $rs['num']);
		}
	}
	function smarty_assign_artikel ($params, &$smarty) {
		global $db;
		$result = $db->query(
			"
			SELECT *, UNIX_TIMESTAMP(datum) AS datum
			FROM tauschboerse
			WHERE id = ".$params['id']."
			",
			__FILE__,
			__LINE__
		);
		$rs = $db->fetch($result);
		$smarty->assign("artikel", $rs);
	}

	/**
	 * Usersystem
	 */
	function smarty_FormFieldUserlist($params) {
		return usersystem::getFormFieldUserlist($params['name'], $params['size']);
	}
	function smarty_onlineusers($params) {
		if (!isset($params[images])) $params[images] = false;
		return usersystem::online_users($params[images]);
	}

	/**
	 * Addle
	 */
    function smarty_addle_highscore ($params) {
	    // wrapper function for addle highscore
        if (!isset($params[anzahl])) $params[anzahl] = 5;
        return highscore_dwz($params[anzahl]);
    }
	/*function smarty_addle_highscore ($params) {
      if (!isset($params[anzahl])) $params[anzahl] = 5;
      return highscore_dwz($params[anzahl]);
    }*/
    
    /**
	 * Quotes
	 */
    function smarty_getrandomquote ($params) {
	   return Quotes::getRandomQuote();
	}
	function smarty_getdailyquote ($params) {
		return Quotes::getDailyQuote();
	}
	
    /**
	 * Polls
	 */
	function smarty_poll ($params) {
		return getPoll($params['id']);
	}
	
	/**
	 * APOD
	 */
	function smarty_apod ($params, &$smarty) {
		$rs = get_apod_id();
		return formatGalleryThumb($rs);
	}
	
	/**
	 * Events
	 */
	function smarty_assign_yearevents($params, &$smarty) {
		$smarty->assign("yearevents", Events::getEvents($params['year']));
	}
	function smarty_assign_event ($params, &$smarty) {
		$smarty->assign("event", Events::getEvent($params['id']));
	}
	function smarty_assign_visitors ($params, &$smarty) {
		$smarty->assign("visitors", Events::getVisitors($params['event_id']));
	}
	function smarty_assign_event_hasjoined($params, &$smarty) {
		global $user;
		$smarty->assign("event_hasjoined", Events::hasJoined($user->id, $params['event_id']));
	}
	function smarty_event_hasjoined($params, &$smarty) {
		global $user;
		return Events::hasJoined($user->id, $params['event_id']);
	}
	
	/**
	 * Rezepte
	 */
	function smarty_assign_rezepte ($params, &$smarty) {
		$smarty->assign("rezepte", Rezepte::getRezepte($params['category']));
	}
	function smarty_assign_rezept ($params, &$smarty) {
		$smarty->assign("rezept", Rezepte::getRezept($params['id']));
	}
	function smarty_assign_rezept_voted ($params, &$smarty) {
		// nur für eingeloggte
		$smarty->assign("rezept_voted", Rezepte::hasVoted($params['user_id'], $params['rezept_id']));
	}
	function smarty_assign_rezept_score ($params, &$smarty) {
		$smarty->assign("rezept_score", Rezepte::getScore($params['rezept_id']));
	}

	/**
	 * SQL Errors
	 */
    function smarty_sql_errors($params) {
   		return  get_sql_errors($params['num'],$params['order'],$params['oby']);
	}
	
	/**
	 * URL Handling
	 */
	function smarty_link ($params) {
		// url to a template called by smarty.php;
	    // if parameter button is set, the link is shown as a button.
	   	global $smarty;
	   	$vars = $smarty->get_template_vars();
	
	   	if (isset($params['url'])) {
	   		$ret = $params['url'];
	   	}elseif (isset($params['word'])) {
	   		$ret = "/smarty.php?word=".$params['word'];
	   	}elseif (isset($params['tpl'])) {
	      	$ret = "/smarty.php?tpl=".$params['tpl'];
	   	}elseif (isset($params['comment'])) {
	   		$ret = Comment::getLinkComment($params['comment']);
	   	}elseif (isset($params['user'])) {
	   		if (is_numeric($params['user'])) $ret = "/profil.php?user_id=$params[user]";
	   		else $ret = '/profil.php?user_id='.usersystem::user2id($params['user']);
	   	}elseif (isset($params['action'])) {
	   		$ret .= "/actions/$params[action]?".url_params();
	   	}else{
	   		$ret = "/smarty.php?tpl=".$vars[tpl][root];
	   	}

        if (isset($params['param'])) $ret .= "&".$params[param];
		
        if (isset($params['hash'])) $ret .= '#'.$params['hash'];
        return $ret;
    }
    function smarty_get_changed_url ($params) {
		return getChangedURL ($params['change']);
	}

	/**
	 * HTML
	 */
    function smarty_space ($params) { // inserts &nbsp;
        return str_repeat("&nbsp;", $params[i]);
    }
    
    /**
	 * String, Integer, Date and Array Functions
	 */
	function smarty_sizeof ($params) {
		return sizeof($params['array']);
	}
	function smarty_rand ($params, &$smarty) {
		mt_srand();

		if (isset($params['min']) && isset($params['max'])) $z = mt_rand($params['min'], $params['max']);
		elseif (isset($params['min']) && !isset($params['max'])) $z = mt_rand($params['min']);
		elseif (!isset($params['min']) && isset($params['max'])) $z = mt_rand(0, $params['max']);
		elseif (isset($params['min']) && !isset($params['max'])) $z = mt_rand();

		if (isset($params['assign'])) $smarty->assign($params['assign'], $z);
		else return $z;
	}
    
    /**
	 * Files
	 */
	function smarty_gettext ($params, &$smarty) { // Read contents from a textfile on the Server
		global $db;

		if ($params['file']) {
			$file = $params['file'];
			if (substr($file, -4) != '.txt') return "<font color='red'><b>[gettext: Can only read from txt-File]</b></font><br />";
			if (substr($file, 0, 1) == '/') $file = $_SERVER['DOCUMENT_ROOT'].$file;
			if (!file_exists($file)) return "<font color='red'><b>[gettext: File '$file' not found]</b></font><br />";
		}elseif ($params['id']) {
			$e = $db->query("SELECT * FROM files WHERE id='$params[id]'", __FILE__, __LINE__);
			$d = $db->fetch($e);
			if ($d) {
				if (substr($d['name'], -4) != '.txt') return "<font color='red'><b>[gettext: Can only read from txt-File]</b></font><br />";
				$file = $_SERVER['DOCUMENT_ROOT']."/../data/files/$d[user]/$d[name]";
			}else{
				return "<font color='red'><b>[gettext: File mit id '$params[id]' in Filemanager nicht gefunden]</b></font><br />";
			}
		}else{
			return "<font color='red'><b>[gettext: Gib mittels dem Parameter 'file' oder 'id' eine Datei an]</b></font><br />";
		}

		$out = "";
		$out .= "<div align='left'><xmp>";


		if ($params['linelength']) {
			$len = $params['linelength'];
			if (!is_numeric($len) || $len < 1) {
				return "<font color='red'><b>[gettext: Parameter linelength has to be numeric and greater than 0]</b></font><br />";
			}
			$fcontent = file($file);
			foreach ($fcontent as $it) {
				while (strlen($it) > $len) {
					$out .= substr($it, 0, $len) . "\n   ";
					$it = substr($it, $len);
				}
				$out .= $it;
			}
		}else{
			$out .= file_get_contents($file);
		}

		$out .= "</xmp></div>";

		return $out;
	}

	/**
	 * Commenting System und Forum Threads
	 */
	function smarty_commentingsystem($params) {
		Forum::printCommentingSystem($params['board'], $params['thread_id']);
	}
    function smarty_comments ($params) {
      	global $smarty, $user;

      	$tplvars = $smarty->get_template_vars();
      	if (!$params['board'] || !$params['thread_id']) {
   			$params['board'] = 't';
   			$params['thread_id'] = $tplvars['tpl']['id'];
   		}

		if (Thread::hasRights($params['board'], $params['thread_id'], $user->id)) {
		      if ($user->show_comments) {
		         if ($tplvars[tpl][id] == $_GET[tpl]) {
		            echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td width="100%" class="small border" align="right">'.
		                 '<a href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=0">'.
		                 'Kommentare ausblenden</a>'.
		                 '</td></tr><tr><td>';
		            Forum::printCommentingSystem($params['board'], $params['thread_id']);
		            echo '</td></tr></table>';
	
		            return "";
		         }else{
		            return '<p><font color="green"><i><b>Kommentare</b> werden in Includes ausgeblendent. '
		                  .'Klick <a href="smarty.php?tpl='.$tplvars['tpl']['id'].'">hier</a>,'
		                  .'um sie zu sehen</i></font></p>';
		         }
	
		      }else{
		         return '<table cellspacing="0" width="100%"><tr><td width="100%" class="small" align="right">'.
		                '<font color="green"><b>Kommentare</b> sind zur Zeit ausgeblendet. '.
		                '<a href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=1">'.
		                'Kommentare einblenden</a></font></td></tr></table>';
		      }
		}
    }
    function smarty_latest_threads ($params) {
	   return Forum::getLatestThreads();
	}
	function smarty_latest_comments ($params) {
	   return Forum::getLatestComments($params['anzahl'], $params['title'], $params['board']);
	}
	function smarty_3yearold_threads ($params) {
	   return Forum::get3YearOldThreads();
	}
	function smarty_unread_comments ($params) {
	   return Forum::getLatestUnreadComments($params[title], $params[board]);
	}
	
	/**
	 * IMAP
	 */
	function smarty_getNumNewImap ($params) {
		global $user;

		return ImapStatic::getNumnewmessages($user);
	}
	
	/**
	 * Smarty Information
	 */
    function smarty_error ($params) {
	      if ($params[msg]) {
	         return '<p><font color="red"><b>'.$params[msg].'</b></font></p>';
	      }else{
	         return "";
	      }
    }
    function smarty_state ($params) {
	      if ($params[msg]) {
	         return '<p><font color="green"><b>'.$params[msg].'</b></font></p>';
	      }else{
	         return "";
	      }
    }
   	function smarty_edit_link_url ($params, &$smarty) {
		if (!$params['tpl']) {
			$vars = $smarty->get_template_vars();
			$params['tpl'] = $vars['tpl']['id'];
		}
		return edit_link_url($params['tpl']);
	}
		function edit_link_url ($tpl) {
			return "/smarty.php?tpleditor=1&tplupd=$tpl&location=".base64_encode($_SERVER['PHP_SELF'].'?'.url_params());
		}
		/**
		 * Letze Smarty Updates
		 *
		 * Gibt eine Tabelle mit Links zu den letzten upgedateten Smartys
		 *
		 * @return String
		 */
		function getLatestUpdates($params = array()) {
			global $db, $user;
	
			if (!$params['anzahl']) $params['anzahl'] = 5;
	
			$sql =
				"SELECT *, UNIX_TIMESTAMP(last_update) as date"
				." FROM templates"
				." ORDER BY last_update desc"
				." LIMIT 0, $params[anzahl]"
			;
			$result = $db->query($sql, __FILE__, __LINE__);
	
			$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>letzte Änderungen</b></td></tr>';
			while($rs = $db->fetch($result)) {
		    $i++;
	
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
	
		    $html .=
		      '<tr class="small"><td align="left" bgcolor="#'.$color.'">'
		      .'<a href="/smarty.php?tpl='.$rs[id].'">'.stripslashes($rs[title]).' ('.$rs[id].')'.'</a>'
		      .'</td><td align="left" bgcolor="#'.$color.'" class="small">'
		      .$user->link_userpage($rs['update_user'])
		      .'</td><td align="left" bgcolor="#'.$color.'" class="small"><nobr>'
		      .datename($rs[date])
		      .'</nobr></td></tr>'
		    ;
	
		  }
		  $html .= '</table>';
	
		  return $html;
		}
	
	/**
	 * Menu
	 */
	function smarty_menu ($params, &$smarty) {
		global $db, $user;

		$vars = $smarty->get_template_vars();

		if ($vars['tpl_parent']['id'] == $vars['tpl_root']['id']) {
			if ($params['tpl']) {
				$e = $db->query("SELECT * FROM templates WHERE id='$params[tpl]'", __FILE__, __LINE__);
				$d = $db->fetch($e);
				if (tpl_permission($d['read_rights'], $d['owner'])) {
					return $smarty->fetch("tpl:$params[tpl]");
				}else{
					return '';
				}
			}else{
				$e = $db->query(
					"SELECT m.* FROM menus m, templates t
					WHERE name='$params[name]' AND t.id = m.tpl_id", __FILE__, __LINE__);
				$d = $db->fetch($e);
				if ($d && tpl_permission($d['read_rights'], $d['owner'])) {
					return $smarty->fetch("tpl:$d[tpl_id]");
				}elseif ($d) {
					return '';
				}else{
					return "<font color='red'><b>[Menu '$params[name]' not found]</b></font><br />";
				}
			}
		}
	}
		function smarty_menuname_exec ($name) {
			global $db, $smarty;
	
			$vars = $smarty->get_template_vars();
			$tpl = $vars['tpl']['id'];
	
			$name = htmlentities($name, ENT_QUOTES);
			$name = explode(" ", $name);
	
			for ($i=0; $i<sizeof($name); $i++) {
				if ($it) {
					$menu = $db->fetch($db->query("SELECT * FROM menus WHERE name='$name[$i]'", __FILE__, __LINE__));
					if ($menu && $menu['tpl_id'] != $tpl) return "Menuname '$name[$i]' existiert schon und wurde nicht registriert.<br />";
					unset($name[$i]);
				}
			}
	
			$db->query("DELETE FROM menus WHERE tpl_id='$tpl'", __FILE__, __LINE__);
			foreach ($name as $it) {
				$db->query("INSERT INTO menus (tpl_id, name) VALUES ($tpl, '$it')", __FILE__, __LINE__);
			}
			return "";
		}

	/**
	 * Gallery
	 */
	function smarty_assign_users_on_pic ($params, &$smarty) {
		$smarty->assign("users_on_pic", Gallery::getUsersOnPic($params['picID']));
	}
	function smarty_get_randomalbumpic($params) {
		return getAlbumLinkRandomThumb($params['album_id']);
	}
		/**
		 * Smarty Function "top_pics"
		 *
		 * Returns a specific amount of best rated images from a given gallery
		 * Usage: {top_pics album=41 limit=1}
		 *
		 * @author IneX <IneX@gmx.net>
		 */
		function smarty_top_pics ($params)
		{
		   	$album_id = ($params['album'] == '' ? 0 : $params['album']);
	
		   	$limit = ($params['limit'] == '' ? 5 : $params['limit']);
	
		   	$options = ($params['options'] == '' ? '' : $params['options']);
	
	   		//Nur zum kontrollieren...
	   		//print('Album-ID: '.$album_id.'<br />Limit: '.$limit.'<br />');
	
	   		return getTopPics($album_id, $limit, $options);
		}
		/**
		 * Smarty Function "user_pics"
		 *
		 * Returns a specific amount of Gallery Pictures on which a given User has been tagged
		 * Usage: {user_pics user=41 limit=1}
		 *
		 * @author IneX <IneX@gmx.net>
		 * @date 18.10.2013
		 */
		function smarty_user_pics($params)
		{
			$userid = ($params['user'] == '' ? 0 : $params['user']);
		   	$limit = ($params['limit'] == '' ? 0 : $params['limit']);
		   	//$options = ($params['options'] == '' ? '' : $params['options']);
	
	   		//return getUserPics($userid, $limit, $options);
	   		return getUserPics($userid, $limit);
		}
	
	/**
	 * Chat
	 */
	/*function smarty_chat() { Inaktiv?! IneX, 2.5.09
    	return Chat::getInterfaceHTML();
	}*/
	function smarty_assign_chatmessages($params, &$smarty) {
		global $db;

		$anzahl = ($params['anzahl'] == '' ? 10 : $params['anzahl']);
		$page = ($params['page'] == '' ? 0 : $params['page']);

		$sql = "SELECT * from chat";
		$result = $db->query($sql, __FILE__, __LINE__);
		$num = $db->num($result);

		$sql =
			"
			SELECT
				chat.text
				, UNIX_TIMESTAMP(date) AS date
				, user.username AS username
				, user.clan_tag AS clantag
				, chat.user_id
				, chat.from_mobile
			FROM chat
			LEFT JOIN user ON (chat.user_id = user.id)
			ORDER BY date ASC
			LIMIT ".(($num-$anzahl)-($page*$anzahl)).", ".$anzahl."
			"
		;
		//echo $sql;
		$result = $db->query($sql, __FILE__, __LINE__);

		while ($rs = mysql_fetch_array($result)) {
		  $chatmessages[] = $rs;
		}
		$smarty->assign("chatmessages", $chatmessages);
	}


/**
 * Compiler Functions
 * 
 * ACHTUNG: compiler-funktionen müssen php-code zurückgeben!
 */
function smarty_menuname ($name, &$smarty) {
	return "echo smarty_menuname_exec ('$name');";
}


/**
 * Smarty Plugins
 *
 * Third party plugin functions to extend Smarty with new functionality
 */
	/*
	 * assign_adv
	 * -------------------------------------------------------------
	 * Type:     function
	 * Name:     assign_adv --> geändert zu "assign_array" damit es verständlicher ist, 6.8.07/IneX
	 * File:     function.assign_adv.php
	 * Version:  0.11
	 * Purpose:  assigns smarty variables including arrays and range arrays
	 * Author:   Bill Wheaton <billwheaton atsign mindspring fullstop com>
	 * Synopsis:
	 *      {assign_adv var="myvar" value="array('x','y',array('a'=>'abc'))"}
	 *      or
	 *      {assign_adv var="myvar" value="range(1,2)"}
	 *      or
	 *      {assign_adv var="myvar" value="myvalue"}
	 *
	 * Description: assign_adv is a direct and backward compatable replacement
	 *  of assign.  It adds extra features, hence the '_adv' extention.
	 *  The extra features are:
	 *      value - can now contain a string formatted as a valid PHP array code or range code.
	 *          the code is checked to see if it matches array(...) or range(...), and if so
	 *          evaluates an array or range code from the contents of them (...).
	 *
	 * Examples:
	 *  assign an array of hashes of javascript events (useful for html_field_group):
	 *      {assign_adv
	 *              var='events'
	 *              value="array(
	 *                      array(
	 *                          'onfocus'=>'alert(\'Dia guit\');',
	 *                          'onchange'=>'alert(\'Slainte\');'
	 *                          ),
	 *                      array(
	 *                          'onfocus'=>'alert(\'God be with you\');',
	 *                          'onchange'=>'alert(\'Cheers\');'
	 *                          )
	 *                      )" }
	 * or assign a range of days to select for calendaring & scheduling
	 *      {assign_adv var='repeatdays' value="range(1,30)" }
	 *
	 * Justification: Some might say "shoot, why not just write all your code in templates".  Well,
	 *      I'm not really.  assign already assigns scalars, so allowing arrays and hashes seems
	 *      logical.  I'm willing to draw the line there.
	 *
	 * Downside: Its slower to use assign_adv, so while you can use it as a replacement for
	 *      assign, unless you need to assign an array, use assign instead.  assign_adv uses
	 *      a PHP eval statement to facilitate it which can eat some time.
	 *
	 * See Also: function.assign.php
	 *
	 * ChangeLog: beta 0.10 first release (Bill Wheaton)
	 *            beta 0.11 changed regular expression and flow control (Soeren Weber)
	 *
	 * COPYRIGHT:
	 *     Copyright (c) 2003 Bill Wheaton
	 *     This software is released under the GNU Lesser General Public License.
	 *     Please read the following disclaimer
	 *
	 *      THIS SOFTWARE IS PROVIDED ''AS IS'' AND ANY EXPRESSED OR IMPLIED
	 *      WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
	 *      OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	 *      DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE
	 *      LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	 *      OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	 *      OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	 *      OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
	 *      LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
	 *      NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	 *      SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	 *
	 *     See the GNU Lesser General Public License for more details.
	 *
	 * You should have received a copy of the GNU Lesser General Public
	 * License along with this library; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	 *
	 * -------------------------------------------------------------
	 */
	function smarty_function_assign_array($params, &$smarty)
	{
		extract($params);

		if (empty($var)) {
			$smarty->trigger_error("assign_array: missing 'var' parameter");
			return;
		}

		if (!in_array('value', array_keys($params))) {
			$smarty->trigger_error("assign_array: missing 'value' parameter");
			return;
		}

		if (!in_array('array', array_keys($params)) XOR !in_array('range', array_keys($params))) {
			$smarty->trigger_error("assign_array: missing 'value=array()' or 'value=range()'");
			return;
		}

		if (preg_match('/^\s*array\s*\(\s*(.*)\s*\)\s*$/s',$value,$match)){
			eval('$value=array('.str_replace("\n", "", $match[1]).');');
		}
		else if (preg_match('/^\s*range\s*\(\s*(.*)\s*\)\s*$/s',$value,$match)){
			eval('$value=range('.str_replace("\n", "", $match[1]).');');
		}

		$smarty->assign($var, $value);
	}


?>