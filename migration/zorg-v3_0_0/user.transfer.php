<?php
include(__DIR__ .'/../../www/includes/main.inc.php');

/*
$us = new usersystem();
echo "delete old data...";
flush();
$sql = "DELETE FROM zooomclan.user";
$db->query($sql);
echo "done!<br /><br />";
flush();


echo "old data fetching...";
flush();

$sql = "SELECT 
username, 
useremail, 
userpw, 
last_login, 
current_login, 
clan_tag, 
status,
bild,
id
FROM v3.user 
ORDER by id ASC";

$result = $db->query($sql);

while($rs = $db->fetch($result)) {
	$username[] = $rs['username'];
	$email[] = $rs['useremail'];
	$userpw[] = $rs['userpw'];
	$clan_tag[] = $rs['clan_tag'];
	$lastlogin[] = $rs['last_login'];
	$currentlogin[] = $rs['current_login'];
	$usertype[] = $rs['status'];
	$image[] = $rs['bild'];
	$id[] = $rs['id'];
}
echo "done.<br /><br />";
flush();

echo "parsing passwords...";
flush();
foreach($userpw as $pass) {
	$plain = base64_decode($pass);
	$crypted = $us->crypt_pw($plain);
	$new_pass[] = $crypted;
}

echo "done.<br /><br />";
flush();

echo "parsing user status...";
flush();
foreach($usertype as $status) {
	$status == "member" ? $new_status[] = 1 : $new_status[] = 0;
}
echo "done.<br /><br />";
flush();

echo "<br />";
echo "insert new data...";
flush();
$new_user = 0;
for($i = 0;$i<=count($username)-1;$i++) {
	$sql = "SELECT username FROM zooomclan.user WHERE username = '".$username[$i]."'";
	$result = $db->query($sql);
	if(!$db->num($result)) {
		$sql = "INSERT into zooomclan.user (username, userpw, clan_tag, 
		email, lastlogin, currentlogin, usertype, image, active, id) VALUES
		('".$username[$i]."','".$new_pass[$i]."','".$clan_tag[$i]
		."','".$email[$i]."','".$lastlogin[$i]."','".
		$currentlogin[$i]."','".$new_status[$i]."','".$image[$i]."',1,'".$id[$i]."')";
		$db->query($sql);
		$new_user++;
	}
}

echo $new_user." new user inserted.<br /><br />";

echo "<br />";
echo "converting images...";
flush();
$sql = "SELECT id, image FROM zooomclan.user";
$result = $db->query($sql);
$i = 0;
while($rs = $db->fetch($result)) {
	$ending = substr($rs['image'],strrpos($rs['image'],"."));
	if($ending == ".jpg") {
		$fp = @fopen("/home/CME/z/zooomclan/old/img/users/".$rs['image'],"r");
		if($fp) {
			
			$string = fread($fp,filesize("/home/CME/z/zooomclan/old/img/users/".$rs['image']));
			fclose($fp);
			$new_fp = fopen($_SERVER['DOCUMENT_ROOT']."/images/users/".$rs['id'].".jpg","w");
			fwrite($new_fp,$string);
			fclose($new_fp);
			thumbnail($_SERVER['DOCUMENT_ROOT']."/images/users/".$rs['id'].".jpg",$_SERVER['DOCUMENT_ROOT']."/images/users/thumbnail/".$rs['id'].".jpg",131,81);
			$i++;
		}
	}
}

echo "$i images done.<br />";
flush();
echo "<br />update user table...";
flush();
$sql = "UPDATE zooomclan.user set image = id + \".jpg\"";
//$db->query($sql);
echo "done.<br />";
flush();
*/
