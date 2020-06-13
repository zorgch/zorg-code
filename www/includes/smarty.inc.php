<?php function shutdown(){
  var_dump(error_get_last());
} register_shutdown_function('shutdown');
// ^- Put this at the very beginning of the php file

/**
 * zorg Smarty Template Engine Manager
 *
 * Instanziert und konfiguriert ein neues Smarty-Object für zorg.
 * Dieses wird in der globalen Variable $smarty gespeichert.
 *
 * @link https://github.com/smarty-php/smarty/ 
 *
 * @package zorg\Smarty
 * @author [z]biko
 * @author IneX
 * @version 3.1.36
 * @since 2.6.2 `05.06.2004` `[z]biko` Initial release with Smarty 2
 * @since 2.6.25 `23.05.2009` `IneX` Smarty 2.6.25 compatibility update
 * @since 2.6.29 `21.06.2015` `IneX` Smarty 2.6.29 compatibility update
 * @since 3.1.36 `30.04.2020` `IneX` Upgrade to Smarty 3.x using v3.1.36 compatibility
 *
 * @include config.inc.php Required
 * @include Smarty.class.php Required
 * @include usersystem.inc.php Include
 * @include comments.res.php (Included at the end of this file) An additional Smarty Resource Handler specific to Comments
 * @include smarty.fnc.php (Included at the end of this file) Additional Smarty Vars, Functions, Modifiers and Plugins
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once SMARTY_DIR.'Smarty.class.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

/**
 * Fehler nicht auszugeben, sondern in $_last_fatal_error speichern (OWN BY BIKO)
 * @var boolean $_manual_compiler_active Veranlasst den trigger_fatal_error den fehler nicht auszugeben, sondern in $_last_fatal_error zu speichern.
 */
$_manual_compiler_active = 0;
/**
 * Speichert den Fehler, falls $_redirect_fatal_error = true (OWN BY BIKO)
 * @var array $_manual_compiler_errors trigger_fatal_error speichert den Fehler hier rein, falls $_redirect_fatal_error = true
 */
$_manual_compiler_errors = array();
/**
 * Array mit allen Template Variablen (OWN BY BIKO)
 * @var array $_tpl_stack
 */
$_tpl_stack = array();

/**
 * Erweiterungen der Smarty Klasse für zorg
 *
 * Konfiguriert und lädt ein $smarty Objekt basierend auf /smartylib/Smarty.class.php
 *
 * @link https://www.smarty.net/docs/en/installing.smarty.extended.tpl A slightly more flexible way to setup Smarty
 *
 * @author IneX
 * @version 2.0
 * @since 1.0 `03.01.2016` `IneX` Class added
 * @since 2.0 Class updated with __construct for Smarty 3.x compatibility
 * @package zorg\Smarty
 */
class ZorgSmarty extends Smarty
{
	/**
	 * OWN BY BIKO
	 * Templates that can be called recursive
	 * used in function $this->_smarty_include
	 *
	 * @var array
	 * @deprecated Wurde in Smarty.class.php [entfernt](https://github.com/zorgch/zorg-code/commit/2123479dc35af1aa2f9ddd8d5101e9f59b8985ec#diff-8dfbc90aaacd06ebceaab45258530e66) seit Version 2.6.29 (IneX, 01.05.2020)
	 */
	var $recur_allowed_tpls = [];

	/**
	 * OWN BY BIKO
	 * Template that is called if a recursion was detected
	 * used in function $this->_smarty_include
	 *
	 * @var string
	 * @deprecated Wurde in Smarty.class.php [entfernt](https://github.com/zorgch/zorg-code/commit/2123479dc35af1aa2f9ddd8d5101e9f59b8985ec#diff-8dfbc90aaacd06ebceaab45258530e66) seit Version 2.6.29 (IneX, 01.05.2020)
	 */
	var $recur_handler = null;

	/**
	 * The class constructor.
	 * Used to set configs of our new Smarty object
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 4.0
	 * @since 1.0 `[z]biko` method added
	 * @since 2.0 `03.01.2016` `IneX` Moved Smarty directory paths to global configs
	 * @since 3.0 `21.06.2019` `IneX` (Deprecated) registration of Smarty prefilter and postfilter functions
	 * @since 4.0 `01.05.2020` `IneX` Replaces old StartSmarty() function for compatibility with Smarty 3
	 *
	 * @uses SMARTY_TEMPLATES_DIR
	 * @uses SMARTY_COMPILE
	 * @uses SMARTY_CACHE
	 * @uses SMARTY_TRUSTED_DIRS
	 */
	public function __construct()
	{
		/**
		 * Parent Class Constructor.
		 * These automatically get set with each new instance.
		 */
		parent::__construct();

		/**
		 * Configure Smarty Debugging.
		 *
		 * setDebugging() Muss auf PROD ausgeschaltet sein. Wird auf DEV zu debug-zwecken automatisch eingeschaltet.
		 * setForceCompile() Erzwingt das neukompilieren jedes Templates das an Smarty zum parsen gegeben wird.
		 */
		$this->setDebugging((DEVELOPMENT === true ? true : false));
		$this->setForceCompile(false);//false); @TODO DEAKTIVIEREN NACH DEV-TESTING!

		/**
		 * Set Smarty directories.
		 * @link https://www.smarty.net/docsv2/en/config.files.tpl Smarty For Template Designers: Example of config file syntax
		 */
		$this->setTemplateDir(SMARTY_TEMPLATES_DIR);
		$this->setCompileDir(SMARTY_COMPILE);
		$this->setCacheDir(SMARTY_CACHE);
		/** @TODO $this->setConfigDir('/web/www.example.com/guestbook/configs/'); */

		/**
		 * Configure Template Comlile behaviour.
		 */
		//$this->setCompileCheck(Smarty::COMPILECHECK_CACHEMISS); @FIXME => führt dazu dass jedes tpl/word Request identisch ist

		/**
		 * Configure Template Caching behaviour.
		 * - `Smarty::CACHING_OFF` to disable caching at all
		 * - `Smarty::CACHING_LIFETIME_CURRENT` tells Smarty to use the current $cache_lifetime variable to determine if the cache has expired
		 * - `Smarty::CACHING_LIFETIME_SAVED` tells Smarty to use the $cache_lifetime value at the time the cache was generated.
		 *    This way you can set the $cache_lifetime just before fetching the template to have granular control over when that particular cache expires. Use this with isCached().
		 * - `$cache_lifetime` is the length of time in seconds that a template cache is valid. Once this time has expired, the cache will be regenerated.
		 *    Value of 0 will cause the cache to always regenerate, -1 will force the cache to never expire.
		 */
		$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		$this->cache_lifetime = 0; // Default Cache Lifetime: always regenerate

		/**
		 * Enable custom Smarty Security Policy
		 */
		$this->enableSecurity('zorg_Smarty_Security_Policy');
	}

	/** OWN BY BIKO ************************************************************************************** */
	/**
	 * @TODO was macht diese Funktion anders als Smarty 3.1 Template Compile/fetch? (IneX, 01.05.2020)
	 *
	 * compile a template manualy.
	 *
	 * @param string $template: Ressource, die kompiliert werden soll
	 * @param string &$errormsg: Da wird die Fehlermeldung (falls es eine gibt) hingeschrieben
	 * @return boolean
	 */
	 public function compile ($template, &$errors)
	 {
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
	 * @TODO was macht diese Funktion anders als Smarty 3.1 Template Compile/fetch? (IneX, 01.05.2020)
	 *
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
		$_cache_include	= substr($compile_path, 0, -4).'.inc';

		if ($this->_compile_source($resource_name, $_source_content, $_compiled_content, $_cache_include)) {
			// if a _cache_serial was set, we also have to write an include-file:
			if ($this->_cache_include_info) {
				require_once SMARTY_CORE_DIR . 'core.write_compiled_include.php';
				smarty_core_write_compiled_include(array_merge($this->_cache_include_info, array('compiled_content'=>$_compiled_content, 'resource_name'=>$resource_name)),  $this);
			}

			$_params = array('compile_path'=>$compile_path, 'compiled_content' => $_compiled_content);
			require_once SMARTY_CORE_DIR . 'core.write_compiled_resource.php';
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
 * @package zorg\Smarty
 */
class ZorgSmarty_Compiler extends Smarty
{
	/**
	 * The class constructor.
	 */
	//private function __construct(){}

	/**
	 * @TODO was macht diese Funktion anders als Smarty 3.1 Template-Parser? (IneX, 01.05.2020)
	 *
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

/**
 * Custom Smarty Security Policy
 *
 * Needed to configure our custom Smarty Security white-listings of PHP functions, PHP modifiers, etc.
 * IMPORTANT: when used, will not include default settings! They have to be included here as well.
 *
 * @example $smarty->enableSecurity('My_Security_Policy');
 * @link https://www.smarty.net/docs/en/advanced.features.tpl#advanced.features.security Setting security policy by extending the Smarty_Security class
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `01.05.2020` `IneX` Method added for compatibility with Smarty 3
 * @package zorg\Smarty
 */
class zorg_Smarty_Security_Policy extends Smarty_Security
{
	/** Array list of all directories that are considered trusted (php scripts that are executed from templates) */
	public $trusted_dir = SMARTY_TRUSTED_DIRS;

	/** Array list of template directories that are considered secure */
	public $secure_dir = SMARTY_TEMPLATES_DIR;

	/** Tell Smarty how to handle (non-)execution of "<?php ... ?>" tags in templates */
	public $php_handling = Smarty::PHP_QUOTE;

	/** Array containing trusted PHP functions that can be used in templates. Default: 'isset', 'empty', 'count', 'sizeof', 'in_array', 'is_array', 'time' */
	public $php_functions = [
								 'isset' // default
								,'empty' // default
								,'count' // default
								,'sizeof' // default
								,'in_array' // default
								,'is_array' // default
								,'time' // default
								,'array' // zorg
								,'base64_encode' // zorg
							  	,'chr' // zorg
							  	,'comment_permission' // zorg
							  	,'list' // zorg
							  	,'ord' // zorg
							  	,'tpl_permission' // zorg
							];

	/** Array containing trusted PHP modifiers that can be used in templates. Default: 'escape', 'count', 'nl2br' */
	public $php_modifiers = [
								 'escape' // default
								,'count' // default
								,'nl2br' // default
							  	,'base64_decode' // zorg
							  	,'base64_encode' // zorg
							  	,'ceil' // zorg
							  	,'date_format' // zorg
							  	,'explode' // zorg
							  	,'htmlentities' // zorg
							  	,'in_array' // zorg
							  	,'microtime' // zorg
							  	,'round' // zorg
							  	,'truncate' // zorg
							  	,'stripslashes' // zorg
							  	,'strstr' // zorg
							  	,'stristr' // zorg
						];
}

/**
 * Register a custom Smarty "tpl:" & "db:" Resource-Handler
 *
 * @link https://www.smarty.net/docs/en/resources.custom.tpl Custom Smarty Template Resources
 *
 * @TODO Funktion so überarbeiten, dass 'border == 1' schönen Output hat (tplfoot.html soll obsolet werden)
 * @TODO Views aus dem Code entfernen
 *
 * @package zorg\Smarty
 * @author IneX
 * @version 1.0
 * @since 1.0 `30.04.2020` `IneX` Class added for Smarty 3.x compatibility
 */
class Smarty_Resource_Tpl extends Smarty_Resource_Custom
{
	/**
	 * prepared fetch() statement
	 * @var object Protected Variable to prevent using otherwise
	 */
	protected $fetch;
	/**
	 * prepared fetchTimestamp() statement
	 * @var object Protected Variable to prevent using otherwise
	 */
	protected $mtime;

	/**
	 * Load Smarty TPL Resource
	 *
	 * Datenbankabfrage um unser Template zu laden, und '$tpl_source' zuzuweisen
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 4.1
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `26.09.2018` [Bug #761] enhanced $output with nl2br()
	 * @since 3.0 `20.06.2019` `IneX` Updated to fetch and process new layout options such as "sidebar_tpl"
	 * @since 3.1 `21.06.2019` `IneX` Fixed FIXME "Funktion so überarbeiten, dass 'border == 1' schönen Output" und TODO "Views aus dem Code entfernen"
	 * @since 3.2 `02.11.2019` `IneX` Added output of {comments} if `allow_comments` on tpl=true.
	 * @since 4.0 `30.04.2020` `IneX` Converted to method, renamed "smartyresource_tpl_get_template" to "fetch" for compatibility with Smarty 3
	 * @since 4.1 `02.05.2020` `IneX` Added fetching of related Packages
	 * @since 4.2 `31.05.2020` `IneX` Changed output of Comments from {comments} to {commentingsystem board="t" thread_id=[TPL-ID]}
	 *
	 * @TODO Add nl2br($output) to convert all newlines to `<br>` (IneX)
	 *
	 * @uses _tpl_assigns()
	 * @uses self::fetchTimestamp()
	 * @uses self::load_packages()
	 * @uses ZorgSmarty_Functions::show_comments()
	 * @param string $tpl_name Smarty Template Name
	 * @param string $tpl_source Pass by reference: Smarty Template-Source (Template content)
	 * @param integer $mtime Pass by reference: Smarty Template-Source creation time
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return callable
	 */
	protected function fetch($tpl_name, &$tpl_source, &$mtime)
	{
		global $db, $user;

		if (!empty($tpl_name) && is_numeric($tpl_name))
		{
			$templateDataQuery = $db->query('SELECT * FROM templates tpl WHERE id='.$tpl_name, __FILE__, __LINE__, __METHOD__);
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

			/** Load required packages for the current template */
			$this->load_packages($d['id']);//, $smarty);

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
						.$output /** @TODO Add nl2br($output) to convert all newlines to `<br>` */
						.($templateData['allow_comments'] ? '{commentingsystem board="t" thread_id=$tpl.id}' : '' ) // Add Commenting-System for Board 't' (Template)
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
			$mtime = self::fetchTimestamp($tpl_name);

		} else {
			$tpl_source .= '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl '.$tpl_name.' existiert nicht.]"}</td></tr></table>';
			$mtime = null;
		}
	}

	/*
	 * Set Timestamp of the Smarty TPL Resource
	 *
	 * Datenbankabfrage um '$tpl_timestamp' zuzuweisen
	 * zusätzlich lokale tpl-infos setzen (smarty-variable $tpl)
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 3.1
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `19.06.2019` `IneX` Updated to fetch Packages new via tpl_packages > packages relationship and Comments from Tpl-Setting
	 * @since 3.0 `30.04.2020` `IneX` Converted to method, renamed "smartyresource_tpl_get_timestamp" to "fetchTimestamp" for compatibility with Smarty 3
	 * @since 3.1 `02.05.2020` `IneX` Moved fetching Packages to $this->fetch()
	 *
	 * @param string $tpl_name Smarty Template Name
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global array $_tpl_stack Globales Array mit allen Template-Variablen
	 * @return string Returns a strotime()-Result string of the TPL Resource timestamp
	 */
	protected function fetchTimestamp($tpl_name)
	{
		global $db, $_tpl_stack;

		if (!empty($tpl_name) && is_numeric($tpl_name))
		{
			$e = $db->query('SELECT id, title, word, LENGTH(tpl) size, owner, update_user, 
							 UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights, 
							 write_rights, force_compile, border, sidebar_tpl, allow_comments FROM templates WHERE id='.$tpl_name
							 , __FILE__, __LINE__, __FUNCTION__);
			$d = $db->fetch($e);
		} else {
			return null;
		}

		/** Check if recompile of template is necessary */
		if ($d['force_compile']) {
			$tpl_timestamp = 9999999999;
			$db->query('UPDATE templates SET force_compile="0" WHERE id='.$tpl_name, __FILE__, __LINE__, __METHOD__);
		}elseif ($d) {
			$tpl_timestamp = $d['last_update'];
		}else{
			$tpl_timestamp = 9999999999;
		}

		/** Assign tpl-infos to $_tpl_stack Array */
		$d['title'] = stripslashes($d['title']);
		$d['update'] = $d['last_update'];
		$d['root'] = stripslashes($d['id']);
		array_push($_tpl_stack, $d);

		return $tpl_timestamp;
	}

	/**
	 * Load required PHP-Package files for this Smarty-Template
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/index.php Used in zorg root (index.php)
	 *
	 * @TODO sollte das besser als Smarty PREfilter gelöst werden? https://www.smarty.net/docsv2/en/advanced.features.prefilters.tpl (IneX)
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.3
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `19.06.2019` `IneX` Updated to process Packages from tpl_packages > packages relationship instead of a Field-String
	 * @since 2.1 `18.04.2020` `IneX` replaced 'stream_resolve_include_path' with more performant 'is_file' (https://stackoverflow.com/a/19589043/5750030)
	 * @since 2.2 `30.04.2020` `IneX` Removed parameter "$smarty"
	 * @since 2.3 `13.05.2020` `IneX` Moved funtion to Class Smarty_Resource_Tpl and disabled duplicate call to it from `index.php`
	 *
	 * @uses SMARTY_PACKAGES_DIR
	 * @param integer $tpl_id Template ID for which to require() PHP-files (aka packages)
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool Returns true/false depening on if any packages could be found and loaded
	 */
	protected function load_packages($tpl_id)
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
				if (is_file($package_filepath) !== false)
				{
					/** Include Package-File */
					require_once $package_filepath;
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
}

/**
 * Register a custom Smarty "word:" Resource-Handler
 *
 * Datenbankabfrage um Word-Template zu laden und '$tpl_source' zuzuweisen
 * based on a word => tpl-id lookup from the database
 *
 * @link https://www.smarty.net/docs/en/resources.custom.tpl Custom Smarty Template Resources
 *
 * @package zorg\Smarty
 * @author IneX
 * @version 1.0
 * @since 1.0 `30.04.2020` `IneX` Class added for Smarty 3.x compatibility
 */
class Smarty_Resource_Word extends Smarty_Resource_Custom
{
	/**
	 * prepared fetch() statement
	 * @var object Protected Variable to prevent using otherwise
	 */
	protected $fetch;
	/**
	 * prepared fetchTimestamp() statement
	 * @var object Protected Variable to prevent using otherwise
	 */
	protected $mtime;

	/**
	 * Get Templates based on word
	 *
	 * Datenbankabfrage um Word-Template zu laden und '$tpl_source' zuzuweisen
	 * based on a word => tpl-id lookup from the database
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `30.04.2020` `IneX` Converted to method, renamed "smartyresource_word_get_template" to "fetch" for compatibility with Smarty 3
	 *
	 * @uses Smarty_Resource_Tpl::fetch()
	 * @uses self::fetchTimestamp()
	 * @param string $tpl_name Smarty Template Name
	 * @param string $tpl_source Pass by reference: Smarty Template-Source (Template content)
	 * @param integer $mtime Pass by reference: Smarty Template-Source creation time
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return callable
	 */
	protected function fetch($tpl_name, &$tpl_source, &$mtime)
	{
		global $db;

		$e = $db->query('SELECT id FROM templates WHERE word="'.$tpl_name.'"');
		$d = $db->fetch($e);

		if ($d !== false) Smarty_Resource_Tpl::fetch($d['id'], $tpl_source, $mtime);
		else $tpl_source = '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl '.$tpl_name.' existiert nicht.]"}</td></tr></table>';

		$mtime = self::fetchTimestamp($tpl_name);
	}

	/*
	 * Set Timestamp of the Smarty "word" Template Resource
	 *
	 * Datenbankabfrage um '$tpl_timestamp' zuzuweisen
	 * zusätzlich lokale tpl-infos setzen (smarty-variable $tpl)
	 * based on a word => tpl-id lookup from the database
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `30.04.2020` `IneX` Converted to method, renamed "smartyresource_word_get_timestamp" to "fetchTimestamp" for compatibility with Smarty 3
	 *
	 * @uses Smarty_Resource_Tpl::fetchTimestamp()
	 * @param string $tpl_name Smarty Template Name
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string Returns a strotime()-Result string of the TPL Resource timestamp
	 */
	protected function fetchTimestamp($tpl_name)
	{
		global $db;

		$e = $db->query('SELECT id FROM templates WHERE word="'.$tpl_name.'"', __FILE__, __LINE__, __METHOD__);
		$d = $db->fetch($e);

		return Smarty_Resource_Tpl::fetchTimestamp($d['id']);
	}

}

/**
 * Register a custom Smarty "comments:" Resource-Handler
 *
 * Comments-Thread Template-Resource handling fürs Forum.
 * Boards können mit dem einzelnen character (aus dem boards db-table) angegeben werden.
 * Aufbau:
 * - `comments:[BOARD]-[ID]` (z.B. `comments:b-23`) -> holt Comment #23 aus dem Bugtracker Board "b"
 * - `comments:12345` -> holt Comment #12345 aus dem default Board Forum "f"
 *
 * @link https://www.smarty.net/docs/en/resources.custom.tpl Custom Smarty Template Resources
 *
 * @package zorg\Smarty
 * @author IneX
 * @version 1.0
 * @since 1.0 `30.04.2020` `IneX` Class added for Smarty 3.x compatibility
 */
class Smarty_Resource_Comments extends Smarty_Resource_Custom
{
	/**
	 * prepared fetch() statement
	 * @var object Protected Variable to prevent using otherwise
	 */
	protected $fetch;
	/**
	 * prepared fetchTimestamp() statement
	 * @var object Protected Variable to prevent using otherwise
	 */
	protected $mtime;
	/**
	 * @var string Board key as extracted from request
	 */
	private $board_key;
	/**
	 * @var integer Comment-ID as extracted from request
	 */
	private $comment_id;
	/**
	 * @var boolean Stores if User is_loggedin() - or not
	 */
	private $user_is_loggedin;
	/**
	 * @var integer If User is logged-in, will hold the user's ID
	 */
	private $current_user_id;

	/**
	 * Comments get TPL resource
	 *
	 * Datenbankabfrage um Comment Template zu laden, und '$tpl_source' zuzuweisen
	 * e.g. Request string "comments:23" will be Comment #23 - defaulting to the Forum board "f"
	 * e.g. Request string "comments:b-5" will be Comment #5 from the Bugtracker board "b"
	 *
	 * @example {show_forumthread thread_id=23}
	 * @example {show_comment_first board=b comment_id=5}
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `26.10.2018` `IneX` various optimizations, structured html (schema.org)
	 * @since 2.1 `22.01.2020` `IneX` Fix sizeof() to only be called when variable is an array, and therefore guarantee it's Countable (eliminating parsing warnings)
	 * @since 3.0 `09.05.2020` `IneX` Moved funtion to new Class, renamed "smartyresource_comments_get_template" to "fetch" for compatibility with Smarty 3
	 *
	 * @param string $tpl_name Smarty Template Name
	 * @param string $tpl_source Pass by reference: Smarty Template-Source (Template content)
	 * @param integer $mtime Pass by reference: Smarty Template-Source creation time
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @uses Comment::isThread() Used to check and - if true - assign self::$comment_thread_id
	 * @uses usersystem::is_loggedin() Used to check and assign a true/false bool to self::$user_is_loggedin
	 * @uses show_comment_forumthread()
	 * @uses show_comment_thread()
	 * @return callable
	 */
	protected function fetch($tpl_name, &$tpl_source, &$mtime)
	{
		global $db, $user;

		/** Get Board and Comment-ID from request */
		$boardkey_and_commentid = explode('-', $tpl_name);

		/** Store Board and Comment-ID for futher usage in Class */
		$this->board_key = (sizeof($boardkey_and_commentid) === 2 ? (string)$boardkey_and_commentid[0] : null);
		$this->comment_id = (sizeof($boardkey_and_commentid) === 2 ? (is_numeric($boardkey_and_commentid[1]) && $boardkey_and_commentid[1] > 0 ? (integer)$boardkey_and_commentid[1] : null) : (integer)$boardkey_and_commentid[0]);
		$this->user_is_loggedin = $user->is_loggedin();
		$this->current_user_id = ($this->user_is_loggedin === true ? $user->id : null);

		/** Comment als Tree holen */
		if (!empty($this->board_key) && !empty($this->comment_id))
		{
			$tpl_source = sprintf('{show_comments comment_id=%d board=%s}', $this->comment_id, $this->board_key); // smartyresource_comments_get_childposts
			$mtime = $this->fetchTimestamp($this->comment_id);
		}
		/** Missing or invalid Board or Comment-ID */
		else {
			$tpl_source = '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl "'.$tpl_name.'" existiert nicht.]"}</td></tr></table>';
			$mtime = null;
		}
	}

	/*
	 * Set Timestamp of the Smarty Comment TPL Resource
	 *
	 * Datenbankabfrage um Timestamp des Comment-Templates auf `$mtime` zuzuweisen
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `19.06.2019` `IneX` Updated to fetch Packages new via tpl_packages > packages relationship and Comments from Tpl-Setting
	 * @since 3.0 `09.05.2020` `IneX` Converted to method, renamed "smartyresource_tpl_get_timestamp" to "fetchTimestamp" for compatibility with Smarty 3
	 *
	 * @uses Smarty_Resource_Comments::fetchTimestamp()
	 * @param integer $comment_id Comment ID which was requested through self::fetch()
	 * @return string Returns a strotime()-Result string of the TPL Resource timestamp
	 */
	protected function fetchTimestamp($comment_id)
	{
		global $db;

		if (!empty($comment_id) && is_numeric($comment_id))
		{
			$commentTimestamps = $db->fetch($db->query('SELECT date, date_edited FROM comments WHERE id='.$comment_id.' LIMIT 1', __FILE__, __LINE__, __METHOD__));
			return ($commentTimestamps['date_edited'] > 0 ? $commentTimestamps['date_edited'] : $commentTimestamps['date']);
		} else {
			return null;
		}
	}
}

/**
 * Smarty block-function to assign additional specific tpl-vars
 *
 * Basic vars are already assigned in Smarty_Resource_Custom::fetch()
 * **WICHTIG:** Muss _ausserhalb_ der Smarty_Resource-Klasse sein damit Verwendung von $smarty->assign() geht!
 *
 * @example {_tpl_assigns}
 * @link https://www.smarty.net/docs/en/plugins.block.functions.tpl Extending Smarty With Plugins: Block Functions
 *
 * @version 1.0
 * @since 1.0 `[z]biko` function added
 *
 * @global array $_tpl_stack Globales Array mit allen Template-Variablen
 */
function _tpl_assigns ($params, $content, &$smarty, &$repeat)
{
	global $_tpl_stack;

  	if ($repeat == true) { // öffnendes Smarty tag {_tpl_assigns}
  		// push wird in get_timestamp gemacht.

  		$smarty->assign('tpl', $_tpl_stack[sizeof($_tpl_stack)-1]);
		$smarty->assign('tpl_parent', $_tpl_stack[sizeof($_tpl_stack)-2]);
		$smarty->assign('tpl_level', sizeof($_tpl_stack));

	} else { // schliessendes Smarty tag {/_tpl_assigns}
		array_pop($_tpl_stack);

		$smarty->assign('tpl', $_tpl_stack[sizeof($_tpl_stack)-1]);
		$smarty->assign('tpl_parent', $_tpl_stack[sizeof($_tpl_stack)-2]);
		$smarty->assign('tpl_level', sizeof($_tpl_stack));

		return $content;
	}
}

/**
 * Assigned ein Smarty Array mit diversen Infos zum aktuellen Template Page Request
 *
 * @example {$request.tpl} = 23
 * @example {$request._tpl} = tpl:23
 * @example {$request._word} = word:yarak
 *
 * @author [z]biko
 * @version 2.0
 * @since 1.0 `[z]biko` function added
 * @since 2.0 `01.05.2020` `IneX` function moved from `smarty.fnc.php` - weil sie relativ zentral ist, nöd wohr.
 *
 * @return array Für Zugriff auf Werte via {$request.key}
 */
function var_request()
{
   return [
   			 'page' => $_SERVER['PHP_SELF']
   			,'params' => $_SERVER['QUERY_STRING']
			,'url' => $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']
			,'tpl' => isset($_GET['tpl'])?$_GET['tpl']:''
			,'_tpl' => 'tpl:'.(isset($_GET['tpl'])?$_GET['tpl']:'')
			,'_word' => 'word:'.(isset($_GET['tpl'])?$_GET['tpl']:'')
		  ];
}

/**
 * Check permission to access template.
 *
 * @author [z]biko
 * @version 2.1
 * @since 1.0 `[z]biko` function added
 * @since 2.0 `20.06.2019` `IneX` Failsafe hinzugefügt
 * @since 2.1 `04.11.2019` `kassiopaia` fixes undefined indexes errors
 *
 * @FIXME ACHTUNG: template read_rights sind != der USER_xxx Level! z.B. read_rights=3 bedeutet "Template Owner only"...
 *
 * @uses hasTplAccess()
 * @param integer $group Gruppe Level-Nummer to check
 * @param integer $owner User-ID to check
 * @return boolean Result der Funktion tpl_permission()
 */
function tpl_permission($group, $owner)
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
 * @since 1.0 `[z]biko` function added
 */
function hasTplAccess($group, $owner, $userid, $usertyp)
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
 * Check permission to access a Forum Thead.
 * Access check a Thread comment template
 *
 * @author [z]IneX
 * @version 1.0
 * @since 1.0 `07.05.2020` `IneX` function added
 *
 * @uses tpl_permission()
 * @param integer $thread_id Thread-ID
 * @return boolean Result der Funktion tpl_permission()
 */
function tpl_comment_thread_permission($thread_id)
{
	global $db;
	$e = $db->query('SELECT * FROM templates WHERE id='.$thread_id, __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	return tpl_permission($d['read_rights'], $d['owner']);
}

/**
 * Auf Comment-Ebene statt wie tpl_comment_permission() für Threads? (IneX, 06.05.2020)
 *
 * @TODO Muss DB check zwecks Read-Permission des Users machen. Nicht implementiert aktuell; immer `true`. (IneX, 06.05.2020)
 *
 * @author [z]biko
 * @version 2.0
 * @since 1.0 `[z]biko` function added
 * @since 2.1 `06.05.2020` `IneX` Method moved from `comments.fnc.php` to `smarty.inc.php` for compatibility with Smarty 3
 */
function comment_read_permission($comment_id)
{
	return true;
}

/**
 * Commenting - Fetch threaded Comment-Tree-Structure TPL Resource
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `09.05.2020` `IneX` function added
 *
 * @used-by Smarty_Resource_Comments::fetch()
 */
function show_comments_tree($params, $smarty)
{
	global $db, $user;

	$commenttree_tpl_src = null;

	/** Fetch the initial Comment */
	$queryCommentDetails = 'SELECT 
								 c.*
								,UNIX_TIMESTAMP(c.date) date
								,UNIX_TIMESTAMP(c.date_edited) date_edited
								,(SELECT COUNT(id) FROM comments WHERE board="'.$params['board'].'" AND parent_id='.$params['comment_id'].') numchildposts
							 FROM comments c 
							 WHERE c.id = '.$params['comment_id'].'
							 GROUP BY c.id ASC';
	$commentDetails = $db->fetch($db->query($queryCommentDetails, __FILE__, __LINE__, __METHOD__));
	$smarty->assign('comment_data', $commentDetails);
	
	/** Fetch Breadcrumb (only if comments_top_additional=true) */
	if ($smarty->getTemplateVars('comments_top_additional') !== false)
	{
		$commenttree_tpl_src .= get_comment_breadcrumb($commentDetails['id'], $commentDetails['thread_id'], $commentDetails['board']);
	}
	
	/** Fetch current Comment Tpl Source */
	// TODO add Compile- & Cache-ID to ->fetch!
	$commenttree_tpl_src .= $smarty->fetch('file:modules/commenting/comment.tpl');

	/** Fetch Child-Comments - if any / if comments_no_childposts not false */
	if ($smarty->getTemplateVars('comments_no_childposts') !== false && $commentDetails['numchildposts'] >= 1 && $commentDetails['numchildposts'] > $user->maxdepth)
	{
		$commenttree_tpl_src .= get_comment_childposts(['parent_id' => $params['comment_id'], 'board' => $params['board'], 'maxdepth' => $user->maxdepth]);
	} else {
		
	}

	return $commenttree_tpl_src;
}

/**
 * Commenting - Fetch Comment Childposts TPL Resource
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `09.05.2020` `IneX` function added
 *
 * @used-by Smarty_Resource_Comments::fetch()
 */
function get_comment_childposts($params, $smarty)
{
	global $db;

	$first_childpost_parent = $params['parent_id'];
	$board_scope = $params['board'];
	$max_num_childposts = $params['maxdepth'];

	$childpostsSqlQuery = sprintf('SELECT id, (SELECT COUNT(id) FROM comments WHERE parent_id=%1$d AND board="%2$s") AS numchildposts FROM comments WHERE parent_id=%1$d AND board="%2$s" ORDER BY id', $first_childpost_parent, $board_scope);
	$childpostsMetadata = $db->fetch($db->query($childpostsSqlQuery, __FILE__, __LINE__, __METHOD__));

	// TODO add Compile- & Cache-ID to ->fetch!
	$smarty->assign('childposts_data', $childpostsMetadata);
	return $smarty->fetch('file:modules/commenting/comment_tree.tpl');
}

/**
 * Commenting - Fetch single Comment TPL Resource
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `31.05.2020` `IneX` function added
 *
 * @used-by Smarty_Resource_Comments::fetch()
 * @uses Comment:getRecordset()
 */
function show_comment($params, $smarty)
{
	global $db, $user;

	$board_key = (isset($params['board']) && is_string($params['board']) && strlen($params['board']) === 1 ? $params['board'] : null);
	$comment_id = (isset($params['comment']) && is_numeric($params['comment']) && $params['comment'] >= 1 ? $params['comment'] : null);
	$included_comment = (isset($params['included']) && $params['included'] == true ? true : false); // When a single Comment is included in another Smarty TPL

	if (!empty($board_key) && !empty($comment_id))
	{
		$commentDetails = Comment::getRecordset($comment_id, $board_key);
		if ($included_comment === true) $commentDetails['numchildposts'] = 0; // Force "no childposts"

		// TODO add Compile- & Cache-ID to ->fetch!
		$smarty->assign('comment_data', $commentDetails);
		return $smarty->fetch('file:modules/commenting/comment.tpl');
	}
	/** Comment-ID not found */
	else {
		//return '{error msg="Comment-ID '.$params['comment'].' in Board '.$params['board'].' not found"}';
		return $smarty->fetch('string:{error msg="Comment-ID '.$params['comment'].' in Board '.$params['board'].' not found"}');
	}	
}

/**
 * tpl resource - comments get navigation
 *
 * @author [z]biko
 * @author IneX
 * @version 3.1
 * @since 1.0 `[z]biko` function added
 * @since 2.0 `14.01.2019` `IneX` added schema.org tags
 * @since 3.0 `07.05.2020` `IneX` HTML output wurde in Smarty TPL `/commenting/comment_breadcrumb.tpl` ausgelagert
 * @since 3.1 `31.05.2020` `IneX` Function moved from comments.res.php to smarty.inc.php, renamed from `smartyresource_comments_get_navigation`
 *
 * @param integer $comment_id
 * @param integer $thread_id
 * @param string $board
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 * @return string Result of $smarty->fetch() (parsed output of Template with contents)
 */
function get_comment_breadcrumb($comment_id, $thread_id, $board)
{
	global $db, $smarty;

	$parent_id = $comment_id;
	
	/** Iterate up to build a Level-Up Array with all previous Comment-IDs */
	while ($parent_id > $thread_id)
	{
		// TODO klarer Kandidat für SQL Stored Procedure FOR-LOOP! (IneX, 07.05.2020)
		$up_query = $db->query('SELECT id, parent_id FROM comments WHERE id='.$parent_id, __FILE__, __LINE__, __FUNCTION__);
		$up = $db->fetch($up_query);
		$levelUps[] = [ 'comment_id' => $up['id'] ];
		$parent_id = $up['parent_id'];
	}

	/** Assign Vars to $smarty for template contents output */
	$smarty->assign('comment_id', $comment_id);
	$smarty->assign('thread_id', $thread_id);
	$smarty->assign('board', $board);
	$smarty->assign('parent_levelups', $levelUps);

	// TODO add Compile- & Cache-ID to ->fetch!
	return $smarty->fetch('file:modules/commenting/comment_breadcrumb.tpl');
}

/**
 * Load Menus for Navigation linked to a Smarty-Template
 *
 * Load Menus from templates > tpl_menus > menus relationship instead of Smarty inline markup {menu}
 *
 * @link https://github.com/zorgch/zorg-code/blob/master/www/index.php Used in zorg root (index.php)
 *
 * @author IneX
 * @version 1.1
 * @since 1.0 `02.07.2019` `IneX` function added
 * @since 1.1 `30.04.2020` `IneX` Removed parameter "$smarty"
 *
 * @param integer $tpl_id Template ID for which to render the linked Menus
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return array|null Returns an Array with Menu names linked to $tpl_id, or NULL if no Menus set
 */
function load_navigation($tpl_id)
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
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Loading menu (template) %d', __FUNCTION__, __LINE__, $menu['tpl_id']));
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
 *
 * Based on a name => tpl-id lookup from the database
 *
 * @author [z]biko
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 changed global $smarty to persistent function param &$smarty
 *
 * @return array
 */
function menu($name, &$smarty)
{
	return smarty_menu(array('name'=>$name), $smarty);
}

/**
 * Instantiate the main ZorgSmarty object as $smarty
 *
 * @see ZorgSmarty()
 * @see ZorgSmarty_Compiler() deprecated? (IneX, 01.05.2020)
 */
if (!isset($smarty)) $smarty = new ZorgSmarty;
//$smarty_compiler = new ZorgSmarty_Compiler; // Smarty-Klasse mit eigenen Baschtels von [z]biko erweitern

/**
 * Custom Smarty Ressourcen-Typ 'db:' registrieren.
 * @see Smarty_Resource_Tpl()
 */
$smarty->registerResource('db', new Smarty_Resource_Tpl());

/**
 * Custom Smarty Ressourcen-Typ 'tpl:' registrieren.
 * @see Smarty_Resource_Tpl()
 */
$smarty->registerResource('tpl', new Smarty_Resource_Tpl());

/**
 * Custom Smarty Ressourcen-Typ 'word:' registrieren.
 * @see Smarty_Resource_Word()
 */
$smarty->registerResource('word', new Smarty_Resource_Word());

/**
 * Custom Smarty Ressourcen-Typ 'Comments:' registrieren.
 * @see Smarty_Resource_Comments()
 */
$smarty->registerResource('comments', new Smarty_Resource_Comments());
$smarty->registerPlugin('function', 'show_comments', 'show_comments_tree');
$smarty->registerPlugin('function', 'show_comment', 'show_comment');

/**
 * This tells smarty what resource type to use implicitly.
 * @link https://www.smarty.net/docs/en/variable.default.resource.type.tpl
 */
$smarty->default_resource_type = 'db';

/**
 * Register custom Smarty Prefilters.
 *
 * @link https://www.smarty.net/docs/en/api.register.filter.tpl
 * @example $smarty->registerFilter(string type "pre"|"post"|"output"|"variable", mixed callback string containing the function name);
 */
//none yet

/**
 * Custom Smarty templates recursion detection.
 * @deprecated Wurde in Smarty.class.php [entfernt](https://github.com/zorgch/zorg-code/commit/2123479dc35af1aa2f9ddd8d5101e9f59b8985ec#diff-8dfbc90aaacd06ebceaab45258530e66) seit Version 2.6.29 (IneX, 01.05.2020)
 */
//$smarty->recur_handler = 'file:recur_handler.html';
//$smarty->recur_allowed_tpls = ['file:layout/partials/tplfooter.tpl'];

/**
 * Register custom Smarty root template functions.
 *
 * Variables can be accessed in Smarty-Templates using: {$variable}
 *
 * @link https://www.smarty.net/docs/en/api.register.plugin.tpl
 * @see _tpl_assigns() Smarty-Block: {_tpl_assigns}
 * @see var_request() Smarty-Array: {$request.key}
 */
$smarty->registerPlugin('block', '_tpl_assigns', '_tpl_assigns');
$smarty->assign('request', var_request());

/**
 * Include and register additional Smarty Vars, Modifiers, Functions and Plugins
 */
require_once INCLUDES_DIR.'smarty.fnc.php';
