<?php
require_once dirname(__FILE__).'/../includes/main.inc.php';
	
if (!$user->id) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}

$frm = $_POST['frm'];

$types = array();
$types[] = "standard";
if ($user->typ == USER_MEMBER) $types[] = "member";

$error = "";

if (!trim($frm['text'])) $error .= "Text / Frage fehlt. <br />";
if (!in_array($frm['type'], $types)) $error .= "Ungültiger Typ '$frm[type]'. <br />";
if (!trim($frm['aw1'])) $error .= "Antwort fehlt (Antwort 1 muss gesetzt sein). <br />";
if (trim($frm['aw1']) && !trim($frm['aw2'])) $error .= "Nur eine Antwort bringts nicht (Antwort 2 muss gesetzt sein). <br />";

//foreach ($frm as $key => $val) {
//	$frm[$key] = htmlentities($val, ENT_QUOTES);
//}

if (!$error) {
	try {
		$poll = $db->query("INSERT INTO polls (text, user, date, type) VALUES ('$frm[text]', $user->id, NOW(), '$frm[type]')", __FILE__, __LINE__);
		if (!$poll) user_error("Error while creating Poll", E_USER_ERROR);
		if (trim($frm['aw1'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw1]')", __FILE__, __LINE__);
		if (trim($frm['aw2'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw2]')", __FILE__, __LINE__);
		if (trim($frm['aw3'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw3]')", __FILE__, __LINE__);
		if (trim($frm['aw4'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw4]')", __FILE__, __LINE__);
		if (trim($frm['aw5'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw5]')", __FILE__, __LINE__);
		if (trim($frm['aw6'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw6]')", __FILE__, __LINE__);
		if (trim($frm['aw7'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw7]')", __FILE__, __LINE__);
		if (trim($frm['aw8'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw8]')", __FILE__, __LINE__);
		if (trim($frm['aw9'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw9]')", __FILE__, __LINE__);
		if (trim($frm['aw10'])) $db->query("INSERT INTO poll_answers (poll, text) VALUES ($poll, '$frm[aw10]')", __FILE__, __LINE__);

		// Activity Eintrag auslösen
		//Activities::addActivity($user->id, 0, 'm&ouml;chte gerne wissen, ob <i>'.$frm['text'].'</i>: <a href="'.SITE_URL.'/?tpl=107">jetzt abstimmen</a>', 'p');
		Activities::addActivity($user->id, 0, 'm&ouml;chte gerne wissen, ob...<br><br>{poll id='.$poll.'}', 'p');
	} catch (Exception $e) {
		user_error($e->getMessage(), E_USER_ERROR);
	}

	$_GET['tpl'] = 109;
	header("Location: /?".url_params());
	die();
}else{
	foreach ($frm as $key => $val) $frm[$key] = stripslashes($val);
	
	$smarty->assign("frm", $frm);
	$smarty->assign("poll_error", $error);
	$smarty->display("file:layout/layout.tpl");
}
