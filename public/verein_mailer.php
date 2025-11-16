<?php
/**
 * zorg Verein Mailing Manager
 *
 * @package zorg\Verein\Mailer
 */

/**
 * File includes
 * @include main.inc.php
 */
require_once __DIR__.'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/** Validate parameters */
$user = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT) ?? null; // $_GET['user'] interger
$mail = filter_input(INPUT_GET, 'mail', FILTER_VALIDATE_INT) ?? null; // $_GET['mail'] integer
$hash = filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['hash'] string
$path = filter_input(INPUT_GET, 'path', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_GET['path'] string
$is_admin = filter_input(INPUT_GET, 'admin', FILTER_VALIDATE_BOOL) ?? false; // $_GET['admin'] bool

/**
 * Initialise MVC Model
 */
$model = new MVC\VereinMailer();

/**
 * Show a mail message's webview
 */
if((!empty($mail) && $mail>0) && (!empty($user) && $user>0) && !empty($hash))
{
	/**
	 * Match Hash from URL against Database
	 */
	$matchingAgainst = md5($mail . $user);
	/** ORDER BY id DESC = ensures, if same message was sent multiple times, that the NEWEST is displayed (and not the oldest) */
	$checkHashQuery = 'SELECT id, template_id, recipient_id, message_text, recipient_confirmation, MD5(CONCAT(template_id, recipient_id)) as hash FROM verein_correspondence WHERE MD5(CONCAT(template_id, recipient_id))=? ORDER BY id DESC';
	try {
		$matchedResult = $db->fetch($db->query($checkHashQuery, __FILE__, __LINE__, 'verein_mailer.php', [$hash]));
	} catch (Exception $e) {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Error', 'message' => $e->getMessage()]);
	}

	/** if Hash returned a result... */
	if ($matchedResult && $hash === $matchedResult['hash'])
	{
		if (intval($matchedResult['template_id']) === $mail && intval($matchedResult['recipient_id']) === $user)
		{
			/**
			 * Update EMAIL read status
			 */
			if (!$matchedResult['recipient_confirmation'])
			{
				zorgDebugger::log()->info('Updating Read State for template %d and user %d', [$matchedResult['template_id'], $matchedResult['recipient_id']]);
				$updateReadStateQuery = 'UPDATE verein_correspondence SET recipient_confirmation=?, recipient_confirmationdate=? WHERE id=?';
				$updateReadState = $db->query($updateReadStateQuery, __FILE__, __LINE__, 'UPDATE verein_correspondence', ['TRUE', timestamp(true), $matchedResult['id']]);
			}

			/**
			 * Only a resource was requested - so show it
			 */
			$allowedPaths = ['/images'];
			$pathDir = pathinfo($path, PATHINFO_DIRNAME);
			if (!empty($path) && in_array($pathDir, $allowedPaths))
			{
				$resource = SITE_URL . $path;
				if (@file_get_contents( $resource ))
			    {
					$fileInfo = getimagesize($resource);
					$fileMime = $fileInfo['mime'];
					http_response_code(200); // Set response code 200 (OK)
					if (!empty($fileMime)) header("Content-type: {$fileMime}");
					readfile($resource);
			    } else {
					http_response_code(404); // Set response code 404 (not found)
					user_error('Resource not found ' . $path, E_USER_NOTICE);
			    }

			/**
			 * Show web-view of the E-Mail Message
			 */
			} else {
				http_response_code(200); // Set response code 200 (OK)
				echo $matchedResult['message_text'];
			}

		/** Template and/or User IDs do NOT match... */
		} else {
			http_response_code(403); // Set response code 403 (forbidden)
			zorgDebugger::log()->warn('Mismatching Template %d and/or User ID %d', [intval($matchedResult['template_id']), intval($matchedResult['recipient_id'])]);
			$smarty->assign('tplroot', array('page_title' => 'zorg Verein Mailer'));
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Nice try - aber Du dörfsch die Message nöd aluege. Yarak!']);
			$model->showOverview($smarty);
			$smarty->display('file:layout/head.tpl');
			$smarty->display('file:layout/footer.tpl');
		}

	/** Hash did NOT match... */
	} else {
		http_response_code(403); // Set response code 403 (forbidden)
		zorgDebugger::log()->warn('Mismatching Hash %s', [$hash]);
		$smarty->assign('tplroot', array('page_title' => 'zorg Verein Mailer'));
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Nope - do stimmt was nöd. Tschau.']);
		$model->showOverview($smarty);
		$smarty->display('file:layout/head.tpl');
		$smarty->display('file:layout/footer.tpl');
	}

	/** mail, user & hash are are NOT set...
	} else {
		http_response_code(403); // Set response code 403 (forbidden)
		$smarty->display('file:layout/head.tpl');
		user_error('Irgendwas isch schief glaufe - oder fehlt.', E_USER_NOTICE);
		$smarty->display('file:layout/footer.tpl');
	}*/

/**
 * Show Verein Mailer application
 */
} elseif (isset($_GET['admin'])) {

	$smarty->assign('tplroot', array('page_title' => 'zorg Verein Mailer'));
	$smarty->display('file:layout/partials/verein_mailer.tpl');

/**
 * Redirect to zorg.ch
 */
} else {
	header('Location: /page/verein'); // Display Template "Verein"
	exit;
}
