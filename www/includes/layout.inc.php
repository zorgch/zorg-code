<?PHP
error_reporting(E_ALL & ~E_NOTICE);

//=============================================================================
// Defines
//=============================================================================
// Set locale to German, Switzerland
setlocale(LC_TIME,"de_CH");

// bodysettings wird verwendet, um den div nach den menüs wieder zu öffnen.
if (!defined('BODYSETTINGS')) define("BODYSETTINGS", 'align="center" valign="top" style="margin: 0px 40px;"');

// Site Settings
//if (!defined('TLD')) define('TLD', $_SERVER['SERVER_NAME']); 		// Extract the Top Level Domain => neu in main.inc.php
//if (!defined('SITE_PROTOCOL')) define('SITE_PROTOCOL', 'http'); 	// TCP/IP Protocol used: HTTP or HTTPS => neu in main.inc.php
//if (!defined('SITE_URL')) define('SITE_URL', SITE_PROTOCOL.'://'.TLD); // Complete HTTP-URL to the website => neu in main.inc.php
if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', ' - '.SITE_HOSTNAME); // General suffix for <title>...[suffix]</title> on every page

// Site Paths (ending with a / slash!)
if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', '/includes/'); // File includes directory
if (!defined('IMAGES_DIR')) define('IMAGES_DIR', '/images/'); 		// Images directory
if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', '/actions/'); 	// Actions directory
if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', '/scripts/'); 	// Scripts directory
if (!defined('UTIL_DIR')) define('UTIL_DIR', '/util/'); 			// Utilities directory
if (!defined('JS_DIR')) define('JS_DIR', '/js/'); 					// JavaScripts directory
if (!defined('CSS_DIR')) define('CSS_DIR', '/css/'); 					// CSS directory



//=============================================================================
// File includes
//=============================================================================

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



//=============================================================================
// Functions
//=============================================================================

/* DEPRECATED 05.01.2016/IneX
   Folgender Code wurde wahrscheinlich fr�her benutzt, als man auf zorg.ch/[username] verlinken konnte...
$username = $_SERVER['QUERY_STRING'];
$sql = "SELECT id FROM user WHERE username = '$username'";
$result = $db->query($sql,__FILE__,__LINE__);
if($db->num($result)) {
	$rs = $db->fetch($result);
	header("Location: profil.php?user_id=".$rs['id']);
}
*/


/**
 * HEADER
 *
 * @author [z]biko, IneX
 * @date 23.10.2013
 * @version 4.0
 * @since 1.0
 * @package Zorg
 * @subpackage Layout
 *
 * @param integer $author_id ID des Autors der jeweiligen Seite (muss manuell beim Function-Call gesetzt werden - voll phehinderet!)
 * @param string $title Titel der Seite, welcher auch im HTML ausgegeben wird
 * @param boolean $return Legt fest ob das ganze HTML returned oder direkt ausgegben wird (und dann returned false). Muss nämlich im smarty.fnc.php unterbunden werden!
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen User-Variablen
 * @global string $sun Enthält den Sonnen-Status (up/down)
 * @global array $smart Array mit allen Smarty-Variablen
 * @global string $layouttype Entweder "day" oder "night" (hängt mit der $sun zusammen)
 * @global string $country Enthält den Country-Namen der für den aktuellen Benutzer ermittelt wurde
 * @global datetime $starttime Keine Ahnung...
 */
function head($author_id=0, $title="", $return = 0) {
	global $starttime, $user, $smarty, $sun, $country, $db, $layouttype;

	// Rosenverkäufer einloggen
	if ($user->typ >= USER_USER) peter::rosenverkaufer();

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
		<title>'.$title.PAGETITLE_SUFFIX.'</title>
		<link rel="prefetch" href="forum.php">
		<link rel="shortcut icon" href="'.$favicon[$sun].'" type="image/x-icon">
		<link rel="stylesheet" type="text/css" href="'.INCLUDES_DIR.$style_array[$sun].'" >
		<link rel="stylesheet" href="'.CSS_DIR.'/fileicon.min.css">
		<script type="text/javascript" src="'.JS_DIR.'zorg.js"></script>
		<script src="'.JS_DIR.'highlight-js/highlight.pack.js"></script>
		<link class="codestyle" rel="stylesheet" href="'.JS_DIR.'/highlight-js/styles/github-gist.css">
		<script type="text/javascript">var layout = "'.str_replace(".css", "", $style_array[$sun]).'";</script>

		<!-- RSS Feeds -->
		<link rel="alternate" type="application/rss+xml" title="RSS @ zorg.ch" href="'.SITE_URL.'/forum.php?layout=rss" />
		<link rel="alternate" type="application/rss+xml" title="Forum Feed @ zorg.ch" href="'.SITE_URL.'/forum.php?layout=rss&board=f" />
		<link rel="alternate" type="application/rss+xml" title="Events Feed @ zorg.ch" href="'.SITE_URL.'/forum.php?layout=rss&board=e" />
		<link rel="alternate" type="application/rss+xml" title="Gallery Feed @ zorg.ch" href="'.SITE_URL.'/forum.php?layout=rss&board=i" />
		<link rel="alternate" type="application/rss+xml" title="Rezepte Feed @ zorg.ch" href="'.SITE_URL.'/forum.php?layout=rss&board=r" />
		<link rel="alternate" type="application/rss+xml" title="Neuste Activities @ zorg.ch" href="'.SITE_URL.'/activities.php?layout=rss" />

		</head>

		';

	// Wenn es ein eingeloggter User ist, wird im Fenstertitel die Anzahl Unreads angezeigt...
	$out .= $user->islogged_in() ? "<body onload=\"init()\">" : "<body>";

	// Wenn die Startseite von einem Mobile Device aufgerufen wird, frage ob zu Mobile Zorg gewechselt werden soll
	if (str_replace('/','',$_SERVER['PHP_SELF']) == 'index.php')
	{
	$out .=	'
		<!-- Redirect Mobile Devices, kudos to http://detectmobilebrowsers.com/ -->
		<script type="text/javascript">
		var isMobile = false;
		(function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))isMobile=b})(navigator.userAgent||navigator.vendor||window.opera,true);
		
		if (isMobile)
			if (confirmPopup("Wechseln zu Mobile Zorg?")) window.location="mobilezorg-v2/";
		</script>';
	}

	$out .=	'
		<center>

		<table height="97%" bgcolor="'.BODYBACKGROUNDCOLOR.'" cellspacing="0" cellpadding="0" width="860">
		<tr><td valign="top" bgcolor="'.BACKGROUNDCOLOR.'" height="100%">';

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



### FOOTER F�R ZOOOMCLAN.ORG ###
function foot($author_id=3) {
	global $starttime, $user, $db, $smarty, $_TPLROOT;

	// sql query tracker
	if ($user->sql_tracker) {
	   $_SESSION['noquerys'] = $db->noquerys;
	   $_SESSION['noquerytracks'] = $db->noquerytracks;
	   $_SESSION['query_track'] = $db->query_track;
	   $_SESSION['query_request'] = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	   $qtracker = '<a href="smarty.php?tpl=25">[Details]</a>';
	}else{
	   $qtracker = "";
	   unset($_SESSION['noquerys']);
	   unset($_SESSION['query_track']);
	   unset($_SESSION['query_request']);
	   unset($_SESSION['noquerytracks']);
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
			' | updated: '.$user->id2user($_TPLROOT['update_user']).', '.datename($_TPLROOT['last_update']).
			' | <a href="smarty.php?tpl='.$_TPLROOT['id'].'">tpl='.$_TPLROOT['id'].'</a>';
		if ($_TPLROOT['word']) $tplinfo .= ' | word='.$_TPLROOT['word'];

		if (tpl_permission($_TPLROOT['write_rights'], $_TPLROOT['owner'])) {
   		$tplinfo .= ' | '. edit_link('[edit]', $_TPLROOT['id'], $_TPLROOT['write_rights'], $_TPLROOT['owner']);
		}
	}


	return(

      '<br />
      </div>

		</td></tr>
      <tr>
         <td width="100%" align="center" valign="center" class="small" bgcolor="'.TABLEBACKGROUNDCOLOR.'"
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



### FOOTER F�R ZORG.CH ###
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
         <td width="100%" align="center" valign="center" class="small" bgcolor="'.TABLEBACKGROUNDCOLOR.'"
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


/**
 * Ausgabe Loginform HTML
 * Neu als Smarty-Template "/templates/loginform.tpl" verf�gbar!
 * Usage im Smarty Template: {include file='file:loginform.tpl'}
 * 
 * @DEPRECATED
 * @author IneX
 * @date 12.01.2016
 */
function loginform() {

	global $user, $login_error, $smarty;

	$smarty->display("file:loginform.tpl");

	/*if($user->islogged_in()) {

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

	}*/
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
	header("Content-Type: text/xml; charset=UTF-8");
	//header("Content-Type: text/xml; charset=iso-8859-1");

	// xml header erstellen
	$xml =
		'<?xml version="1.0" encoding="utf-8" ?>
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