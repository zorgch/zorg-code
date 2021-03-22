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
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'gallery.inc.php';

define('TAUSCHARTIKEL_IMGPATH', SITE_ROOT.'/../data/tauschboerse/');

if($_POST['do'] == 'new')
{
	$sql ="INSERT INTO
			tauschboerse
				(art, user_id, datum, bezeichnung, wertvorstellung, zustand, lieferbedingung, kommentar)
			VALUES
				(
		  		'".$_POST['art']."'
					, ".$user->id."
		  		, now()
		  		, '".$_POST['bezeichnung']."'
		  		, '".$_POST['wertvorstellung']."'
					, '".$_POST['zustand']."'
					, '".$_POST['lieferbedingung']."'
					, '".$_POST['kommentar']."'
				)
			";
	$artikelId = $db->query($sql, __FILE__, __LINE__);

	if ($_FILES['image']['name']) { // Falls ein Bild gewählt wurde.

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
		@unlink($tmpfile);

	}


  header('Location: '.base64_decode($_POST['url']));
  exit;
}

if($_GET['do'] == 'old') {
  $sql =
  	"
  	UPDATE
  	tauschboerse
  	SET aktuell = '0'
  	WHERE
  		id = '".$_GET['artikel_id']."'
  		AND
  		user_id = ".$user->id."
  	"
  ;
  $db->query($sql, __FILE__, __LINE__);
  header('Location: /?tpl=190');
  exit;
}
