<?PHP



//=============================================================================



// includes



//=============================================================================



setlocale(LC_TIME,"de_CH");



include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');
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

//=============================================================================
// Functions
//=============================================================================
function head($menu_id, $title="", $return = 0) {
	global $starttime, $user, $smarty;

	$out = "";
	$starttime = microtime();
	$out .= '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
		<html>
		<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<title>'.$title.'@zooomclan.org</title>
		<link rel="stylesheet" type="text/css" href="/includes/style.css">
	  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" >
		<script language="javascript" type="text/javascript">
	  function onoff(id) {
			layer = document.getElementById("layer" + id)
			image = "img" + id;
		  if(layer.style.visibility == "hidden") {
		    document.images[image].src = "/images/forum/minus.gif";
		    layer.style.display = "block";
		    layer.style.visibility = "visible";
		  } else {
		    document.images[image].src = "/images/forum/plus.gif";
		    layer.style.display = "none";
		    layer.style.visibility = "hidden";
		  }
		}
	
		function reply() {
			location.hash = "reply";
			document.commentform.text.focus();
		}
		</script>
		</head>
	
		<body>
	
		<center>
	
	
		<table cellpadding="4" style="align: center; background-color: #'.BODYBACKGROUNDCOLOR.'; height: 100%; margin:0px; width: 900px;">
		<tr>
		<td align="center" style="height: 87%;" valign="top">
	
		<table align="center" cellpadding="0" cellspacing="0" height="100%" style="height: 100%; margin:0px; padding: 0px; width: 100%;">
		<tr><td><div onDblClick="document.location.href=\'http://www.zooomclan.org/smarty.php?tpl='.$_GET[tpl].'&tpleditor=1&tplupd=56\';">
	';
	
		
	$out .= $smarty->fetch("tpl:56");
		
	$out .= '
		</div></td></tr><tr><td>
		<div onDblClick="document.location.href=\'http://www.zooomclan.org/smarty.php?tpl='.$_GET[tpl].'&tpleditor=1&tplupd=92\';">';
		
	if ($menu_id) {
		$smarty->assign("active_tab", $menu_id);
		$out .= $smarty->fetch("tpl:92");
	}
	
		
	$out .= '
		</div></td></tr><tr height="100%" style="height: 100%; margin: 0px; padding: 0px;"><td height="100%" style="height: 100%;">
	
		<table height="100%" style="background-color: #'.BACKGROUNDCOLOR.'; height: 100%; margin:0px; padding: 0px; width: 100%;">
		<tr>
		<td width="5%"></td>
		<td align="center" style="height: 100%; width: 90%;" valign="top">
	
	';
	
	if ($return) {
		return $out;
	}else{
		echo $out;
		return "";
	}
}



function foot($author_id=3) {
	global $starttime, $user, $db, $smarty, $_TPLROOT;

	// sql query tracker
	if ($user->sql_tracker) {
	   $_SESSION[noquerys] = $db->noquerys;
	   $_SESSION[noquerytracks] = $db->noquerytracks;
	   $_SESSION[query_track] = $db->query_track;
	   $_SESSION[query_request] = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	   $qtracker = '<a href="/smarty.php?tpl=25">[Details]</a>';
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
   
	   if ($_TPLROOT[id] == $user->tpl_favourite) {
   		$tplinfo .= ' | <a href="'.$curlnk.'&usersetfavourite=0">Favoriten-Seite entfernen</a>';
	   }
  
   	if ($_TPLROOT[id] != $user->tpl_favourite && !$user->tpl_favourite) {
//      	$tplinfo .= ' | <a href="'.$curlnk.'&usersetfavourite={$tpl.id}">als Favoriten-Seite setzen</a>';
   	}
   	
   	$tplinfo .= 
			' | '. smarty_sizebytes($_TPLROOT[size]).
//			' | r: '. smarty_usergroup($_TPLROOT[read_rights]).
//			' | w: '. smarty_usergroup($_TPLROOT[write_rights]).
			' | updated: '.$user->id2user($_TPLROOT[update_user]).', '.datename($_TPLROOT['last_update']).
			' | <a href="http://www.zooomclan.org/smarty.php?tpl='.$_TPLROOT[id].'">tpl='.$_TPLROOT[id].'</a>';
		if ($_TPLROOT[word]) $tplinfo .= ' | word='.$_TPLROOT[word];
		
		if (tpl_permission($_TPLROOT[write_rights], $_TPLROOT[owner])) {
   		//$tplinfo .= ' | <a href="/smarty.php?tpleditor=1&tplupd='.$_TPLROOT[id].'&tpl='.$_TPLROOT[id].'">[edit]</a>'; 
		}
	}
	

	return(

      '<br /></td><td width="5%"></td></tr>
      <tr>
         <td width="100%" colspan=3 align="center" class="small" bgcolor="#'.TABLEBACKGROUNDCOLOR.'"'.
      		'style="border-top-style: solid; border-top-width: 1px; border-top-color: #'.BORDERCOLOR.';"'.
      	'>'.
            //'<a href="/wiki.php?word=impressum">Impressum</a> | <a href="/wiki.php?word=privacy">Privacy-Policy</a> |'.
            'Parsetime: 
            '.round((microtime()-$startparse), 2).'s |
            '.$db->noquerys.' SQL Querys '.$qtracker.'
      		'.$tplinfo.'
         </td>
      </tr></table>
	</td></tr></table>
	</td></tr></table>
		
		</center>
		
		</body>
		</html>
	');

}





function loginform() {

	global $user, $login_error;

	if($user->islogged_in()) {

		return '
			<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="logoutform">
			<td align="right" valign="middle">		
			<b class="small">'.$user->id2user($_SESSION['user_id']).' eingeloggt</b> 
			<input name="logout" type="submit" value="logout" class="button">
		  </td>
			</form>
			'
		;
	} else {
		is_string($login_error) ? $add = "<br /><b align='left' class='small'>".$login_error."</b>" : $add = "";
		return '
			<td align="right">
			<table><tr>
			<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="loginform">
			<td align="left" class="small">
			<input tabindex="1" type="text" name="username" value="'.$_POST['username'].'" class="text">
			<input tabindex="2" type="password" name="password" class="text">
			<input tabindex="3" type="submit" value="login" class="button">
			<input type="checkbox" name="cookie"> autologin <br />
			'.$add.'
			</td></tr>
			</form></table>
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

?>