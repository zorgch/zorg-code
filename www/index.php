<?php

### Zooomclan/Zorg Parsing Stuff ###
/*
global $zorg, $zooomclan;

if (!$zorg || !$zooomclan) {
	$url =  $_SERVER['SCRIPT_URI'];
	$parsed = parse_url($url);

	if (preg_match('/\bzorg\b/i', $parsed[host])) {
		//print('Er het gmerkt, dass es zorg.ch isch...<br />');
		$zorg = true;
	} else {
		//print('Er meint es sig zooomclan.ch...<br />');
		$zooomclan = true;
	}
}
*/


	
	### Hier fŠngts an mit der Ausgabe der ganzen Seite... ###
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	
	include($_SERVER['DOCUMENT_ROOT'].'/smarty.php');


/*

DAS IST DAS ALTE index.php

//=============================================================================
// Includes
//=============================================================================

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/gallery.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/poll.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.fnc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/wiki.inc.php');


//=============================================================================
// Actions
//=============================================================================
$public_poll = new poll("public");
$logged_poll = new poll("logged");

if($_POST['vote']) {
	$logged_poll->exec();
}

//da auf der v2 alle links auf index.php weisen, die vom forum kommen
if($_GET['parent_id']) {
	header("Location: http://www.zooomclan.org/forum.php?parent_id=".$_GET['parent_id']);
}


//=============================================================================
// Output
//=============================================================================

echo(
	head(2, "home").'
	<table cellpadding="5" width="100%">
	<tr><td valign="top" width="350">
	'.Wiki::getContent('welcome').'
	<br />
	<br />
	<br />
	'.Forum::getLatestComments().'
	<br /><br />
	
	<table class="border" cellpadding="5" width="350"><tr><td>
	<b>Online Users:</b>
	<br />
	</td></tr><tr><td bgcolor="'.TABLEBACKGROUNDCOLOR.'">
	'.$user->online_users(180, $pic=TRUE).'
	</td></tr></table>
	
	</td>
	<td align="center" valign="top">
	'.getRandomThumb().'
	<br />
	<br />
	'.getRandomThumb().'
	<br />
	<br />
	'.getRandomThumb().'
	<br />
	<br />
	'.getRandomThumb().'
	</td>
	<td valign="top" width="350">
	'.$public_poll->getpoll().'
	<br />
	<br />
	'.Forum::getLatestThreads().'
	<br />
	'.($user->typ == USER_EINGELOGGT ? '<br />'.$logged_poll->getpoll() : '').'
	<br />
	'.Forum::getLatestUnreadComments().'
	<br />
	'.Wiki::getContent('bugreport').'
	<br />
	'.getLatestUpdates().'
	</td></tr></table>
	'.foot()
);
*/
?>
