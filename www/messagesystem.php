<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

Messagesystem::execActions();

//echo head(24).menu("zorg").'<br />';
$smarty->assign('tplroot', array('page_title' => 'Messagesystem'));
$smarty->display('file:layout/head.tpl');
menu("zorg")

if($_GET['message_id'] == "") {
	echo 'Keine Nachricht angegeben!';
	exit;
}

$sql = "SELECT *, UNIX_TIMESTAMP(date) as date FROM messages where id = '".$_GET['message_id']."'";
$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

if($rs == false) {
	echo 'Nachricht '.$_GET['message_id'].' existiert nicht!';
	exit;
}

if($rs['owner'] == $user->id) {
	
	echo Messagesystem::getMessage($_GET['message_id']);
	
	if(!is_int(strpos($rs['subject'], "Re:"))) { 
		$subject = $rs['subject'];
	} else {
		$subject = 'Re: '.$rs['subject'];
	}
	
	echo '<br />';
	echo(
		Messagesystem::getFormSend(
			array($rs['from_user_id'])
			, $subject, '> '.str_replace("\n", "\n> "
			, $rs['text'])
			, $_GET['message_id']
		)
	);

} else {
	echo '<b>Du darfst diese Message nicht lesen!</b>';
}

//echo foot();
$smarty->display('file:layout/footer.tpl');

?>