<?php
/**
 * Picture Gallery
 * 
 * Die Bilder der Gallery liegen in ../data/gallery/ und in der Datenbank
 * Folgende Tables gehören zur Gallery:
 * gallery_albums, gallery_pics, gallery_pics_user, gallery_pics_votes
 *
 * @author [z]biko, IneX
 * @package zorg
 * @subpackage Gallery
 * @date 01.01.2002
 * @version 1.6
 * @since 1.0 01.01.2002 file added
 * @since 1.5 04.11.2013 Gallery nur noch für eingeloggte User anzeigen
 * @since 1.6 11.09.2018 APOD Gallery & Pics auch für nicht-eingeloggte User anzeigen
 */
/**
 * File includes
 * @include main.inc.php
 */
require_once( __DIR__ .'/includes/main.inc.php');

// fuer mod_rewrite solltes
//header("Cache-Control: no-store, no-cache, must-revalidate");
//echo head(29, 'gallery');
$smarty->assign('tplroot', array('page_title' => 'gallery'));
$smarty->display('file:layout/head.tpl');

echo menu("zorg");
echo menu("gallery");

/** Pic-ID zu Album-ID auflösen */
if(!empty($_GET['albID'])) $albumId = $_GET['albID'];
if((!empty($_GET['picID']) && $_GET['picID'] > 0) && empty($albumId)) $albumId = pic2album($_GET['picID']);

/**
 * [Bug #708] Gallery nur für eingeloggte User anzeigen
 * Ausnahme: APOD Gallery
 * @link https://zorg.ch/bugtracker.php?bug_id=708
 */
if ($user->typ == USER_NICHTEINGELOGGT && $albumId != APOD_GALLERY_ID)
{
	$smarty->assign('error', ['type' => 'warn', 'title' => t('error-not-logged-in', 'gallery', SITE_URL), 'dismissable' => false]);
	$smarty->display('file:layout/elements/block_error.tpl');

/** Gallery / Pics anzeigen */
} else {

	// Das Benoten (und mypic markieren) können nebst Schönen auch die registrierten User, deshalb müssen wirs vorziehen...
	if ($_GET['do'] && $user->typ == USER_NICHTEINGELOGGT) user_error( t('permissions-insufficient', 'gallery', $_GET['do']), E_USER_ERROR);
	switch ($_GET['do']) {
		case "benoten":
	  	 	doBenoten($_POST['picID'], $_POST['score']);
	  	 	break;
	  	 	
	  	 case "mypic":
	  	 	// Ein <input type="image" ...> übergibt die X & Y Positionen via "inputName_x" & "inputName_y"
	  	 	if ($_POST['picID'] > 0 && $_POST['mypic_x'] <> "" && $_POST['mypic_y'] <> "") {
	  	 		//DEBUGGING: print_r($_POST);
	  	 		doMyPic($_POST['picID'], $_POST['mypic_x'], $_POST['mypic_y']);
	  	 	}
	  	 	break;
	}
	
	
	// Ab hier kommt nur noch Zeugs dass Member & Schöne machen dürfen
	if ($_GET['do'] && $user->typ != USER_MEMBER) user_error( t('permissions-insufficient', 'gallery', $_GET['do']), E_USER_ERROR);
	
	switch ($_GET['do']) {
	  case "editAlbum":
	     $res = doEditAlbum($_GET['albID'], $_POST['frm']);
	     if (!$_GET['albID']) $_GET['albID'] = $res['id'];
	     break;
	  case "editAlbumFromEvent":
	     $res = doEditAlbumFromEvent($_GET['albID'], $_POST['event']);
	     if (!$_GET['albID']) $_GET['albID'] = $res['id'];
	     break;
	  case "delAlbum":
	     $res = doDelAlbum($_GET['albID'], $_POST['del']);
	     $_GET['show'] = $res['show'];
	     break;
	  case "zensur":
	     doZensur($_GET['picID']);
	     break;
	  case "delPic":
	     $res = doDelPic($_POST['picID']);
	     break;
	  case "upload":
	     $res = doUpload($_GET['albID'], $_POST['frm']);
	     break;
	  case "delUploadDir":
	     $res = doDelUploadDir($_POST['frm']['folder']);
	     break;
	  case "mkUploadDir":
	     $res = doMkUploadDir($_POST['frm']);
	     break;
	  case "editFotoTitle":
	  	$res = doEditFotoTitle($_GET['picID'], $_POST['frm']);
	  	break;
	  case "doRotatePic":
	  	$res = doRotatePic($_GET['picID'], $_POST['rotatedir']);
	  	break;
	  /*case "markieren":
	     doMark($_GET['picID']);
	     break;*/
	}
	
	unset($_GET['do']);
	
	switch ($_GET['show']) {
	  case "editAlbum": editAlbum($_GET['albID'], $_GET['do'], $res['state'], $res['error'], $res['frm']); break;
	  case "albumThumbs": albumThumbs($_GET['albID'], $_GET['page']); break;
	  case "pic": pic($_GET['picID']); break;
	  default: echo '<br />'.galleryOverview($res['state'], $res['error']);
	}

}

//echo foot(7);
$smarty->display('file:layout/footer.tpl');
