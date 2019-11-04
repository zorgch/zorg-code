<?php
/**
 * zorg Smarty Template Engine Manager
 *
 * Instanziert und konfiguriert ein neues Smarty-Object. Dieses wird in
 * der globalen Variable $smarty gespeichert.
 *
 * @author [z]biko
 * @date 5.6.2004
 * @package zorg\Smarty\Templates
 */

/**
 * File includes
 * @include config.inc.php Required
 * @include Smarty.class.php Required
 * @include usersystem.inc.php Include
 * @include comments.res.php Include
 */
require_once(__DIR__ . '/config.inc.php');
require_once(__DIR__ . '/../smartylib/Smarty.class.php');
include_once(__DIR__ . '/usersystem.inc.php');
include_once(__DIR__ . '/comments.res.php');

/**
 * OWN BY BIKO
 *
 * @var boolean $_manual_compiler_active Veranlasst den trigger_fatal_error den fehler nicht auszugeben, sondern in $_last_fatal_error zu speichern.
 * @var array $_manual_compiler_errors trigger_fatal_error speichert den Fehler hier rein, falls $_redirect_fatal_error = true
 * @var array $_tpl_stack
 */
$_manual_compiler_active = 0;
$_manual_compiler_errors = array();
$_tpl_stack = array();

function tpl_comment_permission ($thread_id) {
	global $db;
	$e = $db->query('SELECT * FROM templates WHERE id='.$thread_id, __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	return tpl_permission($d['read_rights'], $d['owner']);
}

/**
 * Check permission to access template
 *
 * @author [z]biko
 * @version 2.0
 * @since 1.0 <biko> function added
 * @since 2.0 <inex> 20.06.2019 Failsafe hinzugefügt
 *
 * FIXME ACHTUNG: template read_rights sind != der USER_xxx Level! z.B. read_rights=3 bedeutet "Template Owner only"...
 *
 * @see hasTplAccess()
 * @param integer $group Gruppe Level-Nummer to check
 * @param integer $owner User-ID to check
 */
function tpl_permission ($group, $owner)
{
	global $user;

	/** Failsafe: wenn Parameter ungültig dann immer low level Rechte setzen */
	if ($group === '' || $group === null) $group = USER_ALLE;
	if ($owner === '' || $owner === null) $owner = ROSENVERKAEUFER;

	$userid = isset($user->id)?$user->id:0;
	$usertyp = isset($user->typ)?$user->typ:0;

	return hasTplAccess($group, $owner, $userid, $usertyp);
}

/**
 * Check ob Group / Usertyp Kombination Zugriff zum Lesen des Templates hat
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 <biko> function added
 */
function hasTplAccess ($group, $owner, $userid, $usertyp)
{
	/** Template Owner hat immer Zugriff auf sein Template... */
	if ($owner == $userid) return true;

	/** member und schöne */
	if ($group == USER_MEMBER)
	{
		if ($usertyp == USER_MEMBER) 
		{
			return true;
		} else {
			return false;
		}
	}

	/** normale user */
	elseif ($group == USER_USER) {
		if ($usertyp == USER_MEMBER || $usertyp == USER_USER)
		{
			return true;
		} else {
			return false;
		}
	}

	/** ausgeloggte (=alle) */
	elseif ($group == USER_ALLE) {
		return true;
	} else {
		return false;
	}
}


/**
 * Smarty function to assigns additional specific tpl-vars
 * basic vars are assigned in smartyresource_tpl_get_template() Smarty-code {_tpl_assigns}
 *
 * @version 1.0
 * @since 1.0 <biko> function added
 *
 * @global array $_tpl_stack Globales Array mit allen Template-Variablen
 */
function _tpl_assigns ($params, $content, &$smarty, &$repeat) {
	global $_tpl_stack;

  	if ($repeat == true) { // öffnendes tag
  		// push wird in get_timestamp gemacht.

  		$smarty->assign('tpl', $_tpl_stack[sizeof($_tpl_stack)-1]);
		$smarty->assign('tpl_parent', $_tpl_stack[sizeof($_tpl_stack)-2]);
		$smarty->assign('tpl_level', sizeof($_tpl_stack));

	} else {  // schliessendes tag
		array_pop($_tpl_stack);

		$smarty->assign('tpl', $_tpl_stack[sizeof($_tpl_stack)-1]);
		$smarty->assign('tpl_parent', $_tpl_stack[sizeof($_tpl_stack)-2]);
		$smarty->assign('tpl_level', sizeof($_tpl_stack));

		return $content;
	}
}


/**
 * Load Smarty TPL Resource
 *
 * Datenbankabfrage um unser Template zu laden, und '$tpl_source' zuzuweisen
 *
 * @author [z]biko
 * @author IneX
 * @version 3.2
 * @since 1.0 function added
 * @since 2.0 26.09.2018 [Bug #761] enhanced $output with nl2br()
 * @since 3.0 <inex> 20.06.2019 Updated to fetch and process new layout options such as "sidebar_tpl"
 * @since 3.1 <inex> 21.06.2019 Fixed FIXME "Funktion so überarbeiten, dass 'border == 1' schönen Output" und TODO "Views aus dem Code entfernen"
 * @since 3.2 <inex> 02.11.2019 Added output of {comments} if `allow_comments` on tpl=true.
 *
 * @TODO Add nl2br($output) to convert all newlines to <br>
 *
 * @see _tpl_assigns()
 * @param string $tpl_name Smarty Template Name
 * @param string $tpl_source Pass by reference: Smarty Template-Source (Template content)
 * @param object $smarty Pass by reference: Smarty Class-object
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return bool Returns true/false depening on if a successful execution was possible, or not 
 */
function smartyresource_tpl_get_template($tpl_name, &$tpl_source, &$smarty)
{
	global $db, $user;

	if (!empty($tpl_name) && is_numeric($tpl_name))
	{
		$templateDataQuery = $db->query('SELECT * FROM templates tpl WHERE id='.$tpl_name, __FILE__, __LINE__, __FUNCTION__);
		$templateData = $db->fetch($templateDataQuery);
	} else {
		$templateData = false;
	}

	if ($templateData !== false)
	{
		$tpl_source = null;
		$output = null;

		if ($templateData['error']) $output .= '{literal}'.$templateData['error'].'{/literal}<br>{edit_link}[edit]{/edit_link}';
		else $output .= $templateData['tpl'];

		/** Set template layout settings */
		if ($templateData['border'] == 0) {
				$class = '';
				$footer = '';
		}else if ($templateData['border'] == 1) {
				$class = 'class="border"';
				$footer = '<tr><td>{include file="file:layout/partials/tplfooter.tpl"}</td></tr>';
		}else if ($templateData['border'] == 2) {
				$class = 'class="border"';
				$footer = '';
		}

		/** Ad-hoc Template-Struktur mit Content bauen */
		$tpl_source .= stripslashes(
			 '{_tpl_assigns}'
				.'{if tpl_permission($tpl.read_rights, $tpl.owner)}'
					.'{if $tpl.border > 0 && $tpl.id != $tpl.root}'
						.'<table width="100%" class="border">'
						.'<tr><td width="100%">'
					.'{/if}'
					.$output // TODO Add nl2br($output) to convert all newlines to <br>
					.($templateData['allow_comments'] ? '{comments}' : '' ) // Add Commenting-System
					.'{if $tpl.border > 0 && $tpl.id != $tpl.root}' // Wenn Template in anderem Template included wurde...
						.'</td></tr>'
							.'{if $tpl.border==1}'
								.'<tr><td>{include file="file:layout/partials/tplfooter.tpl"}</td></tr>'
							.'{/if}'
						.'</table>'
					.'{/if}'
				.'{else}'.
					'{error msg="[Error: Access denied on '.$tpl_name.']"}'.
				'{/if}'.
			'{/_tpl_assigns}'
		);

	} else {
		$tpl_source .= '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl '.$tpl_name.' existiert nicht.]"}</td></tr></table>';
	}
	//DEBUG VARS: var_dump($tpl_source);
	return true;
}


/*
 * Load Smarty TPL Resource
 *
 * Datenbankabfrage um '$tpl_timestamp' zuzuweisen
 * zusätzlich lokale tpl-infos setzen (smarty-variable $tpl)
 *
 * @author [z]biko
 * @author IneX
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 <inex> 19.06.2019 Updated to fetch Packages new via tpl_packages > packages relationship and Comments from Tpl-Setting
 *
 * @see load_packages()
 * @param string $tpl_name Smarty Template Name
 * @param string $tpl_timestamp Pass by reference: Timestamp des aktuellen Smarty Templates
 * @param object $smarty Pass by reference: Smarty Class-object
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $_tpl_stack Globales Array mit allen Template-Variablen
 * @return bool Returns true/false depening on if a successful execution was possible, or not 
 */
function smartyresource_tpl_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
	global $db, $_tpl_stack;

	if (!empty($tpl_name) && is_numeric($tpl_name))
	{
		$e = $db->query('SELECT id, title, word, LENGTH(tpl) size, owner, update_user, 
						 UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights, 
						 write_rights, force_compile, border, sidebar_tpl, allow_comments FROM templates WHERE id='.$tpl_name, __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
	} else {
		return false;
	}

	/** Check if recompile of template is necessary */
	if ($d['force_compile']) {
		$tpl_timestamp = 9999999999;
		$db->query('UPDATE templates SET force_compile="0" WHERE id='.$tpl_name, __FILE__, __LINE__, __FUNCTION__);
	}elseif ($d) {
		$tpl_timestamp = $d['last_update'];
	}else{
		$tpl_timestamp = 9999999999;
	}

	/** Assign tpl-infos to $_tpl_stack Array */
	$d['title'] = stripslashes($d['title']);
	$d['update'] = $d['last_update'];
	$d['root'] = $_GET['tpl'];	// @DEPRECATED (?)
	array_push($_tpl_stack, $d);

	/** Load required packages for the current template */
	load_packages($d['id'], $smarty);

	return true;
}

/**
 * tpl resource
 */
function smartyresource_tpl_get_secure($tpl_name, &$smarty_obj)
{
	// sicherheit des templates $tpl_name überprüfen
	return true;
}

/**
 * tpl resource
 */
function smartyresource_tpl_get_trusted($tpl_name, &$smarty_obj)
{
	// nicht verwendet; funktion muss aber existieren
}

/**
 * Load PHP-Package files required for a Smarty-Template
 * 
 * @author [z]biko
 * @author IneX
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 <inex> 19.06.2019 Updated to process Packages from tpl_packages > packages relationship instead of a Field-String
 *
 * TODO sollte das besser als Smarty PREfilter gelöst werden? https://www.smarty.net/docsv2/en/advanced.features.prefilters.tpl
 *
 * @see SMARTY_PACKAGES_DIR
 * @see index.php
 * @param integer $tpl_id Template ID for which to require() PHP-files (aka packages)
 * @param object $smarty Pass by reference: Smarty Class-object
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return bool Returns true/false depening on if any packages could be found and loaded
 */
function load_packages($tpl_id, &$smarty)
{
	global $db;

	/** Validate function parameters  */
	if (empty($tpl_id) || is_array($tpl_id) || !is_numeric($tpl_id)) return false;

	/** Retrieve packages link to $tpl_id from database */
	$packagesQuery = 'SELECT pkg.name as name FROM packages pkg INNER JOIN tpl_packages tplp ON pkg.id = tplp.package_id WHERE tplp.tpl_id='.$tpl_id;
	$packagesFound = $db->query($packagesQuery, __FILE__, __LINE__, __FUNCTION__);
	$numPackagesFound = $db->num($packagesFound);
	if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Found %d packages for template #%d', __FUNCTION__, __LINE__, $numPackagesFound, $tpl_id));

	/** 1 or more Packages found */
	if ($numPackagesFound > 0)
	{
		while ($package = $db->fetch($packagesFound))
		{
			/** Check if $package matches a PHP-File (Package) */
			$package_filepath = SMARTY_PACKAGES_DIR.$package['name'].SMARTY_PACKAGES_EXTENSION;
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Loading package "%s" from %s', __FUNCTION__, __LINE__, $package['name'], $package_filepath));
			if (stream_resolve_include_path($package_filepath) !== false)
			{
				require_once($package_filepath);
				return true;
			}
			/** Package-File NOT FOUND */
			else {
				error_log(sprintf('[WARN] <%s:%d> Package "%s" not found for template %s (#%d).', __FUNCTION__, __LINE__, $package_filepath, $package['name'], $tpl_id));
				trigger_error(t('error-package-missing', 'tpl', $package['name']), E_USER_WARNING);
				return false;
			}
		}
	}
	/** 0 Packages found (but this is no error) */
	elseif ($numPackagesFound === 0)
	{
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Template %s (#%d) has no packages associated', __FUNCTION__, __LINE__, $package['name'], $tpl_id));
		return true;
	}
}

/**
 * Load Menus for Navigation linked to a Smarty-Template
 *
 * Load Menus from templates > tpl_menus > menus relationship instead of Smarty inline markup {menu}
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 <inex> 02.07.2019 function added
 *
 * @see index.php
 * @param integer $tpl_id Template ID for which to render the linked Menus
 * @param object $smarty Pass by reference: Smarty Class-object
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return array|null Returns an Array with Menu names linked to $tpl_id, or NULL if no Menus set
 */
function load_navigation($tpl_id, &$smarty)
{
	global $db;

	/** Validate function parameters  */
	if (empty($tpl_id) || is_array($tpl_id) || !is_numeric($tpl_id)) return false;

	/** Retrieve Menus to $tpl_id from database */
	$menusQuery = 'SELECT m.tpl_id as tpl_id, m.name as name, (SELECT read_rights FROM templates WHERE id=m.tpl_id) as read_rights, (SELECT owner FROM templates WHERE id=m.tpl_id) as owner FROM menus m INNER JOIN tpl_menus tplm ON m.id = tplm.menu_id WHERE tplm.tpl_id='.$tpl_id.' ORDER BY name ASC';
	$menusFound = $db->query($menusQuery, __FILE__, __LINE__, __FUNCTION__);
	$numMenusFound = $db->num($menusFound);
	if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Found %d Menus for template #%d', __FUNCTION__, __LINE__, $numMenusFound, $tpl_id));

	/** 1 or more Menus found */
	if ($numMenusFound > 0)
	{
		while ($menu = $db->fetch($menusFound))
		{
			/** Validate permissions */
			error_log(sprintf('[DEBUG] <%s:%d> Loading menu (template) %d', __FUNCTION__, __LINE__, $menu['tpl_id']));
			if (tpl_permission($menu['read_rights'], $menu['owner'])) $tplMenus[] = $menu['name'];
			elseif (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> No permissions to load menu (template) #%d: owner %d vs read_rights %d', __FUNCTION__, __LINE__, $menu['tpl_id'], $menu['owner'], $menu['read_rights']));
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> All menus loaded for tpl #%d: %s', __FUNCTION__, __LINE__, $tpl_id, print_r($tplMenus,true)));
		return $tplMenus;
	}
	/** 0 Packages found (but this is no error) */
	elseif ($numMenusFound === 0)
	{
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Template %s (#%d) has no menus associated', __FUNCTION__, __LINE__, $package['name'], $tpl_id));
		return null;
	}
	/** for anything else... */
	else {
		return false;
	}
}

/**
 * Return a Smarty Menu Template
 * based on a name => tpl-id lookup from the database
 *
 * @author [z]biko
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 changed global $smarty to persistent function param &$smarty
 * @return array
 */
function menu ($name, &$smarty)
{
	//global $smarty;
	return smarty_menu(array('name'=>$name), $smarty);
}

/**
 * Get Templates based on word
 *
 * Datenbankabfrage um Word-Template zu laden und '$tpl_source' zuzuweisen
 * based on a word => tpl-id lookup from the database
 *
 * @author [z]biko
 * @global Object $db
 * @return boolean
 */
function smartyresource_word_get_template($tpl_name, &$tpl_source, &$smarty)
{
  global $db;

  $e = $db->query('SELECT id FROM templates WHERE word="'.$tpl_name.'"');
  $d = $db->fetch($e);

  if ($d) {
     smartyresource_tpl_get_template($d['id'], $tpl_source, $smarty);
  }else{
     $tpl_source = '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl '.$tpl_name.' existiert nicht.]"}</td></tr></table>';
  }

  return true;
}

/**
 * Get Templates based on word
 *
 * Datenbankabfrage um '$tpl_timestamp' zuzuweisen
 * zusätzlich lokale tpl-infos setzen (smarty-variable $tpl)
 * based on a word => tpl-id lookup from the database
 *
 * @author [z]biko
 * @global Object $db
 * @return boolean
 */
function smartyresource_word_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
  global $db;

  $e = $db->query('SELECT id FROM templates WHERE word="'.$tpl_name.'"', __FILE__, __LINE__, __FUNCTION__);
  $d = mysqli_fetch_array($e);

  smartyresource_tpl_get_timestamp($d['id'], $ts, $smarty);


  return true;
}

// word resource
function smartyresource_word_get_secure($tpl_name, &$smarty_obj)
{
	// sicherheit des templates $tpl_name überprüfen
	return true;
}

// word resource
function smartyresource_word_get_trusted($tpl_name, &$smarty_obj)
{
	// nicht verwendet; funktion muss aber existieren
}

/**
 * @see tpleditor.php
 * TODO <biko> deaktiviert bis ein besserer syntax checker gebaut ist
 */
function smarty_remove_invalid_html ($tpl, &$smarty)
{
	$tpl = preg_replace("(</*html[^>]*>)", "", $tpl);
	$tpl = preg_replace("(</*body[^>]*>)", "", $tpl);
	return $tpl;
}

/**
 * Build and return path to PHP-Files from the /www/packages/ dir
 * @DEPRECATED
 */
function package_path ($package)
{
	return $_SERVER['DOCUMENT_ROOT'].'/packages/'.$package.'.php';
}

/**
 * Smarty Klassen-Objekt instanzieren
 *
 * Konfiguriert und lädt ein $smarty Objekt basierend auf /smartylib/Smarty.class.php
 * @link https://www.smarty.net/docs/en/api.register.resource.tpl
 *
 * @author [z]biko
 * @author IneX
 * @date 03.01.2016
 * @version 3.0
 * @since 1.0 function added
 * @since 2.0 <inex> Moved Smarty directory paths to global configs
 * @since 3.0 <inex> 21.06.2019 Added registration of Smarty prefilter and postfilter functions
 *
 * @see config.inc.php, SMARTY_TEMPLATES_HTML, SMARTY_COMPILE, SMARTY_CACHE, SMARTY_TRUSTED_DIRS, SMARTY_TEMPLATES_HTML
 * @return Smarty Class-object
 */
function startSmarty()
{
	// start smarty
	$smarty = new ZorgSmarty; // Smarty-Klasse mit eigenen Baschtels von [z]biko erweitern
	$smarty_compiler = new ZorgSmarty_Compiler; // Smarty-Klasse mit eigenen Baschtels von [z]biko erweitern

	// debugging
	$smarty->debugging = false; // sollte ausgeschaltet sein. kann zu debug-zwecken eingeschaltet werden.
	$smarty->force_compile = false; // sollte ausgeschaltet sein. kann zu debug-zwecken eingeschaltet werden.

	// security
	$smarty->security = true;

	/** Smarty directories */
	$smarty->template_dir = SMARTY_TEMPLATES_HTML;
	$smarty->compile_dir = SMARTY_COMPILE;
	$smarty->cache_dir = SMARTY_CACHE;
	$smarty->trusted_dir = array(SMARTY_TRUSTED_DIRS);
	$smarty->secure_dir = array(SMARTY_TEMPLATES_HTML);

		// don't execute {php} tag
		$smarty->php_handling = SMARTY_PHP_QUOTE;

		// php functions that can be accessed in if-statements
		array_push($smarty->security_settings['IF_FUNCS'], 'tpl_permission');
		array_push($smarty->security_settings['IF_FUNCS'], 'comment_permission');
		array_push($smarty->security_settings['IF_FUNCS'], 'chr');
		array_push($smarty->security_settings['IF_FUNCS'], 'ord');

	// Ressourcen-Typ 'db:' registrieren
	$smarty->register_resource('db', array('smartyresource_tpl_get_template',
											'smartyresource_tpl_get_timestamp',
											'smartyresource_tpl_get_secure',
											'smartyresource_tpl_get_trusted'));

	$smarty->register_resource('tpl', array('smartyresource_tpl_get_template',
											'smartyresource_tpl_get_timestamp',
											'smartyresource_tpl_get_secure',
											'smartyresource_tpl_get_trusted'));

	$smarty->register_resource('word', array('smartyresource_word_get_template',
											 'smartyresource_word_get_timestamp',
											 'smartyresource_word_get_secure',
											 'smartyresource_word_get_trusted'));

	$smarty->register_resource('comments', array('smartyresource_comments_get_template',
												'smartyresource_comments_get_timestamp',
												'smartyresource_comments_get_secure',
												'smartyresource_comments_get_trusted'));
	$smarty->default_resource_type = 'db';

	/** Register Prefilters */
	//$smarty->register_prefilter('...');


	// recursion detection
	$smarty->recur_handler = 'file:recur_handler.html';
	$smarty->recur_allowed_tpls = array('file:layout/partials/tplfooter.tpl');

	// system functions
	$smarty->register_block('_tpl_assigns', '_tpl_assigns');

	return $smarty;
}


/**
 * Erweiterungen der Smarty Klasse für Zorg
 *
 * @author IneX
 * @date 03.01.2016
 * @version 1.0
 * @package zorg
 * @subpackage Smarty
 */
class ZorgSmarty extends Smarty
{
    /**
     * OWN BY BIKO
     * Templates that can be called recursive
     * used in function $this->_smarty_include
     *
     * @var string-array
     */
    var $recur_allowed_tpls = array();

    /**
     * OWN BY BIKO
     * Template that is called if a recursion was detected
     * used in function $this->_smarty_include
     *
     * @var string
     */
    var $recur_handler = "";

    /**
     * The class constructor.
     */
    /*private function __construct(){
	    //parent::__construct();
	    $smarty_compiler = new ZorgSmarty_Compiler;
    }*/

	/** OWN BY BIKO **************************************************************************************
     * compile a template manualy.
     *
     * @param string $template: Ressource, die kompiliert werden soll
     * @param string &$errormsg: Da wird die Fehlermeldung (falls es eine gibt) hingeschrieben
     * @return boolean
     */
     public function compile ($template, &$errors) {
        //$this->_redirect_fatal_error = true;
        global $_manual_compiler_active, $_manual_compiler_errors, $smarty;

        $old_force_compile = $this->force_compile;

        $this->force_compile = true;

        $_manual_compiler_active = 1;

        $result = $this->fetch($template);

        if (sizeof($_manual_compiler_errors)) {
           $errors = $_manual_compiler_errors;
           $ret = false;
        }else{
           	$ret = true;
        }

        $_manual_compiler_errors = array();
        $_manual_compiler_active = 0;
        $this->force_compile = $old_force_compile;

        return $ret;
     }


    /**
     * compile the template
     *
     * @param string $resource_name
     * @param string $compile_path
     * @return boolean
     */
    public function _compile_resource($resource_name, $compile_path)
    {

        $_params = array('resource_name' => $resource_name);
        if (!$this->_fetch_resource_info($_params)) {
            return false;
        }

        $_source_content = $_params['source_content'];
        $_cache_include    = substr($compile_path, 0, -4).'.inc';

        if ($this->_compile_source($resource_name, $_source_content, $_compiled_content, $_cache_include)) {
            // if a _cache_serial was set, we also have to write an include-file:
            if ($this->_cache_include_info) {
                require_once(SMARTY_CORE_DIR . 'core.write_compiled_include.php');
                smarty_core_write_compiled_include(array_merge($this->_cache_include_info, array('compiled_content'=>$_compiled_content, 'resource_name'=>$resource_name)),  $this);
            }

            $_params = array('compile_path'=>$compile_path, 'compiled_content' => $_compiled_content);
            require_once(SMARTY_CORE_DIR . 'core.write_compiled_resource.php');
            smarty_core_write_compiled_resource($_params, $this);

            return true;
        } else {


        	// OWN BY BIKO -----------------
	    	  // weil wenn ein manual compile error passiert eine leere error msg kommt.
	    	  global $_manual_compiler_active;
	    	  if ($_manual_compiler_active) return false;
	    	// END OWN -------------



            return false;
        }

    }
    /** END OWN BY BIKO **********************************************************************************/
}

/**
 * Erweiterungen der Smarty Compiler Klasse für Zorg
 *
 * @author IneX
 * @date 03.01.2016
 * @version 1.0
 * @package zorg
 * @subpackage Smarty
 */
class ZorgSmarty_Compiler extends Smarty
{
	/**
     * The class constructor.
     */
    //private function __construct(){}

	/**
     * display Smarty syntax error
     *
     * @param string $error_msg
     * @param integer $error_type
     * @param string $file
     * @param integer $line
     */
    function _syntax_error($error_msg, $error_type = E_USER_ERROR, $file=null, $line=null)
    {
        // OWN BY biko
		global $_manual_compiler_active, $_manual_compiler_errors;

		if ($_manual_compiler_active) {
			array_push($_manual_compiler_errors, "smarty syntax error on line ".$this->_current_line_no.": $error_msg");
		}else{
			$this->_trigger_fatal_error("smarty syntax error: $error_msg", $this->_current_file, $this->_current_line_no, $file, $line, $error_type);
		}
		//original code:
        //$this->_trigger_fatal_error("syntax error: $error_msg", $this->_current_file, $this->_current_line_no, $file, $line, $error_type);
    	// END OWN

    }
}

if (!isset($smarty)) $smarty = startSmarty();

// required smarty files for registering all smarty features etc.
require_once(__DIR__.'/smarty.fnc.php');
//require_once(__DIR__.'/smarty_menu.php'); // @DEPRECATED
require_once(__DIR__.'/comments.fnc.php');
