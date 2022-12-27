<?php
/**
 * Tauschbörse Actions
 *
 * @package zorg\Tauschbörse
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';

/** This is all just for logged-in Users */
if ($user->is_loggedin())
{
	/** Validate passed parameters */
	if (isset($_POST['do']) && $_POST['do'] === 'new') $doAction = 'add';
	elseif (isset($_GET['do']) && $_GET['do'] === 'old' && isset($_GET['artikel_id']) && is_numeric($_GET['artikel_id'])) $doAction = 'archive';

	if (isset($_POST['url']) && is_string($_POST['url'])) $redirectUrl = base64url_decode($_POST['url']);
	else $redirectUrl = '/tpl/190'; // Tauschbörse TPL-ID

	switch($doAction)
	{
		/**
		 * Add new Tauschbörse Angebot
		 */
		case 'add':
			$sql = 'INSERT INTO tauschboerse
					 	(art, user_id, datum, bezeichnung, wertvorstellung, zustand, lieferbedingung, kommentar)
					VALUES (
						"'.$_POST['art'].'"
						,'.$user->id.'
						,NOW()
						,"'.$_POST['bezeichnung'].'"
						,"'.$_POST['wertvorstellung'].'"
						,"'.$_POST['zustand'].'"
						,"'.$_POST['lieferbedingung'].'"
						,"'.$_POST['kommentar'].'"
					)';
			$artikelId = $db->query($sql, __FILE__, __LINE__);

			/** Falls ein Bild gewählt wurde. */
			if ($_FILES['image']['name'])
			{
				if($_FILES['image']['error'] != 0) {
				  echo "Das Bild konnte nicht &uuml;bertragen werden!<br />".__FILE__.__LINE__;
				  exit;
				}

				if ($_FILES['image']['type'] != "image/jpeg" && $_FILES['image']['type'] != "image/pjpeg") {
				   echo "Dies ist kein JPEG Bild!<br />".__FILE__.__LINE__;
				   exit;
				}

				$tmpfile = TAUSCHARTIKEL_IMGPATH.$artikelId.".jpg";
				if (!move_uploaded_file($_FILES['image']['tmp_name'], $tmpfile)) {
				  echo "Bild konnte nicht bearbeitet werden.".__FILE__.__LINE__;
				  exit;
				}

				$e = createPic($tmpfile, TAUSCHARTIKEL_IMGPATH.$artikelId."_tn.jpg", 100, 100, array(0,0,0));
				if ($e['error']) {
				  echo $e['error'].__FILE__.__LINE__;
				  exit;
				}

				$e = createPic($tmpfile, TAUSCHARTIKEL_IMGPATH.$artikelId.".jpg", 500, 500);
				if ($e['error']) {
				  echo $e['error'].__FILE__.__LINE__;
				  exit;
				}
				unlink($tmpfile);
			}
			header('Location: '.$redirectUrl);
			exit;
			break;

		/**
		 * Archive an old Tauschbörse Angebot
		 */
		case 'archive':
			$artikelId = (int)$_GET['artikel_id'];
			$result = $db->update('tauschboerse', ['id', $artikelId, 'user_id', $user->id], ['aktuell' => '0'],__FILE__,__LINE__,__METHOD__);
			header('Location: '.$redirectUrl);
			exit;
			break;

		/**
		 * Fallback on empty Params/Action
		 */
		default:
			header('Location: '.$redirectUrl);
			exit;
	}
}

/** Permission error */
else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error('Du bist nicht eingeloggt.', E_USER_WARNING);
	exit;
}
