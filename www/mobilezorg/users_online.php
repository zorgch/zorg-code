<?php
/**
 * Users die online sind
 * 
 * Gibt alle Benutzer für mobielzorg aus, welche gerade als ONLINE markiert sind
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage users
 */

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }

?>

<!-- ONLINE USERS -->
<ul id="onlineusers" title="Online Users">

<?php
// Query for online Users
$sql = "
		SELECT
			id, username, clan_tag, from_mobile
		FROM
			user 
		WHERE
			UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(NOW()) - ".USER_TIMEOUT.")
		ORDER BY
			activity DESC
		";
	
$result = $db->query($sql, __FILE__, __LINE__);

while($rs = $db->fetch($result)) {
	
	echo '<li><a class="linklabel" href="userlist.php?user_id='.$rs['id'].'">'.$rs['clan_tag'].$rs['username'];
	echo( $rs['from_mobile'] <> "" ? " <img src=\"/images/mobile15x11px.gif\" border=\"none\" width=15 heigh=11 alt=\"von unterwegs geschrieben\"></a></li>" : "</a></li>" );
	
}
?>
</ul>