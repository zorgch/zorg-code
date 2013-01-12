<?php
/**
 * Users heute aktiv gewesen
 * 
 * Gibt alle Benuter für mobilezorg aus, welche am heutigen Tag online waren
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage users
 */

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }

$today_unixdate = date('Ymd', time());

?>

<!-- ACTIVE USERS OF TODAY -->
<ul id="activeusers" title="Heute aktiv">

<?php
// Query for active Users of today
$sql = "
		SELECT
			id, username, clan_tag, active, UNIX_TIMESTAMP(activity) AS activity
		FROM
			user 
		WHERE
			 active = 1
		ORDER BY
			activity DESC
		";
	
$result = $db->query($sql, __FILE__, __LINE__);

while($rs = $db->fetch($result)) {

	echo (date('Ymd', $rs['activity']) == $today_unixdate) ? '<li><small>'.date('H:i', $rs['activity']).' Uhr</small><br/><a class="linklabel" href="userlist.php?user_id='.$rs['id'].'">'.$rs['clan_tag'].$rs['username'].'</a></li>' : '';
	
}

?>
</ul>