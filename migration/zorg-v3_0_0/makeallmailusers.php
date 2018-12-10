<?php /** @DEPRECATED
include_once(__DIR__ .'/../../www/includes/mysql.inc.php');
include_once(__DIR__ .'/../../www/includes/util.inc.php');

function createpass($len=23) {
	for($i=1;$i<=$len;$i++)	{
		srand((double)microtime()*100000);
		$rand .= chr(rand(65,90));
	}
	$rand = strtolower($rand);
	return $rand;
}

$sql = "select * from user where usertype <> 1";
$result = $db->query($sql, $db);
while($rs = $db->fetch($result)) {
	
	// 1. Mailname ermitteln
	$username = emailusername($rs[username]);
	
	// 2. Passwort ermitteln
	$pass = createpass();
	
	// 3. mail erstellen
	//system("~/bin/muser add ".$_GET[username]." ".$_GET[password]);
	virtual('../createmailuser.php?username='.$username.'&password='.$pass);
	
	// 4. in db schreiben
	$sql = "update user set mail_username = '".$username."', mail_userpw = '".$pass."' where id = ".$rs[id];
	$db->query($sql, $db);
}
*/
