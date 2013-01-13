<?php
/**
 * Picture Gallery
 * 
 * Die Bilder der Gallery liegen in ../data/gallery/ und in der Datenbank
 * Folgende Tables gehören zur Gallery:
 * gallery_albums, gallery_pics, gallery_pics_user, gallery_pics_votes
 *
 * @author [z]biko
 * @date 01.01.2002
 * @version 1.5
 * @package Zorg
 * @subpackage Gallery
 */

//=============================================================================
// Includes
//=============================================================================
/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT']."/includes/layout.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/gallery.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");



// fuer mod_rewrite solltes
//header("Cache-Control: no-store, no-cache, must-revalidate");
echo head(29, 'gallery');

echo menu("zorg");
echo menu("gallery");

// Das Benoten (und mypic markieren) können nebst Schönen auch die registrierten User, deshalb müssen wirs vorziehen...
if ($_GET['do'] && $user->typ == USER_NICHTEINGELOGGT) user_error("Permission denied for <i>".$_GET['do']."</i>", E_USER_ERROR);
switch ($_GET['do']) {
	case "benoten":
		//if ($user->typ >= USER_USER) {
  	 	doBenoten($_POST[picID], $_POST[score]);
  	 	//}
  	 	break;
  	 case "mypic":
  	 	if ($_POST[picID] > 0 && $_POST[mypic_x] <> "" && $_POST[mypic_y] <> "") {
  	 		doMyPic($_POST[picID], $_POST[mypic_x], $_POST[mypic_y]);
  	 	}
  	 	break;
}


// Ab hier kommt nur noch Zeugs dass Member & Schöne machen dürfen
if ($_GET['do'] && $user->typ != USER_MEMBER) user_error("Permission denied for <i>".$_GET['do']."</i>", E_USER_ERROR);

switch ($_GET['do']) {
  case "editAlbum":
     $res = doEditAlbum($_GET[albID], $_POST[frm]);
     if (!$_GET[albID]) $_GET[albID] = $res[id];
     break;
  case "editAlbumFromEvent":
     $res = doEditAlbumFromEvent($_GET[albID], $_POST[event]);
     if (!$_GET[albID]) $_GET[albID] = $res[id];
     break;
  case "delAlbum":
     $res = doDelAlbum($_GET[albID], $_POST[del]);
     $_GET[show] = $res[show];
     break;
  case "zensur":
     doZensur($_GET[picID]);
     break;
  case "delPic":
     $res = doDelPic($_POST[picID]);
     break;
  case "upload":
     $res = doUpload($_GET[albID], $_POST[frm]);
     break;
  case "delUploadDir":
     $res = doDelUploadDir($_POST[frm][folder]);
     break;
  case "mkUploadDir":
     $res = doMkUploadDir($_POST[frm]);
     break;
  case "editFotoTitle":
  	$res = doEditFotoTitle($_GET['picID'], $_POST['frm']);
  	break;
  case "doRotatePic":
  	$res = doRotatePic($_GET['picID'], $_POST['rotatedir']);
  	break;
  /*case "markieren":
     doMark($_GET[picID]);
     break;*/
}

unset($_GET['do']);

switch ($_GET[show]) {
  case "editAlbum": editAlbum($_GET[albID], $_GET['do'], $res[state], $res[error], $res[frm]); break;
  case "albumThumbs": albumThumbs($_GET[albID], $_GET[page]); break;
  case "pic": pic($_GET[picID]); break;
  default: echo '<br />'.galleryOverview($res[state], $res[error]);
}

echo foot(7);
?>