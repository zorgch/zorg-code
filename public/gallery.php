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
$getAlbId = filter_input(INPUT_GET, 'albID', FILTER_VALIDATE_INT) ?? null;
$getPicId = filter_input(INPUT_GET, 'picID', FILTER_VALIDATE_INT) ?? null;
$album_id = $model->setAlbumId($getAlbId, $getPicId);

/**
 * [Bug #708] Gallery nur für eingeloggte User anzeigen. Ausnahme: APOD Gallery
 * @link https://zorg.ch/bugtracker.php?bug_id=708
 */
if ($album_id !== APOD_GALLERY_ID && !$user->is_loggedin())
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
elseif ($album_id !== APOD_GALLERY_ID && empty($user->vereinsmitglied))
{
	$model->showOverview($smarty);
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-no-member', 'gallery'), 'dismissable' => 'false']);
	$smarty->display('file:layout/head.tpl');
}

/** Gallery / Pics anzeigen */
else {

	/** Sanitize User Inputs */
	$doAction = filter_input(INPUT_GET, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['do']
	$show = filter_input(INPUT_GET, 'show', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['show']
	$showPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 0; // $_GET['page'] Default page: 0

	/** User muss mindestens eingeloggt sein, um DO Actions zu machen */
	if (!empty($doAction) && $user->is_loggedin())
	{
		switch ($doAction)
		{
			case 'benoten':
				$picIDfromPOST = filter_input(INPUT_POST, 'picID', FILTER_VALIDATE_INT) ?? null; // $_POST['picID']
				$benotenScore = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT) ?? null; // $_POST['score']
				if ($user->typ >= USER_USER && $benotenScore > 0) { // Dürfen alle eingeloggten
					doBenoten($picIDfromPOST, $benotenScore);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'mypic':
				if ($user->typ >= USER_USER) { // Dürfen alle eingeloggten
					$picIDfromPOST = filter_input(INPUT_POST, 'picID', FILTER_VALIDATE_INT) ?? null; // $_POST['picID']

					/** Ein <input type="image" ...> übergibt die X & Y Positionen via "inputName_x" & "inputName_y" */
					$mypic_xcoord = filter_input(INPUT_POST, 'mypic_x', FILTER_VALIDATE_INT) ?? null; // $_POST['mypic_x']
					$mypic_ycoord = filter_input(INPUT_POST, 'mypic_y', FILTER_VALIDATE_INT) ?? null; // $_POST['mypic_y']
					if ($mypic_xcoord > 0 && $mypic_ycoord > 0) {
						doMyPic($picIDfromPOST, $mypic_xcoord, $mypic_ycoord);
					}
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'editAlbum':
				if ($user->typ >= USER_MEMBER) { // Dürfen nur Member+Schöne
					$frm = filter_input(INPUT_POST, 'frm', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
					$res = doEditAlbum($album_id, $frm);
					if (!$album_id) $album_id = $res['id'];
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'zensur':
				if ($user->typ >= USER_MEMBER && $getPicId > 0) { // Dürfen nur Member+Schöne
					doZensur($getPicId);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;


			case 'upload':
				$frm = filter_input(INPUT_POST, 'frm', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				if ($user->typ >= USER_MEMBER & !empty($frm)) { // Dürfen nur Member+Schöne
					$res = doUpload($album_id, $frm);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;


			case 'mkUploadDir':
				$frm = filter_input(INPUT_POST, 'frm', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				if ($user->typ >= USER_MEMBER && !empty($frm)) { // Dürfen nur Member+Schöne
					$res = doMkUploadDir($frm);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'editFotoTitle':
				$frm = filter_input(INPUT_POST, 'frm', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				if ($user->typ >= USER_MEMBER && !empty($frm)) { // Dürfen nur Member+Schöne
					$res = doEditFotoTitle($getPicId, $frm);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'doRotatePic':
				$rotateLeftOrRight = filter_input(INPUT_POST, 'rotatedir', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['rotatedir']
				if ($user->typ >= USER_MEMBER && !empty($rotateLeftOrRight)) { // Dürfen nur Member+Schöne
					$res = doRotatePic($getPicId, $rotateLeftOrRight);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'delAlbum':
				if ($user->typ >= USER_SPECIAL) { // Dürfen nur Schöne+Admins
					$res = doDelAlbum($album_id, $_POST['del']);
					$_GET['show'] = $res['show'];
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'delPic':
				$deleteThisPicID = filter_input(INPUT_POST, 'del', FILTER_VALIDATE_INT) ?? null; // $_POST['del']
				if ($user->typ >= USER_SPECIAL && $deleteThisPicID > 0) { // Dürfen nur Schöne+Admins
					$res = doDelPic($deleteThisPicID);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			case 'delUploadDir':
				$frm = filter_input(INPUT_POST, 'frm', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
				if ($user->typ >= USER_SPECIAL && !empty($frm['folder'])) { // Dürfen nur Schöne+Admins
					$res = doDelUploadDir($frm['folder']);
				} else {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$doAction])]);
				}
				break;

			// case 'markieren': DISABLED
			// 		doMark($getPicId);
			// 	break;

			// case 'editAlbumFromEvent': NOT IMPLEMENTED
			// 		$res = doEditAlbumFromEvent($album_id, $_POST['event']);
			// 		if (!$album_id) $album_id = $res['id'];
			// 	break;
		}
	} else {
		$res = array( 'state' => '', 'error' => '' );
	}
	unset($_GET['do']);
	$doAction = null;

	switch ($show)
	{
		case 'editAlbum':
			$model->showAlbumedit($smarty, $album_id);
			if ($user->typ >= USER_MEMBER && !empty($rotateLeftOrRight)) { // Dürfen nur Member+Schöne
				editAlbum($album_id, $doAction, (isset($res['state']) ? $res['state'] : ''), (isset($res['error']) ? $res['error'] : ''), (isset($res['frm']) ? $res['frm'] : ''));
			} else {
				$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$show])]);
			}
			$smarty->display('file:layout/head.tpl');
			break;

		case 'editAlbumV2':
			if ($user->typ >= USER_USER && !empty($user->vereinsmitglied) || $user->typ >= USER_SPECIAL) { // Dürfen nur Vereinsmitglieder, Schöne+Admins
				header('Location: /gallery_maker.php'.($getAlbId > 0 ? '?album_id='.$getAlbId : ''));
			} else {
				$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$show])]);
			}
			exit;

		case 'albumThumbs':
			if ($album_id === APOD_GALLERY_ID ||
				($user->typ >= USER_USER && !empty($user->vereinsmitglied))) { // Dürfen nur eingeloggte + Vereinsmitglieder
				$model->showAlbum($smarty, $album_id);
				albumThumbs($album_id, $showPage);
			} else {
				$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$show])]);
			}
			break;

		case 'pic':
			if ($album_id === APOD_GALLERY_ID ||
				($user->typ >= USER_USER && !empty($user->vereinsmitglied))) { // Dürfen nur eingeloggte + Vereinsmitglieder
				$model->showPic($smarty, $user, $getPicId, $album_id);
				$smarty->display('file:layout/head.tpl');
				pic($getPicId);
			} else {
				$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', [$show])]);
			}
			break;

		default:
			$model->showOverview($smarty);
			galleryOverview($res['state'], $res['error']);
			$smarty->display('file:layout/head.tpl');
			$smarty->display('file:layout/partials/gallery/overview.tpl');
	}

}

$smarty->display('file:layout/footer.tpl');
