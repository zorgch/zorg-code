<?php
/**
 * Picture Gallery
 *
 * Die Bilder der Gallery liegen in ../data/gallery/ und in der Datenbank
 * Folgende Tables gehören zur Gallery:
 * gallery_albums, gallery_pics, gallery_pics_user, gallery_pics_votes
 *
 * @author [z]biko
 * @author IneX
 * @package zorg\Gallery
 * @date 01.01.2002
 * @version 2.0
 * @since 1.0 `01.01.2002` file added
 * @since 1.5 `04.11.2013` Gallery nur noch für eingeloggte User anzeigen
 * @since 1.6 `11.09.2018` `IneX` APOD Gallery & Pics auch für nicht-eingeloggte User anzeigen
 * @since 2.0 `14.11.2019` `IneX` GV Beschluss 2018: added check if User is logged-in & Vereinsmitglied
 */

/**
 * File includes
 * @include main.inc.php
 * @include core.model.php
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Gallery();

/** Pic-ID zu Album-ID auflösen */
$getAlbId = isset( $_GET['albID'] ) ? (int) $_GET['albID'] : null;
$getPicId = isset( $_GET['picID'] ) ? (int) $_GET['picID'] : null;
$album_id = $model->setAlbumId($getAlbId, $getPicId);

/**
 * [Bug #708] Gallery nur für eingeloggte User anzeigen. Ausnahme: APOD Gallery
 * @link https://zorg.ch/bugtracker.php?bug_id=708
 */
if (!$user->is_loggedin() && (int)$album_id !== APOD_GALLERY_ID)
{
	$model->showOverview($smarty);
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-not-logged-in', 'gallery', [ SITE_URL ]), 'dismissable' => 'false']);
	http_response_code(403); // Set response code 403 (forbidden).
	$smarty->display('file:layout/head.tpl');
}

/**
 * User & Vereinsmitglieder-Check: nur Vereinsmitglieder dürfen Pics sehen (Ausnahme: APOD Gallery & Pics)
 * @link https://github.com/zorgch/zorg-verein-docs/blob/master/GV/GV%202018/2018-12-23%20zorg%20GV%202018%20Protokoll.md
 */
elseif ((int)$album_id !== APOD_GALLERY_ID && (empty($user->vereinsmitglied) || $user->vereinsmitglied === '0'))
{
	$model->showOverview($smarty);
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-no-member', 'gallery'), 'dismissable' => 'false']);
	$smarty->display('file:layout/head.tpl');
}

/** Gallery / Pics anzeigen */
else {

	if (!empty($_GET['do']))
	{
		$doAction = (string)$_GET['do'];
		/** Das Benoten (und mypic markieren) können nebst Schönen auch die registrierten User,
			deshalb müssen wirs vorziehen... */
		if ($user->is_loggedin() && isset($_POST['picID']) && !empty($_POST['picID']) && $_POST['picID'] > 0)
		{
			switch ($doAction)
			{
				case 'benoten':
					if (isset($_POST['score']) && !empty($_POST['score']) && $_POST['score'] > 0) {
						doBenoten($_POST['picID'], $_POST['score']);
					}
					break;

				case 'mypic':
					// Ein <input type="image" ...> übergibt die X & Y Positionen via "inputName_x" & "inputName_y"
					if (isset($_POST['mypic_x']) && isset($_POST['mypic_y']) && $_POST['mypic_x'] > 0 && $_POST['mypic_y'] > 0) {
						doMyPic($_POST['picID'], $_POST['mypic_x'], $_POST['mypic_y']);
					}
					break;
			}
		} else {
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
		}

		/** Ab hier kommt nur noch Zeugs dass Member & Schöne machen dürfen */
		if ($user->typ >= USER_MEMBER)
		{
			switch ($doAction)
			{
				case 'editAlbum':
					$res = doEditAlbum($album_id, $_POST['frm']);
					if (!$album_id) $album_id = $res['id'];
					break;
				case 'editAlbumFromEvent':
					$res = doEditAlbumFromEvent($album_id, $_POST['event']);
					if (!$album_id) $album_id = $res['id'];
					break;
				case 'delAlbum':
					$res = doDelAlbum($album_id, $_POST['del']);
					$_GET['show'] = $res['show'];
					break;
				case 'zensur':
					doZensur($getPicId);
					break;
				case 'delPic':
					$res = doDelPic($_POST['picID']);
					break;
				case 'upload':
					$res = doUpload($album_id, $_POST['frm']);
					break;
				case 'delUploadDir':
					$res = doDelUploadDir($_POST['frm']['folder']);
					break;
				case 'mkUploadDir':
					$res = doMkUploadDir($_POST['frm']);
					break;
				case 'editFotoTitle':
					$res = doEditFotoTitle($getPicId, $_POST['frm']);
					break;
				case 'doRotatePic':
					$res = doRotatePic($getPicId, $_POST['rotatedir']);
					break;
				/*case 'markieren':
					doMark($getPicId);
					break;*/

			}
		} else {
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
		}

		unset($_GET['do']);
		$doAction = null;
	} else {
		$res = array( 'state' => '', 'error' => '' );
	}
	$show = (isset($_GET['show']) ? $_GET['show'] : null);
	$showPage = (isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 0); // Default page: 0
	switch ($show)
	{
		case 'editAlbum':
			$model->showAlbumedit($smarty, $album_id);
			$smarty->display('file:layout/head.tpl');
			editAlbum($album_id, $doAction, (isset($res['state']) ? $res['state'] : ''), (isset($res['error']) ? $res['error'] : ''), (isset($res['frm']) ? $res['frm'] : ''));
			break;
		case 'editAlbumV2':
			header('Location: /gallery_maker.php'.($getAlbId > 0 ? '?album_id='.$getAlbId : ''));
			exit;
		case 'albumThumbs':
			$model->showAlbum($smarty, $album_id);
			albumThumbs($album_id, $showPage);
			break;
		case 'pic':
			$model->showPic($smarty, $user, $getPicId, $album_id);
			$smarty->display('file:layout/head.tpl');
			pic($getPicId);
			break;
		default:
			$model->showOverview($smarty);
			galleryOverview($res['state'], $res['error']);
			$smarty->display('file:layout/head.tpl');
			$smarty->display('file:layout/partials/gallery/overview.tpl');
	}

}

$smarty->display('file:layout/footer.tpl');
