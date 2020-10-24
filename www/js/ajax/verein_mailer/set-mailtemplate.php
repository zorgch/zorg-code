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
	try {
		error_log('[INFO] Updating existing Mail Template ' . $update_template_id);
		$updateTplQuery = 'INSERT INTO templates (id, tpl, title, page_title, last_update, update_user)
							VALUES (
								 '.$update_template_id.'
								,"'.escape_text($compiledMailTpl).'"
								,"'.escape_text($_POST['text_mail_subject']).'"
								,"'.escape_text($_POST['text_mail_subject']).'"
								,NOW()
								,'.$user->id.'
							)
							ON DUPLICATE KEY UPDATE
								 id = LAST_INSERT_ID(id)
								,tpl = VALUES(tpl)
								,title = VALUES(title)
								,page_title = VALUES(page_title)
								,last_update = VALUES(last_update)
								,update_user = VALUES(update_user)';
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Update Mail-Template Query: %s', __FILE__, __LINE__, $updateTplQuery));
		$tplid = $db->query($updateTplQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)');
	} catch(Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ERROR Updating existing Mail Template', __FILE__, __LINE__));
		echo $e->getMessage();
	}

	/**
	 * Update existing E-Mail message entry
	 */
	try {
		error_log('[INFO] Updating E-Mail message entry for ' . $tplid);
		$updateMailQuery = 'UPDATE verein_correspondence SET
					 subject_text = "'.escape_text($_POST['text_mail_subject']).'"
					,preview_text = "'.escape_text($_POST['text_mail_description']).'"
					,message_text = "'.escape_text($_POST['text_mail_message']).'"
					WHERE template_id = '.$tplid.' AND recipient_id = '.VORSTAND_USER;
	
		if ( $db->query($updateMailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)') )
		{
			http_response_code(200); // Set response code 200 (OK)
			echo $tplid;
		} else {
			http_response_code(500); // Set response code 500 (internal server error)
			die('Template could not be updated');
		}
	} catch(Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ERROR Updating E-Mail message entry', __FILE__, __LINE__));
		echo $e->getMessage();
	}

} elseif ( $_GET['action'] === 'save' && isset($_POST['text_mail_subject']) ) {
	/**
	 * Save new Template
	 *
	 * border '0' = No Border
	 * read_rights '2' = USER_MEMBER
	 */
	try {
		error_log('[INFO] Saving a new Mail Template');
		$insertTplQuery = 'INSERT INTO templates SET
							 tpl = "'.escape_text($compiledMailTpl).'"
							,title = "'.escape_text($_POST['text_mail_subject']).'"
							,page_title = "'.escape_text($_POST['text_mail_subject']).'"
							,border = "0"
							,owner = '.VORSTAND_USER.'
							,read_rights = 2
							,write_rights = 3
							,created = NOW()
							,last_update = NOW()
							,update_user = '.$user->id;
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Saving a new Mail Template: %s', __FILE__, __LINE__, $insertTplQuery));
		$tplid = $db->query($insertTplQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)');
	} catch(Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ERROR Saving a new Mail Template', __FILE__, __LINE__));
		echo $e->getMessage();
	}

	if (empty($tplid) || $tplid === 0)
	{
		http_response_code(500); // Set response code 500 (internal server error)
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ERROR Template could not be created', __FILE__, __LINE__));
		die('Template could not be created');
	} else {
		/**
		 * Create new E-Mail message entry
		 */
		try {
			error_log('[INFO] Creating a new E-Mail message entry for ' . $tplid);
			$insertMailQuery = 'INSERT INTO verein_correspondence SET
						 communication_type = "EMAIL"
						,subject_text = "'.escape_text($_POST['text_mail_subject']).'"
						,preview_text = "'.escape_text($_POST['text_mail_description']).'"
						,message_text = "'.escape_text($_POST['text_mail_message']).'"
						,template_id = '.$tplid.'
						,sender_id = '.$user->id.'
						,recipient_id = '.VORSTAND_USER;
			$messageId = $db->query($insertMailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailtemplate)');
		} catch(Exception $e) {
			http_response_code(500); // Set response code 500 (internal server error)
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ERROR Creating a new E-Mail message entry', __FILE__, __LINE__));
			echo $e->getMessage();
		}

		http_response_code(200); // Set response code 200 (OK)
		echo $tplid;
	}

} else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	if (DEVELOPMENT) error_log('Method not allowed');
	die('Method not allowed');
}
