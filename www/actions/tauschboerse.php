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

	$allowedFiletypes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/jpg', 'image/gif']; // Use MIME type notation

	switch($doAction)
	{
		/**
		 * Add new Tauschbörse Angebot
		 */
		case 'add':
			$angebot = filter_input(INPUT_POST, 'art', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['art']
			$angebotTyp = (in_array($angebot, ['angebot', 'nachfrage']) ? $angebot : 'angebot'); // Default: 'angebot'
			$bezeichnung = text_width(filter_input(INPUT_POST, 'bezeichnung', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 255) ?? null; // $_POST['bezeichnung']
			$wertvorstellung = text_width(filter_input(INPUT_GET, 'wertvorstellung', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 25) ?? '0'; // $_POST['wertvorstellung'] : Default: '0'
			$zustand = text_width(filter_input(INPUT_POST, 'zustand', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 100) ?? null; // $_POST['zustand']
			$lieferbedingungen = text_width(filter_input(INPUT_POST, 'lieferbedingung', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 150) ?? null; // $_POST['lieferbedingung']
			$kommentar = filter_input(INPUT_POST, 'kommentar', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['kommentar']

			$sql = 'INSERT INTO tauschboerse (art, user_id, datum, bezeichnung, wertvorstellung, zustand, lieferbedingung, kommentar)
					VALUES (? ,? ,? ,? ,? ,? ,? ,?)';
			$artikelId = $db->query($sql, __FILE__, __LINE__, 'Tauschbörse Add', [
																					$angebotTyp
																					,$user->id
																					,timestamp(true)
																					,$bezeichnung
																					,$wertvorstellung
																					,$zustand
																					,$lieferbedingungen
																					,$kommentar
																				]);

			/** Falls ein Bild gewählt wurde. */
			if ($_FILES['image']['name'] && $artikelId>0)
			{
				if(!empty($_FILES['image']['error']) && !$_FILES['image']['error']) {
				  echo "Das Bild konnte nicht &uuml;bertragen werden!<br />";
				  exit;
				}

				if (!in_array($_FILES['image']['type'], $allowedFiletypes)) {
				   echo "Dies ist kein unterstützes Bildformat (JPEG, PNG, oder GIF)!<br />";
				   exit;
				}

				$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$allowedPath = realpath(TAUSCHARTIKEL_IMGPATH);
				$tmpFilePath = TAUSCHARTIKEL_IMGPATH.'upload/'.$_FILES['image']['tmp_name'].'.'.$extension;
				$realPathOfFile = realpath($tmpFilePath);
				$moveToFilePath = TAUSCHARTIKEL_IMGPATH.$artikelId.'.'.$extension;
				$moveToFilePathThumb = TAUSCHARTIKEL_IMGPATH.$artikelId.'_tn.'.$extension;
				if ($realPathOfFile === false || strpos($realPathOfFile, $allowedPath) !== 0) {
					echo "Ungültiger Pfad um das Bild zu speichern.";
					exit;
				}

				if (file_exists($_FILES['image']['tmp_name']) && !move_uploaded_file($_FILES['image']['tmp_name'], $tmpFilePath)) {
					echo "Bild konnte nicht modifiziert werden.";
					exit;
				}

				$e = createPic($tmpFilePath, $moveToFilePathThumb, 100, 100, array(0,0,0));
				if ($e['error']) {
					echo $e['error'];
					exit;
				}
				$e = createPic($tmpFilePath, $moveToFilePath, 500, 500);
				if ($e['error']) {
					echo $e['error'];
					exit;
				}

				if (file_exists($tmpFilePath) && !@unlink($tmpFilePath)) {
					echo 'File konnte nicht gel&ouml;scht werden.';
					exit;
				}
			}
			header('Location: '.$redirectUrl);
			exit;
			break;

		/**
		 * Archive an old Tauschbörse Angebot
		 */
		case 'archive':
			$artikelId = filter_input(INPUT_GET, 'artikel_id', FILTER_VALIDATE_INT) ?? null; // $_GET['artikel_id']

			if ($artikelId > 0) {
				$result = $db->update('tauschboerse', ['id', $artikelId, 'user_id', $user->id], ['aktuell' => '0'],__FILE__,__LINE__,'Tauschbörse Archive');
			}
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
