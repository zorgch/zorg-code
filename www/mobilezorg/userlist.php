<?php
/**
 * Userliste
 * 
 * Auflistung aller aktiven Benutzeraccounts für mobilezorg
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage users
 */

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }


setlocale(LC_TIME,"de_CH");

$html = '';


if ($_GET['user_id'] == '') {
	
	$userlist = array();

	$today_unixdate = date('Ymd', time());
	$todayGroupOut = false;
	
	
	// Array mit Alphabet generieren
	$alphabet = array();
	$charGroupOut = false;
	
	for($i=65;$i<=96;$i++) {
		array_push($alphabet, chr($i));
	}
	
	
	// Query for Userlist
	$sql =
		$db->query(
			"
			SELECT
				id, username, clan_tag, active, UNIX_TIMESTAMP(currentlogin) as currentlogin
			FROM
				user
			WHERE
				active = 1
			ORDER BY
				currentlogin DESC, username ASC
			", __FILE__, __LINE__);
	while ($rs = mysql_fetch_array($sql)) {
		array_push($userlist, $rs);
	}
	
	sort($userlist, SORT_STRING); ?>
	
	
<!-- USERLIST -->
<ul id="userlist" title="Userlist">
	<?php foreach ((array) $alphabet as $char) {
	
		echo '<li class="group">'.$char.'</li>';
	
		/**
		 * USERS SORTED BY USERNAME
		 */
		foreach ((array) $userlist as $i => $user) {
			
			// Check if first Char of the Username is equal to the current alphabetical Char
			echo ($user['username'][0] == $char) ? '<li><a href="userlist.php?user_id='.$user[id].'">'.$user['clan_tag'].$user['username'].'</a></li>' : '';
			
		}
		
		/**
		 * USERS SORTED BY ACTIVITY
		 *
		// Today active Users
		if ($todayGroupOut == false && date('Ymd', $user['currentlogin']) == $today_unixdate) {
			echo '<li class="group">Heute aktiv</li>';
			$todayGroupOut = true;
		}
		
		if ($todayGroupOut == true && date('Ymd', $user['currentlogin']) < $today_unixdate) {
			echo '<li class="group">Weitere User</li>';
			$todayGroupOut = ''; // set the output to NULL, so nothing happens
		}
		
		echo '<li><a href="userlist.php?user_id='.$user[id].'">'.$user['clan_tag'].$user['username'].'</a></li>';
		*/
		
	} ?>
</ul>
<?php


} else {


	$user_id = $_GET['user_id'];
	
	define(USER_IMGPATH, $_SERVER['DOCUMENT_ROOT'].'/images/userimages/');
		
	/**
	 * User Bild
	 * 
	 * Gibt den Pfad zum Bild des Users. Falls kein Bild: none.jpg
	 * 
	 * @return string Pfad zum Bild des Users
	 * @param $user_id int User ID
	 */
	function userImage($user_id, $large=0) {
	   if (usersystem::checkimage($user_id)) {
		if ($large) return USER_IMGPATH.$user_id.'.jpg';
		else return USER_IMGPATH.$user_id.'_tn.jpg';
	   }else{
		  return USER_IMGPATH."none.jpg";
	   }
	}
	
	/**
	 * Bild Prüfen
	 * 
	 * überprüft ob ein Bild zum User existiert
	 * 
	 * @return bool
	 * @param $user_id int User ID
	 */
	function checkimage($user_id) {
		if(file_exists(USER_IMGPATH.$user_id.".jpg")) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	// Query for User
	$sql =
		$db->query(
			"
			SELECT
				*, UNIX_TIMESTAMP(currentlogin) AS currentlogin, UNIX_TIMESTAMP(activity) AS activity, from_mobile
			FROM
				user
			WHERE
				id = ".$user_id."
			", __FILE__, __LINE__);
	$user = mysql_fetch_array($sql);
	


?>
<!-- USER DETAILS -->
<ul id="user" title="<?php echo $user['username']; ?>">
<?php

	global $user;
	
	$html .= '<li align="center"><img src="'.userImage($user['id']).'" alt="'.$user['username'].'"/></li>';
	$html .= '<li>'.$user['clan_tag'].$user['username'].'</li>';
	$html .= '<li><small>Letzte Aktivit&auml;t</small><br/>'.strftime('%e. %B %Y %H:%M Uhr', $user['activity']);
	$html .= ($user['from_mobile'] <> "" ? " <img src=\"/images/mobile15x11px.gif\" border=\"none\" width=15 heigh=11 alt=\"von unterwegs geschrieben\"></li>" : "</li>" );
	if ($_GET['user_id'] != $_SESSION['user_id'] && $user['addle']) $html .= '<br/><a class="whiteButton" name="newAddle" type="submit" href="addle_action.php?do=new&amp;user_id='.$user['id'].'" target="_self">Zum Addle herausfordern</a>';
		
		
	echo $html;

}
?>			
</ul>