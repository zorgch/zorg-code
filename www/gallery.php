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
 * @version 1.6
 * @since 1.0 01.01.2002 file added
 * @since 1.5 04.11.2013 Gallery nur noch für eingeloggte User anzeigen
 * @since 1.6 11.09.2018 APOD Gallery & Pics auch für nicht-eingeloggte User anzeigen
 */

/**
 * File includes
 * @include main.inc.php
 * @include core.model.php
 */
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Gallery();

// fuer mod_rewrite solltes
//header("Cache-Control: no-store, no-cache, must-revalidate");
//echo head(29, 'gallery');
//$smarty->assign('tplroot', array('page_title' => 'gallery'));
//echo menu("zorg");
//echo menu("gallery");

/** Pic-ID zu Album-ID auflösen */
$getAlbId = (int)$_GET['albID'];
$getPicId = (int)$_GET['picID'];
$album_id = $model->setAlbumId($getAlbId, $getPicId);

/**
 * [Bug #708] Gallery nur für eingeloggte User anzeigen
 * Ausnahme: APOD Gallery
 * @link https://zorg.ch/bugtracker.php?bug_id=708
 */
if (!$user->is_loggedin() && $album_id != APOD_GALLERY_ID)
{
	$model->showOverview($smarty);
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-not-logged-in', 'gallery', SITE_URL), 'dismissable' => 'false']);
	$smarty->display('file:layout/head.tpl');

/** Gallery / Pics anzeigen */
} else {

	if (!empty($_GET['do']))
	{
		$doAction = (string)$_GET['do'];
		// Das Benoten (und mypic markieren) können nebst Schönen auch die registrierten User, deshalb müssen wirs vorziehen...
		if ($user->is_loggedin())
		{
			switch ($doAction)
			{
				case 'benoten':
					 	doBenoten($_POST['picID'], $_POST['score']);
					 	break;
	
					 case 'mypic':
					 	// Ein <input type="image" ...> übergibt die X & Y Positionen via "inputName_x" & "inputName_y"
					 	if ($_POST['picID'] > 0 && $_POST['mypic_x'] <> "" && $_POST['mypic_y'] <> "") {
					 		doMyPic($_POST['picID'], $_POST['mypic_x'], $_POST['mypic_y']);
					 	}
					 	break;
			}
		} else {
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', $doAction)]);
		}
	
		// Ab hier kommt nur noch Zeugs dass Member & Schöne machen dürfen
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
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('permissions-insufficient', 'gallery', $doAction)]);
		}
	
		unset($_GET['do']);
		$doAction = null;
	}

	switch ($_GET['show'])
	{
		case 'editAlbum':
			$model->showAlbumedit($smarty, $album_id);
			$smarty->display('file:layout/head.tpl');
			editAlbum($album_id, $doAction, $res['state'], $res['error'], $res['frm']);
			break;
		case 'albumThumbs':
			$model->showAlbum($smarty, $album_id);
			albumThumbs($album_id, (int)$_GET['page']);
			break;
		case 'pic':
			$model->showPic($smarty, $user, $getPicId, $album_id);
			$smarty->display('file:layout/head.tpl');
			pic($getPicId);
			break;
		default:
			$model->showOverview($smarty);
			$smarty->display('file:layout/head.tpl');
			echo galleryOverview($res['state'], $res['error']);
	}

}

//echo foot(7);
$smarty->display('file:layout/footer.tpl');
