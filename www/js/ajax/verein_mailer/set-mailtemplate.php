<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || ( $_GET['action'] != 'save' && $_GET['action'] != 'update' ))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/../../../includes/config.inc.php';
require_once INCLUDES_DIR.'main.inc.php';;

/**
 * Compile template & save or update it into the database
 */
$smarty->assign('mail_param', $_POST['template_id']);
$smarty->assign('user_param', $user->id);
$smarty->assign('hash_param', md5($_POST['template_id'] . $user->id) );
$leMailTemplate = 'email/verein/verein_htmlmail.tpl';
//$leTemplateInclude = "{include file='file:$leMailTemplate'}";
$compiledMailTpl = $smarty->fetch('file:' . $leMailTemplate);

if ( $_GET['action'] === 'update' && !empty($_POST['template_id']) && is_numeric($_POST['template_id']) )
{
	/**
	 * Update existing Template
	 */
	$update_template_id = (int)$_POST['template_id'];
	error_log('[INFO] Updating existing Mail Template ' . $update_template_id);
	$updateTplQuery = 'INSERT INTO templates (id, tpl, title, page_title, last_update, update_user)
							VALUES (?, ?, ?, ?, NOW(), ?)
						ON DUPLICATE KEY UPDATE
							 id = LAST_INSERT_ID(id)
							,tpl = VALUES(tpl)
							,title = VALUES(title)
							,page_title = VALUES(page_title)
							,last_update = VALUES(last_update)
							,update_user = VALUES(update_user)';
	$updateTplParams = [
		$update_template_id,
		escape_text($compiledMailTpl),
		escape_text($_POST['text_mail_subject']),
		escape_text($_POST['text_mail_subject']),
		$user->id
	];
	if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Update Mail-Template Query: %s', __FILE__, __LINE__, $updateTplQuery));
	$tplid = $db->query($updateTplQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)', $updateTplParams);
	if (empty($tplid) || false === $tplid)
	{
		http_response_code(500); // Set response code 500 (internal server error)
		error_log(sprintf('[ERROR] <%s:%d> Updating existing Mail Template failed: %s', __FILE__, __LINE__, $updateTplQuery));
		die('Template '.$tplid.' could not be updated');
	}

	/**
	 * Update existing E-Mail message entry
	 */
	error_log('[INFO] Updating E-Mail message entry for ' . $tplid);
	$updateMailQuery = 'UPDATE verein_correspondence SET
							subject_text=?
							,preview_text=?
							,message_text=?
						WHERE template_id=?
						AND recipient_id=?';
	$updateMailParams = [
		escape_text($_POST['text_mail_subject']),
		escape_text($_POST['text_mail_description']),
		escape_text($_POST['text_mail_message']),
		$tplid,
		VORSTAND_USER
	];

	if ( $db->query($updateMailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)', $updateMailParams) )
	{
		http_response_code(200); // Set response code 200 (OK)
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> SUCCESS Updating E-Mail message entry', __FILE__, __LINE__));
		echo $tplid;
	} else {
		http_response_code(500); // Set response code 500 (internal server error)
		error_log(sprintf('[ERROR] <%s:%d> Updating E-Mail message entry failed: %s', __FILE__, __LINE__, $updateMailQuery));
		die('Template '.$tplid.' could not be updated');
	}


}
/**
 * Save new Template
 *
 * border '0' = No Border
 * read_rights '2' = USER_MEMBER
 */
elseif ( $_GET['action'] === 'save' && isset($_POST['text_mail_subject']) )
{
	error_log('[INFO] Saving a new Mail Template');
	$insertTplQuery = 'INSERT INTO templates SET
						 tpl=?
						,title=?
						,page_title=?
						,border=?
						,owner=?
						,read_rights=?
						,write_rights=?
						,created=?
						,last_update=?
						,update_user=?';
	$params = [
		escape_text($compiledMailTpl),
		escape_text($_POST['text_mail_subject']),
		escape_text($_POST['text_mail_subject']),
		'0',
		VORSTAND_USER,
		'2',
		'3',
		date('Y-m-d H:i:s'),
		date('Y-m-d H:i:s'),
		$user->id
	];
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Saving a new Mail Template: %s', __FILE__, __LINE__, $insertTplQuery));
	$tplid = $db->query($insertTplQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)', $params);

	if (empty($tplid) || !is_numeric($tplid))
	{
		http_response_code(500); // Set response code 500 (internal server error)
		error_log(sprintf('[ERROR] <%s:%d> Mail Template could not be created', __FILE__, __LINE__));
		die('Template could not be created');
	} else {
		/**
		 * Create new E-Mail message entry
		 */
		error_log('[INFO] Creating a new E-Mail message entry for ' . $tplid);
		$insertMailQuery = 'INSERT INTO verein_correspondence SET
								communication_type=?
								,subject_text=?
								,preview_text=?
								,message_text=?
								,template_id=?
								,sender_id=?
								,recipient_id=?';
		$params = [
			'EMAIL',
			escape_text($_POST['text_mail_subject']),
			escape_text($_POST['text_mail_description']),
			escape_text($_POST['text_mail_message']),
			$tplid,
			$user->id,
			VORSTAND_USER
		];
		$messageId = $db->query($insertMailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)', $params);
		if (empty($messageId) || false === $messageId) {
			http_response_code(500); // Set response code 500 (internal server error)
			error_log(sprintf('[ERROR] <%s:%d> Failed creating a new E-Mail message entry', __FILE__, __LINE__));
			die('Template could not be created');
		}

		http_response_code(200); // Set response code 200 (OK)
		echo $tplid;
	}

} else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	if (DEVELOPMENT) error_log('Method not allowed');
	die('Method not allowed');
}
