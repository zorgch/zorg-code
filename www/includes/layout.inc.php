<?PHP
error_reporting(E_ALL ^ E_NOTICE);
//=============================================================================
// includes
//=============================================================================

setlocale(LC_TIME,"de_CH");

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/sunrise.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/css.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/imap.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/menu.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/schach.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/wiki.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/peter.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/rezepte.inc.php');


$username = $_SERVER['QUERY_STRING'];
$sql = "SELECT id FROM user WHERE username = '$username'";
$result = $db->query($sql,__FILE__,__LINE__);
if($db->num($result)) {
	$rs = $db->fetch($result);
	header("Location: profil.php?user_id=".$rs['id']);
}




//=============================================================================
// Defines
//=============================================================================

// bodysettings wird verwendet, um den div nach den menüs wieder zu öffnen.
define("BODYSETTINGS", 'align="center" valign="top" style="margin: 0px 40px;"');


//=============================================================================
// Functions
//=============================================================================



### HEADER ###
function head($menu, $title="", $return = 0) {
	global $starttime, $user, $smarty, $sun, $country, $db, $layouttype;

	//rosenverkäufer
	peter::rosenverkaufer();

	$style_array = array("up" => "day.css", "down" => "night.css");
	$favicon = array("up" => "fav_day.ico","down" => "fav_night.ico");
	$out = "";
	$starttime = microtime();
	$out .= '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta name="geo.position" content="47.4233;9.37">
		<meta name="geo.region" content="CH-SG">
		<meta name="geo.placename" content="St. Gallen">
		<meta name="ICBM" content="47.4233, 9.37" />
		<title>'.$title.'@zorg.ch</title>
		<link rel="prefetch" href="forum.php">
		<link rel="stylesheet" type="text/css" href="includes/'.$style_array[$sun].'" >
		<link rel="shortcut icon" href="'.$favicon[$sun].'"  type="image/x-icon">
		<script type="text/javascript" src="includes/javascript.js"></script>
		
		<!-- RSS Feeds -->
		<link rel="alternate" type="application/rss+xml" title="RSS @ zorg.ch" href="http://www.zorg.ch/forum.php?layout=rss" />
		<link rel="alternate" type="application/rss+xml" title="Forum Feed @ zorg.ch" href="http://www.zorg.ch/forum.php?layout=rss&board=f" />
		<link rel="alternate" type="application/rss+xml" title="Events Feed @ zorg.ch" href="http://www.zorg.ch/forum.php?layout=rss&board=e" />
		<link rel="alternate" type="application/rss+xml" title="Gallery Feed @ zorg.ch" href="http://www.zorg.ch/forum.php?layout=rss&board=i" />
		<link rel="alternate" type="application/rss+xml" title="Rezepte Feed @ zorg.ch" href="http://www.zorg.ch/forum.php?layout=rss&board=r" />
		<link rel="alternate" type="application/rss+xml" title="Neuste Activities @ zorg.ch" href="http://www.zorg.ch/activities.php?layout=rss" />
		
		</head>
		
		';
	
	// Wenn es ein eingeloggter User ist, wird im Fenstertitel die Anzahl Unreads angezeigt...
	$out .= $user->islogged_in() ? "<body onload=\"unreads_2_title(document.getElementById('unreads'))\">" : "<body>";
	
	// Wenn die Startseite von einem Mobile Device aufgerufen wird, frage ob zu Mobile Zorg gewechselt werden soll
	if (str_replace('/','',$_SERVER['PHP_SELF']) == 'index.php')
	{
	$out .=	'
		<!-- Redirect to Mobile WebApp -->
		<script type="text/javascript">
		if (screen.width<640)
		{
			var check = confirm(\'Wechseln zu Mobile@Zorg?\');
			if (check)
				window.location="mobilezorg/"
		}
		</script>';
	}
	
	$out .=	'
		<center>

		<table height="97%" bgcolor="#'.BODYBACKGROUNDCOLOR.'" cellspacing="0" cellpadding="0" width="860">
		<tr><td valign="top" bgcolor="#'.BACKGROUNDCOLOR.'" height="100%">';

	$out .= $smarty->fetch( $user->zorger ? "tpl:56" : "tpl:672" ); // holt das main header (TPL #679)

	$out .= '<div '.BODYSETTINGS.'>';


	if ($user->mymenu) $out .= $smarty->fetch("tpl:$user->mymenu");

	if ($return) {
		return $out;
	}else{
		echo $out;
		return "";
	}
}



### FOOTER FÜR ZOOOMCLAN.ORG ###
function foot($author_id=3) {
	global $starttime, $user, $db, $smarty, $_TPLROOT;

	// sql query tracker
	if ($user->sql_tracker) {
	   $_SESSION[noquerys] = $db->noquerys;
	   $_SESSION[noquerytracks] = $db->noquerytracks;
	   $_SESSION[query_track] = $db->query_track;
	   $_SESSION[query_request] = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	   $qtracker = '<a href="smarty.php?tpl=25">[Details]</a>';
	}else{
	   $qtracker = "";
	   unset($_SESSION[noquerys]);
	   unset($_SESSION[query_track]);
	   unset($_SESSION[query_request]);
	   unset($_SESSION[noquerytracks]);
	}

	// tpl infos
	$tplinfo = "";
	$curlnk = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

	if (isset($_TPLROOT)) {
	   $vars = $smarty->get_template_vars();

	   $_TPLROOT = $vars['tplroot'];

   	$tplinfo .=
			' | '. smarty_sizebytes($_TPLROOT['size']).
			' | r: '. smarty_usergroup($_TPLROOT['read_rights']).
//			' | w: '. smarty_usergroup($_TPLROOT['write_rights']).
			' | updated: '.$user->id2user($_TPLROOT[update_user]).', '.datename($_TPLROOT['last_update']).
			' | <a href="smarty.php?tpl='.$_TPLROOT['id'].'">tpl='.$_TPLROOT['id'].'</a>';
		if ($_TPLROOT[word]) $tplinfo .= ' | word='.$_TPLROOT[word];

		if (tpl_permission($_TPLROOT[write_rights], $_TPLROOT[owner])) {
   		$tplinfo .= ' | '. edit_link('[edit]', $_TPLROOT['id'], $_TPLROOT['write_rights'], $_TPLROOT['owner']);
		}
	}


	return(

      '<br />
      </div>

		</td></tr>
      <tr>
         <td width="100%" align="center" valign="center" class="small" bgcolor="#'.TABLEBACKGROUNDCOLOR.'"
      		style="padding: 2px; border-top-style: solid; border-top-width: 1px; border-top-color: #'.BORDERCOLOR.';"
      	>'.
            //'<a href="/wiki.php?word=impressum">Impressum</a> | <a href="/wiki.php?word=privacy">Privacy-Policy</a> |'.
            'Parsetime:
            '.round((microtime()-$startparse), 2).'s |
            '.$db->noquerys.' SQL Querys '.$qtracker.'
      		'.$tplinfo.'<br />
            '.spaceweather_ticker().'<br />
            <script type="text/javascript">swisstimeJS()</script>
      	</td>
      </tr>

     	</table>


      </center>

		</body>
		</html>
	');

}



### FOOTER FÜR ZORG.CH ###
function zorg_foot($author_id=3) {
	global $starttime, $user, $db, $smarty, $_TPLROOT;
	

	// tpl infos
	$tplinfo = "";
	$curlnk = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

	if (isset($_TPLROOT)) {
	   $vars = $smarty->get_template_vars();

	   $_TPLROOT = $vars['tplroot'];

   	$tplinfo .=
			' | r: '. smarty_usergroup($_TPLROOT['read_rights']).
			' | updated: '.$user->id2user($_TPLROOT[update_user]).', '.datename($_TPLROOT['last_update']).
			' | <a href="smarty.php?tpl='.$_TPLROOT['id'].'">tpl='.$_TPLROOT['id'].'</a>';
		if ($_TPLROOT[word]) $tplinfo .= ' | word='.$_TPLROOT[word];

		if (tpl_permission($_TPLROOT[write_rights], $_TPLROOT[owner])) {
   		$tplinfo .= ' | '. edit_link('[edit]', $_TPLROOT['id'], $_TPLROOT['write_rights'], $_TPLROOT['owner']);
		}
	}


	return(

      '<br />
      </div>

		</td></tr>
      <tr>
         <td width="100%" align="center" valign="center" class="small" bgcolor="#'.TABLEBACKGROUNDCOLOR.'"
      		style="padding: 2px; border-top-style: solid; border-top-width: 1px; border-top-color: #'.BORDERCOLOR.';"
      	>'
      		.$tplinfo.'
            <br />
            '.swisstime(time(),1).'
      	</td>
      </tr>

     	</table>


      </center>

		</body>
		</html>
	');

}



function loginform() {

	global $user, $login_error;

	if($user->islogged_in()) {

		return '
			<td align="right" valign="middle">
			<b class="small">'.$user->id2user($_SESSION['user_id']).' eingeloggt</b>
			<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="logoutform">
				<input name="logout" type="submit" value="logout" class="button">
			</form>
		  </td>
		'
		;
	} else {
		is_string($login_error) ? $add = "<br /><b align='left' class='small'>".$login_error."</b>" : $add = "";
		return '
		<td align="right">
			<table>
				<tr>
					<td align="left" class="small">
						<form action="'.$_SERVER['PHP_SELF'].'?smarty.php?tpl=23" method="post" name="loginform">
							<a href="./profil.php?do=anmeldung&menu_id=13">Account erstellen</a><br />
							user <input tabindex="1" size="15" type="text" name="username" value="'.$_POST['username'].'" class="text" />&nbsp;&nbsp;<input tabindex="3" type="checkbox" name="cookie" id="cookie" /><label for="cookie"> autologin</label><br />
						pass <input tabindex="2" size="15" type="password" name="password" class="text" />&nbsp;
							<input tabindex="4" type="submit" value="login" class="button" /><br />
							'.$add.'
						</form>
					</td>
				</tr>
			</table>
		  </td>
			'
		;

	}
}



function titlebar($page_title="")  {
	$html =
	"<table width='80%' align='center'><tr>
	<td align='center' class='bottom_border'><b class='titlebar'>"
	.$page_title
	."</b></div></td></tr></table><br /><br />";
	return $html;
}


function menu ($name) {
	global $smarty;

	return smarty_menu(array('name'=>$name), $smarty);
}


/**
 * RSS Page
 * 
 * Zeigt eine XML kompatible RSS Seite an
 * 
 * @author IneX
 * @date 16.03.2008
 * @return String
 */
function rss ($title, $link, $desc, $feeds) {
	
	// Text-codierung an den header senden, damit Umlaute korrekt angezeigt werden
	//header("Content-Type: text/xml; charset=UTF-8");
	header("Content-Type: text/xml; charset=iso-8859-1");
	
	// xml header erstellen
	$xml =
		'<?xml version="1.0" encoding="iso-8859-1" ?>
		<rss version="2.0"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			>
		  <channel>
			<title>'.$title.'</title>
			<link>'.$link.'</link>
			<description>'.$desc.'</description>
			<language>de-DE</language>
			<lastBuildDate>'.date('D, d M Y H:i:s').' GMT</lastBuildDate>'//' '.gmt_diff(time()).'</lastBuildDate>
		;
	
	// xml <item>s zum feed hinzufügen
	$xml .=
			$feeds;
	
	// xml abschliessen
	$xml .= '
			</channel>
		</rss>'
	;
	
	// xml ausgeben
	return $xml;
}

?>