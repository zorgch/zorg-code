<?
global $smarty, $user;

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


// Addle
$smarty->register_function("addle_highscore", "smarty_addle_highscore"); // anzahl

// 
$smarty->register_function("apod", "smarty_apod");

// Chat
//$smarty->register_function("chat", "smarty_chat");
$smarty->register_function("assign_chatmessages", "smarty_assign_chatmessages");


// colors
$color = array(
	'background'			=> "#".BACKGROUNDCOLOR,
	'tablebackground'	=> "#".TABLEBACKGROUNDCOLOR,
	'border'					=> "#".BORDERCOLOR,
	'font' 						=> "#".FONTCOLOR,
	'header'					=> "#".HEADERBACKGROUNDCOLOR,
	'link'						=> "#".LINKCOLOR,
	'newcomment'			=> "#".NEWCOMMENTCOLOR,
	'owncomment'			=> "#".OWNCOMMENTCOLOR,
	'menu1'						=> "#".MENUCOLOR1,
	'menu2'						=> "#".MENUCOLOR2
);
$smarty->assign("color", $color);


// events
if($user != null) { // nur für eingeloggte
	$smarty->assign("num_new_events", Events::getNumNewEvents());
	$smarty->register_function("assign_event_hasjoined", "smarty_assign_event_hasjoined");
	$smarty->register_function("event_hasjoined", "smarty_event_hasjoined");
}
$smarty->assign("event_newest", Events::getEventNewest());
$smarty->assign("nextevents", Events::getNext());
$smarty->register_function("assign_yearevents", "smarty_assign_yearevents");
$smarty->assign("eventyears", Events::getYears());
$smarty->register_function("assign_event", "smarty_assign_event");
$smarty->register_function("assign_visitors", "smarty_assign_visitors");


// rezepte
if($user != null) { // nur fŸr eingeloggte
	$smarty->register_function("assign_rezept_voted", "smarty_assign_rezept_voted");
}
$smarty->assign("rezept_newest", Rezepte::getRezeptNewest());
$smarty->assign("categories", Rezepte::getCategories());
$smarty->register_function("assign_rezepte", "smarty_assign_rezepte");
$smarty->register_function("assign_rezept", "smarty_assign_rezept");
//$smarty->register_function("assign_rezept_voted", "smarty_assign_rezept_voted");
$smarty->register_function("assign_rezept_score", "smarty_assign_rezept_score");


// layout
$smarty->register_block(		"zorg"		, "smarty_zorg");    	// {zorg title="Titel"}...{/zorg}   // displays the zorg layout (including header, menu and footer)
$smarty->register_function(		"url"		, "smarty_link");  		// <a href={link id=x word="x" param="urlparams"}>  default tpl ist das akutelle
$smarty->register_block(		"link"		, "smarty_html_link");  // {html_link tpl=x param="urlparams"}text{/a}   default tpl = das aktuelle
$smarty->register_block(		"new_link"	, "smarty_new_tpl_link"); // shows a link to the editor with new tpl.
$smarty->register_block(		"button"	, "smarty_html_button"); // {button tpl=x param="urlparams"}button-text{/button}
$smarty->register_function(		"spc"		, "smarty_space"); 		// {space i=5}
$smarty->register_block(		"form"		, "smarty_form");   	// {form param="urlparams" formid=23 upload=1}..{/form}
$smarty->register_function(		"error"		, "smarty_error");  	// {error msg="Fehler!"}
$smarty->register_function(		"state"		, "smarty_state");  	// {state msg="Update erfolgreich"}
$smarty->register_block(		"table"		, "smarty_table");
$smarty->register_block(		"tr"		, "smarty_tr");
$smarty->register_block(		"td"		, "smarty_td");


// files / filemanager
$smarty->register_function("gettext", "smarty_gettext");


// forum, comments
$smarty->register_function("comments", "smarty_comments");  // {comments}  f?gt comments zu diesem tpl an.
$smarty->register_function("latest_comments", "smarty_latest_comments");  // {latest_comments anzahl=10 board=t title="Tabellen-Titel"}  // letzte comments aus board (optional)
$smarty->register_function("latest_threads", "smarty_latest_threads");    // {latest_threads}
$smarty->register_function("unread_comments", "smarty_unread_comments");  // {unread_comments board=t title="Tabellen-Titel"}
$smarty->register_function("3yearold_threads", "smarty_3yearold_threads"); // {3yearold_threads}
//$smarty->assign("comment_unread_link", Forum::getUnreadLink());
$smarty->register_function("commentingsystem", "smarty_commentingsystem");


// gallery
$smarty->register_function("random_pic", "getRandomThumb");  // {random_pic}  displays a random thumb out of the gallery
$smarty->register_function("daily_pic", "getDailyThumb");    // {daily_pic}   displays the pic of the day
$smarty->register_function("random_albumpic", "smarty_get_randomalbumpic");
$smarty->register_function("top_pics", "smarty_top_pics");
$smarty->register_function("user_pics", "smarty_user_pics");
$smarty->register_function("assign_users_on_pic", "smarty_assign_users_on_pic");



// imap
$smarty->register_function("new_imap", "smarty_getNumNewImap");


// menu
$smarty->register_compiler_function("menuname", "smarty_menuname", false);
$smarty->register_block("menubar", "smarty_menubar");
$smarty->register_block("menuitem", "smarty_menuitem");
$smarty->register_function("menu", "smarty_menu");


// quotes
$smarty->register_function("random_quote", "smarty_getrandomquote");  // {random_quote}  display a random quote
$smarty->register_function("daily_quote", "smarty_getdailyquote");    // {daily_quote}   display a daily quote


// polls
$smarty->register_function("poll", "smarty_poll"); // {poll id=23}


//Shoot the lamber
$smarty->register_function("open_stl_link", "getOpenSTLLink");


// smarty
$smarty->register_function("latest_updates", "getLatestUpdates");   // {latest_updates}  table mit den letzten smarty-updates
$smarty->register_block("edit_link", "smarty_edit_link");        // {edit_link tpl=x}  link zum tpl-editor, default ist aktuelles tpl
$smarty->register_function("edit_url", "smarty_edit_link_url");	// {edit_url tpl=x}  tpl ist optional. default: aktuelles tpl.


//spaceweather
$smarty->register_function("spaceweather", "spaceweather_ticker");

//peter
$smarty->register_function("peter","peter_zuege");

//sql errors
$smarty->register_function("sql_errors","smarty_sql_errors");
$smarty->assign("num_errors", $num_errors);

// Stockbroker ---------------------------------------------------------------------------
//{assign_stocks anzahl=100 page=$smarty.get.page}
$smarty->register_function("assign_stocklist", "stockbroker_assign_stocklist");
function stockbroker_assign_stocklist($params, &$smarty) {
	$smarty->assign("stocklist", Stockbroker::getStocklist($params['anzahl'], $params['page']));
}
//{assign_kurs symbol=$kurs.symbol}
$smarty->register_function("assign_stock", "stockbroker_assign_stock");
function stockbroker_assign_stock($params, &$smarty) {
	$smarty->assign("stock", Stockbroker::getSymbol($params['symbol']));
}
$smarty->register_function("assign_searchedstocks", "stockbroker_assign_searchedstocks");
function stockbroker_assign_searchedstocks($params, &$smarty) {
	$smarty->assign("searchedstocks", Stockbroker::searchstocks($params['search']));
}
$smarty->register_function("update_kurs", "stockbroker_update_kurs");
function stockbroker_update_kurs($params, &$smarty) {
	Stockbroker::updateKurs($params['symbol']);
}
$smarty->register_function("getkursbought", "stockbroker_getkursbought");
function stockbroker_getkursbought($params) {
	global $user;
	return Stockbroker::getKursBought($user->id, $params['symbol']);
}
$smarty->register_function("getkurs", "stockbroker_getkurs");
function stockbroker_getkurs($params) {
	return Stockbroker::getKurs($params['symbol']);
}

//sunrise
$smarty->assign("sun",$sun);
$smarty->assign("sunset",$sunset);
$smarty->assign("sunrise",$sunrise);
$smarty->assign("country",$country);
$smarty->assign("country_image","images/country/flags/".$image_code.".png");


// Tauschbörse
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
$smarty->register_function("assign_artikel", "smarty_assign_artikel");
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


// system
$smarty->assign("request", var_request());  // associative array:  page = requested page / params = url parameter / url = page+params
$smarty->register_function("url_params", "url_params");
$smarty->register_function("sizeof", "smarty_sizeof");
$smarty->register_function("get_changed_url", "smarty_get_changed_url");
$smarty->assign("url", getURL());
$smarty->assign('self', $_SERVER['PHP_SELF']); // Self = Aktuelle Seiten-URL


// text modification
$smarty->register_modifier("datename", "datename");		// {$timestamp|datename}  // konviertiert einen timestamp in ein anst?ndiges datum/zeit
$smarty->register_modifier("stripslashes", "stripslashes"); // Modifier für die Funktion stripslashes() wie in PHP
$smarty->register_block("substr", "smarty_substr");		// {substr from=2 to=-1}text{/substr}  // gleich wie php-fnc substr(text, from, to)
$smarty->register_modifier("strstr", "strstr");			// Modifier für die Funktion strstr() wie in PHP
$smarty->register_modifier("stristr", "stristr");		// Modifier für die Funktion stristr() wie in PHP (Gross-/Kleinschreibung ignorieren)
$smarty->register_modifier("sizebytes", "smarty_sizebytes");	// stellt z.B: ein 'kB' dahinter und konvertiert die zahl.
$smarty->register_modifier("quantity", "smarty_quantity");		// {$anz|quantity:Zug:Züge}
$smarty->register_block("trim", "smarty_trim");
$smarty->register_modifier("number_quotes", "smarty_number_quotes");
$smarty->register_function("htmlentities", "smarty_htmlentities");	// Registriert für Smarty die Funktion htmlentities() aus PHP
$smarty->register_modifier("htmlentities", "htmlentities");			// Registriert für Smarty den Modifier htmlentities() aus PHP
$smarty->register_function("base64encode", "base64_encode");		// Registriert für Smarty die Funktion base64_encode() aus PHP
$smarty->register_modifier("base64encode", "base64_encode");		// Registriert für Smarty den Modifier base64_encode() aus PHP
$smarty->register_modifier("concat", "smarty_concat");				// Registriert für Smarty den Modifier concat() aus PHP
$smarty->register_modifier("ltrim", "smarty_ltrim");				// Registriert für Smarty den Modifiert ltrim() aus PHP
$smarty->register_modifier("maxwordlength", "smarty_maxwordlength");	// Registriert für Smarty den Modifier maxwordlength() aus PHP


// usersystem -----------------------------------------------------------------
$smarty->register_modifier("name", "smarty_name");
$smarty->register_modifier("username", "smarty_username");  // {$userid|username}  // konvertiert userid zu username
$smarty->register_modifier("userpic", "smarty_userpic");    // {$userid|userpic}
$smarty->register_modifier("userpic2", "smarty_userpic2");	// {$userid|userpic2:0}
$smarty->register_modifier("usergroup", "smarty_usergroup");        // {$id|usergroup}   f?r tpl schreib / lese rechte
$smarty->assign("user", $user);
$smarty->assign("usertyp", array('alle'=>USER_ALLE, 'user'=>USER_USER, 'member'=>USER_MEMBER, 'special'=>USER_SPECIAL));
$smarty->assign("user_mobile", $user->from_mobile);
$smarty->assign("user_ip", $user->last_ip);
/* deprecated */	$smarty->register_modifier("userpage", "smarty_userpage");  // {$userid|userpage:0}  // 1.param = username (0) or userpic (1)
/* deprecated */	$smarty->register_function("onlineusers", "smarty_onlineusers");  //
$smarty->register_function("loginform", "loginform");
$smarty->register_block("member", "smarty_member");  // {member}..{/member}   {member noborder=1}..{/member}
$smarty->assign("comments_default_maxdepth", DEFAULT_MAXDEPTH);
$smarty->assign("online_users", var_online_users());
$smarty->register_modifier("ismobile", "smarty_userismobile");

$smarty->register_function("formfielduserlist", "smarty_FormFieldUserlist");
function smarty_FormFieldUserlist($params) {
	return usersystem::getFormFieldUserlist($params['name'], $params['size']);
}


// util
$smarty->register_function("datename", "smarty_datename");					// stellt ein Datum leserlich dar
$smarty->register_function("rand", "smarty_rand");							// {rand min=2 max=10 assign=var}
$smarty->register_function("assign_array", "smarty_function_assign_array");	// erlaubt es, mit Smarty Arrays zu erzeugen
$smarty->register_modifier("strip_anchor", "smarty_strip_anchor");			// link
$smarty->register_modifier("change_url", "smarty_change_url");				// newquerystring
$smarty->register_modifier("print_r", "smarty_print_r");					// ACHTUNG $myarray|@print_r verwenden!
$smarty->register_modifier("implode", "smarty_implode");					// String glue
$smarty->register_modifier("floor", "smarty_floor");
$smarty->register_modifier("print_array", "print_array");					// {print_array arr=$hans})




//----------------------------------------------------------------------------------



		// wrapper function for addle highscore
   function smarty_addle_highscore ($params) {
      if (!isset($params[anzahl])) $params[anzahl] = 5;
      return highscore_dwz($params[anzahl]);
   }

   function smarty_sql_errors($params) {
   		return  get_sql_errors($params['num'],$params['order'],$params['oby']);

	}



  ### Seitenausgabe ZORG ###
  function smarty_zorg ($params, $content, &$smarty, &$repeat) {
	$out = "";
	
	$out .= head($params['page_title'], true);
   	
   	$out .= $content;
   	
  	$out .= foot(0);
   	
   	
   	return $out;
   }
   


   // url to a template called by smarty.php;
   // if parameter button is set, the link is shown as a button.
   function smarty_link ($params) {
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


   // gibt einen link aus
   function smarty_html_link ($params, $content, &$smarty, &$repeat) {
      if (!$content) $content = "link";
      return '<a href="'.smarty_link($params).'">'.$content.'</a>';
   }


   // gibt einen button als link aus
   function smarty_html_button ($params, $content, &$smarty, &$repeat) {
      return '<input type="button" class="button" value="'.$content.'" onClick="self.location.href=\''.smarty_link($params).'\'">';
   }
   


   // inserts &nbsp;
   function smarty_space ($params) {
      return str_repeat("&nbsp;", $params[i]);
   }

   function smarty_name ($userid) {
      global $user;
      return $user->id2user($userid, false, false);
   }

   // converts id to username
   function smarty_username ($userid) {
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
   
	function smarty_userismobile ($userid)
	{
		global $user;
		return $user->ismobile($userid);
	}
	
	
	function smarty_userpic2 ($userid, $displayName=FALSE)
	{
		global $user;
		return $user->userpic($userid, $displayName);
	}


   // returns an opening-tag for a html-form. action is always 'smarty.php'
   // if you set the parameter 'formid', a hidden input with this formid is added.
   function smarty_form ($params, $content, &$smarty, &$repeat) {
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


	function smarty_latest_threads ($params) {
	   return Forum::getLatestThreads();
	}

	function smarty_getrandomquote ($params) {
	   return Quotes::getRandomQuote();
	}

	function smarty_latest_comments ($params) {
	   return Forum::getLatestComments($params['anzahl'], $params['title'], $params['board']);
	}


	function smarty_3yearold_threads ($params) {
	   return Forum::get3YearOldThreads();
	}


	/*
	function smarty_addle_highscore ($params) {
      if (!isset($params[anzahl])) $params[anzahl] = 5;
      return highscore_dwz($params[anzahl]);
   }*/

	function smarty_unread_comments ($params) {
	   return Forum::getLatestUnreadComments($params[title], $params[board]);
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


	function smarty_substr ($params, $content, &$smarty, &$repeat) {
	   if (isset($params['to'])) {
	      return substr($content, $params['from'], $params['to']);
	   }else{
	      return substr($content, $params['from']);
	   }
	}
	
	

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

	function smarty_onlineusers($params)
	{
		if (!isset($params[images])) $params[images] = false;
    return usersystem::online_users($params[images]);
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

	function edit_link_url ($tpl) {
		return "/smarty.php?tpleditor=1&tplupd=$tpl&location=".base64_encode($_SERVER['PHP_SELF'].'?'.url_params());
	}

	function smarty_edit_link_url ($params, &$smarty) {
		if (!$params['tpl']) {
			$vars = $smarty->get_template_vars();
			$params['tpl'] = $vars['tpl']['id'];
		}
		return edit_link_url($params['tpl']);
	}


	function smarty_getdailyquote ($params) {
		return Quotes::getDailyQuote();
	}


	function smarty_new_tpl_link ($params, $content, &$smarty, &$repeat) {
		global $smarty;

		$vars = $smarty->get_template_vars();

		return '<a href="/smarty.php?tpleditor=1&tplupd=new&location='.base64_encode($_SERVER['PHP_SELF'].'?'.url_params()).'">'.$content.'</a>';
	}


	function smarty_getNumNewImap ($params) {
		global $user;

		return ImapStatic::getNumnewmessages($user);
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

	function smarty_quantity ($count, $singular="", $plural="") {
		if ($count == 1) return "$count $singular";
		else return "$count $plural";
	}

	function smarty_trim ($params, $content, &$smarty, &$repeat) {
		if ($content) {
			return trim($content);
		}
	}

	function smarty_poll ($params) {
		return getPoll($params['id']);
	}

	function smarty_sizeof ($params) {
		return sizeof($params['array']);
	}

	function smarty_get_changed_url ($params) {
		return getChangedURL ($params['change']);
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

	// ACHTUNG, compiler-funktion (muss php-code zurückgeben)
	function smarty_menuname ($name, &$smarty) {
		return "echo smarty_menuname_exec ('$name');";
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


	function smarty_gettext ($params, &$smarty) {
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

	function smarty_datename($params) {
    return datename($params['date']);
	}

	function smarty_htmlentities($params) {
    return htmlentities($params['text']);
	}

	/* Inaktiv?! IneX, 2.5.09
	function smarty_chat() {
    return Chat::getInterfaceHTML();
	}*/


	function smarty_number_quotes($number, $num_decimal_places='', $dec_seperator='', $thousands_seperator='') {
		if (!$thousands_seperator) $thousands_seperator = '\'';
		if ($thousands_seperator)
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
		else
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
	}
	
	mt_srand();
	function smarty_rand ($params, &$smarty) {
		if (isset($params['min']) && isset($params['max'])) $z = mt_rand($params['min'], $params['max']);
		elseif (isset($params['min']) && !isset($params['max'])) $z = mt_rand($params['min']);
		elseif (!isset($params['min']) && isset($params['max'])) $z = mt_rand(0, $params['max']);
		elseif (isset($params['min']) && !isset($params['max'])) $z = mt_rand();

		if (isset($params['assign'])) $smarty->assign($params['assign'], $z);
		else return $z;
	}
	
	function smarty_apod ($params, &$smarty) {
		$rs = get_apod_id();
		return formatGalleryThumb($rs);
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
	   	$limit = ($params['limit'] == '' ? 5 : $params['limit']);
	   	//$options = ($params['options'] == '' ? '' : $params['options']);
   		
   		error_log("Call: getUserPics($userid, $limit, $options) in ".__FILE__,0);
   		//return getUserPics($userid, $limit, $options);
   		return getUserPics($userid, $limit);
	}
	

	function smarty_strip_anchor($url) {
		return substr($url, 0, strpos($url, "#"));
	}

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



	function smarty_assign_yearevents($params, &$smarty) {
		$smarty->assign("yearevents", Events::getEvents($params['year']));
	}

	function smarty_assign_event ($params, &$smarty) {
		$smarty->assign("event", Events::getEvent($params['id']));
	}


	// rezepte
	function smarty_assign_rezepte ($params, &$smarty) {
		$smarty->assign("rezepte", Rezepte::getRezepte($params['category']));
	}

	function smarty_assign_rezept ($params, &$smarty) {
		$smarty->assign("rezept", Rezepte::getRezept($params['id']));
	}

	if($user != null) { // nur fŸr eingeloggte
	function smarty_assign_rezept_voted ($params, &$smarty) {
		$smarty->assign("rezept_voted", Rezepte::hasVoted($params['user_id'], $params['rezept_id']));
	}
	}

	function smarty_assign_rezept_score ($params, &$smarty) {
		$smarty->assign("rezept_score", Rezepte::getScore($params['rezept_id']));
	}



	function smarty_commentingsystem($params) {
		Forum::printCommentingSystem($params['board'], $params['thread_id']);
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

	function smarty_change_url($url, $querystringchanges) {
		return changeURL($url, $querystringchanges);
	}

	function smarty_print_r($myarray) {
		return print_r($myarray, true);
	}

	function smarty_implode($myarray, $zeichen) {
		return implode($zeichen, $myarray);
	}

	function smarty_floor($zahl) {
		return floor($zahl);
	}

	function smarty_get_randomalbumpic($params) {
		return getAlbumLinkRandomThumb($params['album_id']);
	}

	function smarty_maxwordlength($text, $maxlength) {
		return maxwordlength($text, $maxlength);
	}
	
	
	function smarty_assign_users_on_pic ($params, &$smarty) {
		$smarty->assign("users_on_pic", Gallery::getUsersOnPic($params['picID']));
	}



	/*
	 * Smarty plugin
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
