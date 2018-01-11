<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

Messagesystem::execActions();

//echo head(24).menu("zorg").'<br />';
$smarty->assign('tplroot', array('page_title' => 'Messagesystem'));
$html = $smarty->fetch('file:layout/head.tpl');
$html .= menu("zorg");
$html .= menu("user");

if($_GET['message_id'] == '' || empty($_GET['message_id']) || $_GET['message_id'] == '0') {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Keine Nachricht angegeben!', E_USER_WARNING);
}

try {
	$sql = "SELECT *, UNIX_TIMESTAMP(date) as date FROM messages where id = '".$_GET['message_id']."'";
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	
	if($rs == false) {
		http_response_code(400); // Set response code 400 (bad request) and exit.
		user_error('Nachricht '.$_GET['message_id'].' konnte nicht geladen werden.', E_USER_WARNING);
	
	} elseif($rs['owner'] == $user->id) {
		
		$html .= Messagesystem::getMessage($_GET['message_id']);
		
		if(!is_int(strpos($rs['subject'], "Re:"))) { 
			$subject = $rs['subject'];
		} else {
			$subject = 'Re: '.$rs['subject'];
		}
		
		$html .= '<br />';
		$html .= Messagesystem::getFormSend(
					array($rs['from_user_id'])
					, $subject, '> '.str_replace("\n", "\n> "
					, $rs['text'])
					, $_GET['message_id']
				);
	
	} else {
		http_response_code(403); // Set response code 403 (access denied) and exit.
		user_error('<b>Du darfst diese Message nicht lesen!</b>', E_USER_NOTICE);
	}
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	user_error($$e->getMessage(), E_USER_WARNING);
}

echo $html;

//echo foot();
$smarty->display('file:layout/footer.tpl');
