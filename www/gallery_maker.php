<?php function shutdown(){
  var_dump(error_get_last());
} register_shutdown_function('shutdown');
// ^- Put this at the very beginning of the php file

/**
 * Picture Gallery Maker
 *
 * Tool um eine neue Gallery für zorg zu erstellen.
 * Nutzt DropzoneJS für Image file uploads.
 *
 * @author IneX
 * @package zorg\Gallery
 * @version 1.0
 * @since 1.0 <inex> 27.12.2019 File added
 */

/**
 * File includes
 * @include core.controller.php Required
 */
require_once( __DIR__ .'/controller/core.controller.php');

/**
 * Initialise MVC Controller
 */
$gallerymaker = new MVC\Controller\GalleryMaker();

/** Check permissions */
if (!$user->is_loggedin())
{
	$gallerymaker->model->showOverview($smarty);
	http_response_code(403); // Set response code 403 (access denied) and exit.
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-not-logged-in', 'gallery', SITE_URL), 'dismissable' => 'false']);
	$smarty->display('file:layout/head.tpl');
	exit;
}

/** User is logged in */
else {
	$smarty->display('file:layout/head.tpl');
	echo 'hi';
}

$smarty->display('file:layout/footer.tpl');
