<?php
include_once(__DIR__ .'/../../www/includes/mysql.inc.php');
/*
function ubb2html($text) {
  global $u_member;
  $search = array();
  $replace = array();
  
  // U = ungreedy regexp modifier
  $search[0] = "/(\[clan\](.+)\[\/clan\])/sU";
  $replace[0] = "<clan>\\2</clan>";
  
  $search[1] = 	"/(\[url=(.+)\](.+)\[\/url\])/sU";
  $replace[1] = "<a href=\"\\2\">\\3</a>";
  
  $search[2] =  "/(\[email\](.+)\[\/email\])/sU";
  $replace[2] = "<a href=\"mailto:\\2\">\\2</a>";
  
  $search[3] =  "/(\[b\](.+)\[\/b\])/sU";
  $replace[3] = "<b>\\2</b>";
  
  $search[4] =  "/(\[i\](.+)\[\/i\])/sU";
  $replace[4] = "<i>\\2</i>";
  
  $text = preg_replace($search, $replace, $text);
  
  return $text;
}

// ====================================================================

$sql = "DROP TABLE IF EXISTS `zooomclan`.`comments`";
if($db->query($sql)) echo 'table dropped...<br />';
flush();


$sql = "CREATE TABLE `zooomclan`.`comments` ("
	."`id` int( 11 ) NOT NULL AUTO_INCREMENT ,"
	."`thread_id` int( 11 ) NOT NULL default '0',"
	."`parent_id` int( 11 ) NOT NULL default '0',"
	."`user_id` int( 11 ) NOT NULL default '0',"
	."`text` blob NOT NULL ,"
	."`date` datetime NOT NULL default '0000-00-00 00:00:00',"
	."`lastpost` datetime NOT NULL default '0000-00-00 00:00:00',"
	."`board` char( 1 ) NOT NULL default 'c',"
	."PRIMARY KEY ( `id` ) ,"
	."KEY `lastpost` ( `lastpost` ) ,"
	."KEY `date` ( `date` ) ,"
	."KEY `board` ( `board` , `date` ) ,"
	."KEY `thread_id` ( `thread_id` , `parent_id` ) ,"
	."KEY `parent_id` ( `parent_id` , `id` )"
	.") TYPE = MYISAM PACK_KEYS = 1 AUTO_INCREMENT = 23627;"
;
if($db->query($sql)) echo 'new table created...<br />';
flush();


$sql = "INSERT INTO `zooomclan`.`comments` SELECT * FROM `zooomclan`.`v2_comments`";
if($db->query($sql)) echo 'content copied...<br />';
flush();

$sql = "select * from comments";
$result = $db->query($sql);
while($rs = $db->fetch($result)) {	
	//echo $text;
	$sql2 = "update comments set text ='".addslashes(ubb2html($rs[text]))."' where id =".$rs[id];
	$result2 = $db->query($sql2);
}
echo 'ubb converted to html...<br />';
flush();


echo 'done!';
flush();
*/
