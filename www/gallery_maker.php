<?php
/**
 * Gallery Maker and Pic Uploader
 *
 * @package zorg\Gallery\Gallery Maker
*/

/**
 * File includes
 * @include main.inc.php
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Gallery();
$model->showOverview($smarty);

/**
 * Gallery Maker nur für eingeloggte User anzeigen.
 */
if (!$user->is_loggedin())
{
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-not-logged-in', 'gallery', [ SITE_URL ]), 'dismissable' => 'false']);
	http_response_code(403); // Set response code 403 (forbidden).
	$smarty->display('file:layout/head.tpl');
	$smarty->display('file:layout/footer.tpl');
}

/**
 * Show Gallery Maker
 */
else {
	if ($user->typ >= USER_MEMBER || $user->vereinsmitglied !== '0')
	{
		/* Make a NONCE to protect later AJAX requests (Bin2Hex for unscrambled Chars) */
		$nonce = bin2hex(random_bytes(32));
		$_SESSION['nonce']['gallery_maker']['add'] = $nonce;
		$_SESSION['nonce']['activities']['post'] = $nonce;

		/* Page Layout */
		$smarty->assign('tplroot', array('page_title' => 'zorg Gallery Maker'));
		$smarty->assign('nonce', $nonce);
		$smarty->display('file:layout/pages/gallery_maker.tpl');
	}

	/**
	 * Redirect to zorg.ch
	 */
	else {
		header('Location: /gallery.php'); // Display Template "Verein"
		exit;
	}
}
