<?php /** @DEPRECATED
require_once( __DIR__ .'/includes/main.inc.php');

//echo head(27);
$smarty->assign('tplroot', array('page_title' => 'IMAP Messages'));
$smarty->display('file:layout/head.tpl');
if($_SESSION['user_id']) {
	if($_SESSION['user_id']) {
		
		$imap = new imap($user->mail_username, $user->mail_userpw);
		if(count($_POST['check']) > 0 && $_POST['del']) {
			foreach($_POST['check'] as $to_del) {
				imap_delete($imap->conn, $to_del);	
			}	
			imap_expunge($imap->conn);
			//header("Location: messages.php?".session_name()."=".session_id()); // rem by db
		}
		if(!$_GET['do']) {
			echo imapStatic::getOverview($imap);
		} else 
		if($_GET['do'] == "view" && $_GET['uid']) {
			echo imapStatic::getMail($_GET['uid'],$imap);
		} else 
		if($_GET['do'] == "new") {
			echo imapStatic::newMail($_GET['toid']);
		} else 
		if($_GET['do'] == "reply" && $_GET['message_id'] > 0) {
			echo imapStatic::replyMail($imap, $_GET['message_id']);
		} else 
		if($_GET['do'] == "send") {
			imap::sendMail(
				$_POST['mailto'], 
				$_POST['subject'], 
				$_POST['message'], 
				"From: ".$user->username." <".$user->username."@zorg.ch>\n"
			);
			echo "<b>Nachricht gesendet!</b><br />";
			echo imapStatic::getOverview($imap);
			//echo imapStatic::newMail($imap, $_GET['message_id']);
		} else 
		if($_GET['do'] == "delete" && $_GET['message_id'] > 0) {
			imap_delete ($imap->conn, $_GET['message_id']);
			imap_expunge($imap->conn);
			echo "<b>Nachricht gel?scht!</b><br />";
			echo imapStatic::getOverview($imap);
		}
		
		$imap->close();
	}
}
//echo foot(1);
$smarty->display('file:layout/footer.tpl');
*/