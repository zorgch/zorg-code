<?

/* File: includes/smarty.inc.php
 * ====================================
 *
 * Author:        biko
 * Created:       5.6.2004
 *
 * Instanziert und konfiguriert ein neues Smarty-Object. Dieses wird in
 * der globalen Variable $smarty gespeichert.
 *
 */

/** Pfad zu den Smarty Ordnern */
define('SMARTY_DIR', $_SERVER['DOCUMENT_ROOT'].'/../data/smartylib/');
define('SMARTY_TEMPLATES_HTML', $_SERVER['DOCUMENT_ROOT'].'/templates/');
define('SMARTY_CACHE', $_SERVER['DOCUMENT_ROOT'].'/../data/smartylib/cache/');
define('SMARTY_COMPILE', $_SERVER['DOCUMENT_ROOT'].'/../data/smartylib/templates_c/');


//$prof->startTimer( "smarty.inc.php: include_once smarty.class.php" );
include_once($_SERVER['DOCUMENT_ROOT'].'/../data/smartylib/Smarty.class.php');
//$prof->stopTimer( "smarty.inc.php: include_once smarty.class.php" );
//$prof->startTimer( "smarty.inc.php: include_once usersystem.inc.php" );
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
//$prof->stopTimer( "smarty.inc.php: include_once usersystem.inc.php" );
//$prof->startTimer( "smarty.inc.php: include_once comments.res.php" );
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/comments.res.php');
//$prof->stopTimer( "smarty.inc.php: include_once comments.res.php" );


	$_tpl_stack = array();

	function tpl_comment_permission ($thread_id) {
		global $db;
		$e = $db->query("SELECT * FROM templates WHERE id='$thread_id'", __FILE__, __LINE__);
		$d = $db->fetch($e);
		return tpl_permission($d['read_rights'], $d['owner']);
	}

   function tpl_permission ($group, $owner) {
      global $user;

      return hasTplAccess($group, $owner, $user->id, $user->typ);
   }

   function hasTplAccess ($group, $owner, $userid, $usertyp) {
   	if ($owner == $userid) return true;

      if ($group == USER_MEMBER) {  // member und schöne
         if ($usertyp == USER_MEMBER) {
            return true;
         }else{
            return false;
         }
      }elseif ($group == USER_USER) {  // normale user
         if ($usertyp == USER_MEMBER || $usertyp == USER_USER) {
            return true;
         }else{
            return false;
         }
      }elseif ($group == USER_ALLE) {  // ausgeloggte (=alle)
         return true;
      }else{
         return false;
      }
   }


   // assigns additional tpl-vars, basic vars are assigned smartyresource_tpl_get_template
   function _tpl_assigns ($params, $content, &$smarty, &$repeat) {
   	global $_tpl_stack;

	  	if ($repeat == true)  {   // öffnendes tag
	  		// push wird in get_timestamp gemacht.

	  		$smarty->assign("tpl", $_tpl_stack[sizeof($_tpl_stack)-1]);
   		$smarty->assign("tpl_parent", $_tpl_stack[sizeof($_tpl_stack)-2]);
   		$smarty->assign("tpl_level", sizeof($_tpl_stack));

   	}else{  // schliessendes tag
   		array_pop($_tpl_stack);

   		$smarty->assign("tpl", $_tpl_stack[sizeof($_tpl_stack)-1]);
   		$smarty->assign("tpl_parent", $_tpl_stack[sizeof($_tpl_stack)-2]);
   		$smarty->assign("tpl_level", sizeof($_tpl_stack));

   		return $content;
   	}
   }



   // tpl resource
   function smartyresource_tpl_get_template ($tpl_name, &$tpl_source, &$smarty) {
      // Datenbankabfrage um unser Template zu laden,
      // und '$tpl_source' zuzuweisen
      global $db, $user;

      $e = $db->query("SELECT * FROM templates WHERE id='$tpl_name'");
      $d = mysql_fetch_array($e);

      if ($d) {
         if ($d[border] == 0) {
            $class = "";
            $footer = "";
         }else if ($d[border] == 1) {
            $class = 'class="border"';
            $footer = '<tr><td bgcolor="{$color.border}">{include file="file:tplfooter.html"}</td></tr>';
         }else if ($d[border] == 2) {
            $class = 'class="border"';
            $footer = "";
         }


         if ($d[error]) $output = "{literal} $d[error]<br />{/literal}{edit_link}[edit]{/edit_link}";
         else $output = $d[tpl];


         $tpl_source = stripslashes(
         	'{_tpl_assigns}'.
			 	'{if tpl_permission($tpl.read_rights, $tpl.owner)}'.
			 		'{if $tpl.border > 0 && $tpl.id != $tpl.root}'.
			 			'<table width="100%" class="border">'.
				   	'<tr><td width="100%">'.
				   '{/if}'.
				   $output.
				   '{if $tpl.border > 0 && $tpl.id != $tpl.root}'.
				   	'</td></tr>'.
				      '{if $tpl.border==1}'.
				      	'<tr><td bgcolor="{$color.border}">{include file="file:tplfooter.html"}</td></tr>'.
				      '{/if}'.
				   	'</table>'.
				   '{/if}'.
				'{else}'.
					'{error msg="[Error: Access denied on '.$tpl_name.']"}'.
			   '{/if}'.
			   '{/_tpl_assigns}'
         );

      }else{
         $tpl_source = '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl '.$tpl_name.' existiert nicht.]"}</td></tr></table>';
      }

      return true;
   }


   // tpl resource
   function smartyresource_tpl_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
      // Datenbankabfrage um '$tpl_timestamp' zuzuweisen
      // zusätzlich lokale tpl-infos setzen (smarty-variable $tpl)

      global $db, $_tpl_stack;

      $e = $db->query("SELECT id, title, word, LENGTH(tpl) size, owner, update_user, packages,
                       UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights,
                       write_rights, force_compile, border FROM templates WHERE id='$tpl_name'", __FILE__, __LINE__);
      $d = mysql_fetch_array($e);

      // check compile necessary
      if ($d['force_compile']) {
         $tpl_timestamp = 9999999999;
         $db->query("UPDATE templates SET force_compile='0' WHERE id='$tpl_name'", __FILE__, __LINE__);
      }elseif ($d) {
         $tpl_timestamp = $d['last_update'];
      }else{
         $tpl_timestamp = 9999999999;
      }

      // assign tpl-infos.
      $d['title'] = stripslashes($d['title']);
      $d['update'] = $d['last_update'];
      $d['root'] = $_GET['tpl'];  // depricated
      array_push($_tpl_stack, $d);

      // load packages
	  	load_packages($d['packages']);

      return true;
   }

   // tpl resource
   function smartyresource_tpl_get_secure($tpl_name, &$smarty_obj) {
      // sicherheit des templates $tpl_name überprüfen
      return true;
   }

   // tpl resource
   function smartyresource_tpl_get_trusted($tpl_name, &$smarty_obj) {
      // nicht verwendet; funktion muss aber existieren
   }


   function load_packages ($packages) {
      $packs = explode("; ", $packages);
      foreach ($packs as $p) {
         if ($p) {
         	if (file_exists(package_path($p))) {
         		require_once(package_path($p));
         	}else{
         		user_error("Package '$p' not found.", E_USER_WARNING);
         	}
         }
      }
   }


   // word resource
   function smartyresource_word_get_template ($tpl_name, &$tpl_source, &$smarty) {
      // Datenbankabfrage um unser Template zu laden,
      // und '$tpl_source' zuzuweisen
      global $db;

      $e = $db->query("SELECT id FROM templates WHERE word='$tpl_name'");
      $d = mysql_fetch_array($e);

      if ($d) {
         smartyresource_tpl_get_template($d[id], $tpl_source, $smarty);
      }else{
         $tpl_source = '<table class="border"><tr><td>{error msg="[<b>Error:</b> tpl '.$tpl_name.' existiert nicht.]"}</td></tr></table>';
      }

      return true;
   }


   // word resource
   function smartyresource_word_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
      // Datenbankabfrage um '$tpl_timestamp' zuzuweisen
      // zusätzlich lokale tpl-infos setzen (smarty-variable $tpl)

      global $db;

      $e = $db->query("SELECT id FROM templates WHERE word='$tpl_name'", __FILE__, __LINE__);
      $d = mysql_fetch_array($e);

      smartyresource_tpl_get_timestamp($d[id], $ts, $smarty);


      return true;
   }

   // word resource
   function smartyresource_word_get_secure($tpl_name, &$smarty_obj) {
      // sicherheit des templates $tpl_name überprüfen
      return true;
   }

   // word resource
   function smartyresource_word_get_trusted($tpl_name, &$smarty_obj) {
      // nicht verwendet; funktion muss aber existieren
   }






   function smarty_remove_invalid_html ($tpl, &$smarty) {
      $tpl = preg_replace("(</*html[^>]*>)", "", $tpl);
      $tpl = preg_replace("(</*body[^>]*>)", "", $tpl);
      return $tpl;
   }


   function package_path ($package) {
      return $_SERVER['DOCUMENT_ROOT'].'/packages/'.$package.'.php';
   }

   function startSmarty () {
      // start smarty
      $smarty = new Smarty;

      $smarty->force_compile = false;    // sollte ausgeschaltet sein. kann zu debug-zwecken eingeschaltet werden.


      // security
      $smarty->security = true;

      // directories
      $smarty->template_dir = SMARTY_TEMPLATES_HTML;
      $smarty->compile_dir = SMARTY_COMPILE;
      $smarty->cache_dir = SMARTY_CACHE;
      $smarty->trusted_dir = array($_SERVER['DOCUMENT_ROOT'].'/scripts/');
      $smarty->secure_dir = array(SMARTY_TEMPLATES_HTML);

      // don't execute {php} tag
      $smarty->php_handling = SMARTY_PHP_QUOTE;

      // php functions that can be accessed in if-statements
      array_push($smarty->security_settings['IF_FUNCS'], "tpl_permission");
      array_push($smarty->security_settings['IF_FUNCS'], "comment_permission");
      array_push($smarty->security_settings['IF_FUNCS'], "chr");
      array_push($smarty->security_settings['IF_FUNCS'], "ord");


      // Ressourcen-Typ 'db:' registrieren
      $smarty->register_resource("db", array("smartyresource_tpl_get_template",
                                             "smartyresource_tpl_get_timestamp",
                                             "smartyresource_tpl_get_secure",
                                             "smartyresource_tpl_get_trusted"));
      $smarty->default_resource_type = "db";

      $smarty->register_resource("tpl", array("smartyresource_tpl_get_template",
                                              "smartyresource_tpl_get_timestamp",
                                              "smartyresource_tpl_get_secure",
                                              "smartyresource_tpl_get_trusted"));

      $smarty->register_resource("word", array("smartyresource_word_get_template",
                                               "smartyresource_word_get_timestamp",
                                               "smartyresource_word_get_secure",
                                               "smartyresource_word_get_trusted"));

		$smarty->register_resource("comments", array("smartyresource_comments_get_template",
	                                               "smartyresource_comments_get_timestamp",
	                                               "smartyresource_comments_get_secure",
	                                               "smartyresource_comments_get_trusted"));

      // recursion detection
      $smarty->recur_handler = "file:recur_handler.html";
      $smarty->recur_allowed_tpls = array("file:tplfooter.html");


      // system functions
      $smarty->register_block("_tpl_assigns", "_tpl_assigns");

      return $smarty;
   }


   //$prof->stopTimer( "smarty.inc.php: all functions" );


   if (!isset($smarty)) $smarty = startSmarty();


   // includes for smarty register
   require_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.fnc.php');
   require_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty_menu.php');
   require_once($_SERVER['DOCUMENT_ROOT'].'/includes/comments.fnc.php');

?>