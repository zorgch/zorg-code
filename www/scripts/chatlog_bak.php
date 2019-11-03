<?php
/**
 * [z]Chat log
 * @package zorg\Chat
 */
global $db, $smarty;

$chatmessages = array();

$sql = 
	"
	SELECT
		chat.text
		, UNIX_TIMESTAMP(date) AS date 
		, user.username AS username
		, user.clan_tag AS clantag
		, chat.user_id
	FROM chat
	LEFT JOIN user ON (chat.user_id = user.id)
	ORDER BY date ASC
	"
;
$result = $db->query($sql, __FILE__, __LINE__);

while ($rs = $db->fetch($result)) {
  array_push($chatmessages, $rs);
}

$smarty->assign("chatmessages", $chatmessages);
