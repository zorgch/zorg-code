<?php
/**
 * zorg Custom Smarty Functions, Variables, Modifiers & Blocks
 *
 * @package zorg\Smarty\Templates
 * @author [z]biko
 * @author IneX
 * @since 2.6.2 `05.06.2004` `[z]biko` Initial release with Smarty 2
 * @since 2.6.25 `23.05.2009` `IneX` Smarty 2.6.25 compatibility update
 * @since 2.6.29 `21.06.2015` `IneX` Smarty 2.6.29 compatibility update
 * @since 3.1.36 `01.05.2020` `IneX` Upgrade to Smarty 3.x using v3.1.36 compatibility
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/config.inc.php';
include_once INCLUDES_DIR.'addle.inc.php';
include_once INCLUDES_DIR.'apod.inc.php';
include_once INCLUDES_DIR.'bugtracker.inc.php';
include_once INCLUDES_DIR.'error.inc.php';
include_once INCLUDES_DIR.'events.inc.php';
include_once INCLUDES_DIR.'forum.inc.php';
include_once INCLUDES_DIR.'gallery.inc.php';
include_once INCLUDES_DIR.'go_game.inc.php';
include_once INCLUDES_DIR.'hz_game.inc.php';
include_once INCLUDES_DIR.'poll.inc.php';
include_once INCLUDES_DIR.'peter.inc.php';
include_once INCLUDES_DIR.'quotes.inc.php';
include_once INCLUDES_DIR.'rezepte.inc.php';
include_once INCLUDES_DIR.'spaceweather.inc.php';
include_once INCLUDES_DIR.'stl.inc.php';
include_once INCLUDES_DIR.'stockbroker.inc.php';
include_once INCLUDES_DIR.'util.inc.php';
if ($user->is_loggedin()) include_once INCLUDES_DIR.'tpleditor.inc.php';

/**
 * zorg Smarty Handler to register Variables & Arrays
 *
 * These are all the PHP Functions and Arrays we want to register in Smarty as Variable or Array
 * Format: [Variable-Name] => array ([Werte] | [Kategorie] | [Beschreibung] | [Assign by Reference true/false])
 * 
 * This Class contains various functions/function-mappings between
 * PHP and the Smarty Template engine. Further, it takes care
 * of properly registering/assigning the functions or methods
 * to Smarty as Template Functions, so they can be used within
 * Smarty templates using somethling like {custom_function}
 *
 * @package zorg\Smarty\Templates
 * @author IneX
 * @version 1.0
 * @since 1.0 `04.05.2020` `IneX` Class added
 */
class ZorgSmarty_Vars
{
	/** Array where we will put all $var into later */
	private $zorg_smarty_vars;

	/**
	 * @var array $colorlist Array mit allen Standardfarben (wechselt zwischen Tag und Nacht)
	 */
	private $colorlist = [
						 'background'		=> BACKGROUNDCOLOR
						,'tablebackground'	=> TABLEBACKGROUNDCOLOR
						,'tableborder'		=> TABLEBORDERC
						,'border'			=> BORDERCOLOR
						,'font' 			=> FONTCOLOR
						,'header'			=> HEADERBACKGROUNDCOLOR
						,'link'				=> LINKCOLOR
						,'newcomment'		=> NEWCOMMENTCOLOR
						,'owncomment'		=> OWNCOMMENTCOLOR
						,'menu1'			=> MENUCOLOR1
						,'menu2'			=> MENUCOLOR2
					];
	/**
	 * @var array $usertypes Array mit allen vorhandenen Usertypen: alle, user, member und special
	 */
	private $usertypes = [
							 'alle'=>USER_ALLE
							,'user'=>USER_USER
							,'member'=>USER_MEMBER
							,'special'=>USER_SPECIAL
						];

	/**
	 * Class Constructor.
	 *
	 * Takes care of defining $var values & triggers to register them to Smarty
	 * e.g. Array "$colors['background']" will be available in Smarty as {$colors.background}
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `04.05.2020` `IneX` Constructor added
	 * @since 1.1 `04.05.2020` `IneX` Removed '[Members only true/false]' flag because breaks for not logged-in users
	 *
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	public function __construct()
	{
		global $user;

		/**
		 * Assign values to every $var
		 * Format: [Variable-Name] => array ([Werte] | [Kategorie] | [Beschreibung] | [Assign by Reference true/false])
		 */
		$this->zorg_smarty_vars = [
			 'color' => [ $this->colorlist, 'Layout', 'Array mit allen Standardfarben (wechselt zwischen Tag und Nacht)', false ]
			,'num_errors' => [ $this->get_num_errors(), 'System', 'Zeigt Anzahl geloggter SQL-Errors an', false ]
			,'sun' => [ $this->get_sun(), 'Layout', 'Zeigt an ob Sonne "up" oder "down" ist', false ]
			,'sunset' => [ $this->get_sunset(), 'Layout', 'Zeit des nächsten SonnenUNTERgangs', false ]
			,'sunrise' => [ $this->get_sunrise(), 'Layout', 'Zeit des nächsten SonnenAUFgangs', false ]
			,'country' => [ $this->get_country_iso(), 'Layout', 'ISO-Code des ermittelten Landes des aktuellen Besuchers', false ]
			,'country_image' => [ $this->get_country_image(), 'Layout', 'Bildpfad zur Länderflagge des ermittelten Landes', false ]
			,'self' => [ $this->get_phpself(), 'URL Handling', 'Self = Aktuelle Seiten-URL', false ]
			,'usertyp' => [ $this->usertypes, 'Usersystem', 'Array mit allen vorhandenen Usertypen: alle, user, member und special', false ]
			,'login_error' => [ $this->get_login_error(), 'Usersystem', 'Ist leer oder enthält Fehlermeldung eines versuchten aber fehlgeschlagenen Logins eines Benutzers', false ]
			,'smarty_menus' => [ $this->get_menus_list(), 'Smarty', 'Array mit allen verfügbaren Smarty-Menutemplates (auf die der User Leserechte hat). Usage: {$smarty_menus}', true ]
			,'packages' => [ $this->get_packages_list(), 'Smarty', 'Array mit allen verfügbaren Package-Files in Smarty abrufen. Usage: {$packages}', true ]
			,'online_users' => [ $this->get_online_users(), 'Usersystem', 'Array mit allen zur Zeit eingeloggten Usern', false ]

			// Vars/Vars-Outputs not in this Class
			,'comments_default_maxdepth' => [ DEFAULT_MAXDEPTH, 'Layout', 'Standart angezeigte Tiefe an Kommentaren z.B. im Forum', false ]
			,'event_newest' => [ Events::getEventNewest(), 'Events', 'Zeigt neusten Event an', false ]
			,'nextevents' => [ Events::getNext(), 'Events', 'Zeigt nächsten kommenden Event an', false ]
			,'eventyears' => [ Events::getYears(), 'Events', 'Zeigt alle Jahre an, in denen Events erfasst sind', false ]
			,'num_new_events' => [ Events::getNumNewEvents(), 'Events', 'Zeigt Anzahl neu erstellter Events an', true ]
			,'rezept_newest' => [ Rezepte::getRezeptNewest(), 'Rezepte', 'Zeigt neustes Rezept an', false ]
			,'categories' => [ Rezepte::getCategories(), 'Rezepte', 'Zeigt Liste von Rezept-Kategorien an', false ]
			,'url' => [ getURL(), 'URL Handling', 'Gesamte aktuell aufgerufene URL (inkl. Query-Parameter)', false ]
			,'user' => [ $user, 'Usersystem', 'Array mit allen User-Informationen des aktuellen Besuchers', false ]
			,'user_mobile' => [ (isset($user->from_mobile)?$user->from_mobile:''), 'Usersystem', 'Zeigt an ob aktueller Besucher mittels Mobiledevice die Seite aufgerufen hat', false ] // @FIXME wieso nicht über $user-Object abgreifen?
			,'user_ip' => [ (isset($user->last_ip)?$user->last_ip:''), 'Usersystem', 'IP-Adresse des aktuellen Besuchers', false ] // @FIXME wieso nicht über $user-Object abgreifen?
			,'code_info' => [ getGitCodeVersion(), 'Code Info', 'Holt die aktuellen Code Infos (Version, last commit, etc. ] aus dem Git HEAD', false ]
			,'spaceweather' => [ spaceweather_ticker(), 'Smarty', 'Array mit allen Spaceweather-Werten in Smarty abrufen. Usage: {$spaceweather}', true ]
		];

		/** Call the register-Method which assigns $this->zorg_smarty_vars to Smarty */
		//DISABLED $this->register();
	}

  	/**
	 * Function to register PHP Variables to Smarty
	 *
	 * Maps custom Zorg PHP Variables and Arrays to a Smarty Variable
	 * e.g. Array "$colors['background']" will be available in Smarty as {$colors.background}
	 *
	 * @example $smarty->assign([smarty var name], [value])
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `03.01.2016` `IneX` function added
	 * @since 2.0 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Vars, disabled generic $smarty_vars_documentation
	 *
	 * @global object $smarty Smarty Class-Object
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	public function register()
	{
		global $smarty, $user;

		foreach ($this->zorg_smarty_vars as $smarty_var_key => $smarty_var_data)
		{
			if (!$smarty_var_data[3]) $smarty->assign($smarty_var_key, $smarty_var_data[0]);
			else $smarty->assignByRef($smarty_var_key, $smarty_var_data[0]); // Assign by Reference
		}
	}

	/**
	 * Function to retrieve Smarty Variables-documentation array from this Class
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `06.05.2020` `IneX` Method added to output zorgSmarty documentation not on every Template request & with more control
	 *
	 * @return array
	 */
	public function documentation()
	{
		//$docuArray = natcasesort($this->zorg_smarty_vars);
		return $this->zorg_smarty_vars;
	}

	/**
	 * Get $num_errors
	 */
	private function get_num_errors()
	{
		global $num_errors;
		return $num_errors;
	}

	/**
	 * Get $sun
	 */
	private function get_sun()
	{
		global $sun;
		return $sun;
	}

	/**
	 * Get $sunset
	 */
	private function get_sunset()
	{
		global $sunset;
		return $sunset;
	}

	/**
	 * Get $sunrise
	 */
	private function get_sunrise()
	{
		global $sunrise;
		return $sunrise;
	}

	/**
	 * Get $country
	 */
	private function get_country_iso()
	{
		global $country;
		return $country;
	}

	/**
	 * Get $country_image
	 */
	private function get_country_image()
	{
		global $country_code;
		return IMAGES_DIR.'country/flags/'.$country_code.'.png';
	}

	/**
	 * Get $_SERVER['PHP_SELF']
	 */
	private function get_phpself()
	{
		return $_SERVER['PHP_SELF'];
	}

	/**
	 * Get $login_error
	 */
	private function get_login_error()
	{
		global $login_error;
		return (isset($login_error) ? $login_error : null);
	}

	/**
	 * Smarty Array "$smarty_menus"
	 *
	 * Returns all Smarty Menus (Smarty-Menutemplates) as an Array
	 * Usage: {$smarty_menus}
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/pages/tpleditor.tpl Primarily used in Template-Editor
	 *
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `30.09.2018` `IneX` function added
	 * @since 2.0 `03.05.2020` `IneX` function merged with code from package `/scripts/menu_overview.php` because {include_php} of it is deprecated in Tpleditor
	 * @since 2.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Vars, renamed from 'smarty_get_menus()' to 'get_menus_list()'
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array List with all Menus the current User has access to (permissions match)
	 */
	private function get_menus_list()
	{
		global $db;

		/** Get all menus with their corresponding tpl_id & include Template Permissions from templates-reference */
		$menusQuery = $db->query('SELECT menu.id id, menu.name name, menu.tpl_id tpl_id, tpls.read_rights read_rights, tpls.owner owner 
									FROM menus menu 
								  LEFT JOIN templates tpls 
									ON menu.tpl_id = tpls.id'
									, __FILE__, __LINE__, __FUNCTION__);
		while ($menusQueryResult = $db->fetch($menusQuery)) {
			$menus[$menusQueryResult['tpl_id']] = $menusQueryResult; // set key=tpl_id so we can delete specific entries later
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> array($menus): %s', __FILE__, __LINE__, print_r($menus,true)));

		/** Check permissions of the associated tpl_id of each menu */
		foreach ($menus as $menuToCheckPermission)
		{
			/** Remove any menu from the Array, if permissions are denied for the user */
			if (!tpl_permission($menuToCheckPermission['read_rights'], $menuToCheckPermission['owner'])) unset($menus[$menuToCheckPermission['tpl_id']]);
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> array($menus) - cleaned: %s', __FILE__, __LINE__, print_r($menus,true)));

		return (count($menus) > 0 ? $menus : false); // If no Menu Entries left after cleanup, then return false
	}

	/**
	 * Smarty Array "$packages"
	 *
	 * Returns all available Packages (PHP-Includes) as an Array
	 * Usage: {$packages}
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/pages/tpleditor.tpl Primarily used in Template-Editor
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `03.05.2020` `IneX` function added from code in `/scripts/packages_overview.php` because {include_php} of it is deprecated in Tpleditor
	 * @since 1.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Vars
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array List with all available Packages
	 */
	private function get_packages_list()
	{
		global $db;

		/** Get all packages from the database */
		$packagesQuery = $db->query('SELECT id, name FROM packages', __FILE__, __LINE__, __FUNCTION__);
		while ($packagesQueryResult = $db->fetch($packagesQuery)) {
			$packages[] = $packagesQueryResult;
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> array($packages): %s', __FILE__, __LINE__, print_r($packages,true)));

		return (count($packages) > 0 ? $packages : false); // If no Packages found, then return false
	}

	/**
	 * List of Online Users
	 *
	 * Array mit allen zur Zeit eingeloggten Usern
	 *
	 * @return array Array mit allen zur Zeit eingeloggten Usern
	 */
	private function get_online_users()
	{
		global $db;

		$online_users = array();
		$sql = 'SELECT id FROM user
				WHERE UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(now()) - '.USER_TIMEOUT.')
				ORDER by activity DESC';
		$e = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while ($d = mysqli_fetch_row($e)) {
			array_push($online_users, $d[0]);
		}
		return $online_users;
	}
}

/**
 * zorg Smarty Handler to register Modifiers
 *
 * These are PHP Functions acting as Smarty Modifiers on Smarty Vars
 * Format: [Variable-Name] => array ([Werte] | [Kategorie] | [Beschreibung] )
 * 
 * This Class contains various functions/function-mappings between
 * PHP and the Smarty Template engine. Further, it takes care
 * of properly registering/assigning the functions or methods
 * to Smarty as Template Functions, so they can be used within
 * Smarty templates using somethling like {$value|custom_modifier}
 *
 * @example Call the (new ZorgSmarty_Modifiers)->register() method to assign ZorgSmarty_Modifiers::$zorg_smarty_modifiers to Smarty
 *
 * @package zorg\Smarty\Templates
 * @author IneX
 * @version 1.0
 * @since 1.0 `04.05.2020` `IneX` Class added
 */
class ZorgSmarty_Modifiers
{
	/** Array where we will put all $var into later */
	private $zorg_smarty_modifiers;

	/**
	 * Class Constructor.
	 *
	 * Takes care of defining $var values & triggers to register them to Smarty
	 * e.g. Function "datename()" will be available in Smarty as Modifier {$timestamp|datename}
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `04.05.2020` `IneX` Constructor added
	 * @since 1.1 `04.05.2020` `IneX` Removed '[Members only true/false]' flag because breaks for not logged-in users
	 *
	 * @uses print_array() Function from util.inc.php
	 */
	public function __construct()
	{
		/**
		 * Assign values to every $var
		 * Format: [Smarty-Modifier] => array ([PHP-Funktion] | [Kategorie] | [Beschreibung])
		 */
		$this->zorg_smarty_modifiers = [
										'datename' => [ ['ZorgSmarty_Modifiers', 'convert_datename'], 'Datum und Zeit', '{$timestamp|datename} konvertiert einen Unix-Timestamp in ein anständiges datum/zeit Format' ]
										// ALLOWED VIA SEC POLICY,'htmlentities' => [ ['ZorgSmarty_Modifiers', 'convert_htmlentities'], 'Variablen', 'Registriert für Smarty den Modifier htmlentities() aus PHP' ]
										,'concat' => [ ['ZorgSmarty_Modifiers', 'do_concat'], 'Variablen', 'Registriert für Smarty den Modifier concat() aus PHP' ]
										,'ltrim' => [ ['ZorgSmarty_Modifiers', 'do_ltrim'], 'Variablen', 'Registriert für Smarty den Modifiert ltrim() aus PHP' ]
										,'print_r' => [ ['ZorgSmarty_Modifiers', 'do_printr'], 'Variablen', 'ACHTUNG {$myarray|@print_r} verwenden!' ]
										,'implode' => [ ['ZorgSmarty_Modifiers', 'do_implode'], 'Variablen', 'String glue' ]
										,'floor' => [ ['ZorgSmarty_Modifiers', 'do_floor'], 'Mathematische Funktionen', 'util' ]
										,'sizebytes' => [ ['ZorgSmarty_Modifiers', 'convert_sizebytes'], 'Variablen', 'stellt z.B: ein "kB" dahinter und konvertiert die zahl.' ]
										,'quantity' => [ ['ZorgSmarty_Modifiers', 'convert_quantity'], 'Variablen', '{$anz|quantity:Zug:Züge}' ]
										,'number_quotes' => [ ['ZorgSmarty_Modifiers', 'convert_number_quotes'], 'Variablen', 'Registriert für Smarty den Modifier number_quotes() aus PHP' ]
										,'maxwordlength' => [ ['ZorgSmarty_Modifiers', 'do_maxwordlength'], 'Variablen', 'Registriert für Smarty den Modifier maxwordlength() aus PHP, 1.param = word length' ]
										,'change_url' => [ ['ZorgSmarty_Modifiers', 'do_change_url'], 'URL Handling', 'newquerystring' ]
										,'strip_anchor' => [ ['ZorgSmarty_Modifiers', 'do_strip_anchor'], 'URL Handling', 'link' ]
										,'ismobile' => [ ['ZorgSmarty_Modifiers', 'check_userismobile'], 'Usersystem', '{$userid|ismobile} ermittelt ob letzter Login eines Users per Mobile war' ]
										,'usergroup' => [ ['ZorgSmarty_Modifiers', 'convert_usergroup'], 'Usersystem', '{$id|usergroup} für tpl schreib / lese rechte' ]
										,'name' => [ ['ZorgSmarty_Modifiers', 'convert_name'], 'Usersystem', 'usersystem' ]
										,'username' => [ ['ZorgSmarty_Modifiers', 'convert_username'], 'Usersystem', '{$userid|username} konvertiert userid zu username' ]
										,'userpic' => [ ['ZorgSmarty_Modifiers', 'convert_userpic'], 'Usersystem', '{$userid|userpic:0} zeigt Userpic für eine User-ID, 1.param = Username anzeigen ja/nein' ]
										,'check_userimage' => [ ['ZorgSmarty_Modifiers', 'check_userimage'], 'Usersystem', '{$userid|@check_userimage} - ersetzt $userid mit Array["typ","pfad"],' ]
										,'userpage' => [ ['ZorgSmarty_Modifiers', 'convert_userpage'], 'Usersystem', '{$userid|userpage:0} mit 1 param: (0) = username or (1) = userpic' ]
										,'locked' => [ ['ZorgSmarty_Modifiers', 'check_template_lock'], 'Smarty', 'Check ob ein Template bearbeitet werden kann oder locked ist. Usage: {$tpl_id|locked}', true ]
										,'nohtml' => [ ['ZorgSmarty_Modifiers', 'do_remove_html'], 'Utilities', 'Entfernt alles HTML aus einem Smarty Variable-String. Usage: {$fancytext|nohtml}', true ]
										,'format_comment' => [ ['ZorgSmarty_Modifiers', 'do_format_comment'], 'Commenting', 'Macht Textformatierungen eines Comments Forum konfom. Usage: {$comment_data.text|format_comment}', true ]

										// Functions not in this Class
										,'print_array' => [ 'print_array', 'Variablen', '{print_array arr=$hans} gibt die Elemente eines Smarty {$array} aus' ]
									];
	}

	/**
	 * Function to register the PHP Functions as Modifiers for Smarty Functions
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "convert_datename()" will be available in Smarty as {$var|datename}
	 *
	 * @example $smarty->registerPlugin('modifier', [template modifier], [php function])
	 *
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `03.01.2016` `IneX` function added
	 * @since 2.0 `01.05.2020` `IneX` Changed to use 'registerPlugin('modifier'...)' from previous 'register_modifier' for compatibility with Smarty 3
	 * @since 2.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Modifiers, disabled generic $documentation
	 *
	 * @global object $smarty Smarty Class-Object
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	public function register()
	{
		global $smarty, $user;

		foreach ($this->zorg_smarty_modifiers as $modifier => $data)
		{
			$smarty->registerPlugin('modifier', $modifier, $data[0]);
		}
	}

	/**
	 * Function to retrieve Smarty Modifiers-documentation array from this Class
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `06.05.2020` `IneX` Method added to output zorgSmarty documentation not on every Template request & with more control
	 *
	 * @return array
	 */
	public function documentation()
	{
		//$docuArray = natcasesort($this->zorg_smarty_modifiers);
		return $this->zorg_smarty_modifiers;
	}

	/**
	 * String, Integer, Date and Array Modifiers
	 */
	public static function convert_datename($content)
	{
		return datename($content);
	}
	/**
	 * @deprecated Use standard PHP htmlentities() method
	 */
	public static function convert_htmlentities($params) {
		return htmlentities($params['text']);
	}
	public static function do_concat($text1, $text2) {
		return $text1.$text2;
	}
	public static function do_ltrim($text, $chars='') {
		if($chars != '') {
			return ltrim($text, $chars);
		} else {
			return ltrim($text);
		}
	}
	public static function do_printr($myarray) {
		return print_r($myarray, true);
	}
	public static function do_implode($myarray, $zeichen) {
		return implode($zeichen, $myarray);
	}
	public static function do_floor($zahl) {
		return floor($zahl);
	}
	public static function convert_sizebytes ($size) {
	   $units = array("B", "kB", "MB", "GB", "TB");
	   $i = 0;
	   while ($size >= 1000 && $i<5) {
		  $i++;
		  $size /= 1024;
	   }
	   $size = round($size, 2);
	   return '<nobr>'.$size.' '.$units[$i].'</nobr>';
	}
	public static function convert_quantity ($count, $singular="", $plural="") {
		if ($count == 1) return $count.' '.$singular;
		else return $count.' '.$plural;
	}
	public static function convert_number_quotes($number, $num_decimal_places='', $dec_seperator='', $thousands_seperator='') {
		if (!$thousands_seperator) $thousands_seperator = '\'';
		if ($thousands_seperator)
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
		else
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
	}
	public static function do_maxwordlength($text, $maxlength) {
		return maxwordlength($text, $maxlength);
	}

	/**
	 * URL Modifiers
	 */
	public static function do_change_url($url, $querystringchanges) {
		return changeURL($url, $querystringchanges);
	}
	public static function do_strip_anchor($url) {
		return substr($url, 0, strpos($url, "#"));
	}

	/**
	 * User Information
	 */
	public static function check_userismobile($userid)
	{
		global $user;
		return $user->ismobile($userid);
	}
	public static function convert_usergroup($groupid)
	{
	   switch ($groupid) {
		  case 0: return "Alle"; break;
		  case 1: return "Normale User"; break;
		  case 2: return "Member &amp; Sch&ouml;ne"; break;
		  case 3: return "Nur Besitzer"; break;
		  default: return "unknown_usergroup";
	   }
	}
	public static function convert_name($userid)
	{
    	global $user;
    	return $user->id2user($userid, false);
    }
    /** converts id to username */
    public static function convert_username($userid)
    {
    	global $user;
    	//Original: return $user->link_userpage($userid);
    	return (is_numeric($userid) && $userid > 0 ? $user->userprofile_link($userid, ['username' => TRUE, 'pic' => FALSE, 'link' => TRUE]): '');
    }
    public static function convert_userpic($userid, $displayName=TRUE)
    {
    	global $user;
    	//Original: return $user->link_userpage($userid, $displayName);
    	//return $user->userpic($userid, $displayName);
    	return $user->userprofile_link($userid, ['username' => $displayName, 'clantag' => TRUE, 'pic' => TRUE, 'link' => TRUE]);
    }
    /**
	 * Smarty Funktion checkimage
	 * Gibt an, ob Userpic als Gravatar existiert oder nur als zorg Userpic
	 * @return array Array mit Userpic-Typ (gravatar / zorg) und dazugehörigem Userpic-Pfad
	 */
    public static function check_userimage($userid)
    {
		global $user;
		if (!empty($userid) && is_numeric($userid)) $userimagePath = $user->checkimage($userid);
		if ($userimagePath !== false) return ['type' => (strpos($userimagePath, 'gravatar') !== false ? 'gravatar' : 'zorg'), 'path' => $userimagePath];
		else return 'false';
    }
    public static function convert_userpage($userid, $pic=0)
    {
    	global $user;
    	//Original: return $user->link_userpage($userid, $pic);
    	return $user->userprofile_link($userid, ['username' => TRUE, 'pic' => $pic, 'link' => TRUE]);
    }

    /**
	 * Smarty |rendertime modifier function
	 *
	 * Type:	Modifier
	 * Name:	timer --> geändert zu "rendertime" damit es verständlicher ist, 07.01.2017/IneX
	 * Date:	Sat Nov 01, 2003
	 * Author:	boots
	 * 
	 * Assuming you do little or nothing after display()
	 * and that you do minimal work before starting the timer,
	 * this technique should be close to actual processing time
	 * close enough that it is splitting hairs, IMHO
	 * Usage:
	 * - in index.php
	 *    util::timer('begin', 'Page'); // starts a timer block with the message 'Page' 
	 *    // other code
	 *    echo 'history: '.util::timer('list'); // returns a history of timed block results 
	 * - in template.tpl
	 *    {"begin"|timer:"template block"}
	 *    template stuff...
	 *    {"end"|timer:true}
	 *    {"stop"|timer:true|nl2br}
	 * Call : $smarty->register_modifier('rendertime', 'rendertime');
	 *
	 * @link https://www.smarty.net/forums/viewtopic.php?p=5750&sid=ede53d9870b3a4cd1fd6a4cfd8cfae1b#5750 Smarty '|timer' modifier function
	 */
	public static function smarty_modifier_rendertime($mode='begin')
	{
		global $_timer_blocks, $_timer_history;
		switch ($mode) {
		case 'begin':
			$_timer_blocks[] =array(microtime(true));
			break;

		case 'end':
			$last = array_pop($_timer_blocks);
			$_start = $last[0];
			list($a_micro, $a_int) = explode(' ', $_start);
			list($b_micro, $b_int) = explode(' ', microtime(true));
			$elapsed = ($b_int - $a_int) + ($b_micro - $a_micro);
			$_timer_history[] = [ $elapsed ];
			return $elapsed;
			break;

		case 'list':
			$o = '';
			foreach ($_timer_history as $mark) {
				$o .= $mark[2] . " \n";
			}
			return $o;
			break;

		case 'stop':
			$result = '';
			while(!empty($_timer_blocks)) {
				$result .= self::smarty_modifier_rendertime('end');
			}
			return $result;
			break;
		}
	}

	/**
	 * Modifier: Check if a Template is already locked for editing
	 *
	 * Migriert aus dem PHP file /scripts/tpleditor.php wegen Smarty 3.1 Kompatibilität weil {include_php} deprecated ist.
	 * Usage: {$tpl_id|locked}
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `03.05.2020` `IneX` function added from code in `/scripts/tpleditor.php` because {include_php} of it is deprecated in Tpleditor
	 * @since 1.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Vars
	 *
	 * @uses tpleditor_access_lock()
	 * @param integer $template_id ID of the Template to check if locked - or not
	 * @return bool|string Returns boolean 'false' if Template is not locked (= free to edit), or Error message string if locked already
	 */
	public static function check_template_lock($tpl_id)
	{
		$access_error = null;

		if (is_numeric($tpl_id) && $tpl_id > 0)
		{
			tpleditor_access_lock($tpl_id, $access_error);

			if (!empty($access_error)) return $access_error;
			else return false;
		} else {
			return t('invalid-id', 'tpl', [$tpl_id]);
		}
	}

	/**
	 * Entfert alles HTML aus einem Smarty Variable-String.
	 *
	 * @example {$fancytext|nohtml}
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `09.05.2020` `IneX` Method added
	 *
	 * @uses remove_html()
	 * @param string $text Any Text String (containing HTML)
	 * @return string Returns input $text but stripped from all HTML
	 */
	public static function do_remove_html($text)
	{
		return remove_html($text);
	}

	/**
	 * Macht Textformatierungen eines Comments Forum konfom.
	 *
	 * @example {$comment_data.text|format_comment}
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `09.05.2020` `IneX` Method added
	 *
	 * @uses Comment:formatPost()
	 * @param string $comment_text Comment-Text String
	 * @return string Returns input $comment_text checked and formatted (e.g. error message on illegal tag detection)
	 */
	public static function do_format_comment($comment_text)
	{
		return Comment::formatPost($comment_text);
	}
}

/**
 * zorg Smarty Handler to register Blocks
 *
 * These are PHP Functions to be used with Values encapsulated in Smarty Blocks
 * Format: Format: [Block] => array ([PHP-Funktion] | [Kategorie] | [Beschreibung]
 * 
 * This Class contains various functions/function-mappings between
 * PHP and the Smarty Template engine. Further, it takes care
 * of properly registering/assigning the functions or methods
 * to Smarty as Template Function-BLocks, so they can be used within
 * Smarty templates using somethling like {member}Secret text for Members only{/member}
 *
 * @example Call the (new ZorgSmarty_Blocks)->register() method to assign ZorgSmarty_Blocks::$zorg_smarty_blocks to Smarty
 *
 * @package zorg\Smarty\Templates
 * @author IneX
 * @version 1.0
 * @since 1.0 `05.05.2020` `IneX` Class added
 */
class ZorgSmarty_Blocks
{
	/** Array where we will put all $var into later */
	private $zorg_smarty_blocks;

	/**
	 * Class Constructor.
	 *
	 * Takes care of defining $var values & triggers to register them to Smarty
	 * e.g. Function "member()" will be available in Smarty as Block {member}Text for Members only{/member}
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `04.05.2020` `IneX` Constructor added
	 * @since 1.1 `04.05.2020` `IneX` Removed '[Members only true/false]' flag because breaks for not logged-in users
	 */
	public function __construct()
	{
		/**
		 * Assign values to every $var
		 * Format: [Smarty-Block] => array ([PHP-Funktion] | [Kategorie] | [Beschreibung])
		 */
		$this->zorg_smarty_blocks = [
									 'substr' => [ ['ZorgSmarty_Blocks', 'do_substr'], 'Variablen', '{substr from=2 to=-1}text{/substr} => gleich wie php-fnc substr(text, start, length)' ]
									,'trim' => [ ['ZorgSmarty_Blocks', 'do_trim'], 'Variablen', 'text modification' ]
									,'link' => [ ['ZorgSmarty_Blocks', 'make_link'], 'HTML', '{link tpl=x param="urlparams"}text{/link} => default tpl = das aktuelle' ]
									,'button' => [ ['ZorgSmarty_Blocks', 'make_button'], 'HTML', '{button tpl=x param="urlparams"}button-text{/button}' ]
									,'form' => [ ['ZorgSmarty_Blocks', 'make_form'], 'HTML', '{form param="urlparams" formid=23 upload=1}..{/form}' ]
									,'table' => [ ['ZorgSmarty_Blocks', 'make_table'], 'HTML', 'layout, table' ]
									,'tr' => [ ['ZorgSmarty_Blocks', 'make_table_row'], 'HTML', 'layout, table > tr' ]
									,'td' => [ ['ZorgSmarty_Blocks', 'make_table_cell'], 'HTML', 'layout, table > tr > td' ]
									,'member' => [ ['ZorgSmarty_Blocks', 'make_member_text'], 'Layout', 'Usage: {member}..{/member} or {member noborder=1}..{/member}' ]
									,'mail_infoblock' => [ ['ZorgSmarty_Blocks', 'add_mailinfoblock'], 'Verein Mailer', 'Info Block {mail_infoblock topic="headline"}...{/mail_infoblock}' ]
									,'mail_button' => [ ['ZorgSmarty_Blocks', 'add_mailctabutton'], 'Verein Mailer', 'Call-to-Action-Button {mail_button style="NULL|secondary" position="left|center|right" action="mail|link" href="url"}button-text{/mail_button}' ]
									,'telegram_button' => [ ['ZorgSmarty_Blocks', 'add_mailtelegrambutton'], 'Verein Mailer', 'Telegram Messenger Button {telegram_button}button-text{/telegram}' ]
									,'menubar' => [ ['ZorgSmarty_Blocks', 'make_menubar'], 'Layout', 'menu' ]
									,'menuitem' => [ ['ZorgSmarty_Blocks', 'make_menuitem'], 'Layout', 'menu' ]
									,'new_link' => [ ['ZorgSmarty_Blocks', 'make_link_new_tpl'], 'Smarty Template', 'Shows a link to the Tpleditor with new Template.' ]
									,'edit_link' => [ ['ZorgSmarty_Blocks', 'make_link_edit_tpl'], 'Smarty Template', '{edit_link tpl=x} Link zum Tpleditor, default ist aktuelles tpl' ]

									// Functions not in this Class
									//none yet
								];
	}

	/**
	 * Function to register PHP Function Outputs as HTML-Blocks for Smarty Templates
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function-Block
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 *
	 * @example $smarty->registerPlugin('block', [template block], [php function])
	 *
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `03.01.2016` `IneX` function added
	 * @since 2.0 `01.05.2020` `IneX` Changed to use 'registerPlugin('block'...)' from previous 'register_block' for compatibility with Smarty 3
	 * @since 2.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Blocks, disabled generic $documentation
	 *
	 * @global object $smarty Smarty Class-Object
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	public function register()
	{
		global $smarty, $user;

		foreach ($this->zorg_smarty_blocks as $block => $data)
		{
			$smarty->registerPlugin('block', $block, $data[0]);
		}
	}

	/**
	 * Function to retrieve Smarty Blocks-documentation array from this Class
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `06.05.2020` `IneX` Method added to output zorgSmarty documentation not on every Template request & with more control
	 *
	 * @return array
	 */
	public function documentation()
	{
		//$docuArray = natcasesort($this->zorg_smarty_blocks);
		return $this->zorg_smarty_blocks;
	}

	/**
	 * String, Integer, Date und Array HTML-Ausgabe
	 */
	/**
	 * Return part of a string.
	 * @example {substr from=start to=length}subject{/substr}
	 */
	public static function do_substr($params, $content, $smarty, &$repeat)
	{
	   if (isset($params['to'])) {
		  return substr($content, $params['from'], $params['to']);
	   }else{
		  return substr($content, $params['from']);
	   }
	}
	public static function do_trim($params, $content, $smarty, &$repeat)
	{
		if ($content) return trim($content);
	}

	/**
	 * HTML Elemente
	 */
	/**
	 * gibt einen link aus
	 *
	 * @version 1.0
	 * @since 1.0 `[z]biko` public static function added
	 */
	public static function make_link($params, $content, $smarty, &$repeat)
	{
		if (!$repeat)
		{
			if (!$content) $content = 'link';
			return '<a href="'.ZorgSmarty_Functions::show_internal_link($params).'">'.$content.'</a>';
		}
	}
	/**
	 * gibt einen button als link aus
	 *
	 * @version 1.1
	 * @since 1.0 `[z]biko` public static function added
	 * @since 1.1 `29.09.2019` `IneX` added optional $params[class] setting
	 */
	public static function make_button($params, $content, $smarty, &$repeat)
	{
		return '<input type="button" value="'.$content.'" onClick="self.location.href=\''.ZorgSmarty_Functions::show_internal_link($params).'\'" '.($params['class'] ? 'class="'.$params['class'].'"' : '').'>';
	}
	/**
	 * HTML <form>-tag
	 * returns an opening-tag for a html-form. action is always 'smarty.php'
	 * if you set the parameter 'formid', a hidden input with this formid is added.
	 *
	 * @version 1.1
	 * @since 1.0 `[z]biko` public static function added
	 * @since 1.1 `29.09.2019` `IneX` added autocomplete=off as default
	 */
	public static function make_form($params, $content, $smarty, &$repeat)
	{
		$ret = null;

		if (!$_GET['tpl']) $_GET['tpl'] = '0';

		if ($params['url']) $url = $params['url'];
		elseif ($params['action']) $url = '/actions/'.$params['action'].'?'.url_params();
		else $url = "/?".url_params();

		if ($params['param']) $url .= '&'.$params['param'];

		$ret .= '<form method="post" action="'.$url.'" autocomplete="off" ';
		if ($params['upload']) $ret .= 'enctype="multipart/form-data"';
		$ret .= '>';
		if ($params['formid']) $ret .= '<input name="formid" type="hidden" value="'.$params['formid'].'">';
		$ret .= $content;
		$ret .= '</form>';

		return $ret;
    }

	public static function make_table($params, $content, $smarty, &$repeat)
	{
		global $table_color, $tr_count, $table_align, $table_valign;

		if (!$content)
		{
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
		} else {
			$out = '<table ';
			foreach ($params as $key => $value) {
				if (!in_array($key, array("align", "valign", "nocolor")))
					$out .= $key.'="'.$value.'" ';
			}
			$out .= '>'.$content.'</table>';

			return $out;
		}
	}

	public static function make_table_row($params, $content, $smarty, &$repeat)
	{
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
		} else {
			$out = '<tr ';
			foreach ($params as $key => $value) {
				if (!in_array($key, array("align", "valign", "title")))
					$out .= $key.'="'.$value.'" ';
			}
			$out .= '>'.$content.'</tr>';

			return $out;
		}
	}

	public static function make_table_cell ($params, $content, $smarty, &$repeat) {
		global $table_color, $table_align, $table_valign, $tr_count, $tr_title, $tr_align, $tr_valign, $user;

		if (!$content) {
			// do nothing...
		} else {
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
				$out .= 'bgcolor="'.NEWCOMMENTCOLOR.'" ';
			} else {
				if (!$params['bgcolor'] && $table_color) {
					if (! ($tr_count % 2)) $out .= 'bgcolor="'.TABLEBACKGROUNDCOLOR.'" ';
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

	public static function make_member_text($params, $content, $smarty, &$repeat)
	{
		global $user;

		if ($content)
		{
			if ($user->typ == USER_MEMBER)
			{
				if ($params['width']) $width = "width='$params[width]'";
				else $width = '';

				if (!$params['noborder'])
				{
					return
						"<table class='border' cellspacing=0 cellpadding=3 $width>".
							"<tr><td align='left' bgcolor=".TABLEBACKGROUNDCOLOR."><b><font color='red'>Member only:</font></b></td></tr>".
							"<tr><td>$content</td></tr>".
						"</table>"
					;
				} else {
					return $content;
				}
			} else {
				return '';
			}
		}
	}

	/**
	 * Verein Mailer - Info Block
	 *
	 * @example {mail_infoblock topic="headline"}content{/mail_infoblock}
	 */
	public static function add_mailinfoblock($params, $content, $smarty, &$repeat)
	{
		//if (!$repeat) {  // closing tag
		$smarty->assign('infoblock', ['topic' => $params['topic'], 'text' => $content ]);
		return $smarty->fetch('file:email/verein/elements/block_info.tpl');
		//}
	}
	
	/**
	 * Verein Mailer - Call-to-Action Button Block
	 *
	 * @example {mail_button style="NULL|secondary" position="left|center|right" action="mail|link" href="url"}button-text{/mail_button}
	 */
	public static function add_mailctabutton($params, $content, $smarty, &$repeat)
	{
		if ($params['position'] == 'left') $ctaposition = 'float:left';
		if ($params['position'] == 'right') $ctaposition = 'float:right;';
		if ($params['position'] == 'center') $ctaposition = 'margin:0 auto';
		$smarty->assign('cta', [
								 'style' => $params['style']
								,'position' => $ctaposition
								,'action' => $params['action']
								,'href' => $params['href']
								,'text' => $content
							]);
		return $smarty->fetch('file:email/verein/elements/block_ctabutton.tpl');
	}
	
	/**
	 * Verein Mailer - Telegram Messenger Button Block
	 *
	 * @example {telegram_button}button-text{/telegram_button}
	 */
	public static function add_mailtelegrambutton($params, $content, $smarty, &$repeat)
	{
		$smarty->assign('telegrambtn', [
										 'text' => (!empty($content) ? $content : 'Telegram Chat beitreten' )
										,'href' => TELEGRAM_CHATLINK
									]);
		return $smarty->fetch('file:email/verein/elements/block_telegrambutton.tpl');
	}

	/**
	 * Smarty Menubar
	 *
	 * Usage: 	{menubar}
	 *				{menuitem tpl=x group="x"}text{/menuitem}
	 *				{menuitem...}
	 *			{/menubar}
	 *
	 * @author [z]biko
	 * @version 2.0
	 * @since 1.0 `[z]biko` migrated from smarty_menu.php (smarty_menu_old)
	 * @since 2.0 `02.07.2019` `IneX` moved inline HTML to block_menubar.tpl, output changed to Array
	 *
	 * @uses self::make_menuitem()
	 * @uses Smarty::getTemplateVars()
	 * @return object
	 */
	public static function make_menubar($params, $content, $smarty, &$repeat)
	{
		global $user;
		$vars = $smarty->getTemplateVars();

		/** One iteration only */
		if (!$repeat)
		{
			$smarty->assign('menubar_content', [
								 'edit_link' => (tpl_permission($vars['tpl']['write_rights'], $vars['tpl']['owner']) ? ZorgSmarty_Functions::edit_link_url($vars['tpl']['id']) : null )
								,'items' => preg_replace('/<\/a> *<a/', '</a><a', trim($content))
							]);
			return $smarty->fetch('file:layout/elements/block_menubar.tpl');
		}
	}
	/**
	 * Smarty Menubar Menu-Item Link
	 *
	 * Usage: {menuitem tpl=TplID-Link|url=URL-Link group="all|guest|user|member"}menuitem text{/menuitem}
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 `[z]biko` migrated from smarty_menu.php (smarty_menu_old)
	 *
	 * @uses USER_MEMBER
	 * @uses self::make_link()
	 * @return string
	 */
	public static function make_menuitem($params, $content, $smarty, &$repeat)
	{
		global $user;

		/** One iteration only */
		if (!$repeat)
		{
			if (!isset($params['group'])) $params['group'] = "all";
			if(
				$params['group'] == "all"
				|| $params['group'] == "guest" && !$user->id
				|| $params['group'] == "user" && $user->id
				|| $params['group'] == "member" && $user->typ==USER_MEMBER
			) {
				if (!$content) $content = "???";
				return '<a href="'.ZorgSmarty_Functions::show_internal_link($params).'">'.trim($content).'</a>';
			}
		}
	}

	/**
	 * Smarty Templates
	 *
	 * @uses Smarty::getTemplateVars()
	 * @uses url_params()
	 */
	public static function make_link_new_tpl($params, $content, $smarty, &$repeat)
	{
		global $smarty;

		$vars = $smarty->getTemplateVars();

		return '<a href="/?tpleditor=1&tplupd=new&location='.base64_encode($_SERVER['PHP_SELF'].'?'.url_params()).'">'.$content.'</a>';
	}
	/**
	 * @uses tpl_permission()
	 * @uses ZorgSmarty_Functions::edit_link_url()
	 */
	public static function make_link_edit_tpl($params, $content, $smarty, &$repeat)//$text='[edit]', $tpl=0, $rights=0, $owner=0)
	{
		global $db;

		$text = (!is_string($content) ? '[edit]' : $content);
		$tpl = (!is_numeric($params['tpl']) || $params['tpl'] <= 0 ? (integer)$_GET['tpl'] : (integer)$params['tpl']);

		if (!$repeat)
		{
			if ($tpl && (!$rights || !$owner))
			{
				$d = $db->fetch($db->query('SELECT * FROM templates WHERE id='.$tpl, __FILE__, __LINE__, __METHOD__));
				$rights = $d['write_rights'];
				$owner = $d['owner'];
			}

			if ($tpl && tpl_permission($rights, $owner))
			{
				return '<a href="'.ZorgSmarty_Functions::edit_link_url($tpl).'">'.$text.'</a>';
			} else {
				return "";
			}
		}
	}
	/**
	 * smarty_edit_link.
	 *
	 * @deprecated Nowhere used? (IneX, 05.05.2020)
	 * @TODO remove function (IneX, 05.05.2020)
	 */
	public static function smarty_edit_link ($params, $content, $smarty, &$repeat)
	{
		if (!$repeat) {  // closing tag
			if ($params['tpl']) {
				$tpl = $params['tpl'];
			}else{
				$vars = $smarty->getTemplateVars();
				$tpl = $vars['tpl']['id'];
				$rights = $vars['tpl']['write_rights'];
				$owner = $vars['tpl']['owner'];
			}

			return edit_link($content, $tpl, $rights, $owner);
		}
	}
}

/**
 * zorg Smarty Handler to register Blocks
 *
 * These are PHP Functions to be used with Values encapsulated in Smarty Blocks
 * Format: [Smarty Function Name] = array( array(PHP-Klasse,PHP-Funktion)|[PHP-Funktion], [Kategorie], [Beschreibung], [Compiler Function true/false])
 * 
 * This Class contains various functions/function-mappings between
 * PHP and the Smarty Template engine. Further, it takes care
 * of properly registering/assigning the functions or methods
 * to Smarty as Template Functions, so they can be used within
 * Smarty templates using somethling like {daily_pic} or {apod}
 *
 * @example Call the (new ZorgSmarty_Functions)->register() method to assign ZorgSmarty_Functions::$zorg_smarty_functions to Smarty
 *
 * @package zorg\Smarty\Templates
 * @author IneX
 * @version 1.0
 * @since 1.0 `05.05.2020` `IneX` Class added
 */
class ZorgSmarty_Functions
{
	/** Array where we will put all $var into later */
	private $zorg_smarty_functions;

	/**
	 * Class Constructor.
	 *
	 * Takes care of defining $var values & triggers to register them to Smarty
	 * e.g. Function "getDailyThumb()" will be available in Smarty as Function {daily_pic}
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `04.05.2020` `IneX` Constructor added
	 * @since 1.1 `04.05.2020` `IneX` Removed '[Members only true/false]' flag because breaks for not logged-in users
	 */
	public function __construct()
	{
		/**
		 * Assign values to every $var
		 *
		 * Format: [Smarty Function Name] = array( array(PHP-Klasse,PHP-Funktion)|[PHP-Funktion], [Kategorie], [Beschreibung], [Compiler Function true/false])
		 */
		$this->zorg_smarty_functions = [
										 'smarty_documentation' => [ ['ZorgSmarty_Functions', 'get_zorgsmarty_documentation'], 'Smarty', 'Function to assign Array with zorgSmarty documentation details', false ]
										,'assign_stocklist' => [ ['ZorgSmarty_Functions', 'stockbroker_assign_stocklist'], 'Stockbroker', 'Stockbroker', false ]
										,'assign_stock' => [ ['ZorgSmarty_Functions', 'stockbroker_assign_stock'], 'Stockbroker', 'Stockbroker', false ]
										,'assign_searchedstocks' => [ ['ZorgSmarty_Functions', 'stockbroker_assign_searchedstocks'], 'Stockbroker', 'Stockbroker', false ]
										,'update_kurs' => [ ['ZorgSmarty_Functions', 'stockbroker_update_kurs'], 'Stockbroker', 'Stockbroker', false ]
										,'getkursbought' => [ ['ZorgSmarty_Functions', 'stockbroker_getkursbought'], 'Stockbroker', 'Stockbroker', false ]
										,'getkurs' => [ ['ZorgSmarty_Functions', 'stockbroker_getkurs'], 'Kategorie', 'Stockbroker', false ]
										,'assign_artikel' => [ ['ZorgSmarty_Functions', 'get_tauschartikel'], 'Tauschbörse', 'Tauschbörse Artikeldetails als Array assignen. Usage: {assign_artikel id=artikel_id} => {$artikel}', false ]
										,'formfielduserlist' => [ ['ZorgSmarty_Functions', 'show_formfield_notify_users'], 'Usersystem', 'usersystem', false ]
										,'addle_highscore' => [ ['ZorgSmarty_Functions', 'show_addle_highscores'], 'Addle', 'Addle Highscores Table. Usage: {addle_highscore anzahl=23}', false ]
										,'random_quote' => [ ['ZorgSmarty_Functions', 'show_quote_random'], 'Quotes', '{random_quote} display a random quote', false ]
										,'daily_quote' => [ ['ZorgSmarty_Functions', 'show_dailyquote'], 'Quotes', '{daily_quote} display a daily quote', false ]
										,'poll' => [ ['ZorgSmarty_Functions', 'show_poll'], 'Polls', 'Show a specicif Poll. Usage: {poll id=23}', false ]
										,'apod' => [ ['ZorgSmarty_Functions', 'show_apod'], 'APOD', 'Astronomy Picture of the Day (APOD) anzeigen. Usage: {apod}', false ]
										,'assign_yearevents' => [ ['ZorgSmarty_Functions', 'get_yearevents'], 'Events', 'events', false ]
										,'assign_event' => [ ['ZorgSmarty_Functions', 'get_event'], 'Events', 'events', false ]
										,'assign_visitors' => [ ['ZorgSmarty_Functions', 'get_event_visitors'], 'Events', 'events', false ]
										,'assign_event_hasjoined' => [ ['ZorgSmarty_Functions', 'get_event_user_joined'], 'Events', 'events', false ]
										,'event_hasjoined' => [ ['ZorgSmarty_Functions', 'show_event_user_joined'], 'Events', 'events', false ]
										,'assign_rezepte' => [ ['ZorgSmarty_Functions', 'get_rezepte_list'], 'Rezepte', 'List von Rezepten nach Kategorie. Usage: {assign_rezepte category="kategorie"}', false ]
										,'assign_rezept' => [ ['ZorgSmarty_Functions', 'get_rezept'], 'Rezepte', 'Details eines spetifischen Rezeptes. Usage: {assign_rezept id=rezept_id}', false ]
										,'assign_rezept_voted' => [ ['ZorgSmarty_Functions', 'get_rezept_user_voted'], 'Rezepte', 'Check ob für spezifisches Rezepte voted wurde vom User.', false ]
										,'assign_rezept_score' => [ ['ZorgSmarty_Functions', 'get_rezept_score'], 'Rezepte', 'Voting Score eines spezifischen Rezeptes abfragen.', false ]
										,'url' => [ ['ZorgSmarty_Functions', 'show_internal_link'], 'URL Handling', '&lt;a href={link id=x word="x" param="urlparams"}&gt; => default tpl ist das akutelle', false ]
										,'get_changed_url' => [ ['ZorgSmarty_Functions', 'show_changed_url'], 'URL Handling', 'Usage: {get_changed_url change="key=value"}', false ]
										,'url_exists' => [ ['ZorgSmarty_Functions', 'get_url_exists'], 'URL Handling', 'Utilities Funktion urlExists() um eine URL/Pfad zu validieren. Usage: {url_exists url="[url]" assign="[smarty-variable]"}', false ]
										,'spc' => [ ['ZorgSmarty_Functions', 'show_space'], 'HTML', 'Add some space. Usage: {space i=5}', false ]										
										,'sizeof' => [ ['ZorgSmarty_Functions', 'show_sizeof_array'], 'Smarty Templates', 'Display size of Array elements. Usage: {sizeof array=$arrayVar}', false ]
										,'rand' => [ ['ZorgSmarty_Functions', 'show_random_number'], 'Variablen', 'Get a random number. Usage: {rand min=2 max=10 assign=var}', false ]
										,'gettext' => [ ['ZorgSmarty_Functions', 'show_textfile'], 'File Manager', 'files / filemanager', false ]
										,'commentingsystem' => [ ['ZorgSmarty_Functions', 'show_commentingsystem'], 'Commenting', 'Fügt ein spezifisches Commenting System einem Tpl an. Usage: {commentingsystem board="e" thread_id=$event_id}', false ]
										// DEPRECATED: ,'comments' => [ ['ZorgSmarty_Functions', 'show_comments'], 'Commenting', 'Fügt Commenting System zu einem Page Tpl an. Usage: {comments}', false ]
										,'comment_colorfade' => [ ['ZorgSmarty_Functions', 'show_comment_colorfade'], 'Commenting', 'Comment Color-Fade HEX-Color ausgeben. Usage: {comment_colorfade depth=$size color=$color.newcomment}', false ]
										,'comment_get_link' => [ ['ZorgSmarty_Functions', 'get_comment_url'], 'Commenting', 'Comment URL generieren. Usage: {comment_get_link board=f thread_id=23}', false ]
										,'show_thread_link' => [ ['ZorgSmarty_Functions', 'show_comment_thread_link'], 'Commenting', 'HTML-Link zu einem Thread anzeigen. Usage: {show_thread_link board=f thread_id=23}', false ]
										,'comment_extend_depth' => [ ['ZorgSmarty_Functions', 'get_comment_num_childposts'], 'Commenting', 'Num Childposts als "$hdepth" assignen für "Additional posts"-Linkleiste. Usage: {comment_extend_depth depth=5 childposts=23 rcount=$numChildposts}', false ]
										,'comment_remove_depth' => [ ['ZorgSmarty_Functions', 'get_comment_num_parentposts'], 'Commenting', 'Num Parentposts als "$hdepth" assignen für "^^^ Additional posts ^^^"-Linkleiste. Usage: {comment_remove_depth depth=5}', false ]
										,'comment_mark_read' => [ ['ZorgSmarty_Functions', 'do_comment_mark_read'], 'Commenting', 'Einen bestimmten Comment für einen User als gelesen markieren. Usage: {comment_mark_read comment_id=235 user_id=$user->id}', false ]
										,'latest_threads' => [ ['ZorgSmarty_Functions', 'show_latest_threads'], 'Forum', '{latest_threads}', false ]
										,'latest_comments' => [ ['ZorgSmarty_Functions', 'show_latest_comments'], 'Commenting', 'Letzte comments aus board (optional). Usage: {latest_comments anzahl=10 board=t title="Tabellen-Titel"}', false ]
										,'3yearold_threads' => [ ['ZorgSmarty_Functions', 'show_3yearold_threads'], 'Forum', '{3yearold_threads}', false ]
										,'unread_comments' => [ ['ZorgSmarty_Functions', 'show_latest_unread_comments'], 'Forum', '{unread_comments board=t title="Tabellen-Titel"}', false ]
										,'forum_boards' => [ ['ZorgSmarty_Functions', 'show_forum_boards'], 'Forum', '{forum_boards boards=$user->forum_boards_unread updatable=true/false}', false ]
										,'error' => [ ['ZorgSmarty_Functions', 'show_message_error'], 'System', 'Print an error-message. Usage: {error msg="Fehler!"}', false ]
										,'state' => [ ['ZorgSmarty_Functions', 'show_message'], 'System', 'Print a message. Usage: {state msg="Update erfolgreich"}', false ]
										,'edit_url' => [ ['ZorgSmarty_Functions', 'get_tpl_edit_url'], 'Smarty Templates', '{edit_url tpl=x} tpl ist optional. default: aktuelles tpl.', false ]
										,'latest_updates' => [ ['ZorgSmarty_Functions', 'show_templates_latest_updates'], 'Smarty Templates', '{latest_updates anzahl=5} HTML-Table mit den letzten Smarty-updates', false ]
										,'menu' => [ ['ZorgSmarty_Functions', 'show_menu_tpl'], 'Layout', 'Select and show all Navigation Menus for a specific template. Usage: {menu name=menubar_name}', false ]
										,'users_on_pic' => [ ['ZorgSmarty_Functions', 'get_users_in_pic'], 'Gallery', 'Get Users tagged in specific Gallery Pic. Usage: {users_on_pic picID=523}', false ]
										,'random_albumpic' => [ ['ZorgSmarty_Functions', 'show_random_albumpic'], 'Gallery', 'Show a random Thumbnail Pic of a specific Gallery. Usage: {random_albumpic album_id=41}', false ]
										,'top_pics' => [ ['ZorgSmarty_Functions', 'show_top_rated_pics'], 'Gallery', 'Returns a specific amount of best rated images from a given gallery. Usage: {top_pics album=41 limit=1}', false ]
										,'user_pics' => [ ['ZorgSmarty_Functions', 'show_user_tagged_pics'], 'Gallery', 'Show a Users tagged Pics from Gallery. Usage: {user_pics user=id limit=max}', false ]
										,'assign_chatmessages' => [ ['ZorgSmarty_Functions', 'get_chatmessages'], 'Chat', 'List Chatmessages from Chatlog. Usage: {assign_chatmessages page=1 anzahl=5} => {$chatmessages|@print_r}', false ]
										,'sql_errors' => [ ['ZorgSmarty_Functions', 'list_sqlerrors'], 'System', 'List SQL errors from Error Handling. Usage: {sql_errors num=23 order=3 oby=1}', false ]
										,'logerror' => [ ['ZorgSmarty_Functions', 'log_phperror'], 'System', 'Log an Error to the PHP Error Log. Usage: {logerror tpl=[templatename] line=[integer] string="[custom text]"}', 'errorhandling', false ]
										,'user_notifications' => [ ['ZorgSmarty_Functions', 'user_notifications'], 'Layout', 'Assigns an Array-Variable to Smarty containing all User Notifications. Usage: {user_notifications assignto="[smarty-variable]"}', false ]
										,'tploverview' => [ ['ZorgSmarty_Functions', 'get_templates_list'], 'Smarty Templates', 'Sets different Variables containing infos on a list of all Templates (where user has access to). Usage: {tploverview sort=[id|title|word|owner|last_update] order=[ASC|DESC]}', false ]
										,'template_available_settings' => [ ['ZorgSmarty_Functions', 'get_tpleditor_settings'], 'Assigns various Variable to Smarty containing available Template-Settings used in Tpleditor. Usage: {template_available_settings}', false ]
										,'load_tpl_settings' => [ ['ZorgSmarty_Functions', 'get_tpl_settings'], 'Smarty Templates', 'Assigns an Array-Variable to Smarty containing all Settings of a given Templated as per passed Template-ID. Usage: {tpl_settings id=TemplateID assignto=newSmartyVarName}', false ]

										// Smarty Compiler Functions
										,'menuname' => [ ['ZorgSmarty_Functions', 'echo_menuname'], 'Layout', 'Compiler Funktion: echo() eines Menus basierend auf dessen Menuname (retourniert PHP)', true ]

										// Smarty Plugins (third-party)
										,'assign_array' => [ ['ZorgSmarty_Functions', 'assign_array'], 'Variablen', 'erlaubt es, mit Smarty Arrays zu erzeugen', false ]

										// Functions not in this Class
										,'url_params' => [ 'url_params', 'URL Handling', 'Utilities function url_params() to get current URL Query-Parameters. Usage: {url_params}', false ]
										,'random_pic' => [ 'getRandomThumb', 'Gallery', '{random_pic}  displays a random thumb out of all gallery albums', false ]
										,'daily_pic' => [ 'getDailyThumb', 'Gallery', '{daily_pic} displays the pic of the day', false ]
									];
	}

	/**
	 * Function to register PHP Functions to be used in / map to Smarty Template Functions
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getDailyThumb()" will be available in Smarty as {daily_pic param1=x param2=y}
	 *
	 * @example $smarty->registerPlugin('function', [template function], [php function])
	 * @example $smarty->registerPlugin('function', [template function], array([php class], [php function]))
	 * @example $smarty->registerPlugin('compiler', [template function], [php function], [cacheable true/false])
	 *
	 * @author IneX
	 * @version 3.1
	 * @since 1.0 `03.01.2016` `IneX` function added
	 * @since 2.0 `19.08.2018` `IneX` added support for registering array($class, $method)
	 * @since 3.0 `01.05.2020` `IneX` Changed to use 'registerPlugin('function'|'compiler'...)' from previous 'register_function' for compatibility with Smarty 3
	 * @since 3.1 `05.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions, disabled generic $documentation
	 *
	 * @global object $smarty Smarty Class-Object
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	public function register()
	{
		global $smarty, $user;

		foreach ($this->zorg_smarty_functions as $smarty_function => $data)
		{
			if (!$data[3]) $smarty->registerPlugin('function', $smarty_function, $data[0]);  // Register regular Functions
			else $smarty->registerPlugin('compiler', $smarty_function, $data[0], false); // Register Compiler Functions
		}
	}

	/**
	 * Function to retrieve Smarty Functions-documentation array from this Class
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `06.05.2020` `IneX` Method added to output zorgSmarty documentation not on every Template request & with more control
	 *
	 * @return array
	 */
	public function documentation()
	{
		//$docuArray = natcasesort($this->zorg_smarty_functions);
		return $this->zorg_smarty_functions;
	}

	/**
	 * Function to assign Array with zorgSmarty documentation details in a Smarty Template
	 *
	 * Assigns an Array in Smarty with the documentation details of Smarty Functions, Modifiers, Blocks or Vars.
	 *
	 * @example {smarty_documentation assignto='arrayVarName' type='vars|modifiers|blocks|functions'}{$arrayVarName|@print_r}
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `06.05.2020` `IneX` Method added to output zorgSmarty documentation not on every Template request & with more control
	 *
	 * @uses findStringInArray() Search for a matching $string in an Array, from util.inc.php
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @param array $params Akzeptiert: 'type=vars|modifiers|blocks|functions' und 'assignto=arrayVarName'
	 * @param object $smarty (Pass by reference) Smarty Class-Object
	 */
	public function get_zorgsmarty_documentation($params, $smarty)
	{
		global $user;

		$allowedDocuTypes = ['vars', 'modifiers', 'blocks', 'functions'];
		$docuType = (isset($params['type']) && is_string($params['type']) ? (!findStringInArray(strtolower($params['type']), $allowedDocuTypes) ? null : strtolower($params['type'])) : null);
		$smartyAssignTo = (isset($params['assignto']) && is_string($params['assignto']) ? $params['assignto'] : $docuType);

		if (!empty($docuType))
		{
			/** Build Class-Name and Class-Var-Name to retrieve Docu from */
			$documentationArray = [];
			$classNameForDocuVar = 'ZorgSmarty_'.ucfirst($docuType); // e.g. ZorgSmarty_Modifiers()
			//$docuVarName = 'zorg_smarty_'.$docuType; // e.g. $zorg_smarty_modifiers
			$loadDocumentationFromClassvar = (new $classNameForDocuVar)->documentation();
	
			foreach ($loadDocumentationFromClassvar as $smarty_name => $data)
			{
				switch ($docuType)
				{
					case 'vars':
						$documentationArray['{'.$smarty_name.'}'] = [ 'category' => $data[1], 'description' => $data[2], 'assign_by_ref' => $data[3] ];
						break;
	
					case 'modifiers':
						$documentationArray['{'.$smarty_name.'}'] = [ 'category' => $data[1], 'description' => $data[2] ];
						break;
	
					case 'blocks':
						$documentationArray['{'.$smarty_name.'}'] = [ 'category' => $data[1], 'description' => $data[2] ];
						break;
	
					case 'functions':
						$documentationArray['{'.$smarty_name.'}'] = [ 'category' => $data[1], 'description' => $data[2], 'compiler_function' => $data[3] ];
						break;
				}
			}
		} else {
			$documentationArray[$docuType] = [ 'category' => 'ERROR', 'description' => 'Unknown "type" for {smarty_documentation}.' ];
		}

		/** Pass the Notifications-Array to Smarty */
		$smarty->assign($smartyAssignTo, $documentationArray);
	}

	/** Stockbroker */
		public static function stockbroker_assign_stocklist($params, $smarty) {
			$smarty->assign("stocklist", Stockbroker::getStocklist($params['anzahl'], $params['page']));
			//{assign_stocklist anzahl=100 page=$smarty.get.page}
		}
		public static function stockbroker_assign_stock($params, $smarty) {
			$smarty->assign("stock", Stockbroker::getSymbol($params['symbol']));
			//{assign_kurs symbol=$kurs.symbol}
		}
		public static function stockbroker_assign_searchedstocks($params, $smarty) {
			$smarty->assign("searchedstocks", Stockbroker::searchstocks($params['search']));
		}
		public static function stockbroker_update_kurs($params, $smarty) {
			Stockbroker::updateKurs($params['symbol']);
		}
		public static function stockbroker_getkursbought($params, $smarty) {
			global $user;
			return Stockbroker::getKursBought($user->id, $params['symbol']);
		}
		public static function stockbroker_getkurs($params, $smarty) {
			return Stockbroker::getKurs($params['symbol']);
		}

	/** Tauschbörse */
	public static function get_tauschartikel($params, $smarty)
	{
		global $db;
		$result = $db->query('SELECT *, UNIX_TIMESTAMP(datum) AS datum FROM tauschboerse WHERE id = '.$params['id'], __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result);
		$smarty->assign('artikel', $rs);
	}

	/** Usersystem */
	public static function show_formfield_notify_users($params, $smarty)
	{
		return usersystem::getFormFieldUserlist($params['name'], $params['size']);
	}

	/**
	 * Addle Highscores Table.
	 * @example {addle_highscore anzahl=23}
	 */
    public static function show_addle_highscores($params, $smarty)
    {
		// wrapper function for addle highscore
        if (!isset($params['anzahl'])) $params['anzahl'] = 5;
        return highscore_dwz($params['anzahl']);
    }

	/** Quotes */
		public static function show_quote_random($params, $smarty)
		{
		   return Quotes::getRandomQuote();
		}
		public static function show_dailyquote($params, $smarty)
		{
			return Quotes::getDailyQuote();
		}

	/**
	 * Show a Poll.
	 * @example {poll id=23}
	 * @since 2.0 `19.02.2020` `IneX` Added param validation and Poll Class handling
	 */
	public static function show_poll($params, $smarty)
	{
		if (!isset($params['id']) || empty($params['id']) || !is_numeric($params['id']))
		{
			return smarty_error(['msg' => t('invalid-poll_id', 'poll', [$params['id']])]);
		} else {
			$poll = new Polls();
			return $poll->show($params['id']);
		}
	}

	/**
	 * APOD.
	 * Astronomy Picture of the Day (APOD) anzeigen.
	 * @example {apod}
	 * @uses get_apod_id()
	 * @uses formatGalleryThumn()
	 */
	public static function show_apod($params, $smarty)
	{
		$rs = get_apod_id();
		return formatGalleryThumb($rs);
	}

	/** Events */
		public static function get_yearevents($params, $smarty) {
			$smarty->assign("yearevents", Events::getEvents($params['year']));
		}
		public static function get_event($params, $smarty) {
			$smarty->assign("event", Events::getEvent($params['id']));
		}
		public static function get_event_visitors($params, $smarty) {
			$smarty->assign("visitors", Events::getVisitors($params['event_id']));
		}
		public static function get_event_user_joined($params, $smarty) {
			global $user;
			$smarty->assign("event_hasjoined", Events::hasJoined($user->id, $params['event_id']));
		}
		public static function show_event_user_joined($params, $smarty) {
			global $user;
			return Events::hasJoined($user->id, $params['event_id']);
		}

	/** Rezepte */
		public static function get_rezepte_list($params, $smarty) {
			$smarty->assign("rezepte", Rezepte::getRezepte($params['category']));
		}
		public static function get_rezept($params, $smarty) {
			$smarty->assign("rezept", Rezepte::getRezept($params['id']));
		}
		public static function get_rezept_user_voted($params, $smarty) {
			// nur für eingeloggte
			$smarty->assign("rezept_voted", Rezepte::hasVoted($params['user_id'], $params['rezept_id']));
		}
		public static function get_rezept_score($params, $smarty) {
			$smarty->assign("rezept_score", Rezepte::getScore($params['rezept_id']));
		}

	/**
	 * URL Handling
	 */
		/**
		 * Build an internal URL to use with a Link
		 */
		public static function show_internal_link($params)
		{
		 	global $smarty; // => braucht es statt ($params, $smarty) damit andere Funktionen diese hier nutzen können!
		 	$vars = $smarty->getTemplateVars();

		 	if (isset($params['url'])) {
		 		$ret = $params['url'];
		 	}elseif (isset($params['word'])) {
		 		$ret = '/page/'.$params['word'];
		 	}elseif (isset($params['tpl'])) {
				$ret = "/tpl/".$params['tpl'];
		 	}elseif (isset($params['comment'])) {
		 		$ret = Comment::getLinkComment($params['comment']);
		 	}elseif (isset($params['user'])) {
		 		if (is_numeric($params['user'])) $ret = '/user/'.$params['user'];
		 		else $ret = '/user/'.usersystem::user2id($params['user']);
		 	}elseif (isset($params['action'])) {
		 		$ret .= '/actions/'.$params['action'].'?'.url_params();
		 	}else{
		 		$ret = '/?tpl='.$vars['tpl']['root'];
		 	}
		    if (isset($params['param'])) $ret .= str_replace('?&', '?', (strpos($ret, '?') !== false ? '&' : '?').$params['param']);
		    if (isset($params['hash'])) $ret .= '#'.$params['hash'];
		    return $ret;
		}
		/**
		 * @uses getChangedURL()
		 */
		public static function show_changed_url($params, $smarty) {
			return getChangedURL($params['change']);
		}
		/**
		 * Smarty Funktion urlExists().
		 * Utilities Funktion urlExists() um eine URL/Pfad zu validieren.
		 * @example {url_exists url="[url]" assign="[smarty-variable]"}
		 * @uses urlExists()
		 */
		public static function get_url_exists($params, $smarty)
		{
			$checkUrl = (urlExists($params['url']) === true ? 'true' : 'false');
			if (isset($params['assign'])) $smarty->assign($params['assign'], $checkUrl);
			else return $checkUrl;
		}

	/**
	 * HTML
	 */
		/** inserts &nbsp; */
		public static function show_space($params, $smarty)
		{
		    return str_repeat('&nbsp;', $params['i']);
		}

	/**
	 * String, Integer, Date and Array Functions
	 */
	 	/**
		 * Display size of Array elements.
		 * @example {sizeof array=$arrayVar}
		 */
		public static function show_sizeof_array($params, $smarty)
		{
			/** Fix sizeof() to only be called when variable is an array, and therefore guarantee it's Countable */
			return (is_array($params['array']) ? sizeof($params['array']) : 0);
		}
		/**
		 * Get a random number.
		 * @example {rand min=2 max=10 assign=var}
		 */
		public static function show_random_number($params, $smarty)
		{
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
		/**
		 * Read and print contents from a given textfile or from user files /files/[user-id]/
		 *
		 * Usage: {gettext id=[FILE-ID] linelength=[MAX-LINES]}
		 *    or: {gettext file=[PATH/WITHIN/FILES/FILE-NAME] linelength=[MAX-LINES]}
		 * @link http://zorg.local/thread/44614 Do ä Biispiel im Forum
		 *
		 * @author [z]cylander
		 * @version 1.1
		 * @since 1.0 `20.08.2004` `[z]cylander` function added
		 * @since 1.1 `15.01.2019` `IneX` added HTML escapes to file output
		 */
		public static function show_textfile($params, $smarty)
		{
			global $db;

			if ($params['file']) {
				$file = $params['file'];
				if (substr($file, -4) != '.txt') return '<font color="red"><b>[gettext: Can only read from txt-File]</b></font><br />';
				if (substr($file, 0, 1) == '/') $file = FILES_DIR.$file;
				if (!file_exists($file)) return '<font color="red"><b>[gettext: File "'.$file.'" not found]</b></font><br />';
			}elseif ($params['id']) {
				$e = $db->query('SELECT * FROM files WHERE id='.$params['id'], __FILE__, __LINE__, __FUNCTION__);
				$d = $db->fetch($e);
				if ($d) {
					if (substr($d['name'], -4) != '.txt') return '<font color="red"><b>[gettext: Can only read from txt-File]</b></font><br />';
					$file = sprintf('%s%d/%s', FILES_DIR, $d['user'], $d['name']);
				}else{
					return '<font color="red"><b>[gettext: File mit id "'.$params['id'].'" in Filemanager nicht gefunden]</b></font><br />';
				}
			}else{
				return '<font color="red"><b>[gettext: Gib mittels dem Parameter "file" oder "id" eine Datei an]</b></font><br />';
			}
			if (DEVELOPMENT === true) error_log('file path: '.$file);
			$out = '<div align="left"><pre>';

			/** Output only n lines (as passed) */
			if (isset($params['linelength']))
			{
				$len = $params['linelength'];
				if (DEVELOPMENT === true) error_log('linelength: '.$len);
				if (!is_numeric($len) || $len < 1) {
					return '<font color="red"><b>[gettext: Parameter linelength has to be numeric and greater than 0]</b></font><br />';
				}
				$fcontent = file($file);
				foreach ($fcontent as $it) {
					while (strlen($it) > $len) {
						$out .= htmlspecialchars(substr($it, 0, $len)) . "\n   ";
						$it = htmlspecialchars(substr($it, $len));
					}
					$out .= htmlspecialchars($it);
				}
			}

			/** Output whole textfile at once */
			else{
				$out .= htmlspecialchars(file_get_contents($file));
			}
			if (DEVELOPMENT === true) error_log('out: '.$out);
			$out .= '</pre></div>';

			return $out;
		}

	/**
	 * Commenting System und Forum Threads
	 */
	 	/**
		 * Add a specific pre-configured Commenting System to a Smarty Template
		 *
		 * @example {commentingsystem board="e" thread_id=$event_id} 
		 *
		 * @author [z]biko
		 * @version 1.1
		 * @since 1.0 `[z]biko` Function added
		 * @since 1.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 *
		 * @uses Forum::printCommentingSystem()
		 * @return callable Commenting-System from Forum::printCommentingSystem()
		 */
		public static function show_commentingsystem($params, $smarty)
		{
			global $user;

			/** Check if current Template is not included in another Template */
			$tplvars = $smarty->getTemplateVars();
			if ($tplvars['tpl']['id'] === $_GET['tpl'])
			{
				/** Check User preferences to show/hide Comments */
				if ($user->is_loggedin() && $user->show_comments)
				{
					echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td class="tiny border">
						  <a class="threading switch collapse" href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=0">
						  <span style="padding-left:20px">Kommentare ausblenden</span></a>
						  </td></tr><tr><td>';
				}
				/** User preferences = hide Comments */
				elseif ($user->is_loggedin()) {
					echo '<table width="100%" cellspacing="0" cellpadding="0"><tr><td class="tiny border">
						  <a class="threading switch expand" href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=1">
						  <span style="padding-left: 20px;font-weight: 400;color: green;">Kommentare einblenden</span></a>
						  </td></tr><tr><td>';
				}
				/** For Guests, just show Comments */
				else {
					echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td><h3>Comments</h3></td></tr>
						  <tr><td>';
				}
				Forum::printCommentingSystem($params['board'], $params['thread_id']);
				echo '</td></tr></table>';
			}
			/** If included Template, hide Comments */
			else {
				echo '<p><span style="padding-left: 20px;font-style: italic;font-weight: 400;color: green;">Kommentare werden beim include ausgeblendet<br>
					  <a href="/tpl/'.$tplvars['tpl']['id'].'#'.$comment_id.'">Klick hier um sie zu sehen</a></span></p>';
			}
		}
		/**
		 * Add dynamic TPL Page Commenting System to a Smarty Page Template
		 *
		 * @deprecated 2.0 `31.05.2020` `IneX` Neu wird nur noch {commentingsystem...} this::show_commentingsystem() benutzt in smarty.inc.php
		 * @example {comments}
		 *
		 * @author [z]biko
		 * @version 1.1
		 * @since 1.0 `[z]biko` Function added
		 * @since 1.1 `04.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 * @since 2.0 `31.05.2020` `IneX` Optimized code in alignment with refactored Forum::printComentingSystem()
		 *
		 * @used-by Smarty_Resource_Tpl::fetch() Includes the {comments}-Smarty function on a template with `allow_comments=true`
		 * @uses Forum::printCommentingSystem()
		 * @return string|callable Commenting-System from Forum::printCommentingSystem() - or Error message if Config invalid
		 */
		public static function show_comments($params, $smarty)
		{
			global $user;

			$tplvars = $smarty->getTemplateVars();
			if (!$params['board'] || !$params['thread_id'])
			{
	 			$params['board'] = 't'; // Default Board: 't' (Templates)
	 			$params['thread_id'] = $tplvars['tpl']['id']; // Default Thread = current Template-ID
	 		}

			/** Check if Template is not included in another Template */
			if ($tplvars['tpl']['id'] == $_GET['tpl'])
			{
				/** Check User preferences to show/hide Comments */
				if ($user->is_loggedin() && $user->show_comments)
				{
					echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td width="100%" class="tiny border">'.
					 '<a class="threading switch collapse" href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=0">'.
					 '<span style="padding-left:20px">Kommentare ausblenden</span></a>'.
					 '</td></tr><tr><td>';
					Forum::printCommentingSystem($params['board'], $params['thread_id']);
					echo '</td></tr></table>';

					return '';
				}
				/** User preferences = hide Comments */
				elseif ($user->is_loggedin()) {
					return '<table width="100%" cellspacing="0" cellpadding="0"><tr><td width="100%" class="tiny border">'.
							'<a class="threading switch expand" href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=1">'.
							'<span style="padding-left: 20px;font-weight: 400;color: green;">Kommentare einblenden</span></a>'.
							'</td></tr><tr><td>';
				}
				/** For Guests, just show Comments (if permissions allow it) */
				else {
					echo '<h3>Comments</h3>';
					echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td width="100%">';
					Forum::printCommentingSystem($params['board'], $params['thread_id']);
					echo '</td></tr></table>';
				}
			}
			/** On included Templates, hide Comments */
			else {
				return '<p><span style="padding-left: 20px;font-style: italic;font-weight: 400;color: green;">Kommentare werden beim include ausgeblendent. '
						.'<a href="?tpl='.$tplvars['tpl']['id'].'">Klick hier um sie zu sehen</a></span></p>';
			}
		}
		/**
		 * Comment Color-Fade HEX-Color berechnen & ausgeben.
		 *
		 * @example {comment_colorfade depth=$size color=$color.newcomment}
		 *
		 * @author [z]biko
		 * @version 2.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.1 `06.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 *
		 * @uses Forum::colorfade()
		 */
		public static function show_comment_colorfade($params)
		{
			return Forum::colorfade($params['depth'], $params['color']);
		}
		/**
		 * Comment URL generieren.
		 *
		 * @example {comment_get_link board=f thread_id=23}
		 *
		 * @author [z]biko
		 * @version 2.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.1 `06.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 *
		 * @uses Comment::getLink()
		 */
		public static function get_comment_url($params)
		{
			return Comment::getLink($params['board'], $params['parent_id'], $params['id'], $params['thread_id']);
		}
		/**
		 * HTML-Link zu einem Thread anzeigen.
		 *
		 * @example {show_thread_link board=f thread_id=23}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `07.05.2020` `IneX` Method added
		 *
		 * @uses Comment::getLinkThread()
		 */
		public static function show_comment_thread_link($params)
		{
			$thread_id = (isset($params['thread_id']) && is_numeric($params['thread_id']) && $params['thread_id'] > 0 ? $params['thread_id'] : null);
			$board = (isset($params['board']) && is_string($params['board']) ? $params['board'] : null);
			return Comment::getLinkThread($board, $thread_id);
		}
		/**
		 * Anzahl Childposts eines Comments an "hdepth"-Smarty Var assignen.
		 *
		 * @example {comment_extend_depth depth=5 childposts=23 rcount=$numChildposts}
		 *
		 * @author [z]biko
		 * @version 2.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.1 `06.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 * @return void Directly executes a $smarty->assign() to store the Array containing all Notifications
		 */
		public static function get_comment_num_childposts($params)
		{
			global $smarty;	

			if (isset($params['depth'])) $depth = $params['depth'];
			else $depth = array();	

			if ($params['childposts'] > $params['rcount']) {
			  array_push($depth, "vertline");
			} else {
			  array_push($depth, "space");
			}
			$smarty->assign('hdepth', $depth);
		}
		/**
		 * Anzahl Parentposts eines Comments an "hdepth"-Smarty Var assignen.
		 *
		 * @example {comment_remove_depth depth=5}
		 *
		 * @author [z]biko
		 * @version 2.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.1 `06.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 *
		 * @uses Forum::colorfade()
		 * @return void Directly executes a $smarty->assign() to store the Array containing all Notifications
		 */
		public static function get_comment_num_parentposts($params)
		{
			global $smarty;

			$depth = $params['depth'];

			array_pop($depth);
			$smarty->assign('hdepth', $depth);
		}
		/**
		 * Einen bestimmten Comment für einen User als gelesen markieren.
		 *
		 * @example {comment_mark_read comment_id=235 user_id=$user->id}
		 *
		 * @author [z]biko
		 * @version 2.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.1 `06.05.2020` `IneX` Method moved to Class ZorgSmarty_Functions for compatibility with Smarty 3
		 *
		 * @uses Comment::markasread()
		 */
		public static function do_comment_mark_read($params)
		{
			return Comment::markasread($params['comment_id'], $params['user_id']);
		}
		public static function show_latest_threads($params, $smarty) {
		return Forum::getLatestThreads();
		}
		public static function show_latest_comments($params, $smarty) {
		return Forum::getLatestComments($params['anzahl'], $params['title'], $params['board']);
		}
		public static function show_3yearold_threads($params, $smarty) {
		return Forum::get3YearOldThreads();
		}
		public static function show_latest_unread_comments($params, $smarty) {
		return Forum::getLatestUnreadComments($params['title'], $params['board']);
		}

		/**
		 * Smarty Function "forum_boards"
		 *
		 * Fetch and returns all Forum boards using forum_boards.tpl
		 * Usage: {forum_boards boards=$user->forum_boards_unread updateable=true/false}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `30.09.2018` method added
		 * @uses Forum::getForumBoards()
		 */
		public static function show_forum_boards($params, $smarty)
		{
			return Forum::getForumBoards($params['boards'], $params['updateable']);
		}

		/**
		 * Smarty Information
		 */
		public static function show_message_error($params, $smarty)
		{
			  if ($params['msg']) {
			     return '<p><font color="red"><b>'.$params['msg'].'</b></font></p>';
			  }else{
			     return "";
			  }
		}
		public static function show_message($params, $smarty)
		{
			  if ($params['msg']) {
			     return '<p><font color="green"><b>'.$params['msg'].'</b></font></p>';
			  }else{
			     return "";
			  }
		}
		/**
		 * @uses edit_link_url()
		 */
	 	public static function get_tpl_edit_url($params, $smarty)
	 	{
			if (!$params['tpl']) {
				$vars = $smarty->getTemplateVars();
				$params['tpl'] = $vars['tpl']['id'];
			}
			return self::edit_link_url($params['tpl']);
		}
		/**
		 * NO SMARTY FUNCTION! Compile a href URL for Template-Edit Links
		 *
		 * @TODO Where to put this? What to do with it? Besser /action/ ? (IneX, 05.05.2020)
		 *
		 * @uses url_params()
		 */
		public static function edit_link_url($tpl)
		{
			return '/?tpleditor=1&tplupd='.$tpl.'&location='.base64_encode($_SERVER['PHP_SELF'].'?'.url_params());
		}

		/**
		 * Letze Smarty Updates
		 *
		 * Gibt eine Tabelle mit Links zu den letzten upgedateten Smarty Templates aus
		 *
		 * @return String
		 */
		public static function show_templates_latest_updates($params, $smarty)
		{
			global $db, $user;

			if (!$params['anzahl']) $params['anzahl'] = 5;

			$sql = 'SELECT *, UNIX_TIMESTAMP(last_update) as date FROM templates ORDER BY last_update DESC LIMIT 0,'.$params['anzahl'];
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

			$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>letzte Änderungen</b></td></tr>';
			while($rs = $db->fetch($result))
			{
				$i++;
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
				$html .=
				  '<tr class="small"><td align="left" bgcolor="'.$color.'">'
				  .'<a href="/?tpl='.$rs['id'].'">'.stripslashes($rs['title']).' ('.$rs['id'].')'.'</a>'
				  .'</td><td align="left" bgcolor="'.$color.'" class="small">'
				  .$user->link_userpage($rs['update_user'])
				  .'</td><td align="left" bgcolor="'.$color.'" class="small"><nobr>'
				  .datename($rs['date'])
				  .'</nobr></td></tr>'
				;
			}
			$html .= '</table>';
			return $html;
		}

	/**
	 * Menu
	 */
	 	/**
		 * Display a navigation menubar
		 *
		 * Usage: {menu name=[menu-name]}
		 *
		 * @version 3.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.0 `30.09.2019` `IneX` adjusted with new responsive HTML Layout structure
		 * @since 3.0 `02.05.2020` `IneX` function updated for Smarty 3.1 compatibility
		 */
		public static function show_menu_tpl($params, $smarty)
		{
			global $db, $user;

			$vars = $smarty->getTemplateVars();

			if ($vars['tpl_parent']['id'] == $vars['tpl_root']['id'])
			{
				if ($params['tpl']) {
					$e = $db->query('SELECT * FROM templates WHERE id="'.$params['tpl'].'"', __FILE__, __LINE__, __METHOD__);
					$d = $db->fetch($e);
					if (tpl_permission($d['read_rights'], $d['owner']))
					{
						return $smarty->fetch('tpl:'.$params['tpl']);
					}
				} else {
					$e = $db->query('SELECT m.* FROM menus m, templates t
									 WHERE name="'.$params['name'].'" AND t.id = m.tpl_id', __FILE__, __LINE__, __METHOD__);
					$d = $db->fetch($e);
					if ($d && tpl_permission($d['read_rights'], $d['owner']))
					{
						return $smarty->fetch('tpl:'.$d['tpl_id']);
					//}elseif ($d) {
						//return '</nav>';
					} else {
						return '<font color="red"><b>[Menu "'.$params['name'].'" not found]</b></font><br />';
					}
				}
			}
		}
		/**
		 * NO SMARTY FUNCTION! Add or remove a Menu-Entry from the database table `menus`
		 *
		 * @TODO Where to put this? What to do with it? Besser /action/ ? (IneX, 05.05.2020)
		 *
		 * @author [z]biko
		 * @version 1.0
		 * @since 1.0 `[z]biko` function added
		 * @since 2.0 `16.09.2019` `IneX` function updated for zorg v4 to prevent updating new 'id' table row
		 *
		 * @param string $name Name of a Menubar (e.g. 'zorg'), containing a {menubar}{menuitem...}{/menubar}-structure
		 */
		public static function smarty_menuname_exec($name)
		{
			global $db, $smarty;

			$vars = $smarty->getTemplateVars();
			$tpl_id = $vars['tpl']['id'];

			$namePlain = htmlentities($name, ENT_QUOTES); // remove any html from the menuname
			$nameArray = explode(' ', $namePlain); // convert menuname into Array

			/*for ($i=0; $i<sizeof($name); $i++) {
				if ($it) {
					$menu = $db->fetch($db->query('SELECT * FROM menus WHERE name="'.$name[$i].'"', __FILE__, __LINE__, __METHOD__));
					if ($menu && $menu['tpl_id'] != $tpl) return 'Menuname "'.$name[$i].'" existiert schon und wurde nicht registriert.<br />';
					unset($name[$i]);
				}
			}*/
			foreach ($nameArray as $it)
			{
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> smarty_menuname_exec: "%s" on tpl_id %d', __FUNCTION__, __LINE__, $it, $tpl_id));
				if (!empty($it)) {
					/** Check if menu with same name already exists... */
					$menuExists = $db->fetch($db->query('SELECT * FROM menus WHERE name="'.$it.'"', __FILE__, __LINE__, __FUNCTION__));
					//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $menuExists Query: %s', __FUNCTION__, __LINE__, print_r($menuExists,true)));
					if ($menuExists !== false && $menuExists['tpl_id'] === $tpl_id)
					{
						if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $menuExists: TRUE (tpl_id: %d)', __FUNCTION__, __LINE__, $tpl_id));
						//return sprintf('Menuname "%s" existiert schon mit der id#%d und wurde deshalb nicht gespeichert!<br>Bitte anderen Namen verwenden.', $it, $tpl_id);
					}

					/** Menu mit $name gibt es noch nicht, deshlab erstellen wir es neu */
					else {
						if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $menuExists: FALSE (adding new)', __FUNCTION__, __LINE__));
						$db->query('INSERT INTO menus (tpl_id, name) VALUES ('.$tpl_id.', "'.$it.'")', __FILE__, __LINE__, __FUNCTION__);
						//$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => sprintf('Neues Menu "%s" erfolgreich gespeichert', $it), 'message' => 'Du kannst es jetzt im Template-Editor einer Page auswählen.']);
					}
				}
			}
			return '';
		}


	/**
	 * Gallery
	 */
	 	/**
		 * Get Users tagged in a specific Gallery Pic.
		 * @example {users_on_pic picID=523}
		 */
		public static function get_users_in_pic($params, $smarty) {
			$smarty->assign('users_on_pic', Gallery::getUsersOnPic($params['picID']));
		}
		/**
		 * Show a random Thumbnail Pic of a specific Gallery.
		 * @example {random_albumpic album_id=41}
		 */
		public static function show_random_albumpic($params, $smarty) {
			return getAlbumLinkRandomThumb($params['album_id']);
		}
		/**
		 * Smarty Function "top_pics"
		 *
		 * Returns a specific amount of best rated images from a given gallery
		 * Usage: {top_pics album=41 limit=1}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `23.06.2007` `IneX` function added as part of Bug #609
		 *
		 * @uses getTopPics()
		 * @param array $params All passed Smarty-Function parameters, allowed: album, limit, options
		 * @return string HTML displaying top rated n amount of Gallery-Pics
		 */
		public static function show_top_rated_pics($params, $smarty)
		{
			/** Validate and assign passed $params */
		 	$album_id = (empty($params['album']) ? null : $params['album']);
		 	$limit = (empty($params['limit']) ? 5 : $params['limit']);
		 	$options = (empty($params['options']) ? false : true);

	 		return getTopPics($album_id, $limit, $options);
		}
		/**
		 * Smarty Function "user_pics"
		 *
		 * Returns a specific amount of Gallery Pictures on which a given User has been tagged
		 * Usage: {user_pics user=41 limit=1}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `18.10.2013` `IneX` Function added
		 *
		 * @uses getUserPics()
		 */
		public static function show_user_tagged_pics($params, $smarty)
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
		public static function get_chatmessages($params, $smarty)
		{
			global $db;

			$anzahl = ($params['anzahl'] == '' ? 10 : $params['anzahl']);
			$page = ($params['page'] == '' ? 0 : $params['page']);

			$sql = "SELECT * from chat";
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
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
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

			while ($rs = $db->fetch($result)) {
			  $chatmessages[] = $rs;
			}
			$smarty->assign("chatmessages", $chatmessages);
		}

	/**
	 * List SQL Errors.
	 * @uses get_sql_errors()
	 */
    function list_sqlerrors($params, $smarty)
    {
 		return get_sql_errors($params['num'], $params['order'], $params['oby']);
	}

	/**
	 * Log an Error to the PHP Error Log.
	 * @example {logerror tpl=[templatename] line=[integer] string="[custom text]"}
	 */
	public static function log_phperror($params, $smarty)
	{
		error_log(sprintf('[SMARTY] <%s:%d> %s', $params['tpl'], $params['line'], $params['string']));
	}

	/**
	 * Get all User Notification values to display in zorg Header
	 *
	 * Replaces PHP file /scripts/header.php for Smarty 3.1 compatibility reasons.
	 * Wichtig: Smarty Funktion und KEINE Var {$user_notifications} - damit man den Output an eine frei wählbare Smarty-Var allozieren kann (siehe Usage).
	 * Usage: {user_notifications assignto=newSmartyVarName}
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/head.tpl Primarily used in head.tpl
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `01.05.2020` `IneX` function added because {include_php} is deprecated in Smarty 3.1
	 *
	 * @uses getOpenAddleGames()
	 * @uses hz_open_games()
	 * @uses hz_running_games()
	 * @uses go_open_games()
	 * @uses go_running_games()
	 * @uses getOpenChessGames()
	 * @uses Forum::getNumunreadposts()
	 * @uses Bugtracker::getNumNewBugs()
	 * @uses Bugtracker::getNumOpenBugs()
	 * @uses Bugtracker::getNumOwnBugs()
	 * @uses Messagesystem::getNumNewMessages()
	 * @uses Events::getEventNewest()
	 * @uses Rezepte::getNumNewRezepte()
	 * @uses peter::peter_zuege()
	 * @uses stl::getOpenSTLGames()
	 * @uses stl::getOpenSTLLink()
	 * @param array $params Additional parameters passed to Function from Smarty Template
	 * @param object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return void Directly executes a $smarty->assign() to store the Array containing all Notifications
	 */
	public static function user_notifications($params, $smarty) 
	{
		global $db, $user;

		$userNotifications = [];

		/** Addle */
		$userNotifications['addle'] = ['open' => getOpenAddleGames($user->id)];

		/** Hunting z */
		$userNotifications['hz'] = ['new' => hz_open_games()];
		$userNotifications['hz'] = ['open' => hz_running_games()];

		/** Go */
		$userNotifications['go'] = ['new' => go_open_games()];
		$userNotifications['go'] = ['open' => go_running_games()];

		/**
		 * Chess
		 *
		 * @FIXME Disabled wegen SQL-Fehler
		 */
		//$userNotifications['chess'] = ['open' => getOpenChessGames($user->id)];

		/** Comments */
		$userNotifications['comments'] = ['unread' => Forum::getNumunreadposts($user->id)];

		/** Bugtracker */
		$userNotifications['bugtracker'] = ['new' => Bugtracker::getNumNewBugs()];
		$userNotifications['bugtracker'] = ['open' => Bugtracker::getNumOpenBugs()];
		$userNotifications['bugtracker'] = ['own' => Bugtracker::getNumOwnBugs()];

		/** Messages */
		$userNotifications['messages'] = ['unread' => Messagesystem::getNumNewMessages($user->id)];

		/** Events */
		$userNotifications['events'] = ['new' => [ Events::getEventNewest() ]];

		/** Rezepte */
		$userNotifications['rezepte'] = ['new' => Rezepte::getNumNewRezepte($user->id)];

		/** Peter */
		$userNotifications['peter'] = ['open' => peter::peter_zuege()];

		/** Shoot the Lamber */
		$userNotifications['stl'] = ['new' => stl::getOpenSTLGames()];
		$userNotifications['stl'] = ['open' => stl::getOpenSTLLink()];

		/** Tauschangebote */
		if (isset($user->lastlogin))
		{
			$result = $db->query('SELECT COUNT(*) AS num FROM tauschboerse WHERE datum > from_unixtime('.$user->lastlogin.')', __FILE__, __LINE__, __FUNCTION__);
			$rs = $db->fetch($result);
			$userNotifications['tauschangebote'] = ['new' => ($rs['num'] > 0 ? $rs['num'] : 0)];
		}

		/** Pass the Notifications-Array to Smarty */
		$smartyAssignTo = (isset($params['assignto']) && is_string($params['assignto']) ? $params['assignto'] : 'myUpdates');
		$smarty->assign($smartyAssignTo, $userNotifications);
	}

	/**
	 * Templates & Tpleditor
	 */
	 	/**
		 * Get and assign an Array with all Templates
		 *
		 * Returns all available Templates (where the user has access to) as an Array
		 * Usage: {tploverview sort=[id|title|word|owner|last_update] order=[ASC|DESC]}
		 *
		 * @link https://zorg.ch/tpl/17 Used in "Alle Templates" Page
		 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/pages/tpleditor.tpl Used in Template-Editor
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `03.05.2020` `IneX` function added from code in `/scripts/tploverview.php` because {include_php} of it is deprecated in Smarty
		 *
		 * @uses findStringInArray() Search for a matching $string in an Array, from util.inc.php
		 * @global object $db Globales Class-Object mit allen MySQL-Methoden
		 * @return array List with all available Templates
		 */
		public static function get_templates_list($params, $smarty)
		{
			global $db;

			$allowedSortBy = ['id', 'title', 'word', 'owner', 'last_update'];
			$sortBy = (isset($params['sort']) && is_string($params['sort']) ? (!findStringInArray(strtolower($params['sort']), $allowedSortBy) ? 'id' : strtolower($params['sort'])) : 'id'); // Default: id
			$orderBy = (isset($params['order']) && (strtoupper($params['order']) === 'ASC' || strtoupper($params['order']) === 'DESC') ? strtoupper($params['order']) : 'DESC');
			$sort_order = ' ORDER BY '.$sortBy.' '.$orderBy;
			$totalsize = 0;
			$permissionDeniedOn = 0;

			/** Get all templates from the database */
			$templatesQuery = $db->query('SELECT id, title, word, owner, LENGTH(tpl) size, UNIX_TIMESTAMP(last_update) updated, update_user, read_rights, write_rights FROM templates WHERE del="0"'.$sort_order, __FILE__, __LINE__, __FUNCTION__);
			while ($templatesQueryResult = $db->fetch($templatesQuery))
			{
				$totalsize += $templatesQueryResult['size'];
				if (tpl_permission($templatesQueryResult['read_rights'], $templatesQueryResult['owner'])) $templateList[] = $templatesQueryResult;
				else $permissionDeniedOn++;
			}
			$anz = sizeof($templateList);
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> array($templateList): %s', __FILE__, __LINE__, print_r($templateList,true)));

			/** Pass the Template Overview Variables to Smarty */
			$smarty->assign('tploverview', $templateList);
			$smarty->assign('notemplates', sizeof($templateList));
			$smarty->assign('totalsize', $totalsize);
			$smarty->assign('avgsize', $totalsize/$anz);
			$smarty->assign('tplwithoutpermission', $permissionDeniedOn);
		}

	 	/**
		 * Available Template-Settings {template_available_settings} for Tpleditor
		 *
		 * Diverse Smarty Vars welche im Template-Editor für das Setzen von gültigen Template-Settings benötigt werden.
		 * Migriert aus dem PHP file /scripts/tpleditor.php wegen Smarty 3.1 Kompatibilität weil {include_php} deprecated ist.
		 *
		 * @TODO Move to `/templates/configs/`? See [Smarty For Template Designers: Example of config file syntax](https://www.smarty.net/docsv2/en/config.files.tpl)
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `03.05.2020` `IneX` function added from code in `/scripts/tpleditor.php` because {include_php} of it is deprecated in Tpleditor
		 *
		 * @uses usersystem::id2user()
		 * @uses url_params()
		 * @param array $params Additional parameters passed to Function from Smarty Template
		 * @param object $smarty Globales Class-Object mit allen Smarty-Methoden
		 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
		 * @return void Directly executes a $smarty->assign() to store the Array containing all Notifications
		 */
		public static function get_tpleditor_settings($params, $smarty)
		{
			global $user;

			$currenUsername = $user->id2user($user->id, true);

			$smarty->assign('tpleditor_close_url', '/actions/tpleditor_close.php?'.url_params());
			$smarty->assign('rgroupids', array(0,1,2,3));
			$smarty->assign('rgroupnames', array('Alle (auch nicht eingeloggte)', 'Normale User (eingeloggt)', 'Member und Sch&ouml;ne', 'Nur '.$currenUsername));
			$smarty->assign('wgroupids', array(1,2,3));
			$smarty->assign('wgroupnames', array('Normale User', 'Member und Sch&ouml;ne', 'Nur '.$currenUsername));
			$smarty->assign('bordertypids', array(0,1,2));
			$smarty->assign('bordertypnames', array('kein Rahmen', 'Rahmen mit Footer', 'Rahmen ohne Footer'));
		}

		/**
		 * Get and assign a Template's Settings for Tpleditor
		 *
		 * Get and assign a specific Template's Settings to display & edit in Template-Editor
		 * Migriert aus dem PHP file /scripts/tpleditor.php wegen Smarty 3.1 Kompatibilität weil {include_php} deprecated ist.
		 * Usage: {load_tpl_settings id=TemplateID assignto=newSmartyVarName}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `03.05.2020` `IneX` function added from code in `/scripts/tpleditor.php` because {include_php} of it is deprecated in Tpleditor
		 *
		 * @uses Smarty::getTemplateVars()
		 * @param array $params Additional parameters passed to Function from Smarty Template
		 * @param object $smarty Globales Class-Object mit allen Smarty-Methoden
		 * @global object $db Globales Class-Object mit allen MySQL-Methoden
		 * @return void Directly executes a $smarty->assign() to store the Array containing all Notifications
		 */
		public static function get_tpl_settings($params, $smarty)
		{
			global $db;

			$tpl_id = (isset($params['id']) ? $params['id'] : 'new');
			$smartyAssignTo = (isset($params['assignto']) && is_string($params['assignto']) ? $params['assignto'] : 'tpleditor_frm');
			$definedTplVars = $smarty->getTemplateVars();
			$frm = [];

			/**
			 * It's a new Template
			 */
			if ($tpl_id === 'new' && !$definedTplVars['tpleditor_frm'])
			{
				/** Set default values for new templates */
				$frm['read_rights'] = 0;
				$frm['write_rights'] = 3;
				$frm['border'] = 1;
				$frm['id'] = 'new';
				$frm['tpl'] = '';
				$frm['menus'] = 24;
			}

			/**
			 * Get data of existing Template
			 */
			else {
				/** Template content */
				$templateQuerySql = 'SELECT *, UNIX_TIMESTAMP(created) created, UNIX_TIMESTAMP(last_update) last_update FROM templates WHERE id='.$tpl_id;
				$templateQuery = $db->query($templateQuerySql, __FILE__, __LINE__, __FUNCTION__);
				$templateData = $db->fetch($templateQuery);

				if ($templateData !== false && !$definedTplVars['tpleditor_frm'])
				{
					/** Get associated menus */
					$menusQuerySql = 'SELECT menu_id FROM tpl_menus WHERE tpl_id='.$tpl_id;
					$menusQuery = $db->query($menusQuerySql, __FILE__, __LINE__, __FUNCTION__);

					/** Get associated packages */
					$packagesQuerySql = 'SELECT package_id FROM tpl_packages WHERE tpl_id='.$tpl_id;
					$packagesQuery = $db->query($packagesQuerySql, __FILE__, __LINE__, __FUNCTION__);

					/** Assign Template Values to Tpleditor Frame */
					$frm = $templateData;
					$frm['title'] = stripslashes($templateData['title']);
					$frm['tpl'] = stripslashes(htmlentities($templateData['tpl']));
					while ($menusData = $db->fetch($menusQuery)) {
						$frm['menus'][] = $menusData['menu_id'];
					}
					while ($packagesData = $db->fetch($packagesQuery)) {
						$frm['packages'][] = $packagesData['package_id'];
					}
				}

				/** Template not found or tpleditor_frm already defined */
				elseif (!$templateData)
				{
					$smarty->assign('tpleditor_strongerror', t('invalid-id', 'tpl', [$tpl_id]));
					if (!empty($definedTplVars['tpleditor_frm'])) $frm = $definedTplVars['tpleditor_frm'];
				}
			}

			/** Pass the Template Settings-Array to Smarty */
			$smarty->assign($smartyAssignTo, $frm);
		}

	/**
	 * Smarty Compiler Functions.
	 *
	 * ACHTUNG: compiler-funktionen müssen php-code zurückgeben!
	 * Compiler functions are called only during compilation of the template.
	 * They are useful for injecting PHP code or time-sensitive static content into the template.
	 *
	 * @link https://www.smarty.net/docs/en/plugins.compiler.functions.tpl Extending Smarty With Plugins: Compiler Functions
	 */
		/**
		 * Menu via PHP echo() ausgeben
		 *
		 * @example <?php echo ZorgSmarty_Functions::smarty_menuname_exec($menuName); ?>
		 * @uses self::smarty_menuname_exec()
		 *
		 * @author [z]biko
		 * @version 2.0
		 * @since 1.0 `[z]biko` Function added
		 * @since 2.o `03.05.2020` `IneX` function updated for Smarty 3.1 compatibility
		 *
		 * @uses self::smarty_menuname_exec()
		 * @return string PHP-Code der von Smarty ausgeführt wird wenn Funktion als Compiler-Plugin registriert ist
		 */
		public static function echo_menuname($name, $smarty)
		{
			return '<?php echo ZorgSmarty_Functions::smarty_menuname_exec("'.$name.'"); ?>';
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
		public static function assign_array($params, $smarty)
		{
			extract($params);

			if (empty($var)) {
				trigger_error("assign_array: missing 'var' parameter");
				return;
			}

			if (!in_array('value', array_keys($params))) {
				trigger_error("assign_array: missing 'value' parameter");
				return;
			}

			if (!in_array('array', array_keys($params)) XOR !in_array('range', array_keys($params))) {
				trigger_error("assign_array: missing 'value=array()' or 'value=range()'");
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
}

/**
 * Smarty Page performance measurement
 */
// FIXME $smarty->registerPlugin('function', 'sqltracker', ['dbcon', 'sqltracker']);
$smarty->registerPlugin('modifier', 'rendertime', ['ZorgSmarty_Modifiers', 'smarty_modifier_rendertime']);
ZorgSmarty_Modifiers::smarty_modifier_rendertime('begin'); // Start Smarty-Template Rendering-Timer
$smarty->assign('parsetime_start', $parsetime_start); // PHP-Script Parsetime

/**
 * Register Functions to Smarty
 */
$smartyVars = (new ZorgSmarty_Vars)->register();
$smartyModifiers = (new ZorgSmarty_Modifiers)->register();
$smartyBlocks = (new ZorgSmarty_Blocks)->register();
$smartyFunctions = (new ZorgSmarty_Functions)->register();
